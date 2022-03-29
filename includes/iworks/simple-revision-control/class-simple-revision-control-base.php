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

if ( class_exists( 'iWorks_Simple_Revision_Control_Base' ) ) {
	return;
}

abstract class iWorks_Simple_Revision_Control_Base {

	protected $options;
	protected $capability;
	protected $dir;
	protected $base;

	/**
	 * DB VERSION
	 */
	protected $db_version = 210;

	/**
	 * Plugin version
	 */
	protected $version = 'PLUGIN_VERSION';

	/**
	 * debug
	 */
	protected $debug = false;

	/**
	 * delete revision action name
	 *
	 * @since 2.1.0
	 */
	protected $delete_revision_action_name = 'src_delete_revisions';

	protected function __construct() {
		/**
		 * static settings
		 */
		$this->base       = dirname( dirname( dirname( __FILE__ ) ) );
		$this->dir        = basename( dirname( $this->base ) );
		$this->capability = apply_filters( 'simple_revision_control_capability', 'manage_options' );
		$this->debug      = defined( 'WP_DEBUG' ) && WP_DEBUG;
		/**
		 * global option object
		 */
		$this->options = get_simple_revision_control_options();
	}

}

