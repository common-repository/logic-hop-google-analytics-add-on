<?php

if (!defined('ABSPATH')) die;

/**
 * Google Analytics
 *
 * Provides Google Analytics functionality.
 *
 * @since      1.1.0
 */

class LogicHop_Google_Analytics {

	/**
	 * Core functionality & logic class
	 *
	 * @since    1.1.0
	 * @access   private
	 * @var      LogicHop_Core    $logic    Core functionality & logic.
	 */
	private $logic;

	/**
	 * Plugin version
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      integer    $version    Core functionality & logic.
	 */
	public $version;

	/**
	 * Google Analytics API URL
	 *
	 * @since    1.1.0
	 * @access   private
	 * @var      string    $google_ga_url    Google Analytics API URL
	 */
	private $google_ga_url;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    	1.1.0
	 * @param       object    $logic	LogicHop_Core functionality & logic.
	 */
	public function __construct( $logic ) {
		$this->logic			= $logic;
		$this->version			= '3.1.0';
		$this->google_ga_url 	= 'https://ssl.google-analytics.com/collect';
	}

	/**
	 * Check if Google Analytics has been set
	 *
	 * @since    	1.1.0
	 * @return      boolean     If google_ga_id is set
	 */
	public function active () {
		return $this->logic->get_option('google_ga_id');
	}

	/**
	 * Check if Google Analytics embed option is enabled
	 *
	 * @since    	1.1.0
	 * @return      boolean     If google_ga_embed is set
	 */
	public function embedEnabled () {
		return $this->logic->get_option('google_ga_embed');
	}

	/**
	 * Displays Google Analytics metabox on Goal editor
	 *
	 * @since    	1.0.0
	 * @param		object		$post		Wordpress Post object
	 * @return		string					Echos metabox form
	 */
	public function goal_tag_display ($post) {

		if ( ! $this->logic->addon_active('google-analytics') ) {
			printf('<div>
						<h4>%s</h4>
						<p>
							%s
						</p>
					</div>',
					__('Logic Hop Google Analytics Events is currently disabled.', 'logichop'),
					sprintf(__('Logic Hop Google Analytics Events requires a <a href="%s" target="_blank">Logic Hop Professional Plan</a> or higher.', 'logichop'),
						'https://logichop.com/get-started/?ref=addon-google-analytics'
						)
				);
			return;
		}

		$values	= get_post_custom($post->ID);
		$cb = isset($values['logichop_goal_ga_cb']) ? esc_attr($values['logichop_goal_ga_cb'][0]) : '';
		$ec = isset($values['logichop_goal_ga_ec']) ? esc_attr($values['logichop_goal_ga_ec'][0]) : '';
		$ea = isset($values['logichop_goal_ga_ea']) ? esc_attr($values['logichop_goal_ga_ea'][0]) : '';
		$el = isset($values['logichop_goal_ga_el']) ? esc_attr($values['logichop_goal_ga_el'][0]) : '';
		$ev = isset($values['logichop_goal_ga_ev']) ? esc_attr($values['logichop_goal_ga_ev'][0]) : '';

		$page = isset($values['logichop_goal_ga_page']) ? esc_attr($values['logichop_goal_ga_page'][0]) : '';
		$title = isset($values['logichop_goal_ga_title']) ? esc_attr($values['logichop_goal_ga_title'][0]) : '';

		$hit = isset($values['logichop_goal_ga_hit']) ? esc_attr($values['logichop_goal_ga_hit'][0]) : '';

		if ($this->logic->google->active()) {
			printf('<div>
						<p>
							<select id="logichop_goal_ga_cb" name="logichop_goal_ga_cb" class="logichop_ga_event">
								<option value="">Tracking disabled</option>
								<option value="event" %s>Send Event Tracking to Google Analytics</option>
								<option value="page" %s>Send Page Tracking to Google Analytics</option>
							</select>
						</p>
						<div id="logichop_ga_event_fields" class="%s">
							<p>
								<label for="logichop_goal_ga_ec" class="">%s</label><br>
								<input type="text" id="logichop_goal_ga_ec" class="logichop_ga_event" name="logichop_goal_ga_ec" value="%s" placeholder="">
							</p>
							<p>
								<label for="logichop_goal_ga_ea" class="">%s</label><br>
								<input type="text" id="logichop_goal_ga_ea" class="logichop_ga_event" name="logichop_goal_ga_ea" value="%s" placeholder="">
							</p>
							<p>
								<label for="logichop_goal_ga_el" class="">%s <em><small>(%s)</small></em></label><br>
								<input type="text" id="logichop_goal_ga_el" class="logichop_ga_event" name="logichop_goal_ga_el" value="%s" placeholder="">
							</p>
							<p>
								<label for="logichop_goal_ga_ev" class="">%s <em><small>(%s)</small></em></label><br>
								<input id="logichop_goal_ga_ev" class="logichop_ga_event" name="logichop_goal_ga_ev" value="%s" placeholder="" type="number">
							</p>
						</div>
						<div id="logichop_ga_page_fields" class="%s">
							<p>
								<label for="logichop_goal_ga_title" class="">%s</label><br>
								<input type="text" id="logichop_goal_ga_title" class="logichop_ga_event" name="logichop_goal_ga_title" value="%s">
							</p>
							<p>
								<label for="logichop_goal_ga_page" class="">%s</label><br>
								<input type="text" id="logichop_goal_ga_page" class="logichop_ga_event" name="logichop_goal_ga_page" value="%s">
							</p>
						</div>
						<div class="logichop_goal_ga_meta %s">
							<p>
								<select id="logichop_goal_ga_hit" name="logichop_goal_ga_hit" class="logichop_ga_event">
									<option value="">Interaction Hit</option>
									<option value="true" %s>Non-Interaction Hit</option>
								</select>
								<br><label for="logichop_goal_ga_hit" class=""><em>%s</em></label><br>
							</p>
							<p>
								<a href="#" class="logichop_google_analytics_clear">Clear</a>
							</p>
						</div>
					</div>',
					($cb == 1 || $cb == 'event') ? 'selected' : '',
					($cb == 'page') ? 'selected' : '',
					($cb == 1 || $cb == 'event') ? '' : 'logichop_ga_event_hidden',
					__('Category', 'logichop'),
					$ec,
					__('Action', 'logichop'),
					$ea,
					__('Label', 'logichop'),
					__('Optional', 'logichop'),
					$el,
					__('Value', 'logichop'),
					__('Optional numeric value', 'logichop'),
					$ev,
					($cb == 'page') ? '' : 'logichop_ga_event_hidden',
					__('Title', 'logichop'),
					$title,
					__('Page', 'logichop'),
					$page,
					($cb == 'page' || $cb == 'event') ? '' : 'logichop_ga_event_hidden',
					($hit == 'true') ? 'selected' : '',
					__('Specifies the event be considered interactive or non-interactive.', 'logichop')
				);
		} else {
			printf('<div>
						<h4>%s</h4>
						<p>
							%s
						</p>
					</div>',
					__('Google Analytics is currently disabled.', 'logichop'),
					sprintf(__('To enable, add a valid Logic Hop API Key and Google Analytics Account ID on the <a href="%s">Settings page</a>.', 'logichop'),
							admin_url('admin.php?page=logichop-settings')
						)
				);
		}
	}

	/**
	 * Send Tracking Event to Google Analytics
	 *
	 * @since   	1.1.0
	 * @param		integer     $id         Post ID
	 * @param      	$values		array     	WordPress get_post_custom()
	 * @return      object     				Tracking response
	 */
	public function track_event ($id, $values) {
		if (!$this->active()) return false;
		$ec = $ea = $page = $title = false;
		if (isset($values['logichop_goal_ga_cb'][0]) && $values['logichop_goal_ga_cb'][0]) {
			$type = $values['logichop_goal_ga_cb'][0];
			$ec = $values['logichop_goal_ga_ec'][0];
			$ea = $values['logichop_goal_ga_ea'][0];
			$el = ($values['logichop_goal_ga_el'][0] != '') ? $values['logichop_goal_ga_el'][0] : null;
			$ev = ($values['logichop_goal_ga_ev'][0] != '') ? $values['logichop_goal_ga_ev'][0] : null;

			$title = $values['logichop_goal_ga_title'][0];
			$page = $values['logichop_goal_ga_page'][0];
			$hit = $values['logichop_goal_ga_hit'][0];
		}

		$ga_id = $this->logic->get_option( 'google_ga_id' );
		$domain = $this->logic->get_option('domain');
		$client_ip = $this->logic->get_client_IP();

		$cid = md5( $this->logic->data_factory->get_value( 'UID' ) );

		$data = array (
					'v' 	=> 1, 											// Version
					'tid' => urlencode( $ga_id ), 	// Tracking ID
					'cid' => urlencode( $cid ), 			// Anonymous Client ID
					'uip'	=> urlencode( $client_ip ) 		// User IP
				);

		if ( isset( $hit ) && $hit == 'true' ) {
			$data['ni'] = 1;
		}

		if ( $type == 'page' ) {
			if ( ! $title || ! $page ) return false;

			$data['t'] = urlencode( 'pageview' );
			$data['dh'] = urlencode( $domain );	// Domain
			$data['dt'] = $title; // Title
			$data['dp'] = urlencode( $page );	// Page
		} else {
			if ( ! $ec || ! $ea ) return false;

			$ec 	= $this->logic->get_liquid_value( $ec );
			$ea 	= $this->logic->get_liquid_value( $ea );
			$el 	= $this->logic->get_liquid_value( $el );
			$ev 	= $this->logic->get_liquid_value( $ev );

			$data['t'] = urlencode( 'event' );
			$data['ec'] = urlencode( $ec );	// Event Category
			$data['ea'] = urlencode( $ea );	// Event Action
			if ($el) $data['el'] = urlencode( $el );	// Event Label - Optional
			if ($ev) $data['ev'] = urlencode( $ev ); 	// Event Value - Optional
		}

		$post_args = array (
						'headers' => array (
							'User-Agent' => $_SERVER['HTTP_USER_AGENT']
							),
						'body' => $data
					);
		$response = wp_remote_post( $this->google_ga_url, $post_args );

		return $response;
	}
}
