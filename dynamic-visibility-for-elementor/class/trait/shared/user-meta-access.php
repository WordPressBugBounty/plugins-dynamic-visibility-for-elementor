<?php

// SPDX-FileCopyrightText: 2018-2026 Ovation S.r.l. <help@dynamic.ooo>
// SPDX-License-Identifier: GPL-3.0-or-later
namespace DynamicVisibilityForElementor;

trait UserMetaAccess {

	public static function is_validated_user_meta( $meta_name = null ) {
		if ( ! $meta_name ) {
			return true;
		}
		$not_allowed = array(
			'ID',
			'user_login',
			'user_pass',
			'user_nicename',
			'user_email',
			'user_url',
			'user_registered',
			'user_activation_key',
			'user_status',
			'display_name',
		);
		if ( in_array( $meta_name, $not_allowed, true ) ) {
			return false;
		}
		return true;
	}
}
