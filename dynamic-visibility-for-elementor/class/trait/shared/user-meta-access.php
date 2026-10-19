<?php

// SPDX-FileCopyrightText: 2018-2026 Ovation S.r.l. <help@dynamic.ooo>
// SPDX-License-Identifier: GPL-3.0-or-later
namespace DynamicVisibilityForElementor;

trait UserMetaAccess {

	/**
	 * Whether the name is a user meta key and not a column of the users table.
	 *
	 * @param string|null $meta_name
	 * @return boolean
	 */
	public static function is_user_meta( $meta_name = null ) {
		if ( ! $meta_name ) {
			return true;
		}
		$user_table_columns = array(
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
		return ! in_array( $meta_name, $user_table_columns, true );
	}
}
