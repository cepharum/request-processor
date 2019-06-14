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
	'Y-m-d (H:i)' => 'd.m.Y (H:i)',

	'overview.title' => 'Übersicht',

	'admin.title'             => 'Admin-Funktionen',
	'admin.validation.prompt' => 'Validierungsanfragen erneut senden',
	'admin.validation.latest' => 'Nur für letzte Anfrage',
	'admin.validation.all'    => 'Für alle Anfragen',
	'admin.statistics'        => 'Abruf der Nutzerdaten',
	'admin.db.dump'           => 'Alle Anfragen anzeigen',
	'admin.db.drop'           => 'Datenbank leeren',

	'validate.resend.title'      => 'Validierungs-Mail (erneut) versenden',
	'validate.resend.unknown'    => 'Es konnte keine Bestätigungsmail verschickt werden.',
	'validate.resend.none'       => 'Es wurde keine Bestätigungsmail versandt.',
	'validate.resend.success'    => 'Es wurde(n) $$success$$ Bestätigungsmail(s) versandt.',
	'validate.resend.deferred'   => '$$deferred$$ Bestätigungsmail(s) wurde(n) nicht versandt. Bitte versuchen Sie es in ein paar Minuten nochmal.',
	'validate.resend.failure'    => 'Der Versand von $$failure$$ Bestätigungsmail(s) ist fehlgeschlagen.',
	'validate.resend.form-label' => 'E-Mail-Adresse',

	'statistics.title'        => 'Statistik-Abruf',
	'statistics.success'      => 'Die Statistik wurde an den Site-Admin versandt.',
	'statistics.failure'      => 'Beim Versenden der Statistik an den Site-Admin ist ein Fehler aufgetreten.',
	'statistics.label.latest' => 'Letzte 30 Tage',
	'statistics.label.all'    => 'Alle',

	'crash.title'   => 'Kritischer Serverfehler',
	'crash.message' => 'Die Anwendung konnte aufgrund eines kritischen Serverfehlers Ihre Anfrage nicht verarbeiten. Wir bitten Sie, dies zu entschuldigen.',

	'error.title'   => 'Fehler',
	'error.message' => 'Die Anwendung konnte Ihre Anfrage nicht erfolgreich verarbeiten.',

	'mail.statistics.subject'                => 'Ihre gewünschte Datenstatistik',
	'mail.statistics.category-start'         => '<h2>Kategorie "$$category$$"</h2><ul>',
	'mail.statistics.user-start'             => '<li>Kunde mit Mailadresse <a href="mailto:$$mail$$">$$mail$$</a><ul>',
	'mail.statistics.action'                 => '<li>Bestätigungsanfragen neu versenden: <a href="$$url$$">nur letzte</a> oder <a href="$$url$$&send_all=true">alle</a></li>',
	'mail.statistics.entry'                  => '<li>Datum $$date$$: <a href="$$yaml-url$$">YAML</a> oder <a href="$$csv-url$$">CSV</a> abfragen</li>',
	'mail.statistics.category.received-only' => 'empfangen, aber nicht an den Kunden versandt',
	'mail.statistics.category.non-validated' => 'an den Kunden versandt, aber nicht bestätigt',
	'mail.statistics.category.validated'     => 'bestätigt, aber nicht an den Admin versandt',
	'mail.statistics.category.completed'     => 'bestätigt und an den Admin versandt',

	'view.form.submit' => 'Absenden',
] );
