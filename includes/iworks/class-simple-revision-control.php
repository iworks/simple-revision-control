<?php

/*

Copyright 2013-PLUGIN_TILL_YEAR Marcin Pietrzak (marcin@iworks.pl)

this program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( class_exists( 'iWorks_Simple_Revision_Control' ) ) {
	return;
}

class iWorks_Simple_Revision_Control {

	private $options;
	private $capability;
	private $dir;

	public function __construct() {
		/**
		 * static settings
		 */
		$this->dir        = basename( dirname( dirname( __FILE__ ) ) );
		$this->capability = apply_filters( 'simple_revision_control_capability', 'manage_options' );
		/**
		 * WordPress Hooks
		 */
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_filter( 'wp_revisions_to_keep', array( $this, 'wp_revisions_to_keep' ), PHP_INT_MAX, 2 );
		/**
		 * global option object
		 */
		$this->options = get_simple_revision_control_options();
		/**
		 * iWorks Rate Class
		 */
		add_filter( 'iworks_rate_notice_logo_style', array( $this, 'filter_plugin_logo' ), 10, 2 );
	}

	public function wp_revisions_to_keep( $num, $post ) {
		$revisions = $this->options->get_option( $post->post_type );
		if ( empty( $revisions ) ) {
			return $num;
		}
		return $revisions;
	}

	public function admin_init() {
		$this->options->options_init();
		add_filter( 'plugin_row_meta', array( &$this, 'plugin_row_meta' ), 10, 2 );
	}

	public function plugin_row_meta( $links, $file ) {
		if ( $this->dir . '/simple_revision_control.php' == $file ) {
			if ( ! is_multisite() && current_user_can( $this->capability ) ) {
				$links[] = '<a href="themes.php?page=' . $this->dir . '/admin/index.php">' . __( 'Settings' ) . '</a>';
			}
		}
		return $links;
	}

	/**
	 * Plugin logo for rate messages
	 *
	 * @since 1.3.5
	 *
	 * @param string $logo Logo, can be empty.
	 * @param object $plugin Plugin basic data.
	 */
	public function filter_plugin_logo( $logo, $plugin ) {
		if ( is_object( $plugin ) ) {
			$plugin = (array) $plugin;
		}
		if ( 'simple-revision-control' === $plugin['slug'] ) {
			return plugin_dir_url( dirname( dirname( __FILE__ ) ) ) . '/assets/images/logo.svg';
		}
		return $logo;
	}

}

