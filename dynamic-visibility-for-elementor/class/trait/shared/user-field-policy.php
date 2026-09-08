<?php

// SPDX-FileCopyrightText: 2018-2026 Ovation S.r.l. <help@dynamic.ooo>
// SPDX-License-Identifier: GPL-3.0-or-later
namespace DynamicVisibilityForElementor;

final class UserFieldPolicy {
	const NOT_ALLOWED_USER_FIELDS = [
		'user_login', 'login', 'user_pass', 'pass', 'user_email', 'email',
		'user_registered', 'registered', 'user_activation_key', 'activation_key',
		'user_status', 'status',
	];
}
