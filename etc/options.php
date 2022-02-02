<?php

function simple_revision_control_options() {
	$simple_revision_control_options = array();

	/**
	 * main settings
	 */
	$simple_revision_control_options['index'] = array(
		'use_tabs'   => false,
		'version'    => 'PLUGIN_VERSION',
		'page_title' => __( 'Revisions configuration', 'simple-revision-control' ),
		'menu_title' => __( 'Revisions', 'simple-revision-control' ),
		'menu'       => 'options',
		'options'    => array(
			array(
				'type'        => 'heading',
				'label'       => __( 'Number of revisions', 'simple-revision-control' ),
				'description' => __( 'Set <b>0</b> to unlimited revisions, set <b>1</b> to no revisions or set any other number.', 'simple-revision-control' ),
			),
			array(
				'name'    => 'post',
				'class'   => 'small-text',
				'type'    => 'number',
				'th'      => __( 'Post', 'simple-revision-control' ),
				'default' => 0,
				'min'     => 0,
			),
			array(
				'name'    => 'page',
				'class'   => 'small-text',
				'type'    => 'number',
				'th'      => __( 'Page', 'simple-revision-control' ),
				'default' => 0,
				'min'     => 0,
			),
		),
		'metaboxes'  => array(
			'assistance' => array(
				'title'    => __( 'We are waiting for your message', 'simple-revision-control' ),
				'callback' => 'simple_revision_control_options_need_assistance',
				'context'  => 'side',
				'priority' => 'core',
			),
			'love'       => array(
				'title'    => __( 'I love what I do!', 'simple-revision-control' ),
				'callback' => 'simple_revision_control_options_loved_this_plugin',
				'context'  => 'side',
				'priority' => 'core',
			),
		),
	);
	return simple_revision_control_add_custom_post_types_to_options( $simple_revision_control_options, 'index' );
}

function simple_revision_control_add_custom_post_types_to_options( $options, $options_group ) {
	 $custom_post_types = get_post_types( array( '_builtin' => false ), 'object' );
	if ( empty( $custom_post_types ) ) {
		return $options;
	}
	$header = true;
	foreach ( $custom_post_types as $name => $post_type ) {
		if ( ! post_type_supports( $name, 'revisions' ) ) {
			continue;
		}
		if ( $header ) {
			$options[ $options_group ]['options'][] = array(
				'type'  => 'heading',
				'label' => __( 'Custom Post Types', 'simple-revision-control' ),
			);
		}
		$header                                 = false;
		$options[ $options_group ]['options'][] = array(
			'name'    => $name,
			'class'   => 'small-text',
			'type'    => 'number',
			'th'      => $post_type->label,
			'default' => 0,
			'min'     => 0,
		);
	}
	return $options;
}

function simple_revision_control_options_loved_this_plugin( $iworks_iworks_seo_improvements ) {
	$content = apply_filters( 'iworks_rate_love', '', 'simple-revision-control' );
	if ( ! empty( $content ) ) {
		echo $content;
		return;
	}
	?>
<p><?php _e( 'Below are some links to help spread this plugin to other users', 'simple-revision-control' ); ?></p>
<ul>
	<li><a href="https://wordpress.org/support/plugin/simple-revision-control/reviews/#new-post"><?php _e( 'Give it a five stars on WordPress.org', 'simple-revision-control' ); ?></a></li>
	<li><a href="<?php _ex( 'https://wordpress.org/plugins/simple-revision-control/', 'plugin home page on WordPress.org', 'simple-revision-control' ); ?>"><?php _e( 'Link to it so others can easily find it', 'simple-revision-control' ); ?></a></li>
</ul>
	<?php
}

function simple_revision_control_options_need_assistance( $iworks_iworks_seo_improvementss ) {
	$content = apply_filters( 'iworks_rate_assistance', '', 'simple-revision-control' );
	if ( ! empty( $content ) ) {
		echo $content;
		return;
	}

	?>
<p><?php _e( 'We are waiting for your message', 'simple-revision-control' ); ?></p>
<ul>
	<li><a href="<?php _ex( 'https://wordpress.org/support/plugin/simple-revision-control/', 'link to support forum on WordPress.org', 'simple-revision-control' ); ?>"><?php _e( 'WordPress Help Forum', 'simple-revision-control' ); ?></a></li>
</ul>
	<?php
}
