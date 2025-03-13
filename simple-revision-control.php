<?php
/**
 * PLUGIN_TITLE
 *
 * @package           PLUGIN_NAME
 * @author            AUTHOR_NAME
 * @copyright         2013-PLUGIN_TILL_YEAR Marcin Pietrzak (marcin@iworks.pl)
 * @license           GPL-3.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Simple Revision Control
 * Plugin URI:        PLUGIN_URI
 * Description:       PLUGIN_DESCRIPTION
 * Version:           PLUGIN_VERSION
 * Requires at least: PLUGIN_REQUIRES_WORDPRESS
 * Requires PHP:      PLUGIN_REQUIRES_PHP
 * Author:            AUTHOR_NAME
 * Author URI:        AUTHOR_URI
 * Text Domain:       simple-revision-control
 * License:           GPLv3 or later
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt

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
defined( 'ABSPATH' ) || exit; // Exit if accessed directly
/**
 * static options
 */
define( 'SIMPLE_REVISION_CONTROL_VERSION', 'PLUGIN_VERSION' );
define( 'SIMPLE_REVISION_CONTROL_PREFIX', 'simple_revision_control_' );

// require_once dirname(__FILE__).'/includes/common.php';

/**
 * static options
 */
$base     = dirname( __FILE__ );
$includes = $base . '/includes';

/**
 * get plugin settings
 *
 * @since 1.0.1
 */
include_once $base . '/etc/options.php';

/**
 * @since 1.0.6
 */
if ( ! class_exists( 'iworks_options' ) ) {
	include_once $includes . '/iworks/options/options.php';
}

/**
 * load
 */
require_once $includes . '/iworks/simple-revision-control/class-simple-revision-control.php';

/**
 * run
 */
new iWorks_Simple_Revision_Control;


/**
 * load options
 *
 * since 2.6.8
 *
 */
global $simple_revision_control_options;
$simple_revision_control_options = null;

function get_simple_revision_control_options() {
	global $simple_revision_control_options;
	if ( is_object( $simple_revision_control_options ) ) {
		return $simple_revision_control_options;
	}
	$simple_revision_control_options = new iworks_options();
	$simple_revision_control_options->set_option_function_name( 'simple_revision_control_options' );
	$simple_revision_control_options->set_option_prefix( 'simple_revision_control_' );
	if ( method_exists( $simple_revision_control_options, 'set_plugin' ) ) {
		$simple_revision_control_options->set_plugin( basename( __FILE__ ) );
	}
	return $simple_revision_control_options;
}

