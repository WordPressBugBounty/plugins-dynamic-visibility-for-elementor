<?php

// SPDX-FileCopyrightText: 2018-2026 Ovation S.r.l. <help@dynamic.ooo>
// SPDX-License-Identifier: GPL-3.0-or-later
namespace DynamicVisibilityForElementor;

trait Conditions {

	/**
	 * Is Condition Satisfied
	 *
	 * @param mixed $field
	 * @param string $status
	 * @param mixed $value
	 * @return boolean
	 */
	public static function is_condition_satisfied( $field, $status, $value ) {
		switch ( $status ) {
			case 'isset':
				return ! empty( $field );
			case 'not':
				return empty( $field );
			case 'lt':
				if ( is_numeric( $field ) ) {
					$field = floatval( $field );
				}
				if ( is_numeric( $value ) ) {
					$value = floatval( $value );
				}
				if ( is_array( $field ) && count( $field ) < $value ) {
					return true;
				}
				return $field < $value;
			case 'lte':
				if ( is_numeric( $field ) ) {
					$field = floatval( $field );
				}
				if ( is_numeric( $value ) ) {
					$value = floatval( $value );
				}
				if ( is_array( $field ) && count( $field ) <= $value ) {
					return true;
				}
				return $field <= $value;
			case 'gt':
				if ( is_numeric( $field ) ) {
					$field = floatval( $field );
				}
				if ( is_numeric( $value ) ) {
					$value = floatval( $value );
				}
				if ( is_array( $field ) && count( $field ) > $value ) {
					return true;
				}
				return $field > $value;
			case 'gte':
				if ( is_numeric( $field ) ) {
					$field = floatval( $field );
				}
				if ( is_numeric( $value ) ) {
					$value = floatval( $value );
				}
				if ( is_array( $field ) && count( $field ) >= $value ) {
					return true;
				}
				return $field >= $value;
			case 'contain':
				if ( is_array( $field ) && in_array( $value, $field ) ) {
					return true;
				}
				if ( is_string( $field ) && $value !== '' && strpos( $field, $value ) !== false ) {
					return true;
				}
				return false;
			case 'not_contain':
				if ( empty( $field ) ) {
					return true;
				}
				if ( is_array( $field ) && ! in_array( $value, $field ) ) {
					return true;
				}
				if ( is_string( $field ) && $value !== '' && strpos( $field, $value ) === false ) {
					return true;
				}
				return false;
			case 'starts_with':
				$field = Helper::to_readable_string( $field );
				$value = Helper::to_readable_string( $value );
				if ( $value === '' ) {
					return true;
				}
				return strpos( $field, $value ) === 0;
			case 'ends_with':
				$field = Helper::to_readable_string( $field );
				$value = Helper::to_readable_string( $value );
				if ( $value === '' ) {
					return true;
				}
				return substr( $field, -strlen( $value ) ) === $value;
			case 'in_array':
				if ( ! is_array( $value ) ) {
					$value = Helper::to_readable_string( $value );
					$value = Helper::str_to_array( ',', $value );
				}
				if ( in_array( $field, $value ) ) {
					return true;
				}
				return false;
			case 'not_in_array':
				if ( ! is_array( $value ) ) {
					$value = Helper::to_readable_string( $value );
					$value = Helper::str_to_array( ',', $value );
				}
				return ! in_array( $field, $value );
			case 'not_value':
				return $field != $value;
			case 'value':
				return $field == $value;
			case 'not_value_i':
				return strcasecmp( Helper::to_readable_string( $field ), Helper::to_readable_string( $value ) ) !== 0;
			case 'value_i':
				return strcasecmp( Helper::to_readable_string( $field ), Helper::to_readable_string( $value ) ) === 0;
		}
		return false;
	}

	/**
	 * @return array<string,mixed>
	 */
	public static function get_compare_options() {
		return [
			// Empty checks
			'isset' => esc_html__( 'Not empty', 'dynamic-visibility-for-elementor' ),
			'not' => esc_html__( 'Empty or not set', 'dynamic-visibility-for-elementor' ),

			// Equality comparisons
			'value' => esc_html__( 'Equal to', 'dynamic-visibility-for-elementor' ),
			'value_i' => esc_html__( 'Equal to (ignore case)', 'dynamic-visibility-for-elementor' ),
			'not_value' => esc_html__( 'Not equal to', 'dynamic-visibility-for-elementor' ),
			'not_value_i' => esc_html__( 'Not equal to (ignore case)', 'dynamic-visibility-for-elementor' ),

			// Numeric comparisons
			'lt' => esc_html__( 'Less than', 'dynamic-visibility-for-elementor' ),
			'lte' => esc_html__( 'Less than or equal to', 'dynamic-visibility-for-elementor' ),
			'gt' => esc_html__( 'Greater than', 'dynamic-visibility-for-elementor' ),
			'gte' => esc_html__( 'Greater than or equal to', 'dynamic-visibility-for-elementor' ),

			// String comparisons
			'contain' => esc_html__( 'Contains', 'dynamic-visibility-for-elementor' ),
			'not_contain' => esc_html__( 'Does not contain', 'dynamic-visibility-for-elementor' ),
			'starts_with' => esc_html__( 'Starts with', 'dynamic-visibility-for-elementor' ),
			'ends_with' => esc_html__( 'Ends with', 'dynamic-visibility-for-elementor' ),

			// Array operations
			'in_array' => esc_html__( 'Is one of', 'dynamic-visibility-for-elementor' ),
			'not_in_array' => esc_html__( 'Is not one of', 'dynamic-visibility-for-elementor' ),
		];
	}
}
