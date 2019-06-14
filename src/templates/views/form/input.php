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

$title  = Page::read( 'title' );
$url    = Page::read( 'url' );
$hidden = Page::read( 'hidden' );
$label  = Page::read( 'label' );
$name   = Page::read( 'name' );

$button = L10n::translate( 'view.form.submit' );

if ( $title ) {
	$title = '<h1>' . $title . '</h1>';
}

$hidden = is_array( $hidden ) ? implode( '', array_map( function( $name, $value ) {
	return '<input type="hidden" name="' . htmlentities( $name ) . '" value="' . htmlentities( $value ) . '">';
}, array_keys( $hidden ), array_values( $hidden ) ) ) : '';

Page::write( <<<EOT
<div class="regular-content">
	$title
	<div class="forms-processor">
		<form action="$url" method="get" class="form-view">
			$hidden
			<div class="body">
				<div class="form">
					<div class="field">
						<div class="label">
							<label>$label</label>
						</div>
						<div class="widget">
							<input type="text" name="$name" />
						</div>
					</div>
				</div>
			</div>
			<div class="control">
				<div class="form-control">
					<button type="submit">$button</button>
				</div>
			</div>
		</form>
	</div>
</div>
EOT
);

Page::render();
