<?php

// SPDX-FileCopyrightText: 2018-2026 Ovation S.r.l. <help@dynamic.ooo>
// SPDX-License-Identifier: GPL-3.0-or-later
namespace DynamicVisibilityForElementor;

trait ObjectFields {

	public static function get_post_fields( $meta = false, $group = false, $info = true ) {
		$postFieldsKey = array();
		$postTmp = get_post();
		if ( $postTmp ) {
			$postProp = array();
			$postPropAll = get_object_vars( $postTmp );
			if ( ! empty( $meta ) && is_string( $meta ) ) {
				foreach ( $postPropAll as $key => $value ) {
					$pos_key = stripos( $value, $meta );
					$pos_name = stripos( $key, $meta );
					if ( $pos_key === false && $pos_name === false ) {
						continue;
					}
					$postProp[ $key ] = $value;
				}
			} else {
				$postProp = $postPropAll;
			}

			if ( $meta ) {
				$metas = self::get_post_metas( $group, ( is_string( $meta ) ) ? $meta : null, $info );
				$postFieldsKey = $metas;
			}

			$postFields = array_keys( $postProp );
			if ( ! empty( $postFields ) ) {
				foreach ( $postFields as $value ) {
					$name = str_replace( 'post_', '', $value );
					$name = str_replace( '_', ' ', $name );
					$name = ucwords( $name );
					if ( $info ) {
						$name .= ' (' . $value . ')';
					}
					if ( $group ) {
						$postFieldsKey['POST'][ $value ] = $name;
					} else {
						$postFieldsKey[ $value ] = $name;
					}
				}
				if ( $group ) {
					$postFieldsKey = array_merge( [ 'POST' => $postFieldsKey['POST'] ], $postFieldsKey ); // in first position
				}
			}
		}
		return $postFieldsKey;
	}

	public static function get_term_fields( $meta = false, $group = false, $info = true ) {
		$termFieldsKey = array();
		$termTmp = self::get_term_by( 'id', 1, 'category' );
		if ( $termTmp ) {
			$termPropAll = get_object_vars( $termTmp );
			if ( ! empty( $meta ) && is_string( $meta ) ) {
				$termProp = array();
				foreach ( $termPropAll as $key => $value ) {
					$pos_key = stripos( $value, $meta );
					$pos_name = stripos( $key, $meta );
					if ( $pos_key === false && $pos_name === false ) {
						continue;
					}
					$termProp[ $key ] = $value;
				}
			} else {
				$termProp = $termPropAll;
			}

			if ( $meta ) {
				$metas = self::get_term_metas( $group, ( is_string( $meta ) ) ? $meta : null );
				$termFieldsKey = $metas;
			}

			$termFields = array_keys( $termProp );
			if ( ! empty( $termFields ) ) {
				foreach ( $termFields as $value ) {
					$name = str_replace( 'term_', '', $value );
					$name = str_replace( '_', ' ', $name );
					$name = ucwords( $name );
					if ( $group ) {
						$termFieldsKey['TERM'][ $value ] = $name;
					} else {
						$termFieldsKey[ $value ] = $name;
					}
				}
			}

			if ( $group && isset( $termFieldsKey['TERM'] ) ) {
				$termFieldsKey = array_merge( [ 'TERM' => $termFieldsKey['TERM'] ], $termFieldsKey ); // in first position
			}
		}
		return $termFieldsKey;
	}

	public static function get_user_fields( $meta = false, $group = false, $info = true ) {
		$userFieldsKey = array();
		$userTmp = wp_get_current_user();

		$blacklist_user_fields = self::NOT_ALLOWED_USER_FIELDS;

		if ( ! $userTmp ) {
			return array();
		}

		$userProp = get_object_vars( $userTmp );
		if ( ! empty( $userProp['data'] ) ) {
			$userPropAll = (array) $userProp['data'];
			$userProp = array();
			if ( ! empty( $meta ) && is_string( $meta ) ) {
				foreach ( $userPropAll as $key => $value ) {
					if ( ! is_string( $value ) ) {
						continue;
					}
					$pos_key = stripos( $value, $meta );
					$pos_name = stripos( $key, $meta );
					if ( $pos_key === false && $pos_name === false ) {
						continue;
					}
					$userProp[ $key ] = $value;
				}
			} else {
				$userProp = $userPropAll;
			}
		}

		if ( $meta ) {
			$metas = self::get_user_metas( $group, ( is_string( $meta ) ) ? $meta : null, $info );
			$userFieldsKey = $metas;
		}

		$userFields = array_keys( $userProp );
		if ( ! empty( $userFields ) ) {
			foreach ( $userFields as $value ) {
				if ( in_array( $value, $blacklist_user_fields ) ) {
					continue;
				}
				$name = str_replace( 'user_', '', $value );
				$name = str_replace( '_', ' ', $name );
				$name = ucwords( $name ) . ' (' . $value . ')';
				if ( $group ) {
					$userFieldsKey['USER'][ $value ] = $name;
				} else {
					$userFieldsKey[ $value ] = $name;
				}
			}
		}

		$pos_key = is_string( $meta ) ? stripos( 'avatar', $meta ) : false;
		if ( empty( $meta ) || ! is_string( $meta ) || $pos_key !== false ) {
			if ( $group ) {
				$userFieldsKey['USER']['avatar'] = 'Avatar';
			} else {
				$userFieldsKey['avatar'] = 'Avatar';
			}
		}

		if ( $group ) {
			$userFieldsKey = array_merge( [ 'USER' => $userFieldsKey['USER'] ], $userFieldsKey ); // in first position
		}

		return $userFieldsKey;
	}
}
