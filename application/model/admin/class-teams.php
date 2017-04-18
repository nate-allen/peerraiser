<?php

namespace PeerRaiser\Model\Admin;

class Teams extends \PeerRaiser\Model\Admin {

    private $fields = array();

    public function __construct() {
        $this->fields = array(
            array(
                'title'    => 'Team Info',
                'id'       => 'peerraiser-team',
                'context'  => 'normal',
                'priority' => 'default',
                'fields'   => array(
                    'team_leader' => array(
                        'name'       => __('Team Leader', 'peerraiser'),
                        'id'         => '_peerraiser_team_leader',
                        'type'       => 'select',
                        'options_cb' => array( $this, 'get_participants_for_select_field'),
                        'attributes' => array(
                            'data-rule-required' => 'true',
                            'data-msg-required' => __( 'A team leader is required', 'peerraiser' ),
                        ),
                        'default_cb' => array( $this, 'get_field_value'),
                    ),
                    'campaign_id' => array(
                        'name'       => __('Campaign', 'peerraiser'),
                        'id'         => '_peerraiser_campaign_id',
                        'type'       => 'select',
                        'default'    => 'custom',
                        'options_cb' => array( $this, 'get_selected_term'),
                        'attributes' => array(
                            'data-rule-required' => 'true',
                            'data-msg-required' => __( 'A campaign is required', 'peerraiser' ),
                        ),
                        'default_cb' => array( $this, 'get_field_value'),
                    ),
                    'team_goal' => array(
                        'name' => __('Goal Amount', 'peerraiser'),
                        'id'   => '_peerraiser_team_goal',
                        'type' => 'text',
                        'attributes' => array(
                            'pattern' => '^\d*(\.\d{2}$)?',
                            'title'   => __('No commas. Cents (.##) are optional', 'peerraiser')
                        ),
                        'before_field' => $this->get_currency_symbol(),
                        'attributes' => array(
                            'data-rule-currency' => '["",false]',
                            'data-msg-currency' => __( 'Please use the valid currency format', 'peerraiser' ),
                            'data-rule-required' => 'true',
                            'data-msg-required' => __( 'A goal amount is required', 'peerraiser' ),
                        ),
                        'default_cb' => array( $this, 'get_field_value'),
                    ),
                    'thumbnail_image' => array(
                        'name'    => __('Team Thumbnail Image', 'peerraiser'),
                        'id'      => '_peerraiser_thumbnail_image',
                        'type'    => 'file',
                        'options' => array(
                            'url' => false,
                            'add_upload_file_text' => __( 'Add Image', 'peerraiser' )
                        ),

                        'default_cb' => array( $this, 'get_field_value'),
                    ),
                ),
            ),
        );
    }

    /**
     * Get all fields
     *
     * @since     1.0.0
     * @return    array    Field data
     */
    public function get_fields() {
        return $this->fields;
    }

    /**
     * Get a specific field by id
     *
     * @since     1.0.0
     * @param     string    $id    The field ID
     *
     * @return    array|false    The field data if available, or false if not
     */
    public function get_field( $id ) {
        if ( isset( $this->fields[$id] ) ) {
            return $this->fields[$id];
        } else {
            return false;
        }
    }

    /**
     * Add fields
     *
     * @since    1.0.0
     * @param    array    $fields    The fields to add
     *                               format: array( 'id' => array('key' => 'value' ) )
     *
     * @return    array    All of the current fields
     */
    public function add_fields( array $fields ) {
        array_push($this->fields, $fields);

        return $this->fields;
    }

    public function custom_label( $field_args, $field ) {

        $label = $field_args['name'];

        if ( $field_args['options']['tooltip'] ) {
            $label .= sprintf( '<span class="pr_tooltip"><i class="pr_icon fa %s"></i><span class="pr_tip">%s</span></span>', $field_args['options'][ 'tooltip-class' ], $field_args['options'][ 'tooltip' ]);
        }

        return $label;
    }

    /**
     * Get posts for CMB2 Select fields
     *
     * @since     1.0.0
     * @param     CMB2_Field    $field    The CMB2 field object
     * @return    array                   An array of posts
     */
    public function get_posts_for_select_field( $field ) {

        switch ( $field->args['name'] ) {
            case 'Campaign':
            case 'Campaigns':
                $post_type = 'pr_campaign';
                break;
            case 'Team':
            case 'Teams':
                $post_type = 'pr_team';
                break;
            case 'Fundraiser':
            case 'Fundraisers':
                $post_type = 'fundraiser';
                break;
            default:
                $post_type = 'post';
                break;
        }

        // Empty array to fill with posts
        $results = array();

        // WP_Query arguments
        $args = array (
            'post_type'              => array( $post_type ),
            'posts_per_page'         => '-1'
        );

        // The Query
        $query = new \WP_Query( $args );
        $posts = $query->get_posts();

        foreach($posts as $post) {
            $title = '(ID: ' . $post->ID .') '. $post->post_title;
            $results[$post->ID] = $title;
        }

        return $results;
    }

    public function get_selected_post( $field ) {
        // Empty array to fill with posts
        $results = array();

        if ( isset($field->value) && $field->value !== '' ) {
            $post = get_post($field->value);
            $results[$field->value] = get_the_title( $post );
        }

        return $results;
    }

    public function get_selected_term( $field ) {
        // Empty array to fill with posts
        $results = array();
	    $team_model = new \PeerRaiser\Model\Team( $_GET['team'] );
	    $short_field = substr( $field->args['id'], 12 );

        if ( isset($team_model->$short_field) && $team_model->$short_field !== '' ) {
            $term = get_term($team_model->$short_field);
            $results[$team_model->$short_field] = $term->name;
        }

        return $results;
    }

    public function get_participants_for_select_field( $field ) {
        // Empty array to fill with posts
        $results = array();

        if ( isset($field->value) ) {
            $user_info = get_userdata($field->value);
            if ( $user_info ) {
                $results[$field->value] = $user_info->display_name;
            }
        }

        return $results;
    }

    public function get_fundraisers( $post_id, $paged = 1 ){
        $args = array(
            'post_type'       => 'fundraiser',
            'posts_per_page'  => 20,
            'post_status'     => 'publish',
            'connected_type'  => 'fundraiser_to_team',
            'connected_items' => $post_id,
            'paged' => $paged
        );
        return new \WP_Query( $args );
    }

    public function get_participants( $post_id, $paged = 1 ){
        $args = array(
            'number'  => 20,
            // 'connected_type'  => 'team_to_participants',
            // 'connected_items' => $post_id,
            'paged' => $paged
        );
        return new \WP_User_Query( $args );
    }

    private function get_currency_symbol(){
        $plugin_options = get_option( 'peerraiser_options', array() );
        $currency = new \PeerRaiser\Model\Currency();
        return $currency->get_currency_symbol_by_iso4217_code($plugin_options['currency']);
    }

    public function get_teams_by_campaign( $campaign, $count = false ) {
        $args = array(
            "fields"    => "ids",
            "post_type' => 'fundraiser",
            "tax_query" => array(
                "taxonomy" => "peerraiser_campaign",
                "field"    => is_int( $campaign ) ? 'id' : 'slug',
                "terms"    => $campaign
            )
        );

        $fundraiser_ids = get_posts( $args );

        return wp_get_object_terms( $fundraiser_ids, "peerraiser_team" );
    }

	public function get_field_value( $field ) {
		if ( ! isset( $_GET['team'] ) )
			return;

		$team_model = new \PeerRaiser\Model\Team( $_GET['team'] );
		$short_field = substr( $field['id'], 12 );

		switch ( $field['id'] ) {
			default:
				$field_value = isset( $team_model->$short_field ) ? $team_model->$short_field : '';
				break;
		}

		return $field_value;
	}

	public function get_required_field_ids() {
		$required_fields = array();

		foreach ( $this->fields as $field_group ) {
			foreach ( $field_group['fields'] as $field ) {
				if ( isset( $field['attributes']['data-rule-required'] ) ) {
					$required_fields[] =  $field['id'];
				}
			}
		}

		return $required_fields;
	}

	public function get_field_ids() {
		$ids = array();
		foreach ( $this->fields as $field_group ) {
			$ids = array_merge( $ids, wp_list_pluck( $field_group['fields'], 'id' ) );
		}

		return $ids;
	}

}
