<?php

function parseMarkdown( $markdown ) {
	$lines    = explode( "\n", $markdown );
	$isInList = false;
	foreach ( $lines as &$line ) {
		list( $line, $isInList ) = parsLine( $line, $isInList );
	}
	$html = join( $lines );
	if ( $isInList ) {
		$html .= '</ul>';
	}

	return $html;
}

/**
 * @param $line
 * @param $isInList
 *
 * @return array
 */
function parsLine( $line, $isInList ): array {
	$line = parsHeading( $line );

	if ( preg_match( '/\*(.*)/', $line, $matches ) ) {
		if ( ! $isInList ) {
			$isInList = true;
			$line     = "<ul>";
		} else {
			$line = "";
		}
		$line .= parsItalicOrBold( parsBold( $matches[1] ) || parsItalic( $matches ), $matches );
	} else {
		if ( $isInList ) {
			$line     = "</ul>" . $line;
			$isInList = false;
		}
	}

	$line = parsLineWithoutTag( $line );

	parsBold( $line );

	if ( preg_match( '/(.*)_(.*)_(.*)/', $line, $matches ) ) {
		$line = $matches[1] . '<i>' . $matches[2] . '</i>' . $matches[3];
	}

	return array( $line, $isInList );
}

/**
 * @param $line
 *
 * @return string
 */
function parsLineWithoutTag( $line ): string {
	if ( ! preg_match( '/<h|<ul|<p|<li/', $line ) ) {
		$line = "<p>$line</p>";
	}

	return $line;
}

/**
 * @param $matches
 *
 * @return bool
 */
function parsItalic( &$matches ): bool {
	if ( preg_match( '/(.*)_(.*)_(.*)/', $matches[1], $matches3 ) ) {
		$matches[1] = $matches3[1] . '<i>' . $matches3[2] . '</i>' . $matches3[3];

		return true;
	}

	return false;
}

/**
 * @param $isItalicOrBold
 * @param $matches
 *
 * @return string
 */
function parsItalicOrBold( $isItalicOrBold, $matches ): string {
	if ( $isItalicOrBold ) {
		$line = "<li>" . trim( $matches[1] ) . "</li>";
	} else {
		$line = "<li><p>" . trim( $matches[1] ) . "</p></li>";
	}

	return $line;
}

/**
 * @param $matches
 *
 * @return bool
 */
function parsBold( &$line ): bool {
	if ( preg_match( '/(.*)__(.*)__(.*)/', $line, $matches2 ) ) {
		$line = $matches2[1] . '<em>' . $matches2[2] . '</em>' . $matches2[3];

		return true;
	}

	return false;
}

/**
 * @param $line
 *
 * @return string
 */
function parsHeading( $line ): string {
	for ( $i = 6; $i >= 1; $i -- ) {
		if ( preg_match( '/^' . str_repeat( "#", $i ) . '(.*)/', $line, $matches ) ) {
			$line = "<h$i>" . trim( $matches[1] ) . "</h$i>";
			break;
		}
	}

	return $line;
}
