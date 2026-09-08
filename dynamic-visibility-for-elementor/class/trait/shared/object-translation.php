<?php

// SPDX-FileCopyrightText: 2018-2026 Ovation S.r.l. <help@dynamic.ooo>
// SPDX-License-Identifier: GPL-3.0-or-later
namespace DynamicVisibilityForElementor;

trait ObjectTranslation {

	public static function wpml_translate_object_id_by_type( $object_id, $type ) {
		$current_language = apply_filters( 'wpml_current_language', null );
		if ( is_array( $object_id ) ) {
			$translated_object_ids = array();
			foreach ( $object_id as $id ) {
				$translated_object_ids[] = apply_filters( 'wpml_object_id', $id, $type, true, $current_language );
			}
			return $translated_object_ids;
		} elseif ( is_string( $object_id ) ) {
			// check if we have a comma separated ID string
			$is_comma_separated = strpos( $object_id, ',' );

			if ( $is_comma_separated !== false ) {
				// explode the comma to create an array of IDs
				$object_id = explode( ',', $object_id );

				$translated_object_ids = array();
				foreach ( $object_id as $id ) {
					$translated_object_ids[] = apply_filters( 'wpml_object_id', $id, $type, true, $current_language );
				}

				// make sure the output is a comma separated string (the same way it came in!)
				return implode( ',', $translated_object_ids );
			} else { // if we don't find a comma in the string then this is a single ID
				return apply_filters( 'wpml_object_id', intval( $object_id ), $type, true, $current_language );
			}
		} else { // if int
			return apply_filters( 'wpml_object_id', $object_id, $type, true, $current_language );
		}
	}

	/**
	 * Returns the translated object ID(post_type or term) or original if missing
	 *
	 * @template T of int|string|array<int|string>
	 * @param T $object_id The ID/s of the objects to check and return
	 * @return ($object_id is array ? array<int|string> : ($object_id is string ? string : int))
	 */
	public static function wpml_translate_object_id( $object_id ) {
		$current_language = apply_filters( 'wpml_current_language', null );
		// if array
		if ( is_array( $object_id ) ) {
			$translated_object_ids = array();
			foreach ( $object_id as $id ) {
				$translated_object_ids[] = apply_filters( 'wpml_object_id', $id, get_post_type( (int) $id ), true, $current_language );
			}
			return $translated_object_ids;
		}
		// if string
		elseif ( is_string( $object_id ) ) {
			// check if we have a comma separated ID string
			$is_comma_separated = strpos( $object_id, ',' );

			if ( $is_comma_separated !== false ) {
				// explode the comma to create an array of IDs
				$object_id = explode( ',', $object_id );

				$translated_object_ids = array();
				foreach ( $object_id as $id ) {
					$translated_object_ids[] = apply_filters( 'wpml_object_id', $id, get_post_type( $id ), true, $current_language );
				}

				// make sure the output is a comma separated string (the same way it came in!)
				return implode( ',', $translated_object_ids );
			}
			// if we don't find a comma in the string then this is a single ID
			else {
				return apply_filters( 'wpml_object_id', intval( $object_id ), get_post_type( intval( $object_id ) ), true, $current_language );
			}
		}
		// if int
		else {
			return apply_filters( 'wpml_object_id', $object_id, get_post_type( $object_id ), true, $current_language );
		}
	}
}
