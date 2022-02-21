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
		$this->dir        = basename( dirname( dirname( dirname( __FILE__ ) ) ) );
		$this->capability = apply_filters( 'simple_revision_control_capability', 'manage_options' );
		/**
		 * WordPress Hooks
		 */
		add_action( 'init', array( $this, 'change_post_type_revision_support' ), PHP_INT_MAX );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_filter( 'wp_revisions_to_keep', array( $this, 'wp_revisions_to_keep' ), PHP_INT_MAX, 2 );
		/**
		 * Plugin Hooks
		 */
		add_filter( 'iworks_plugin_get_options', array( $this, 'filter_add_post_types_options' ), 10, 2 );
		add_filter( 'simple_revision_control_utilization', array( $this, 'filter_get_utilization' ) );
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
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
	}

	public function plugin_row_meta( $links, $file ) {
		if ( $this->dir . '/simple-revision-control.php' == $file ) {
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

	/**
	 * Filter options for custom added post types
	 */
	public function filter_add_post_types_options( $options, $plugin ) {
		if ( 'simple-revision-control' !== $plugin ) {
			return $options;
		}
		$custom_post_types = get_post_types( array( '_builtin' => false ), 'object' );
		if ( empty( $custom_post_types ) ) {
			return $options;
		}
		$opts = array();
		foreach ( $custom_post_types as $name => $post_type ) {
			if ( ! $post_type->public ) {
				continue;
			}
			$default = 'unlimited';
			if ( ! post_type_supports( $name, 'revisions' ) ) {
				$default = 'off';
			}
			$opts[] = array(
				'name'      => $name . '_mode',
				'type'      => 'radio',
				'th'        => $post_type->label,
				'default'   => $default,
				'options'   => array(
					'unlimited' => array(
						'label' => __( 'Unlimited revisions', 'simple-revision-control' ),
					),
					'off'       => array(
						'label' => __( 'No revisions', 'simple-revision-control' ),
					),
					'custom'    => array(
						'label' => __( 'Custom number of revisions', 'simple-revision-control' ),
					),
				),
				'group'     => 'post_type_mode',
				'post_type' => $name,
			);
			$opts[] = array(
				'name'        => $name,
				'class'       => 'small-text',
				'type'        => 'number',
				'min'         => 1,
				'default'     => 3,
				'description' => $post_type->description,
				'group'       => 'post_type',
			);
		}
		if ( empty( $opts ) ) {
			return $options;
		}
		$o = array_chunk( $options['index']['options'], 5 );
		$n = array_shift( $o );
		$n = array_merge( $n, $opts );
		while ( $x = array_shift( $o ) ) {
			$n = array_merge( $n, $x );
		}
		$options['index']['options'] = $n;
		return $options;
	}

	private function filter_get_utilization_helper_array_map( $a ) {
		return '%d';
	}

	public function filter_get_utilization() {
		$post_types = $this->options->get_options_by_group( 'post_type_mode' );
		global $wpdb;
		$print = array();
		foreach ( $post_types as $post_type ) {
			$one          = $post_type;
			$one['value'] = $this->options->get_option( $post_type['name'] );
			$one['limit'] = $this->options->get_option( $post_type['post_type'] );
			if ( 0 === $one['value'] ) {
				$print[] = $one;
				continue;
			}
			/**
			 * get all ids
			 */
			$args         = array(
				'post_type' => $post_type['name'],
				'nopaging'  => true,
				'fields'    => 'ids',
			);
			$query        = new WP_Query( $args );
			$one['count'] = count( $query->posts );
			if ( 0 < $one['count'] ) {
				$sql = sprintf(
					'select count(*) from %s where post_type = %%s and post_parent in ( %s ) group by post_parent having count(*) > %%d',
					$wpdb->posts,
					implode( ', ', array_map( array( $this, 'filter_get_utilization_helper_array_map' ), $query->posts ) )
				);
				$p   = $query->posts;
				array_unshift( $p, 'revision' );
				array_push( $p, $one['value'] );
				$query         = $wpdb->prepare( $sql, $p );
				$result        = $wpdb->get_results( $query, ARRAY_A );
				$one['extend'] = count( $result );
			}
			$print[] = $one;
		}
		/**
		 * print
		 */

		$content  = '<table class="striped widefat fixed">';
		$content .= '<thead>';
		$content .= '<tr>';
		$content .= sprintf( '<td>%s</td>', esc_html__( 'Post type name', 'simple-revision-control' ) );
		$content .= sprintf( '<td>%s</td>', esc_html__( 'Status', 'simple-revision-control' ) );
		$content .= sprintf( '<td>%s</td>', esc_html__( 'Info', 'simple-revision-control' ) );
		$content .= sprintf( '<td>%s</td>', esc_html__( 'Actions', 'simple-revision-control' ) );
		$content .= '</tr>';
		$content .= '<tbody>';
		foreach ( $print as $one ) {
			$content .= '<tr>';
			$content .= sprintf( '<td>%s</td>', $one['th'] );
			if ( 'custom' === $one['value'] ) {
				$content .= sprintf( '<td>%s</td>', sprintf( __( 'Custom: %d', 'simple-revision-control' ), $one['limit'] ) );
				l( $one );
			} else {
				$content .= sprintf( '<td>%s</td>', $post_types[0]['options'][ $one['value'] ]['label'] );
			}

			if ( 0 === $one['value'] ) {
					$content .= sprintf(
						'<td colspan="2">%s</td>',
						esc_html__( 'There is no limit for this post type.', 'simple-revision-control' )
					);
			} else {
				if ( isset( $one['extend'] ) && 0 < $one['extend'] ) {
					$content .= sprintf(
						'<td><span class="wp-ui-text-notification">%s</span></td>',
						esc_html(
							sprintf(
								_n(
									'There is %2$s with more than one revision.',
									'There is %2$s with more than %1$d revisions.',
									$one['value'],
									'simple-revision-control'
								),
								$one['value'],
								sprintf(
									_n(
										'one entry',
										'%d entries',
										$one['extend'],
										'simple-revision-control'
									),
									$one['extend']
								)
							)
						)
					);
				} else {
					$content .= sprintf(
						'<td colspan="2">%s</td>',
						esc_html(
							sprintf(
								_n(
									'There is no entries with more than one revision.',
									'There is no entries with more than %1$d revisions.',
									$one['value'],
									'simple-revision-control'
								),
								$one['value']
							)
						)
					);
				}
			}
			$content .= '</tr>';
		}
		$content .= '</tbody>';
		$content .= '</table>';
		return $content;
	}

	public function change_post_type_revision_support() {
		$config = $this->options->get_options_by_group( 'post_type_mode' );
		foreach ( $config as $one ) {
			if ( 'off' === $this->options->get_option( $one['name'] ) ) {
				remove_post_type_support( $one['post_type'], 'revisions' );
			} else {
				add_post_type_support( $one['post_type'], 'revisions' );
			}
		}
	}
}

