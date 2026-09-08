<?php

// SPDX-FileCopyrightText: 2018-2026 Ovation S.r.l. <help@dynamic.ooo>
// SPDX-License-Identifier: GPL-3.0-or-later
namespace DynamicVisibilityForElementor;

trait MetaKeys {

	public static function get_acf_group_locations( $aacf_group ) {
		$locations = array();
		if ( is_string( $aacf_group ) ) {
			$acf_groups = get_posts(array(
				'post_type' => 'acf-field-group',
				'post_excerpt' => $aacf_group,
				'numberposts' => -1,
				'post_status' => 'publish',
				'suppress_filters' => false,
			));
			if ( ! empty( $acf_groups ) ) {
				$aacf_group = reset( $acf_groups );
			} else {
				return false;
			}
		}
		$aacf_meta = maybe_unserialize( $aacf_group->post_content );
		if ( ! empty( $aacf_meta['location'] ) ) {
			foreach ( $aacf_meta['location'] as $gkey => $gvalue ) {
				foreach ( $gvalue as $rkey => $rvalue ) {
					$pieces = explode( '_', $rvalue['param'] );
					$location = reset( $pieces );
					$locations[ $location ] = $location;

					if ( $location == 'page' ) {
						$locations['post'] = 'post';
					}
					if ( $location == 'current' ) {
						$locations['user'] = 'user';
					}
				}
			}
		}
		return $locations;
	}

	public static function get_user_metas( $grouped = false, $like = '', $info = true ) {
		$user_metas_grouped = array();
		$user_metas = $user_metas_grouped;

		// ACF
		if ( Helper::is_plugin_active( 'acf' ) ) {
			$acf_groups = acf_get_field_groups();
			if ( ! empty( $acf_groups ) ) {
				foreach ( $acf_groups as $group ) {
					$locations = self::get_acf_group_locations( $group['key'] );
					if ( in_array( 'user', $locations ) ) {
						$fields = acf_get_fields( $group['key'] );
						if ( ! empty( $fields ) ) {
							foreach ( $fields as $field ) {
								if ( $like ) {
									$pos_key = stripos( $field['name'], $like );
									$pos_label = stripos( $field['label'], $like );
									if ( $pos_key === false && $pos_label === false ) {
										continue;
									}
								}
								$field_name = $field['label'];
								if ( $info ) {
									$field_name .= ' [' . $field['type'] . ']';
								}
								$user_metas[ $field['name'] ] = $field_name;
								$user_metas_grouped['ACF'][ $field['name'] ] = $field_name;
							}
						}
					}
				}
			}
		}

		// Standard WordPress user meta + current user custom meta
		$standard_user_meta = [
			'first_name',
			'last_name',
			'nickname',
			'description',
			'locale',
			'avatar',
			'syntax_highlighting',
			'rich_editing',
			'show_admin_bar_front',
			'admin_color',
			'comment_shortcuts',
			'use_ssl',
		];

		foreach ( $standard_user_meta as $meta_key ) {
			if ( $like && stripos( $meta_key, $like ) === false ) {
				continue;
			}
			if ( ! isset( $user_metas[ $meta_key ] ) ) {
				$user_metas[ $meta_key ] = $meta_key;
				$user_metas_grouped['META'][ $meta_key ] = $meta_key;
			}
		}

		$user_id = get_current_user_id();
		if ( $user_id ) {
			$user_meta = get_user_meta( $user_id );
			if ( is_array( $user_meta ) ) {
				$meta_keys = array_keys( $user_meta );
				sort( $meta_keys );
				foreach ( $meta_keys as $meta_key ) {
					if ( $like && stripos( $meta_key, $like ) === false ) {
						continue;
					}
					if ( ! isset( $user_metas[ $meta_key ] ) ) {
						$user_metas[ $meta_key ] = $meta_key;
						$user_metas_grouped['META'][ $meta_key ] = $meta_key;
					}
				}
			}
		}

		if ( $grouped ) {
			return $user_metas_grouped;
		}

		return $user_metas;
	}

	public static function get_term_metas( $grouped = false, $like = '' ) {
		$termMetasGrouped = array();
		$termMetas = $termMetasGrouped;

		// ACF
		$acf_groups = get_posts(array(
			'post_type' => 'acf-field-group',
			'numberposts' => -1,
			'post_status' => 'publish',
			'suppress_filters' => false,
		));
		if ( ! empty( $acf_groups ) ) {
			foreach ( $acf_groups as $aacf_group ) {
				$is_term_group = in_array( 'taxonomy', self::get_acf_group_locations( $aacf_group ) );
				$aacf_meta = maybe_unserialize( $aacf_group->post_content );
				if ( $is_term_group ) {
					$acf = get_posts(array(
						'post_type' => 'acf-field',
						'numberposts' => -1,
						'post_status' => 'publish',
						'post_parent' => $aacf_group->ID,
						'suppress_filters' => false,
					));
					if ( ! empty( $acf ) ) {
						foreach ( $acf as $aacf ) {
							$aacf_meta = maybe_unserialize( $aacf->post_content );
							if ( $like ) {
								$pos_key = stripos( $aacf->post_excerpt, $like );
								$pos_name = stripos( $aacf->post_title, $like );
								if ( $pos_key === false && $pos_name === false ) {
									continue;
								}
							}
							$field_name = $aacf->post_title;
							$termMetas[ $aacf->post_excerpt ] = $field_name;
							$termMetasGrouped['ACF'][ $aacf->post_excerpt ] = $termMetas[ $aacf->post_excerpt ];
						}
					}
				}
			}
		}

		// MANUAL
		global $wpdb;
		$query = $wpdb->prepare( "SELECT DISTINCT meta_key FROM {$wpdb->termmeta} WHERE meta_key LIKE %s", '%' . $wpdb->esc_like( $like ) . '%' );
		$results = $wpdb->get_results( $query );
		if ( ! empty( $results ) ) {
			$metas = array();
			foreach ( $results as $key => $aterm ) {
				$metas[ $aterm->meta_key ] = $aterm->meta_key;
			}
			ksort( $metas );
			$manual_metas = $metas;
			foreach ( $manual_metas as $ameta ) {
				$termMetas[ $ameta ] = $ameta;
				$termMetasGrouped['META'][ $ameta ] = $ameta;
			}
		}

		if ( $grouped ) {
			return $termMetasGrouped;
		}

		return $termMetas;
	}

	public static function get_post_metas( $grouped = false, $like = '', $info = true ) {
		$postMetasGrouped = array();
		$postMetas = $postMetasGrouped;

		// REGISTERED in FUNCTION
		$cpts = self::get_public_post_types();
		foreach ( $cpts as $ckey => $cvalue ) {
			$cpt_metas = get_registered_meta_keys( $ckey );
			if ( ! empty( $cpt_metas ) ) {
				foreach ( $cpt_metas as $fkey => $actpmeta ) {
					if ( $like ) {
						$pos_key = stripos( $fkey, $like );
						if ( $pos_key === false ) {
							continue;
						}
					}
					$field_name = $fkey;
					if ( $info ) {
						$field_name .= ' [' . $actpmeta['type'] . ']';
					}
					$postMetas[ $fkey ] = $field_name;
					$postMetasGrouped[ 'CPT_' . $ckey ][ $fkey ] = $field_name;
				}
			}
		}

		// ACF
		if ( self::is_plugin_active( 'acf' ) ) {
			// ACF
			$acf_groups = get_posts(array(
				'post_type' => 'acf-field-group',
				'numberposts' => -1,
				'post_status' => 'publish',
				'suppress_filters' => false,
			));
			if ( ! empty( $acf_groups ) ) {
				foreach ( $acf_groups as $aacf_group ) {
					$is_post_group = in_array( 'post', self::get_acf_group_locations( $aacf_group ) );
					$aacf_meta = maybe_unserialize( $aacf_group->post_content );
					if ( $is_post_group ) {
						$acf = get_posts(array(
							'post_type' => 'acf-field',
							'numberposts' => -1,
							'post_status' => 'publish',
							'post_parent' => $aacf_group->ID,
							'suppress_filters' => false,
						));
						if ( ! empty( $acf ) ) {
							foreach ( $acf as $aacf ) {
								$aacf_meta = maybe_unserialize( $aacf->post_content );
								if ( $like ) {
									$pos_key = stripos( $aacf->post_excerpt, $like );
									$pos_name = stripos( $aacf->post_title, $like );
									if ( $pos_key === false && $pos_name === false ) {
										continue;
									}
								}
								$field_name = $aacf->post_title;
								if ( $info ) {
									$field_name .= ' [' . $aacf_meta['type'] . ']';
								}
								$postMetas[ $aacf->post_excerpt ] = $field_name;
								$postMetasGrouped['ACF'][ $aacf->post_excerpt ] = $postMetas[ $aacf->post_excerpt ];
							}
						}
					}
				}
			}
		}

		// PODS
		if ( self::is_plugin_active( 'pods' ) ) {
			$pods = get_posts(array(
				'post_type' => '_pods_field',
				'numberposts' => -1,
				'post_status' => 'publish',
				'suppress_filters' => false,
			));
			if ( ! empty( $pods ) ) {
				foreach ( $pods as $apod ) {
					$type = get_post_meta( $apod->ID, 'type', true );
					$field_name = $apod->post_title;
					if ( $info ) {
						$field_name .= ' [' . $type . ']';
					}
					$postMetas[ $apod->post_name ] = $field_name;
					$postMetasGrouped['PODS'][ $apod->post_name ] = $postMetas[ $apod->post_name ];
				}
			}
		}

		// TOOLSET
		if ( self::is_plugin_active( 'wpcf' ) ) {
			$toolset = get_option( 'wpcf-fields', false );
			if ( $toolset ) {
				$toolfields = maybe_unserialize( $toolset );
				if ( ! empty( $toolfields ) ) {
					foreach ( $toolfields as $atool ) {
						$field_name = $atool['name'];
						if ( $info ) {
							$field_name .= ' [' . $atool['type'] . ']';
						}
						$postMetas[ $atool['meta_key'] ] = $field_name;
						$postMetasGrouped['TOOLSET'][ $atool['meta_key'] ] = $postMetas[ $atool['meta_key'] ];
					}
				}
			}
		}

		// MANUAL
		global $wpdb;
		$query = 'SELECT DISTINCT meta_key FROM ' . $wpdb->prefix . 'postmeta';
		if ( $like ) {
			$query .= ' WHERE meta_key LIKE %s';
			$prepared_query = $wpdb->prepare( $query, '%' . $wpdb->esc_like( $like ) . '%' );
		} else {
			$prepared_query = $query;
		}
		$results = $wpdb->get_results( $prepared_query );

		if ( ! empty( $results ) ) {
			$metas = array();
			foreach ( $results as $key => $apost ) {
				$metas[ $apost->meta_key ] = $apost->meta_key;
			}
			ksort( $metas );
			$manual_metas = array_diff_key( $metas, $postMetas );
			foreach ( $manual_metas as $ameta ) {
				if ( substr( $ameta, 0, 8 ) == '_oembed_' ) {
					continue;
				}
				if ( ! isset( $postMetas[ $ameta ] ) ) {
					$postMetas[ $ameta ] = $ameta;
					$postMetasGrouped['NATIVE'][ $ameta ] = $ameta;
				}
			}
		}

		if ( $grouped ) {
			return $postMetasGrouped;
		}

		return $postMetas;
	}
}
