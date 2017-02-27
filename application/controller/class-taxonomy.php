<?php

namespace PeerRaiser\Controller;

/**
 * PeerRaiser taxonomy controller.
 */
class Taxonomy extends Base {

    public function register_actions() {
        add_action( 'init', array( $this, 'register_taxonomies' ) );
    }

    public function register_taxonomies(){
        $this->register_campaign_taxonomy();
        $this->register_team_taxonomy();
    }

    private function register_campaign_taxonomy() {
        $plugin_options = get_option( 'peerraiser_options', array() );

        $labels = array(
            'name'                       => _x( 'Campaigns', 'taxonomy general name', 'peerraiser' ),
            'singular_name'              => _x( 'Campaign', 'taxonomy singular name', 'peerraiser' ),
            'search_items'               => __( 'Search Campaigns', 'peerraiser' ),
            'popular_items'              => __( 'Popular Campaigns', 'peerraiser' ),
            'all_items'                  => __( 'All Campaigns', 'peerraiser' ),
            'parent_item'                => null,
            'parent_item_colon'          => null,
            'edit_item'                  => __( 'Edit Campaign', 'peerraiser' ),
            'update_item'                => __( 'Update Campaign', 'peerraiser' ),
            'add_new_item'               => __( 'Add New Campaign', 'peerraiser' ),
            'new_item_name'              => __( 'New Campaign Name', 'peerraiser' ),
            'separate_items_with_commas' => __( 'Separate Campaigns with commas', 'peerraiser' ),
            'add_or_remove_items'        => __( 'Add or remove Campaigns', 'peerraiser' ),
            'choose_from_most_used'      => __( 'Choose from the most used Campaigns', 'peerraiser' ),
            'not_found'                  => __( 'No Campaigns found.', 'peerraiser' ),
            'menu_name'                  => __( 'Campaigns', 'peerraiser' ),
        );

        $args = array(
            'hierarchical'          => false,
            'labels'                => $labels,
            'show_ui'               => true,
            'show_admin_column'     => true,
            'update_count_callback' => '_update_post_term_count',
            'query_var'             => true
        );

        $objects = array( 'fundraiser', 'donation', 'donor' );

        register_taxonomy(
            'peerraiser_campaign',
            $objects,
            $args
        );
    }

    private function register_team_taxonomy() {
        $plugin_options = get_option( 'peerraiser_options', array() );

        $labels = array(
            'name'                       => _x( 'Teams', 'taxonomy general name', 'peerraiser' ),
            'singular_name'              => _x( 'Team', 'taxonomy singular name', 'peerraiser' ),
            'search_items'               => __( 'Search Teams', 'peerraiser' ),
            'popular_items'              => __( 'Popular Teams', 'peerraiser' ),
            'all_items'                  => __( 'All Teams', 'peerraiser' ),
            'parent_item'                => null,
            'parent_item_colon'          => null,
            'edit_item'                  => __( 'Edit Team', 'peerraiser' ),
            'update_item'                => __( 'Update Team', 'peerraiser' ),
            'add_new_item'               => __( 'Add New Team', 'peerraiser' ),
            'new_item_name'              => __( 'New Team Name', 'peerraiser' ),
            'separate_items_with_commas' => __( 'Separate Teams with commas', 'peerraiser' ),
            'add_or_remove_items'        => __( 'Add or remove Teams', 'peerraiser' ),
            'choose_from_most_used'      => __( 'Choose from the most used Teams', 'peerraiser' ),
            'not_found'                  => __( 'No Teams found.', 'peerraiser' ),
            'menu_name'                  => __( 'Teams', 'peerraiser' ),
        );

        $args = array(
            'hierarchical'          => false,
            'labels'                => $labels,
            'show_ui'               => false,
            'show_admin_column'     => false,
            'update_count_callback' => '_update_post_term_count',
            'query_var'             => true
        );

        $objects = array( 'fundraiser' );

        register_taxonomy(
            'peerraiser_team',
            apply_filters( 'peerraiser_team_objects', $objects ),
            apply_filters( 'peerraiser_team_taxonomy_args', $args )
        );
    }

}