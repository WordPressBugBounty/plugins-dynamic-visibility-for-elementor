<?php

// SPDX-FileCopyrightText: 2018-2026 Ovation S.r.l. <help@dynamic.ooo>
// SPDX-License-Identifier: GPL-3.0-or-later
namespace DynamicVisibilityForElementor;

final class UserFieldPolicy {
	const NOT_ALLOWED_USER_FIELDS = [
		'user_login',
		'login',
		'user_pass',
		'pass',
		'user_email',
		'email',
		'user_registered',
		'registered',
		'user_activation_key',
		'activation_key',
		'user_status',
		'status',
	];

	const SECRET_USER_FIELDS = [
		'user_pass',
		'pass',
		'user_activation_key',
		'activation_key',
	];

	/**
	 * Whether a user meta key holds sessions, credentials or capabilities.
	 *
	 * @param mixed $key
	 * @return bool
	 */
	public static function is_protected_user_meta( $key ) {
		global $wpdb;
		if ( ! is_string( $key ) ) {
			return false;
		}
		// Meta keys are matched case-insensitively by the database.
		$key = strtolower( $key );
		if ( in_array( $key, [ 'session_tokens', 'default_password_nonce', '_new_email', '_application_passwords' ], true ) ) {
			return true;
		}
		// Capabilities and levels of every site of the network.
		return 0 === strpos( $key, strtolower( $wpdb->base_prefix ) ) && 1 === preg_match( '/(?:capabilities|user_level)$/D', $key );
	}
}
