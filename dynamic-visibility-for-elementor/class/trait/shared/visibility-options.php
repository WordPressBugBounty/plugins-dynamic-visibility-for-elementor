<?php

// SPDX-FileCopyrightText: 2018-2026 Ovation S.r.l. <help@dynamic.ooo>
// SPDX-License-Identifier: GPL-3.0-or-later
namespace DynamicVisibilityForElementor;

trait VisibilityOptions {

	/**
	 * @return array<string,string>
	 */
	public static function get_post_formats() {
		return [
			'standard' => esc_html__( 'Standard', 'dynamic-visibility-for-elementor' ),
			'aside' => esc_html__( 'Aside', 'dynamic-visibility-for-elementor' ),
			'chat' => esc_html__( 'Chat', 'dynamic-visibility-for-elementor' ),
			'gallery' => esc_html__( 'Gallery', 'dynamic-visibility-for-elementor' ),
			'link' => esc_html__( 'Link', 'dynamic-visibility-for-elementor' ),
			'image' => esc_html__( 'Image', 'dynamic-visibility-for-elementor' ),
			'quote' => esc_html__( 'Quote', 'dynamic-visibility-for-elementor' ),
			'status' => esc_html__( 'Status', 'dynamic-visibility-for-elementor' ),
			'video' => esc_html__( 'Video', 'dynamic-visibility-for-elementor' ),
			'audio' => esc_html__( 'Audio', 'dynamic-visibility-for-elementor' ),
		];
	}

	/**
	 * @return array<string,string>
	 */
	public static function get_jquery_display_mode() {
		return [
			'' => esc_html__( 'None', 'dynamic-visibility-for-elementor' ),
			'slide' => esc_html__( 'Slide', 'dynamic-visibility-for-elementor' ),
			'fade' => esc_html__( 'Fade', 'dynamic-visibility-for-elementor' ),
		];
	}
}
