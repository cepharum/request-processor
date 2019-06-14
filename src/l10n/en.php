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

L10n::register( [
	'Y-m-d (H:i)' => 'Y-m-d (H:i)',

	'overview.title' => 'Overview',

	'admin.title'             => 'Admin Functions',
	'admin.validation.prompt' => 'Resend validation requests',
	'admin.validation.latest' => 'latest request, only',
	'admin.validation.all'    => 'all requests',
	'admin.statistics'        => 'Statistics',
	'admin.db.dump'           => 'Show all requests',
	'admin.db.drop'           => 'Drop database',

	'validate.resend.title'      => '(Re-)Send Validation',
	'validate.resend.unknown'    => 'Sending any validation mail has failed.',
	'validate.resend.none'       => 'There was not any validation mail sent.',
	'validate.resend.success'    => '$$success$$ validation mail(s) has/have been sent.',
	'validate.resend.deferred'   => 'Sending $$deferred$$ validation mail(s) has been deferred. Please start another attempt in several minutes.',
	'validate.resend.failure'    => 'Sending $$failure$$ validation mail(s) has failed.',
	'validate.resend.form-label' => 'Mail Address',

	'statistics.title'        => 'Retrieval of Statistics',
	'statistics.success'      => 'Statistics have been sent to site admin via mail.',
	'statistics.failure'      => 'An error occurred on trying to send statistics to site admin via mail.',
	'statistics.label.latest' => 'last 30 days',
	'statistics.label.all'    => 'all',

	'crash.title'   => 'Critical Server Issue',
	'crash.message' => 'The application failed to process your request due to critical server issue. We apologize for any inconvenience this may cause.',

	'error.title'   => 'Error',
	'error.message' => 'Processing your request has failed.',

	'mail.statistics.subject'                => 'Your requested statistics',
	'mail.statistics.category-start'         => '<h2>Category "$$category$$"</h2><ul>',
	'mail.statistics.user-start'             => '<li>Customer associated with mail address <a href="mailto:$$mail$$">$$mail$$</a><ul>',
	'mail.statistics.action'                 => '<li>Resend confirmation mail: <a href="$$url$$">latest request, only</a> or <a href="$$url$$&send_all=true">all requests</a></li>',
	'mail.statistics.entry'                  => '<li>Date $$date$$: fetch <a href="$$yaml-url$$">YAML</a> or <a href="$$csv-url$$">CSV</a></li>',
	'mail.statistics.category.received-only' => 'request received, but confirmation request has failed',
	'mail.statistics.category.non-validated' => 'confirmation by requesting user is pending',
	'mail.statistics.category.validated'     => 'confirmed, but not forwarded to site admin',
	'mail.statistics.category.completed'     => 'confirmed and forwarded to site admin',

	'view.form.submit' => 'Submit',
] );
