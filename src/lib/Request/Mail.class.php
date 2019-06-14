<?php
/**
 * (c) 2019 cepharum GmbH, Berlin, http://cepharum.de
 *
 * The MIT License (MIT)
 *
 * Copyright (c) 2019 cepharum GmbH
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to
 * deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE
 * SOFTWARE.
 *
 * @author: cepharum
 */

namespace Request;

use InvalidArgumentException;
use PDO;
use PDOException;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use RuntimeException;
use Throwable;


/**
 * Implements wrapper for conveniently generating and delivering mails in
 * compliance with current setup.
 *
 * @package Request
 */
class Mail {
	/**
	 * Creates instance of mail manager for describing outgoing mail and for
	 * delivering it via SMTP.
	 *
	 * @param int $smtpDebuggingLevel 0 = no debugging, 1 = debug client
	 *     messages, 2 debug client and server messages
	 * @return PHPMailer prepared mail manager object
	 * @throws HttpException
	 */
	public static function create( $smtpDebuggingLevel = 0 ) {
		date_default_timezone_set( 'Etc/UTC' );

		$mail = new PHPMailer( true );

		$mail->SMTPDebug = ( $smtpDebuggingLevel === true ) ? 2 : $smtpDebuggingLevel;
		$mail->CharSet   = PHPMailer::CHARSET_UTF8;

		$smtpHostname = Setup::get( 'smtp.hostname' );
		if ( $smtpHostname ) {
			$mail->isSMTP();
			$mail->Host = $smtpHostname;

			$setup = Setup::get( 'smtp' );

			if ( array_key_exists( 'auth', $setup ) ) {
				$mail->SMTPAuth = $setup['auth'];
			}
			if ( array_key_exists( 'username', $setup ) ) {
				$mail->Username = $setup['username'];
			}
			if ( array_key_exists( 'password', $setup ) ) {
				$mail->Password = $setup['password'];
			}
			if ( array_key_exists( 'secure', $setup ) ) {
				$mail->SMTPSecure = $setup['secure'];
			}
			if ( array_key_exists( 'port', $setup ) ) {
				$mail->Port = $setup['port'];
			}
		}

		return $mail;
	}

	/**
	 * (Re-)sends validation mails for all unconfirmed requests associated with
	 * a given mail address.
	 *
	 * @param string $recipient mail address of recipient
	 * @param bool $latestOnly set false to (re-)send validation mail for every
	 *     incomplete request linked with mail address
	 * @return array maps of processed requests' IDs into information on either
	 *     request's actual result grouped by kind of result
	 * @throws HttpException
	 */
	public static function sendValidation( $recipient, $latestOnly = true ) {
		if ( !is_string( $recipient ) || $recipient == '' ) {
			throw new InvalidArgumentException( 'missing recipient mail address' );
		}


		$results = [
			'success'  => [],
			'deferred' => [],
			'failure'  => [],
		];

		$db    = Database::get();
		$table = Database::tableName( 'requests' );

		$list = $db->prepare( /** @lang mysql */ "SELECT * FROM $table WHERE mailaddress=? AND ts_validated IS NULL ORDER BY ts_received DESC" );
		$list->execute( [ $recipient ] );

		while ( ( $request = $list->fetch( PDO::FETCH_ASSOC ) ) ) {
			if ( $latestOnly ) {
				if ( count( $results['success'] ) + count( $results['deferred'] ) + count( $results['failure'] ) > 0 ) {
					break;
				}
			}

			$id = $request['id'];


			// reject re-sending mails for at least 60 seconds
			if ( intval( $request['ts_clientmail'] ) > time() - 60 ) {
				$results['deferred'][$id] = true;
				continue;
			}


			// prepare interpolations of string in context of request's configuration
			$config = Config::load( $request['formid'] );
			$filter = new Interpolate( $config );

			$info = array_merge( $request, [
				'validation-url' => URL::get( [ 'validate' => "$id-{$request['validationkey']}" ] ),
			] );


			$mail = null;

			try {
				$mail = static::create();

				$offerContent = json_decode( $request['json'], true );
				if ( !$offerContent ) {
					throw new RuntimeException( L10n::translate( 'missing request information' ) );
				}

				$sender  = $config->query( 'validation.from' ) ?: Setup::get( 'admin.mail' );

				$mail->setFrom( $sender );
				$mail->addAddress( $recipient, @$offerContent['summary']['verification_name'] );

				$carbonCopies = $config->query( 'validation.bcc' );
				if ( is_array( $carbonCopies ) ) {
					foreach ( $carbonCopies as $ccRecipient ) {
						$mail->addBCC( $ccRecipient );
					}
				}

				$subject = $config->query( 'validation.subject' );
				$body    = $config->query( 'validation.body' );
				$body    = $filter->interpolateBraces( L10n::localize( $body ), $info );
				$isHTML  = Data::asBoolean( $config->query( 'validation.html' ) );
				if ( $isHTML ) {
					$body = Markdown::get()->text( $body );
				}

				$mail->Subject = $filter->interpolateBraces( L10n::localize( $subject ), $info );
				$mail->Body    = $body;
				$mail->isHTML( $isHTML );

				$mail->send();


				$update = $db->prepare( /** @lang mysql */ "UPDATE $table SET ts_clientmail=? WHERE id=?" );
				$update->execute( [ time(), $id ] );

				$results['success'][$id] = true;
			}
			catch ( Exception $exception ) {
				$results['failure'][$id] = L10n::interpolate( 'sending mail failed ($$mail-error$$)', 1, [ 'mail-error' => $mail->ErrorInfo ] );
			}
			catch ( PDOException $exception ) {
				$results['failure'][$id] = L10n::interpolate( 'mail was sent, but updating database failed' );
			}
			catch ( Throwable $exception ) {
				$results['failure'][$id] = $exception->getMessage();
			}
		}

		return $results;
	}

	/**
	 * Generates mail containing statistics to configured admin mail address.
	 *
	 * @param string $formId ID of configuration statistics are about
	 * @param bool $lastMonth set true to list statistics for the last 30 days,
	 *     only
	 */
	public static function sendStatistics( $formId, $lastMonth = true ) {
		$adminMail = Setup::get( 'admin.mail' );

		$db    = Database::get();
		$table = Database::tableName( 'requests' );

		$filterList = [
			[ L10n::translate( 'mail.statistics.category.received-only' ), 'ts_clientmail IS NULL', 'send_validation' ],
			[ L10n::translate( 'mail.statistics.category.non-validated' ), 'ts_clientmail > 0 AND ts_validated IS NULL', 'send_validation' ],
			[ L10n::translate( 'mail.statistics.category.validated' ), 'ts_validated > 0 AND ts_adminmail IS NULL', null ],
			[ L10n::translate( 'mail.statistics.category.completed' ), 'ts_adminmail > 0', null ]
		];


		$message = "";

		foreach ( $filterList as $filter ) {
			$stmt = $db->prepare( /** @lang mysql */ <<<EOT
SELECT 
	id, mailaddress, validationkey, querykey, ts_received 
FROM $table 
WHERE 
	formid=? 
	AND 
	ts_received>? 
	AND 
	{$filter[1]} 
ORDER BY 
	mailaddress,
	ts_received
EOT
			);
			$stmt->execute( [ $formId, $lastMonth ? time() - 30 * 86400 : 0 ] );

			if ( $stmt->rowCount() > 0 ) {
				$lastMailAddress = null;
				$message         .= L10n::interpolate( 'mail.statistics.category-start', 1, [
					'category' => $filter[0],
				] );

				while ( $row = $stmt->fetch( PDO::FETCH_ASSOC ) ) {
					if ( $lastMailAddress != $row['mailaddress'] ) {
						if ( $lastMailAddress != null ) {
							$message .= "</ul></li>";
						}

						$lastMailAddress = $row['mailaddress'];

						switch ( $filter[2] ) {
							case "send_validation" :
								$url    = Url::get( [ 'send_validation' => $lastMailAddress ] );
								$action = L10n::interpolate( 'mail.statistics.action', 1, [
									'url' => $url,
								] );
								break;

							default :
								$action = '';
								break;
						}

						$message .= L10n::interpolate( 'mail.statistics.user-start', 1, [
								'mail' => $lastMailAddress,
							] ) . $action;
					}

					$authorizedId = $row['id'] . '-' . $row['querykey'];

					$message .= L10n::interpolate( 'mail.statistics.entry', 1, [
						'date'     => date( L10n::translate( 'Y-m-d (H:i)' ), intval( $row['ts_received'] ) ),
						'yaml-url' => Url::get( [ 'query' => "yaml:$authorizedId" ] ),
						'csv-url'  => Url::get( [ 'query' => "csv:$authorizedId" ] ),
					] );
				}

				$message .= "</ul></li></ul>";
			}
		}

		$formURL = Url::get( [ 'auftrag' => $formId ] );

		$mail = static::create();
		$mail->setFrom( $adminMail );
		$mail->addAddress( $adminMail );
		$mail->isHTML( true );

		$mail->Subject = L10n::interpolate( 'mail.statistics.subject' );
		if ( $lastMonth ) {
			$mail->Body = '<h1>Statistik</h1>' .
			              "<p>Über das Formular <a href=\"$formURL\">$formURL</a> wurden in den letzten 30 Tagen folgende Daten erfasst:</p>$message";
		} else {
			$mail->Body = '<h1>Statistik</h1>' .
			              "<p>Über das Formular <a href=\"$formURL\">$formURL</a> wurden bisher folgende Daten erfasst:</p>$message";
		}

		$mail->send();
	}

	/**
	 * Generates mail sending notification on and input of a user's request.
	 *
	 * @param string $id ID of request record in database
	 * @throws HttpException
	 * @throws Exception
	 */
	public static function sendNotification( $id ) {
		$db    = Database::get();
		$table = Database::tableName( 'requests' );

		$stmt = $db->prepare( /** @lang mysql */ "SELECT formid, querykey, yaml FROM $table WHERE id=?" );
		$stmt->execute( [ $id ] );

		$entry = $stmt->fetch( PDO::FETCH_ASSOC );
		if ( !$entry ) {
			throw new HttpException( 'no such entry', 404 );
		}

		$yaml     = $entry['yaml'];
		$formId   = $entry['formid'];
		$queryKey = $entry['querykey'];


		$userInput = spyc_load( $yaml );
		$info      = array_merge( $userInput, [
			'form-url' => Url::getForForm( $formId ),
			'csv-url'  => Url::get( [ 'query' => "csv:$id-$queryKey" ] ),
			'yaml-url' => Url::get( [ 'query' => "yaml:$id-$queryKey" ] ),
		] );


		$config = Config::load( $formId );
		$filter = new Interpolate( $config );


		// send mail to site admin with now validated input of user
		$mail      = Mail::create();
		$sender    = $config->query( 'notification.from' ) ?: Setup::get( 'admin.mail' );
		$recipient = $config->query( 'notification.to' ) ?: $sender;

		$mail->setFrom( $sender );
		if ( $userInput['validation_mail'] ) {
			$mail->addReplyTo( $userInput['validation_mail'] );
		}
		$mail->addAddress( $recipient );

		$carbonCopies = $config->query( 'notification.bcc' );
		if ( is_array( $carbonCopies ) ) {
			foreach ( $carbonCopies as $ccRecipient ) {
				$mail->addBCC( $ccRecipient );
			}
		}

		$subject = $config->query( 'notification.subject' );
		$body    = $config->query( 'notification.body' );
		$body    = $filter->interpolateBraces( L10n::localize( $body ), $info );
		$isHTML  = Data::asBoolean( $config->query( 'notification.html' ) );
		if ( $isHTML ) {
			$body = Markdown::get()->text( $body );
		}

		$mail->Subject = $filter->interpolateBraces( L10n::localize( $subject ), $info );
		$mail->Body    = $body;
		$mail->isHTML( $isHTML );

		// attach user's input in CSV and/or YAML
		if ( Data::asBoolean( $config->query( 'notification.csv' ) ?: Setup::get( 'notification.csv', 'yes' ) ) ) {
			$document = Data::getCSV( [ $userInput ] );

			$mail->addStringAttachment( $document, "request-$formId-id-$id.csv", 'base64', 'text/csv' );
		}

		if ( Data::asBoolean( $config->query( 'notification.yaml' ) ?: Setup::get( 'notification.yaml', 'yes' ) ) ) {
			$document = preg_replace( '/\r\n?|\n/', Setup::get( 'linebreaks', 'crlf' ) === 'crlf' ? "\r\n" : "\n", $yaml );

			$mail->addStringAttachment( $document, "request-$formId-id-$id-yaml.txt", 'base64', 'text/yaml' );
		}

		if ( !$mail->send() ) {
			throw new HttpException( 'failed submitting your request for further processing', 500 );
		}


		// track time of successful transmission of mail sent to site admin for notification
		$stmt = $db->prepare( /** @lang mysql */ "UPDATE $table SET ts_adminmail=? WHERE id=?" );
		$stmt->execute( [ time(), $id ] );
	}
}
