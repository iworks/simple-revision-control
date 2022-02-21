<?php

function simple_revision_control_options() {
	$options = array();

	/**
	 * main settings
	 */
	$options['index'] = array(
		'use_tabs'        => true,
		'version'         => 'PLUGIN_VERSION',
		'page_title'      => __( 'Revisions configuration', 'simple-revision-control' ),
		'menu_title'      => __( 'Revisions', 'simple-revision-control' ),
		'menu'            => 'options',
		'enqueue_scripts' => array(
			'simple-revision-control-admin',
		),
		'enqueue_styles'  => array(),
		'options'         => array(
			array(
				'type'  => 'heading',
				'label' => __( 'Revisions', 'simple-revision-control' ),
			),
			array(
				'name'      => 'post_mode',
				'type'      => 'radio',
				'th'        => __( 'Post', 'simple-revision-control' ),
				'default'   => 'unlimited',
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
				'post_type' => 'post',
			),
			array(
				'name'    => 'post',
				'class'   => 'small-text',
				'type'    => 'number',
				'default' => 3,
				'min'     => 1,
				'group'   => 'post_type',
			),
			array(
				'name'      => 'page_mode',
				'type'      => 'radio',
				'th'        => __( 'Page', 'simple-revision-control' ),
				'default'   => 'unlimited',
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
				'post_type' => 'page',
			),
			array(
				'name'    => 'page',
				'class'   => 'small-text',
				'type'    => 'number',
				'default' => 3,
				'min'     => 1,
				'group'   => 'post_type',
			),
			array(
				'type'  => 'heading',
				'label' => __( 'Info & Tools', 'simple-revision-control' ),
			),
			array(
				'type'   => 'special',
				'th'     => __( 'Data', 'simple-revision-control' ),
				'filter' => 'simple_revision_control_utilization',
			),
		),
		'metaboxes'       => array(
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
	return apply_filters( 'iworks_plugin_get_options', $options, 'simple-revision-control' );
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
