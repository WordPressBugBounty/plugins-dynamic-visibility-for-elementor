<?php

// SPDX-FileCopyrightText: 2018-2026 Ovation S.r.l. <help@dynamic.ooo>
// SPDX-License-Identifier: GPL-3.0-or-later
namespace DynamicVisibilityForElementor;

trait ElementLookup {

	public static $documents = [];

	public static function get_post_id_by_element_id( $element_id, $post_id = 0 ) {
		$ext_post_id = false;
		if ( isset( self::$documents[ $element_id ] ) ) {
			$ext_post_id = self::$documents[ $element_id ];
		} else {
			// find element settings (because it may not be on post, but in a template)
			global $wpdb;
			$table = $wpdb->prefix . 'postmeta';
			$query = $wpdb->prepare( "SELECT post_id FROM {$table} WHERE meta_key LIKE %s AND meta_value LIKE %s", '_elementor_data', '%"id":"' . $wpdb->esc_like( $element_id ) . '",%' );
			if ( $post_id ) {
				$query .= $wpdb->prepare( ' AND post_id = %d', $post_id );
			} else {
				$query .= " AND post_id IN (
					SELECT id FROM {$wpdb->prefix}posts
					WHERE post_status LIKE 'publish'
				)";
			}
			$results = $wpdb->get_results( $query );
			if ( ! empty( $results ) ) {
				$result = reset( $results );
				$ext_post_id = reset( $result );
				self::$documents[ $element_id ] = $ext_post_id;
			}
		}
		return $ext_post_id;
	}

	public static function get_elementor_element_by_id( $element_id, $post_id = null ) {
		if ( ! $post_id ) {
			if ( $element_id ) {
				$post_id = self::get_post_id_by_element_id( $element_id );
			}
			if ( ! $post_id ) {
				$post_id = get_the_ID();
				if ( ! $post_id && isset( $_GET['post'] ) ) {
					$post_id = absint( $_GET['post'] );
				}
				if ( ! $post_id && isset( $_POST['post_id'] ) ) {
					$post_id = absint( $_POST['post_id'] );
				}
			}
		}
		if ( $post_id ) {
			$document = \Elementor\Plugin::$instance->documents->get( $post_id );
			if ( $document ) {
				$element_raw = Helper::find_element_recursive( $document->get_elements_data(), $element_id );
				if ( $element_raw ) {
					$element = \Elementor\Plugin::$instance->elements_manager->create_element_instance( $element_raw );
					return $element;
				} else {
					return false;
				}
			}
		}
		return false;
	}

	public static function get_elementor_element_settings_by_id( $element_id = null, $post_id = null ) {
		$element = self::get_elementor_element_by_id( $element_id, $post_id );
		if ( $element ) {
			$settings = $element->get_settings_for_display();
			return $settings;
		}
		return false;
	}

	/**
	 * @param array<mixed> $elements
	 * @param string $element_id
	 *
	 * @return array<string,mixed>|false
	 */
	public static function find_element_recursive( array $elements, string $element_id ) {
		foreach ( $elements as $element ) {
			if ( $element_id === $element['id'] ) {
				return $element;
			}

			if ( ! empty( $element['elements'] ) ) {
				$element = self::find_element_recursive( $element['elements'], $element_id );

				if ( $element ) {
					return $element;
				}
			}
		}
		return false;
	}
}
