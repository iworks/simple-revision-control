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
	private $base;

	/**
	 * DB VERSION
	 */
	private $db_version = 210;

	/**
	 * Plugin version
	 */
	private $version = 'PLUGIN_VERSION';

	/**
	 * debug
	 */
	private $debug = false;

	/**
	 * delete revision action name
	 *
	 * @since 2.1.0
	 */
	private $delete_revision_action_name = 'src_delete_revisions';

	public function __construct() {
		/**
		 * static settings
		 */
		$this->base       = dirname( dirname( __FILE__ ) );
		$this->dir        = basename( dirname( $this->base ) );
		$this->capability = apply_filters( 'simple_revision_control_capability', 'manage_options' );
		$this->debug      = defined( 'WP_DEBUG' ) && WP_DEBUG;
		/**
		 * WordPress Hooks
		 */
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_notices', array( $this, 'action_maybe_show_notice_after_delete_revisions' ) );
		add_action( 'init', array( $this, 'change_post_type_revision_support' ), PHP_INT_MAX );
		add_action( 'init', array( $this, 'check_db_version' ) );
		add_action( 'init', array( $this, 'register_assets' ), 0 );
		add_action( 'wp_ajax_simple_revision_control_delete_revisions', array( $this, 'action_ajax_delete_revisions' ) );
		add_filter( 'handle_bulk_actions-edit-post', array( $this, 'filter_delete_revisions' ), 10, 3 );
		add_filter( 'post_row_actions', array( $this, 'filter_maybe_add_post_row_actions' ), PHP_INT_MAX, 2 );
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

	/**
	 * register styles
	 *
	 * @since 2.1.0
	 */
	public function register_assets() {
		$filename = sprintf( '/assets/scripts/admin%s.js', $this->debug ? '' : '.min' );
		$filepath = sprintf( '%s%s', dirname( $this->base ), $filename );
		$file     = plugins_url( $filename, $this->base );
		wp_register_script(
			'simple-revision-control-admin',
			$file,
			array( 'jquery' ),
			$this->debug ? filemtime( $filepath ) : $this->version
		);
	}

	public function wp_revisions_to_keep( $num, $post ) {
		switch ( $this->options->get_option( $post->post_type . '_mode' ) ) {
			case 'unlimited':
				return -1;
			case 'custom':
				return $this->options->get_option( $post->post_type );
			case 'off':
				return 0;
		}
		return $num;
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

	/**
	 * helper to build SQL query
	 *
	 * @since 2.1.0
	 */
	private function filter_get_utilization_helper_array_map( $a ) {
		return '%d';
	}

	/**
	 * get utlization data
	 *
	 * @since 2.1.0
	 */
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
				'post_type' => $post_type['post_type'],
				'nopaging'  => true,
				'fields'    => 'ids',
			);
			$query        = new WP_Query( $args );
			$one['count'] = count( $query->posts );
			if ( 'unlimited' !== $one['value'] && 0 < $one['count'] ) {
				$sql = sprintf(
					'select count(*) from %s where post_type = %%s and post_parent in ( %s ) group by post_parent having count(*) > %%d',
					$wpdb->posts,
					implode( ', ', array_map( array( $this, 'filter_get_utilization_helper_array_map' ), $query->posts ) )
				);
				$p   = $query->posts;
				array_unshift( $p, 'revision' );
				if ( 'off' === $one['value'] ) {
					array_push( $p, 0 );
				} else {
					array_push( $p, $one['limit'] );
				}
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
		$content .= sprintf( '<td>%s</td>', esc_html__( 'Info', 'simple-revision-control' ) );
		$content .= sprintf( '<td>%s</td>', esc_html__( 'Actions', 'simple-revision-control' ) );
		$content .= '</tr>';
		$content .= '<tbody>';
		foreach ( $print as $one ) {
			$content .= '<tr>';
			$content .= sprintf( '<td>%s</td>', $one['th'] );
			/**
			 * third column: info
			 */
			switch ( $one['value'] ) {
				case 'off':
					if ( 0 === $one['extend'] ) {
						$content .= sprintf(
							'<td colspan="2">%s</td>',
							esc_html__( 'This post type does not supports revisions.', 'simple-revision-control' )
						);
					} else {
						$content .= sprintf(
							'<td>%s</td>',
							esc_html(
								sprintf(
									_n(
										'This post type does not supports revisions, but there is one entry with revisions.',
										'This post type does not supports revisions, but there are %d entries with revisions.',
										$one['extend'],
										'simple-revision-control'
									),
									$one['extend']
								)
							)
						);
						$content .= sprintf(
							'<td><span class="spinner" style="float: left"></span><button class="button delete" data-posttype="%s" data-nonce="%s">%s</button></td>',
							esc_attr( $one['post_type'] ),
							esc_attr( wp_create_nonce( $one['post_type'] ) ),
							__( 'Delete revisions', 'simple-revision-control' )
						);
					}
					break;
				case 'custom':
					if ( isset( $one['extend'] ) && 0 < $one['extend'] ) {
						$content .= sprintf(
							'<td><span class="wp-ui-text-notification">%s</span></td>',
							esc_html(
								sprintf(
									_n(
										'There is %2$s with more than one revision.',
										'There is %2$s with more than %1$d revisions.',
										$one['limit'],
										'simple-revision-control'
									),
									$one['limit'],
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
						$content .= sprintf(
							'<td><span class="spinner" style="float: left"></span><button class="button delete" data-posttype="%s" data-nonce="%s">%s</button></td>',
							esc_attr( $one['post_type'] ),
							esc_attr( wp_create_nonce( $one['post_type'] ) ),
							__( 'Delete revisions', 'simple-revision-control' )
						);
					} else {
						$content .= sprintf(
							'<td colspan="2">%s</td>',
							esc_html(
								sprintf(
									_n(
										'There is no entries with more than one revision.',
										'There is no entries with more than %1$d revisions.',
										$one['limit'],
										'simple-revision-control'
									),
									$one['limit']
								)
							)
						);
					}
					break;
				case 'unlimited':
					$content .= sprintf(
						'<td colspan="2">%s</td>',
						esc_html__( 'There is no limit for this post type.', 'simple-revision-control' )
					);
					break;
			}
			/**
			 * close tr
			 */
			$content .= '</tr>';
		}
		$content .= '</tbody>';
		$content .= '</table>';
		return $content;
	}

	/**
	 * Change post type support, depend on settings
	 *
	 * @since 2.1.0
	 */
	public function change_post_type_revision_support() {
		$config = $this->options->get_options_by_group( 'post_type_mode' );
		foreach ( $config as $one ) {
			$value = $this->options->get_option( $one['name'] );
			if ( 'off' === $value ) {
				remove_post_type_support( $one['post_type'], 'revisions' );
			} else {
				add_post_type_support( $one['post_type'], 'revisions' );
			}
		}
	}

	/**
	 * check and update (if needed) data in DB
	 *
	 * @since 2.1.0
	 */
	public function check_db_version() {
		$db_version = intval( $this->options->get_option( 'db_version' ) );
		/**
		 * version 210
		 */
		if ( empty( $db_version ) || $this->db_version > $db_version ) {
			$config = $this->options->get_options_by_group( 'post_type_mode' );
			foreach ( $config as $one ) {
				$option_name = $this->options->get_option_name( $one['post_type'] );
				$value       = get_option( $option_name );
				/**
				 * Old "0" - unlimited
				 */
				if ( '0' === $value ) {
					$this->options->update_option( $one['name'], 'unlimited' );
					continue;
				}
				/**
				 * old "1" - rutn off versions
				 */
				if ( '1' === $value ) {
					$this->options->update_option( $one['name'], 'off' );
					continue;
				}
				$value = intval( $value );
				if ( 1 < $value ) {
					$this->options->update_option( $one['name'], 'custom' );
				}
			}
			$this->options->update_option( 'db_version', $this->db_version );
		}
	}

	/**
	 * add row action to remove unwanted revisions
	 *
	 * @since 2.1.0
	 */
	public function filter_maybe_add_post_row_actions( $actions, $post ) {
		global $wpdb;
		$mode  = $this->options->get_option( $post->post_type . '_mode' );
		$limit = $this->options->get_option( $post->post_type );
		switch ( $mode ) {
			case 'off':
			case 'custom':
				$query = sprintf(
					'select count(*) from %s where post_parent = %%d and post_type = %%s',
					$wpdb->posts
				);
				$query = $wpdb->prepare( $query, $post->ID, 'revision' );
				$count = $wpdb->get_var( $query );
				if ( $count > $limit ) {
					$nonce = wp_create_nonce( 'bulk-posts' );
					$actions[ $this->delete_revision_action_name ] = sprintf(
						'<a href="%s" aria-label="%s" class="submitdelete" data-id="%d" data-nonce="%s">%s</a>',
						esc_url(
							add_query_arg(
								array(
									'post[]'   => $post->ID,
									'action'   => $this->delete_revision_action_name,
									'_wpnonce' => $nonce,
								)
							)
						),
						esc_attr__( 'Delete revisions', 'simple-revision-control' ),
						$post->ID,
						esc_attr( $nonce ),
						esc_html__( 'Delete revisions', 'simple-revision-control' )
					);
				}
				break;
		}
		return $actions;
	}

	public function action_ajax_delete_revisions() {
		if ( ! isset( $_POST['posttype'] ) ) {
			wp_send_json_error();
		}
		if ( ! isset( $_POST['_wpnonce'] ) ) {
			wp_send_json_error();
		}
		$post_type = $_POST['posttype'];
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], $post_type ) ) {
			wp_send_json_error();
		}
		global $wpdb;
		$mode  = $this->options->get_option( $post_type . '_mode' );
		$limit = $this->options->get_option( $post_type );
		switch ( $mode ) {
			case 'off':
				$query = sprintf(
					'select s.post_parent from %1$s s where s.post_parent in ( select p.ID from %1$s p where p.post_type = %%s ) and s.post_type = %%s group by s.post_parent having count(s.post_parent) > 0',
					$wpdb->posts
				);
				$query = $wpdb->prepare( $query, $post_type, 'revision' );

				$results = $wpdb->get_col( $query );
				if ( empty( $results ) ) {
					wp_send_json_error( __( 'Something went wrong!', 'simple-revision-control' ) );
				}
				$query    = sprintf(
					'select ID from %s where post_parent in ( %%s )',
					$wpdb->posts
				);
				$query    = sprintf(
					$query,
					implode( ', ', array_map( array( $this, 'filter_get_utilization_helper_array_map' ), $results ) )
				);
				$query    = $wpdb->prepare( $query, $results );
				$results2 = $wpdb->get_col( $query );
				foreach ( $results2 as $ID ) {
					wp_delete_post( $ID, true );
				}
				break;
			case 'custom':
				$query   = sprintf(
					'select s.post_parent from %1$s s where s.post_parent in ( select p.ID from %1$s p where p.post_type = %%s ) and s.post_type = %%s group by s.post_parent having count(s.post_parent) > %%d',
					$wpdb->posts
				);
				$query   = $wpdb->prepare( $query, $post_type, 'revision', $limit );
				$results = $wpdb->get_col( $query );
				if ( empty( $results ) ) {
					wp_send_json_error( __( 'Something went wrong!', 'simple-revision-control' ) );
				}
				foreach ( $results as $parent_id ) {
					$query    = sprintf(
						'select ID from %s where post_parent = %%d order by ID desc limit 65535 offset %%d',
						$wpdb->posts
					);
					$query    = $wpdb->prepare( $query, $parent_id, $limit );
					$results2 = $wpdb->get_col( $query );
					foreach ( $results2 as $ID ) {
						wp_delete_post( $ID, true );
					}
				}
				break;
		}
		wp_send_json_success();
	}

	/**
	 * Delete revisions when it is needed!
	 *
	 * @since 2.1.0
	 */
	public function filter_delete_revisions( $sendback, $doaction, $post_ids ) {
		if ( $this->delete_revision_action_name !== $doaction ) {
			return $sendback;
		}
		$post_id  = $post_ids[0];
		$sendback = remove_query_arg( array( 'msg', 'post_id', 'post_ids' ), $sendback );
		/**
		 * get settings
		 */
		$post_type = get_post_type( $post_id );
		$mode      = $this->options->get_option( $post_type . '_mode' );
		$limit     = $this->options->get_option( $post_type );
		$query     = 'select ID from %s where post_parent = %%s and post_type = %%s';
		switch ( $mode ) {
			case 'unlimited':
				return $sendback;
			case 'custom':
				$query .= ' order by ID desc limit 65535 offset %%d';
				break;
		}
		if ( empty( $query ) ) {
			return $sendback;
		}
		global $wpdb;
		$query   = sprintf( $query, $wpdb->posts );
		$query   = $wpdb->prepare( $query, $post_id, 'revision', $limit );
		$results = $wpdb->get_col( $query );
		foreach ( $results as $ID ) {
			wp_delete_post( $ID, true );
		}

		return add_query_arg(
			array(
				'msg'     => 'src_delete_revisions',
				'post_id' => $post_id,
				'count'   => count( $results ),
			),
			$sendback
		);
	}

	/**
	 * show message
	 *
	 * @since 2.1.0
	 */
	public function action_maybe_show_notice_after_delete_revisions() {
		if ( ! isset( $_GET['msg'] ) ) {
			return;
		}
		if ( $this->delete_revision_action_name !== $_GET['msg'] ) {
			return;
		}
		$post_id = filter_input( INPUT_GET, 'post_id', FILTER_VALIDATE_INT );
		$count   = filter_input( INPUT_GET, 'count', FILTER_VALIDATE_INT );
		$message = __( 'All revisions of "%1$s" was successful deleted.', 'simple-revision-control' );
		if ( 0 < $count ) {
			$message = _n(
				'%2$d revision of "%1$s" was successful deleted',
				'%2$d revisions of "%1$s" was successful deleted',
				$count,
				'simple-revision-control'
			);
		}
		$title = __( 'Unknown', 'simple-revision-control' );
		if ( 0 < $post_id ) {
			$title = get_the_title( $post_id );
		}
		echo '<div class="notice notice-info">';
		echo '<p>';
		printf( $message, sprintf( '<strong>%s</strong>', $title ), $count );
		echo '</p>';
		echo '</div>';
	}

}

