<?php

// SPDX-FileCopyrightText: 2018-2026 Ovation S.r.l. <help@dynamic.ooo>
// SPDX-License-Identifier: GPL-3.0-or-later
namespace DynamicVisibilityForElementor;

trait ValueConversion {

	/**
	 * String to Array
	 *
	 * Converts a string into an array based on a specified delimiter, applies the trim function to each element,
	 * removes empty elements from the array, and optionally applies a formatting function to each element.
	 *
	 * @param string $delimiter The delimiter used to split the string into elements. Defaults to a comma (',').
	 * @param string|array<mixed> $string The input string to be split. If an array is passed, it is returned directly without modifications.
	 * @param callable|null $format An optional callback function to apply to each element of the array. If null, no formatting is applied.
	 * @return array An array of elements derived from the input string, with whitespace trimmed from each element and empty elements removed.
	 *               If a format function is specified, each element will also be formatted accordingly.
	 */
	public static function str_to_array( $delimiter = ',', $string = '', $format = null ) {
		if ( is_array( $string ) ) {
			return $string;
		}
		$pieces = explode( $delimiter, $string );
		$pieces = array_filter(array_map( 'trim', $pieces ), function ( $value ) {
			return $value !== '';
		});
		if ( $format ) {
			$pieces = array_map( $format, $pieces );
		}
		return $pieces;
	}

	/**
	 * Convert a value to a readable string
	 *
	 * @param mixed $avalue
	 * @param boolean $listed
	 * @return string
	 */
	public static function to_readable_string( $avalue, $listed = false ) {
		if ( ! is_array( $avalue ) && ! is_object( $avalue ) ) {
			return (string) $avalue;
		}
		if ( $avalue instanceof \WP_Term ) {
			return esc_html( $avalue->name );
		}
		if ( $avalue instanceof \WP_Post ) {
			return esc_html( $avalue->post_title );
		}
		if ( $avalue instanceof \WP_User ) {
			return esc_html( $avalue->display_name );
		}

		if ( is_object( $avalue ) ) {
			$avalue = (array) $avalue;
		}

		if ( isset( $avalue['post_title'] ) ) {
			return esc_html( $avalue['post_title'] );
		}
		if ( isset( $avalue['display_name'] ) ) {
			return esc_html( $avalue['display_name'] );
		}
		if ( isset( $avalue['name'] ) ) {
			return esc_html( $avalue['name'] );
		}
		if ( count( $avalue ) == 1 ) {
			$first = reset( $avalue );
			return self::to_readable_string( $first );
		}
		return self::implode_recursive( ', ', $avalue, $listed );
	}

	public static function implode_recursive( $separator = ', ', $arrayvar = array(), $listed = false ) {
		$output = '';
		if ( ! empty( $arrayvar ) && is_array( $arrayvar ) ) {
			if ( $listed ) {
				$output .= '<ul>';
			}
			$i = 0;
			foreach ( $arrayvar as $av ) {
				if ( $listed ) {
					$output .= '<li>';
				}
				if ( is_object( $av ) ) {
					$av = self::to_readable_string( $av );
				}
				if ( is_array( $av ) ) {
					$output .= self::implode_recursive( $separator, $av, $listed ); // Recursive Use of the Array
				} elseif ( $i ) {
						$output .= $separator . $av;
				} else {
					$output .= $av;
				}
				if ( $listed ) {
					$output .= '</li>';
				}
				++$i;
			}
			if ( $listed ) {
				$output .= '</ul>';
			}
		}
		return $output;
	}
}
