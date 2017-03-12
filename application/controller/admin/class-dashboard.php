<?php

namespace PeerRaiser\Controller\Admin;

use \PeerRaiser\Controller\Base;
use \PeerRaiser\Model\Currency;
use \PeerRaiser\Model\Activity_Feed;
use \PeerRaiser\Helper\Stats;
use \PeerRaiser\Helper\View;

class Dashboard extends Base {

    public function register_actions() {
        add_action( 'wp_ajax_peerraiser_dismiss_message',              array( $this, 'process_dismiss_message_request' ) );
        add_action( 'wp_ajax_peerraiser_dismiss_message-post-new.php', array( $this, 'peerraiser_on_ajax_send_json' ) );
    }

    /**
     * @see \PeerRaiser\Core\View::load_assets()
     */
    public function load_assets() {
        parent::load_assets();

        // load page-specific CSS
        wp_register_style(
            'peerraiser-admin-dashboard',
            $this->config->get( 'css_url' ) . 'peerraiser-admin-dashboard.css',
            array( 'peerraiser-font-awesome' ),
            $this->config->get( 'version' )
        );
        wp_enqueue_style( 'peerraiser-admin-dashboard' );

        // load page-specific JS
        wp_register_script(
            'peerraiser-select2',
            $this->config->get( 'js_url' ) . 'vendor/select2.min.js',
            array( 'jquery' ),
            $this->config->get( 'version' ),
            true
        );
        wp_register_script(
            'peerraiser-backend-dashboard',
            $this->config->get( 'js_url' ) . 'peerraiser-backend-dashboard.js',
            array( 'jquery', 'peerraiser-select2' ),
            $this->config->get( 'version' ),
            true
        );
        wp_enqueue_script( 'peerraiser-select2' );
        wp_enqueue_script( 'peerraiser-backend-dashboard' );

        // translations
        $i18n = array(
            'total'   => __( 'Total', 'peerraiser' ),
            'sales'   => __( 'sales', 'peerraiser' ),
            'signups' => __( 'signups', 'peerraiser' ),
            'delete'  => __( 'Delete', 'peerraiser' ),
        );

        // pass localized strings and variables to script
        // $time_passes_model  = new \PeerRaiser\Model\TimePass();

        $plugin_options = get_option( 'peerraiser_options', array() );

        wp_localize_script(
            'peerraiser-backend-dashboard',
            'pr_dashboard_variables',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'template_directory' => get_template_directory_uri()
            )
        );
    }

    /**
     * @see \PeerRaiser\Core\View::render_page
     */
    public function render_page() {
        $this->load_assets();

        $plugin_options = get_option( 'peerraiser_options', array() );

        $currency        = new Currency();
        $activity_feed   = new Activity_Feed();
        $currency_symbol = $currency->get_currency_symbol_by_iso4217_code($plugin_options['currency']);


        $view_args = array(
            'activity_feed'        => $activity_feed->get_activity_feed(),
            'currency_symbol'      => $currency_symbol,
            'standard_currency'    => $plugin_options['currency'],
            'show_welcome_message' => filter_var($plugin_options['show_welcome_message'], FILTER_VALIDATE_BOOLEAN),
            'display_name'         => $this->get_current_users_name(),
            'plugin_version'       => $plugin_options['peerraiser_version'],
            'admin_url'            => get_admin_url(),
            'donations_total'      => View::format_number( Stats::get_total_donations(), true, true ),
            'campaigns_total'      => View::format_number( $this->get_campaign_total(), false, true ),
            'fundraisers_total'    => View::format_number( $this->get_fundraiser_total(), false, true ),
            'donors_total'         => View::format_number( $this->get_donor_total(), false, true ),
            'font_awesome_class'   => array(
                'step_1'           => 'fa-square-o',
                'step_2'           => 'fa-square-o',
                'step_3'           => $this->get_campaign_status()
            ),
            'top_donors'           => Stats::get_top_donors(),
            'top_fundraisers'      => Stats::get_top_fundraisers(),
        );

        $this->assign( 'peerraiser', $view_args );

        $this->render( 'backend/dashboard' );
    }


    private function get_current_users_name(){
        $current_user = wp_get_current_user();
        return ( isset($current_user->user_firstname) && !empty($current_user->user_firstname) ) ? $current_user->user_firstname : $current_user->display_name;
    }


    private function get_campaign_status() {
        $campaigns_count = wp_count_posts( 'pr_campaign' );
        return ( $campaigns_count->publish > 0 ) ? 'fa-check-square-o' : 'fa-square-o';
    }


    private function get_campaign_total(){
        $campaigns_count = wp_count_posts( 'pr_campaign' );
        return $campaigns_count->publish;
    }


    private function get_fundraiser_total(){
        $fundraisers_count = wp_count_posts( 'fundraiser' );
        return $fundraisers_count->publish;
    }


    private function get_donor_total(){
        $donors_count = wp_count_posts( 'pr_donor' );
        return $donors_count->publish;
    }


    public function process_dismiss_message_request() {
        $result = array(
            'success' => false,
            'message' => __( 'An error occurred when trying to retrieve the information. Please try again.', 'peerraiser' ),
        );

        // Attempt to verify the nonce, and exit early if it fails
        if ( !wp_verify_nonce( $_POST['nonce'], 'dismiss_'.$_POST['message_type'] ) ) {
            $result = array(
                'success' => false,
                'message' => __( 'Verify nonce failed.', 'peerraiser' ),
            );
            return $result;
        }

        // Set "show welcome message" option to false
        $plugin_options = get_option( 'peerraiser_options', array() );
        $plugin_options['show_welcome_message'] = false;
        update_option( 'peerraiser_options', $plugin_options );

        return array(
            'success' => true,
            'message' => __( 'Message has been dismissed', 'peerraiser' ),
        );
    }

}
