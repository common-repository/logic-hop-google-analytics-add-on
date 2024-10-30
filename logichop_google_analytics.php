<?php

	/*
		Plugin Name: Logic Hop Google Analytics Add-on
		Plugin URI:	https://logichop.com/docs/using-logic-hop-google-analytics/
		Description: Enables Google Analytics event tracking for Logic Hop
		Author: Logic Hop
		Version: 3.1.5
		Author URI: https://logichop.com
	*/

	if (!defined('ABSPATH')) die;

	if ( is_admin() ) {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		if ( ! is_plugin_active( 'logichop/logichop.php' ) && ! is_plugin_active( 'logic-hop/logichop.php' ) ) {
			add_action( 'admin_notices', 'logichop_google_analytics_plugin_notice' );
		}
	}

	function logichop_google_analytics_plugin_notice () {
		$message = sprintf(__('The Logic Hop Google Analytics Add-on requires the Logic Hop plugin. Please download and activate the <a href="%s" target="_blank">Logic Hop plugin</a>.', 'logichop'),
							'http://wordpress.org/plugins/logic-hop/'
						);

		printf('<div class="notice notice-warning is-dismissible">
						<p>
							%s
						</p>
					</div>',
					$message
				);
	}

	require_once 'includes/google_analytics.php';

	/**
	 * Plugin activation/deactviation routine to clear Logic Hop transients
	 *
	 * @since    2.0.1
	 */
	function logichop_google_analytics_activation () {
		delete_transient( 'logichop' );
  }
	register_activation_hook( __FILE__, 'logichop_google_analytics_activation' );
	register_deactivation_hook( __FILE__, 'logichop_google_analytics_activation' );


	/**
	 * Register admin notices
	 *
	 * @since    2.0.0
	 */
	function logichop_google_analytics_admin_notice () {
		global $logichop;

		$message = '';

		if ( ! $logichop->logic->addon_active('google-analytics') ) {
			$message = sprintf(__('The Logic Hop Google Analytics Add-on requires a <a href="%s" target="_blank">Logic Hop License Key or Data Plan</a>.', 'logichop'),
							'https://logichop.com/get-started/?ref=addon-google-analytics'
						);
		}

		if ( $message ) {
			printf('<div class="notice notice-warning is-dismissible">
						<p>
							%s
						</p>
					</div>',
					$message
				);
		}
	}
	add_action( 'logichop_admin_notice', 'logichop_google_analytics_admin_notice' );

	/**
	 * Plugin page links
	 *
	 * @since    1.0.0
	 * @param    array		$links			Plugin links
	 * @return   array  	$new_links 		Plugin links
	 */
	function logichop_plugin_action_links_google_analytics ($links) {
		$new_links = array();
        $new_links['settings'] = sprintf( '<a href="%s" target="_blank">%s</a>', 'https://logichop.com/docs/using-logic-hop-google-analytics/', 'Instructions' );
 		$new_links['deactivate'] = $links['deactivate'];
 		return $new_links;
	}
	add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'logichop_plugin_action_links_google_analytics');

	/**
	 * Initialize functionality
	 *
	 * @since    1.0.0
	 */
	function logichop_google_analytics_init () {
		global $logichop;

		if ( isset( $logichop->logic ) ) {
			$logichop->logic->google = new LogicHop_Google_Analytics($logichop->logic);
		}
	}
	add_action('logichop_integration_init', 'logichop_google_analytics_init');

	/**
	 * Handle event tracking
	 *
	 * @since    1.0.0
	 * @param    integer	$id		Goal ID
	 * @return   boolean   	Event tracked
	 */
	function logichop_check_track_event_google_analytics ($id, $values) {
		global $logichop;

		return $logichop->logic->google->track_event($id, $values);
	}
	add_filter('logichop_check_track_event', 'logichop_check_track_event_google_analytics', 10, 2);

	/**
	 * Generate client meta data
	 *
	 * @since    1.0.0
	 * @param    array		$integrations	Integration names
	 * @return   array    	$integrations	Integration names
	 */
	function logichop_google_analytics_client_meta ($integrations) {
		$integrations[] = 'google-analytics';
		return $integrations;
	}
	add_filter('logichop_client_meta_integrations', 'logichop_google_analytics_client_meta');

	/**
	 * Register settings
	 *
	 * @since    1.0.0
	 * @param    array		$settings	Settings parameters
	 * @return   array    	$settings	Settings parameters
	 */
	function logichop_settings_register_google_analytics ($settings) {
		$settings['google_ga_id'] = array (
							'name' 	=> __('Google Analytics ID', 'logichop'),
							'meta' 	=> __('Enable Google Analytics event tracking. <a href="https://logichop.com/docs/using-logic-hop-google-analytics/" target="_blank">Instructions</a>', 'logichop'),
							'type' 	=> 'text',
							'label' => '',
							'opts'  => null
						);
		$settings['google_ga_embed'] = array (
							'name' 	=> __('Google Analytics', 'logichop'),
							'meta' 	=> __('Include Google Analytics Javascript include on every page.', 'logichop'),
							'type' 	=> 'checkbox',
							'label' => 'Enable Javascript Embed',
							'opts'  => null
						);
		return $settings;
	}
	add_filter('logichop_settings_register', 'logichop_settings_register_google_analytics');

	/**
	 * Validate settings
	 *
	 * @since    1.0.0
	 * @param    string		$key		Settings key
	 * @return   string    	$result		Error object
	 */
	function logichop_settings_validate_google_analytics ($validation, $key, $input) {
		global $logichop;

		if ($key == 'google_ga_id' && $input[$key] != '') {
			if (!preg_match('/^ua-\d{4,9}-\d{1,4}$/i', strval($input[$key]))) {
         		$validation->error = true;
         		$validation->error_msg = '<li>Invalid Google Analytics ID</li>';
         	}
         }
	}
	add_filter('logichop_settings_validate', 'logichop_settings_validate_google_analytics', 10, 3);

	/**
	 * Add goal metabox
	 *
	 * @since    1.0.0
	 */
	function logichop_configure_metabox_google_analytics () {
		global $logichop;

		add_meta_box(
				'logichop_goal_google_event',
				__('Google Event Tracking', 'logichop'),
				array($logichop->logic->google, 'goal_tag_display'),
				array('logichop-goals'),
				'normal',
				'low'
			);
	}
	add_action('logichop_configure_metaboxes', 'logichop_configure_metabox_google_analytics');

	/**
	 * Save event data
	 *
	 * @since    1.0.0
	 * @param    integer	$post_id	WP post ID
	 */
	function logichop_event_save_google_analytics ($post_id) {
		$type = false;
		if ( isset( $_POST['logichop_goal_ga_cb'] ) ) {
			if ( $_POST['logichop_goal_ga_cb'] == 'page' ) {
				$type = 'page';
			} else if ( $_POST['logichop_goal_ga_cb'] == 'event' ) {
				$type = 'event';
			}
		}
		update_post_meta( $post_id, 'logichop_goal_ga_cb', wp_kses( $type, '' ) );

		if (isset($_POST['logichop_goal_ga_ec'])) 	update_post_meta($post_id, 'logichop_goal_ga_ec', wp_kses($_POST['logichop_goal_ga_ec'],''));
		if (isset($_POST['logichop_goal_ga_ea'])) 	update_post_meta($post_id, 'logichop_goal_ga_ea', wp_kses($_POST['logichop_goal_ga_ea'],''));
		if (isset($_POST['logichop_goal_ga_el'])) 	update_post_meta($post_id, 'logichop_goal_ga_el', wp_kses($_POST['logichop_goal_ga_el'],''));
		if (isset($_POST['logichop_goal_ga_ev'])) 	update_post_meta($post_id, 'logichop_goal_ga_ev', wp_kses($_POST['logichop_goal_ga_ev'],''));

		if (isset($_POST['logichop_goal_ga_title'])) 	update_post_meta($post_id, 'logichop_goal_ga_title', wp_kses($_POST['logichop_goal_ga_title'],''));
		if (isset($_POST['logichop_goal_ga_page'])) 	update_post_meta($post_id, 'logichop_goal_ga_page', wp_kses($_POST['logichop_goal_ga_page'],''));

		if (isset($_POST['logichop_goal_ga_hit'])) 	update_post_meta($post_id, 'logichop_goal_ga_hit', wp_kses($_POST['logichop_goal_ga_hit'],''));
	}
	add_action('logichop_event_save', 'logichop_event_save_google_analytics');

	/**
	 * Output GA script in wp_head()
	 *
	 * @since    1.0.0
	 */
	function logichop_embed_google_analytics () {
		global $logichop;

		if ( ! $logichop ) return;

		$ga_id = $logichop->logic->google->active();

		if ( $ga_id && $logichop->logic->google->embedEnabled() ) {

			printf('<!-- Global site tag (gtag.js) - Google Analytics -->
							<script async src="https://www.googletagmanager.com/gtag/js?id=UA-82648131-1"></script>
							<script>
							  window.dataLayer = window.dataLayer || [];
							  function gtag(){dataLayer.push(arguments);}
			  				gtag(\'js\', new Date());
			  				gtag(\'config\', \'%s\');
							</script>',
							$ga_id
					);
		}
	}
	add_action( 'wp_head', 'logichop_embed_google_analytics' );

	/**
	 * Enqueue admin CSS styles
	 *
	 * @since    1.0.0
	 */
	function logichop_admin_enqueue_styles_google_analytics ($hook) {
		global $logichop;

		if (in_array($hook, array('post.php', 'post-new.php'))) {
			$css_path = sprintf('%sadmin/logichop_google_analytics_goals.css', plugin_dir_url( __FILE__ ));
			wp_enqueue_style( 'logichop_google_analytics', $css_path, array(), $logichop->logic->google->version, 'all' );
		}
	}
	add_action('logichop_admin_enqueue_styles', 'logichop_admin_enqueue_styles_google_analytics');

	/**
	 * Enqueue admin scripts
	 *
	 * @since    1.0.0
	 */
	function logichop_admin_enqueue_scripts_google_analytics ($hook, $post_type) {
		global $logichop;

		if ($post_type == 'logichop-goals') {
			$js_path = sprintf('%sadmin/logichop_google_analytics_goals.js', plugin_dir_url( __FILE__ ));
			wp_enqueue_script( 'logichop_google_analytics', $js_path, array( 'jquery' ), $logichop->logic->google->version, false );
		}
	}
	add_action('logichop_admin_enqueue_scripts', 'logichop_admin_enqueue_scripts_google_analytics', 10, 2);
