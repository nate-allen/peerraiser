<?php

namespace PeerRaiser\Controller\Frontend;

class Shortcode extends \PeerRaiser\Controller\Base {

    public function register_actions() {
	    add_shortcode( 'peerraiser_donation_form',         array( $this, 'render_donation_form' ) );
	    add_shortcode( 'peerraiser_receipt',               array( $this, 'render_donation_receipt' ) );
	    add_shortcode( 'peerraiser_login',                 array( $this, 'render_login_form' ) );
	    add_shortcode( 'peerraiser_signup',                array( $this, 'render_signup_form' ) );
	    add_shortcode( 'peerraiser_participant_dashboard', array( $this, 'render_participant_dashboard' ) );

        add_action( 'cmb2_init', array( $this, 'register_settings_fields' ) );
    }

    public function render_donation_form( $atts, $content = '' ) {
    	$atts = shortcode_atts( array(), $atts, 'peerraiser_donation_form' );
	    $plugin_options = get_option( 'peerraiser_options', array() );

	    $view_args = array(
	    	'campaigns' => $this->get_campaigns_accepting_donations(),
		    'fundraisers' => $this->get_fundraisers_for_current_campaign(),
		    'currency_symbol' => peerraiser_get_currency_symbol(),
		    'currency_position' => $plugin_options['currency_position'],
		    'campaign_select_class' => $this->get_campaign_select_class(),
		    'fundraiser_select_class' => $this->get_fundraiser_select_class(),
	    );
	    $this->assign( 'peerraiser', $view_args );

	    return $this->get_text_view( 'frontend/donation-form' );
    }

    public function render_donation_receipt( $atts, $content = '' ) {
        // provide default values for empty shortcode attributes
        $a = shortcode_atts( array(
            'heading_text'     => '',
            'description_text' => '',
        ), $atts );

	    $view_args = array(
		    'test' => 'test result'
	    );
	    $this->assign( 'peerraiser', $view_args );

	    return $this->get_text_view( 'frontend/donation-receipt' );
    }

    public function render_login_form( $atts, $content ='' ) {
        // provide default values for empty shortcode attributes
        $a = shortcode_atts( array(
            'heading_text'     => '',
            'description_text' => '',
        ), $atts );

        $view_args = array(
            'test' => 'test result'
        );
        $this->assign( 'peerraiser', $view_args );

        return $this->get_text_view( 'frontend/partials/login-form' );
    }

    public function render_signup_form( $atts, $content = '' ) {
        // provide default values for empty shortcode attributes
        $a = shortcode_atts( array(
            'heading_text'     => '',
            'description_text' => '',
        ), $atts );

        $view_args = array(
            'test' => 'test result'
        );
        $this->assign( 'peerraiser', $view_args );

        return $this->get_text_view( 'frontend/partials/signup-form' );

    }

    public function render_participant_dashboard( $atts, $content = '' ) {
        // Plugin options
        $plugin_options = get_option( 'peerraiser_options', array() );

        // provide default values for empty shortcode attributes
        $a = shortcode_atts( array(
            'heading_text'     => '',
            'description_text' => '',
        ), $atts );

        // Models
        $dashboard_model = new \PeerRaiser\Model\Frontend\Dashboard();
        $currency_model  = new \PeerRaiser\Model\Currency();

        $navigation_links = apply_filters( 'peerraiser_participant_dashboard_navigation', $dashboard_model->get_navigation() );

        $view_args = array(
	        'navigation'                 => $navigation_links,
	        'donations'                  => $dashboard_model->get_donations(),
	        'fundraisers'                => $dashboard_model->get_fundraisers(),
	        'teams'                      => $dashboard_model->get_teams(),
	        'user_id'                    => get_current_user_id(),
	        'default_campaign_thumbnail' => $plugin_options['campaign_thumbnail_image'],
	        'default_team_thumbnail'     => $plugin_options['team_thumbnail_image'],
	        'currency_symbol'            => $currency_model->get_currency_symbol_by_iso4217_code( $plugin_options['currency'] ),
	        'settings_form'              => cmb2_get_metabox_form( 'dashboard_settings', get_current_user_id() ),
	        'profile_photo'              => \PeerRaiser\Helper\View::get_avatar(),
	        'password_form'              => $this->get_password_form(),
        );
        $this->assign( 'peerraiser', $view_args );

        $page = ( isset( $_GET['page'] ) ) ? $_GET['page'] : 'profile';

        return $this->get_text_view( 'frontend/participant-dashboard-' . $page );
    }

    public function register_settings_fields() {
        $dashboard_model = new \PeerRaiser\Model\Frontend\Dashboard();

        $default_fields    = $dashboard_model->get_fields();
        $fields            = apply_filters( 'peerraiser_participant_dashboard_fields', $default_fields );

        // Fields that come with WordPress by default
        $wordpress_fields = array( 'user_pass', 'user_login', 'user_nicename', 'user_url', 'user_email', 'display_name',
	        'nickname', 'first_name', 'last_name', 'description', 'rich_editing', 'user_registered', 'role', 'jabber',
	        'aim', 'yim', 'show_admin_bar_front' );

        $cmb = new_cmb2_box( array(
            'id'               => 'dashboard_settings',
            'object_types'     => array( 'user' ),
            'new_user_section' => 'add-existing-user'
        ) );

        foreach ( $fields as $field ) {
            if ( is_admin() && in_array($field['id'], $wordpress_fields) )
                continue;

            $cmb->add_field( $field );
        }
    }

    private function get_password_form() {
        return $this->get_text_view( 'frontend/partials/change-password-form' );
    }

    private function get_campaigns_accepting_donations() {
    	$campaign_model = new \PeerRaiser\Model\Campaign();
    	$campaign_statuses = apply_filters( 'peerraiser_campaign_statuses_that_allow_donations', array( 'active', 'ended' ) );

    	return $campaign_model->get_campaigns( array( 'campaign_status' => $campaign_statuses ) );
    }

    private function get_fundraisers_for_current_campaign() {
    	$campaign_slug = get_query_var( 'peerraiser_campaign', false );

    	if ( ! $campaign_slug ) {
    		return array();
	    }

	    $campaign = peerraiser_get_campaign_by_slug( $campaign_slug );

	    return $campaign->get_fundraisers();
    }

    private function get_campaign_select_class() {
	    $campaign_slug = get_query_var( 'peerraiser_campaign', false );

	    if ( ! $campaign_slug || apply_filters( 'peerraiser_donation_show_campaign_select', false ) ) {
	    	return 'show';
	    } else {
	    	return 'hide';
	    }
    }

    private function get_fundraiser_select_class() {
	    $campaign_slug = get_query_var( 'peerraiser_campaign', false );
    	$fundraiser_slug = get_query_var( 'peerraiser_fundraiser', false );

    	if ( ( $campaign_slug && ! $fundraiser_slug ) || apply_filters( 'peerraiser_donation_show_fundraiser_select', false ) ) {
    		return 'show';
	    } else {
    		return 'hide';
	    }
    }

}