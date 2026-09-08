<?php

// SPDX-FileCopyrightText: 2018-2026 Ovation S.r.l. <help@dynamic.ooo>
// SPDX-License-Identifier: GPL-3.0-or-later
namespace DynamicVisibilityForElementor;

trait PostValues {

	public static function get_post_value( $post_id = null, $field = 'ID', $sub_field = '', $single = null ) {
		$postValue = null;

		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}

		$post_id = apply_filters( 'wpml_object_id', $post_id, get_post_type( $post_id ), true );

		if ( $field == 'permalink' || $field == 'get_permalink' ) {
			$postValue = get_permalink( $post_id );
		}

		if ( $field == 'post_excerpt' || $field == 'excerpt' ) {
			$post = get_post( $post_id );
			if ( $post ) {
				$postValue = $post->post_excerpt;
			}
		}

		if ( $field == 'the_author' || $field == 'post_author' || $field == 'author' ) {
			$postValue = get_the_author();
		}

		if ( in_array( $field, array( 'thumbnail', 'post_thumbnail', 'thumb' ) ) ) {
			$postValue = get_the_post_thumbnail();
		}

		if ( $postValue === null ) {
			if ( property_exists( 'WP_Post', $field ) ) {
				$postTmp = get_post( $post_id );
				$postValue = $postTmp->{$field};
			}
		}
		if ( $postValue === null ) {
			if ( property_exists( 'WP_Post', 'post_' . $field ) ) {
				$postTmp = get_post( $post_id );
				if ( $postTmp ) {
					$postValue = $postTmp->{'post_' . $field};
				}
			}
		}
		if ( $postValue === null || ! $single ) {
			if ( metadata_exists( 'post', $post_id, $field ) ) {
				$postValue = get_post_meta( $post_id, $field, $single );
			}
		}

		if ( $postValue === null ) { // for meta created with Toolset plugin
			if ( metadata_exists( 'post', $post_id, 'wpcf-' . $field ) ) {
				$postValue = get_post_meta( $post_id, 'wpcf-' . $field, $single );
			}
		}

		if ( $postValue === null ) { // for meta WooCommerce plugin
			if ( metadata_exists( 'post', $post_id, '_' . $field ) ) {
				$postValue = get_post_meta( $post_id, '_' . $field, $single );
			}
		}

		if ( $postValue === null ) {
			$postValue = array();
			$post_terms = get_the_terms( $post_id, $field );
			if ( ! empty( $post_terms ) && ! is_wp_error( $post_terms ) ) {
				foreach ( $post_terms as $key => $aterm ) {
					$postValue[ $aterm->term_id ] = $aterm;
				}
			} else {
				// WooCommerce taxonomies (Attributes) begin with pa_
				$post_terms = get_the_terms( $post_id, 'pa_' . $field );
				if ( ! empty( $post_terms ) && ! is_wp_error( $post_terms ) ) {
					foreach ( $post_terms as $key => $aterm ) {
						$postValue[ $aterm->term_id ] = $aterm;
					}
				}
			}
		}

		if ( is_array( $postValue ) ) {
			if ( empty( $postValue ) ) {
				return '';
			}
			if ( $single === true || count( $postValue ) == 1 ) {
				return reset( $postValue );
			}
		}

		return $postValue;
	}

	public static function get_post_id_from_url( $url = '' ) {
		if ( ! $url ) {
			global $wp;
			$url = home_url( add_query_arg( array(), $wp->request ) );
		}
		return url_to_postid( $url );
	}
}
