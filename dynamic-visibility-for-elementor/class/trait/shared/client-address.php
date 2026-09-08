<?php

// SPDX-FileCopyrightText: 2018-2026 Ovation S.r.l. <help@dynamic.ooo>
// SPDX-License-Identifier: GPL-3.0-or-later
namespace DynamicVisibilityForElementor;

trait ClientAddress {

	/**
	 * Get Client IP Address
	 *
	 * Determines the real IP address of the client, accounting for proxies,
	 * load balancers, CDNs, and other network configurations.
	 *
	 * SPDX-FileCopyrightText: 2016-2025 Elementor Team <developers@elementor.com>
	 * SPDX-License-Identifier: GPL-3.0-or-later
	 * 
	 * This function is based on ElementorPro\Core\Utils::get_client_ip()
	 * from Elementor Pro v3.29.0 and adapted for use in Dynamic Content 
	 * for Elementor on 2025-07-11 to avoid dependency.
	 *
	 * @return string The client's IP address
	 */
	public static function get_client_ip() {
		$remote_addr = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

		/**
		 * Trusted reverse-proxy IPs. Forwarded headers (X-Forwarded-For, etc.) are
		 * read only when the request comes from one of these proxies; empty by default.
		 * Behind a CDN/proxy, add your edge IPs:
		 *   add_filter( 'dce/visibility/trusted_proxies', fn() => [ '10.0.0.1' ] );
		 *
		 * @param string[] $trusted_proxies
		 */
		$trusted_proxies = (array) apply_filters( 'dce/visibility/trusted_proxies', [] );

		if ( $trusted_proxies && in_array( $remote_addr, $trusted_proxies, true ) ) {
			if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
				$chain = array_map( 'trim', explode( ',', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) ) );
				for ( $i = count( $chain ) - 1; $i >= 0; $i-- ) {
					if ( filter_var( $chain[ $i ], FILTER_VALIDATE_IP ) && ! in_array( $chain[ $i ], $trusted_proxies, true ) ) {
						return $chain[ $i ];
					}
				}
			}
			foreach ( [ 'HTTP_CLIENT_IP', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED' ] as $key ) {
				if ( ! empty( $_SERVER[ $key ] ) ) {
					$value = sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) );
					if ( filter_var( $value, FILTER_VALIDATE_IP ) ) {
						return $value;
					}
				}
			}
		}

		if ( filter_var( $remote_addr, FILTER_VALIDATE_IP ) ) {
			return $remote_addr;
		}

		// Fallback local ip.
		return '127.0.0.1';
	}
}
