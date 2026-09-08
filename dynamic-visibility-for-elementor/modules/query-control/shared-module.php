<?php

// SPDX-FileCopyrightText: 2018-2026 Ovation S.r.l. <help@dynamic.ooo>
// SPDX-License-Identifier: GPL-3.0-or-later

namespace DynamicVisibilityForElementor\Modules\QueryControl;

use Elementor\Core\Editor\Editor;
use DynamicVisibilityForElementor\Helper;
use Elementor\Core\Base\Module as Base_Module;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class SharedModule extends Base_Module {
	/** @return array<string,string> */
	protected function get_autocomplete_handlers() {
		return [
			'capabilities' => 'get_capabilities',
			'fields' => 'get_fields',
			'metas' => 'get_metas',
			'posts' => 'get_posts',
			'terms' => 'get_terms',
		];
	}

	/** @return array<string,string> */
	protected function get_title_handlers() {
		return [
			'capabilities' => 'get_value_titles_for_capabilities',
			'fields' => 'get_value_titles_for_fields',
			'metas' => 'get_value_titles_for_metas',
			'posts' => 'get_value_titles_for_posts',
			'terms' => 'get_value_titles_for_terms',
		];
	}

	public function ajax_call_filter_autocomplete( array $data ) {
		if ( ! current_user_can( Editor::EDITING_CAPABILITY ) ) {
			throw new \Exception( 'Access denied.' );
		}
		if ( empty( $data['query_type'] ) || ! is_string( $data['query_type'] ) || empty( $data['q'] ) ) {
			throw new \Exception( 'Bad Request' );
		}
		$handlers = $this->get_autocomplete_handlers();
		$query_type = sanitize_key( $data['query_type'] );
		if ( ! isset( $handlers[ $query_type ] ) ) {
			throw new \Exception( 'Invalid query type.' );
		}
		// Handler names come exclusively from the module's explicit registry.
		return [ 'results' => $this->{$handlers[ $query_type ]}( $data ) ];
	}

	public function ajax_call_control_value_titles( $request ) {
		if ( ! current_user_can( Editor::EDITING_CAPABILITY ) ) {
			throw new \Exception( 'Access denied.' );
		}
		if ( empty( $request['query_type'] ) || ! is_string( $request['query_type'] ) ) {
			throw new \Exception( 'Invalid query type.' );
		}
		$handlers = $this->get_title_handlers();
		$query_type = sanitize_key( $request['query_type'] );
		if ( ! isset( $handlers[ $query_type ] ) ) {
			throw new \Exception( 'Invalid query type.' );
		}
		return $this->{$handlers[ $query_type ]}( $request );
	}

	public function __construct() {
		$this->add_actions();
	}

	/**
	 * @return string
	 */
	public function get_name() {
		return 'dce-query-control';
	}

	/**
	 * @return void
	 */
	protected function add_actions() {
		add_action( 'elementor/ajax/register_actions', [ $this, 'register_ajax_actions' ] );
	}

	protected function get_fields( $data ) {
		if ( ! current_user_can( Editor::EDITING_CAPABILITY ) ) {
			throw new \Exception( 'Access denied.' );
		}

		$results = [];
		$object_types = $data['object_type'];
		if ( ! is_array( $object_types ) ) {
			$object_types = array( $object_types );
		}

		foreach ( $object_types as $object_type ) {
			switch ( $object_type ) {
				case 'post':
					$fields = Helper::get_post_fields( $data['q'] );
					break;
				case 'user':
					$fields = Helper::get_user_fields( $data['q'] );
					break;
				case 'term':
					$fields = Helper::get_term_fields( $data['q'] );
					break;
				default:
					// The method may not exist when get_fields is called by get_dsh_fields
					continue 2;
			}
			if ( ! empty( $fields ) ) {
				foreach ( $fields as $field_key => $field_name ) {
					$results[] = [
						'id' => $field_key,
						'text' => ( $data['object_type'] == 'any' ? '[' . esc_attr( $object_type ) . '] ' : '' ) . esc_attr( $field_name ),
					];
				}
			}
		}
		return $results;
	}

	protected function get_metas( $data ) {
		if ( ! current_user_can( Editor::EDITING_CAPABILITY ) ) {
			throw new \Exception( 'Access denied.' );
		}

		$results = [];
		switch ( $data['object_type'] ) {
			case 'post':
				$fields = Helper::get_post_metas( false, $data['q'] );
				break;
			case 'user':
				$fields = Helper::get_user_metas( false, $data['q'] );
				break;
			case 'term':
				$fields = Helper::get_term_metas( false, $data['q'] );
				break;
			default:
				throw new \Exception( 'Invalid object type.' );
		}
		foreach ( $fields as $field_key => $field_name ) {
			if ( $field_key ) {
				$results[] = [
					'id' => $field_key,
					'text' => esc_attr( $field_name ),
				];
			}
		}
		return $results;
	}

	protected function get_posts( $data ) {
		if ( ! current_user_can( Editor::EDITING_CAPABILITY ) ) {
			throw new \Exception( 'Access denied.' );
		}

		$results = [];
		$object_type = $data['object_type'] ?? 'any';

		$query_params = [
			'post_type' => $object_type,
			's' => $data['q'],
			'posts_per_page' => -1,
		];
		if ( 'attachment' === $query_params['post_type'] ) {
			$query_params['post_status'] = 'inherit';
		}
		$query = new \WP_Query( $query_params );
		foreach ( $query->posts as $post ) {
			$post_title = $post->post_title;
			if ( empty( $data['object_type'] ) || $object_type == 'any' ) {
				$post_title = '[' . $post->ID . '] ' . $post_title . ' (' . $post->post_type . ')';
			}
			if ( ! empty( $data['object_type'] ) && $object_type == 'elementor_library' ) {
				$etype = get_post_meta( $post->ID, '_elementor_template_type', true );
				$post_title = '[' . $post->ID . '] ' . $post_title . ' (' . $post->post_type . ' > ' . $etype . ')';
			}

			$results[] = [
				'id' => $post->ID,
				'text' => esc_html( $post_title ),
			];
		}

		return $results;
	}

	protected function get_terms( $data ) {
		if ( ! current_user_can( Editor::EDITING_CAPABILITY ) ) {
			throw new \Exception( 'Access denied.' );
		}

		$results = [];
		$taxonomies = ( ! empty( $data['object_type'] ) ) ? $data['object_type'] : get_object_taxonomies( '' );
		$query_params = [
			'taxonomy' => $taxonomies,
			'search' => $data['q'],
			'hide_empty' => false,
		];
		$terms = get_terms( $query_params );
		foreach ( $terms as $term ) {
			$term_name = $term->name;
			if ( empty( $data['object_type'] ) ) {
				$taxonomy = get_taxonomy( $term->taxonomy );
				$term_name = $taxonomy->labels->singular_name . ': ' . $term_name;
			}
			$results[] = [
				'id' => $term->term_id,
				'text' => esc_attr( $term_name ),
			];
		}
		return $results;
	}

	/**
	 * Get all user capabilities from all roles
	 *
	 * @param array<string,mixed> $data
	 * @return array<int,array<string,string>>
	 */
	protected function get_capabilities( $data ) {
		if ( ! current_user_can( Editor::EDITING_CAPABILITY ) ) {
			throw new \Exception( 'Access denied.' );
		}

		$all_capabilities = [];
		foreach ( wp_roles()->roles as $role ) {
			foreach ( array_keys( $role['capabilities'] ?? [] ) as $cap ) {
				$all_capabilities[ $cap ] = $cap;
			}
		}

		// Filter by search term
		if ( ! empty( $data['q'] ) ) {
			$all_capabilities = array_filter( $all_capabilities, function ( $cap ) use ( $data ) {
				return stripos( $cap, $data['q'] ) !== false;
			});
		}

		// Sort alphabetically
		ksort( $all_capabilities );

		return array_map(
			function ( $cap ) {
				return [
					'id' => $cap,
					'text' => esc_attr( $cap ),
				];
			},
			array_keys( $all_capabilities )
		);
	}

	/**
	 * @param array<string,mixed> $request
	 * @return array<string,mixed>
	 */
	protected function get_value_titles_for_metas( $request ) {
		if ( ! current_user_can( Editor::EDITING_CAPABILITY ) ) {
			throw new \Exception( 'Access denied.' );
		}

		$ids = (array) $request['id'];
		$results = [];

		switch ( $request['object_type'] ) {
			case 'post':
				$fields = Helper::get_post_metas( false, $ids[0] );
				break;
			case 'user':
				$fields = Helper::get_user_metas( false, $ids[0] );
				break;
			case 'term':
				$fields = Helper::get_term_metas( false, $ids[0] );
				break;
			default:
				return $results;
		}

		foreach ( $ids as $aid ) {
			foreach ( $fields as $field_key => $field_name ) {
				if ( in_array( $field_key, $ids ) ) {
					$results[ $field_key ] = $field_name;
				}
			}
		}

		return $results;
	}

	/**
	 * @param array<string,mixed> $request
	 * @return array<string,mixed>
	 */
	protected function get_value_titles_for_fields( $request ) {
		if ( ! current_user_can( Editor::EDITING_CAPABILITY ) ) {
			throw new \Exception( 'Access denied.' );
		}

		$ids = (array) $request['id'];
		$results = [];
		if ( $request['object_type'] == 'any' ) {
			$object_types = array( 'post', 'user', 'term' );
		} else {
			$object_types = array( $request['object_type'] );
		}

		foreach ( $object_types as $object_type ) {
			foreach ( $ids as $id ) {
				// Returns a value equal to the key
				$results[ $id ] = $id;
			}
		}
		return $results;
	}

	/**
	 * @param array<string,mixed> $request
	 * @return array<int|string,mixed>
	 */
	protected function get_value_titles_for_posts( $request ) {
		if ( ! current_user_can( Editor::EDITING_CAPABILITY ) ) {
			throw new \Exception( 'Access denied.' );
		}

		$ids = (array) $request['id'];
		$results = [];
		$is_ctp = false;
		if ( ! empty( $ids ) ) {
			$first = reset( $ids );
			$is_ctp = ! is_numeric( $first );
		}
		if ( $is_ctp ) {
			$post_types = Helper::get_public_post_types();
			if ( ! empty( $ids ) ) {
				foreach ( $ids as $aid ) {
					if ( isset( $post_types[ $aid ] ) ) {
						$results[ $aid ] = $post_types[ $aid ];
					}
				}
			}
		} else {
			foreach ( $ids as $id ) {
				if ( ! current_user_can( 'read_post', $id ) ) {
					continue;
				}
				$results[ $id ] = '[' . $id . '] ' . wp_kses_post( get_the_title( $id ) );
			}
		}
		return $results;
	}

	/**
	 * Get term titles based on term IDs or slugs
	 *
	 * @param array<string,mixed> $request Request data containing term identifiers
	 * @return array<int|string,mixed> Array of term IDs/names pairs
	 */
	protected function get_value_titles_for_terms( $request ) {
		if ( ! current_user_can( Editor::EDITING_CAPABILITY ) ) {
			throw new \Exception( 'Access denied.' );
		}

		$id = $request['id'];

		$ids = (array) $id;
		$results = [];

		foreach ( $ids as $term_id ) {
			if ( is_numeric( $term_id ) ) {
				// Search by numeric ID
				$term = get_term( (int) $term_id );
				if ( $term instanceof \WP_Term ) {
					$results[ $term->term_id ] = sanitize_text_field( $term->name );
				}
			} else {
				// Search by slug
				$terms = get_terms([
					'slug' => sanitize_text_field( $term_id ),
					'hide_empty' => false,
				]);

				if ( ! is_wp_error( $terms ) ) {
					foreach ( $terms as $term ) {
						$results[ $term->term_id ] = sanitize_text_field( $term->name );
					}
				}
			}
		}

		return $results;
	}

	/**
	 * Get capability titles by IDs
	 *
	 * @param array<string,mixed> $request
	 * @return array<string,string>
	 */
	protected function get_value_titles_for_capabilities( $request ) {
		if ( ! current_user_can( Editor::EDITING_CAPABILITY ) ) {
			throw new \Exception( 'Access denied.' );
		}

		$ids = (array) $request['id'];
		$results = [];

		foreach ( $ids as $cap ) {
			$results[ $cap ] = esc_attr( $cap );
		}

		return $results;
	}

	public function register_ajax_actions( $ajax_manager ) {
		$ajax_manager->register_ajax_action( 'dce_query_control_value_titles', [ $this, 'ajax_call_control_value_titles' ] );
		$ajax_manager->register_ajax_action( 'dce_query_control_filter_autocomplete', [ $this, 'ajax_call_filter_autocomplete' ] );
	}

}
