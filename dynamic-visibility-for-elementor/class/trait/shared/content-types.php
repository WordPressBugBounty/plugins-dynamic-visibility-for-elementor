<?php

// SPDX-FileCopyrightText: 2018-2026 Ovation S.r.l. <help@dynamic.ooo>
// SPDX-License-Identifier: GPL-3.0-or-later
namespace DynamicVisibilityForElementor;

trait ContentTypes {

	/**
	 * Get Public Post Types
	 *
	 * @param boolean $exclude
	 * @return array<string,string>
	 */
	public static function get_public_post_types( $exclude = true ) {
		$args = array(
			'public' => true,
		);

		$skip_post_types = [ 'attachment', 'elementor_library', 'oceanwp_library' ];

		/**
		 * @var array<string,string> $post_types
		 */
		$post_types = get_post_types( $args );
		if ( $exclude ) {
			$post_types = array_diff( $post_types, $skip_post_types );
		}

		if ( Helper::is_plugin_active( 'woocommerce' ) ) {
			$post_types['product_variation'] = 'product_variation';
		}

		foreach ( $post_types as $akey => $acpt ) {
			$cpt = get_post_type_object( $acpt );
			if ( $cpt !== null ) {
				$post_types[ $akey ] = (string) $cpt->label;
			} else {
				unset( $post_types[ $akey ] );
			}
		}

		return $post_types;
	}

	public static function get_taxonomies( $dynamic = false, $cpt = '', $search = '' ) {
		$args = array();
		$output = 'objects'; // or objects
		$operator = 'and'; // 'and' or 'or'
		$taxonomies = get_taxonomies( $args, $output, $operator );
		$listTax = [];
		if ( $dynamic ) {
			$listTax['dynamic'] = 'Dynamic';
		}
		if ( ! $cpt || $cpt == 'post' ) {
			$listTax['category'] = 'Categories posts (category)';
			$listTax['post_tag'] = 'Tags posts (post_tag)';
		}
		if ( $taxonomies ) {
			foreach ( $taxonomies as $taxonomy ) {
				if ( $taxonomy->name == 'elementor_library_category' || $taxonomy->name == 'elementor_font_type' || $taxonomy->name == 'nav_menu' || $taxonomy->name == 'link_category' ) {
					continue;
				}
				if ( ! $cpt || in_array( $cpt, $taxonomy->object_type ) ) {
					$listTax[ $taxonomy->name ] = $taxonomy->label . ' (' . $taxonomy->name . ')';
				}
			}
		}

		if ( ! empty( $search ) ) {
			$tmp = array();
			foreach ( $listTax as $tkey => $atax ) {
				$pos_key = stripos( $tkey, $search );
				$pos_name = stripos( $atax, $search );
				if ( $pos_key !== false || $pos_name !== false ) {
					$tmp[ $tkey ] = $atax;
				}
			}
			$listTax = $tmp;
		}

		return $listTax;
	}

	public static function get_taxonomy_terms( $taxonomy = null, $flat = false, $search = '', $info = true, $orderby = 'name', $order = 'ASC' ) {
		$listTerms = [];
		$flatTerms = [];
		$listTerms[''] = 'None';
		$args = array(
			'taxonomy' => $taxonomy,
			'hide_empty' => false,
			'orderby' => $orderby,
			'order' => $order,
		);
		if ( $search ) {
			$args['name__like'] = $search;
		}
		if ( $taxonomy ) {
			$terms = get_terms( $args );
			if ( ! empty( $terms ) ) {
				foreach ( $terms as $aterm ) {
					if ( $info ) {
						$listTerms[ $aterm->term_id ] = $aterm->name . ' (' . $aterm->slug . ')';
					} else {
						$listTerms[ $aterm->term_id ] = $aterm->name;
					}
				}
				$flatTerms = $listTerms;
			}
		} else {
			$taxonomies = self::get_taxonomies();
			foreach ( $taxonomies as $tkey => $atax ) {
				if ( $tkey ) {
					$args['taxonomy'] = $tkey;
					$terms = get_terms( $args );
					if ( ! empty( $terms ) ) {
						$tmp = [];
						$tmp['label'] = $atax;
						foreach ( $terms as $aterm ) {
							$term_name = $aterm->name;
							if ( $info ) {
								$term_name .= ' (' . $aterm->slug . ')';
							}
							$tmp['options'][ $aterm->term_id ] = $term_name;
							$flatTerms[ $aterm->term_id ] = $atax . ' > ' . $term_name;
						}
						$listTerms[] = $tmp;
					}
				}
			}
		}
		if ( $flat ) {
			return $flatTerms;
		}
		return $listTerms;
	}

	public static function get_term_by( $field = 'id', $value = 1, $taxonomy = '' ) {
		if ( $field == 'id' || $field == 'term_id' ) {
			$term = get_term( $value );
		} else {
			$term = get_term_by( $field, $value, $taxonomy );
		}
		return $term;
	}
}
