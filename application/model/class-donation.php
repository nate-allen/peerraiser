<?php

namespace PeerRaiser\Model;

use \PeerRaiser\Model\Donor as Donor_Model;
use \PeerRaiser\Model\Campaign as Campaign_Model;
use \PeerRaiser\Model\Fundraiser as Fundraiser_Model;
use \PeerRaiser\Model\Team as Team_Model;
use \PeerRaiser\Model\Database\Donation_Table as Donation_Database;
use \PeerRaiser\Model\Database\Donation_Meta_Table;

//TODO: 1. Set the team when a donation is made

/**
 * Donation Model
 *
 * Use this class to interact with PeerRaiser Donations
 */
class Donation {

    /**
     * The Donation ID
     *
     * @since  1.0.0
     * @var    integer
     */
    public    $ID  = 0;

    /**
     * The Protected Donation ID
     *
     * @since  1.0.0
     * @var    integer
     */
    protected $_ID = 0;

    /**
     * New or existing donation
     *
     * @since  1.0.0
     * @var boolean
     */
    protected $new = false;

    /**
     * The Gateway mode the donation was made in
     *
     * @since  1.0.0
     * @var string
     */
    protected $mode = 'live';

    /**
     * The total amount the donation is for, after fees are applied
     *
     * @since  1.0.0
     * @var float
     */
    protected $total = 0.00;

    /**
     * The Subtotal for the donation before fees
     *
     * @since  1.0.0
     * @var float
     */
    protected $subtotal = 0;

    /**
     * Fees for this donation
     *
     * @since  1.0.0
     * @var float
     */
    protected $fees = 0;

    /**
     * The date the donation was created
     *
     * @since  1.0.0
     * @var string
     */
    protected $date = '';

    /**
     * The status of the donation
     *
     * @since  1.0.0
     * @var string
     */
    protected $status = 'pending';

    /**
     * When updating, the old status prior to the change
     *
     * @since  1.0.0
     * @var string
     */
    protected $old_status = '';

    /**
     * The display name of the current donation status
     *
     * @since  1.0.0
     * @var string
     */
    protected $status_nicename = '';

    /**
     * The ID of the donor that made the donation
     *
     * @since  1.0.0
     * @var integer
     */
    protected $donor_id = null;

    /**
     * The User ID of the donor (if they have one) that made the donation
     *
     * @since  1.0.0
     * @var integer
     */
    protected $user_id = 0;

    /**
     * The Campaign ID this donation was made to
     *
     * @since  1.0.0
     * @var integer
     */
    protected $campaign_id = 0;

    /**
     * The Fundraiser ID this donation was made to (if applicable)
     *
     * @since  1.0.0
     * @var integer
     */
    protected $fundraiser_id = 0;

    /**
     * The Team ID this donation was made to (if applicable)
     *
     * @since  1.0.0
     * @var integer
     */
    protected $team_id = 0;

    /**
     * The first name of the donor
     *
     * @since  1.0.0
     * @var string
     */
    protected $first_name = '';

    /**
     * The last name of the donor
     *
     * @since  1.0.0
     * @var string
     */
    protected $last_name = '';

    /**
     * The email used for the donation
     *
     * @since  1.0.0
     * @var string
     */
    protected $email = '';

    /**
     * The physical address used for the donation if provided
     *
     * @since  1.0.0
     * @var array
     */
    protected $address = array();

    /**
     * The transaction ID returned by the gateway
     *
     * @since  1.0.0
     * @var string
     */
    protected $transaction_id = '';

    /**
     * IP Address donation was made from
     *
     * @since  1.0.0
     * @var string
     */
    protected $ip = '';

    /**
     * The gateway used to process the donation
     *
     * @since  1.0.0
     * @var string
     */
    protected $gateway = '';

    /**
     * The donation type (Credit card, check, cash, etc)
     *
     * @since  1.0.0
     * @var string
     */
    protected $donation_type = '';

    /**
     * The currency the donation was made with
     *
     * @since  1.0.0
     * @var string
     */
    protected $currency = '';

    /**
     * Donation notes
     *
     * @since 1.0.0
     * @var array
     */
    protected $notes = array();

    /**
     * Array of items that have changed since the last save() was run
     * This is for internal use, to allow fewer update_donation_meta calls to be run
     *
     * @since  1.0.0
     * @var array
     */
    private $pending;

    /**
     * Setup donation class
     *
     * @since 1.0.0
     * @param int|boolean $id Donation ID or Transaction ID
     * @param string      $by What to lookup the donation by (donation_id or transaction_id)
     */
    public function __construct( $id = false, $by = 'donation_id' ) {
        if ( empty( $id ) ) {
            return false;
        }

        $donation_table = new Donation_Database();

        $args = array( 'number' => 1 );

        switch ( strtolower($by) ) {
            case 'transaction':
            case 'transaction_id':
                $args['transaction_id'] = $id;
                break;
            case 'id':
            case 'donation_id':
                $args['donation_id'] = absint( $id );
                break;
            default:
                return false;
                break;
        }

        $donation = current( $donation_table->get_donations( $args ) );

        if ( empty( $donation ) ) {
            return false;
        }

        $this->setup_donation( $donation );

        return $this;
    }

    /**
     * Run when reading data from inaccessible properties.
     *
     * @since  1.0.0
     * @param  string $key  The property
     * @return mixed        The value
     */
    public function __get( $key ) {
        if ( method_exists( $this, 'get_' . $key ) ) {
            $value = call_user_func( array( $this, 'get_' . $key ) );
        } else {
            $value = $this->$key;
        }

        return $value;
    }

    /**
     * Run when writing data to inaccessible properties.
     *
     * @since  1.0.0
     * @param string $key   The property name
     * @param mixed $value  The value of the property
     */
    public function __set( $key, $value ) {
        $ignore = array( '_ID' );

        if ( $key === 'status' ) {
            $this->old_status = $this->status;
        }

        if ( ! in_array( $key, $ignore ) ) {
            $this->pending[$key] = $value;
        }

        if( '_ID' !== $key ) {
            $this->$key = $value;
        }
    }

    /**
     * Run when isset() or empty() is called on inaccessible properties.
     *
     * @since  1.0.0
     * @param  string  $name The attribute to get
     * @return boolean       If the item is set or not
     */
    public function __isset( $name ) {
        if ( property_exists( $this, $name) ) {
            return false === empty( $this->$name );
        } else {
            return null;
        }
    }

    /**
     * Setup the donation properties
     *
     * @since     1.0.0
     * @param     object    $donation    A donation object
     * @return    bool                   True if the setup worked, false if not
     */
    private function setup_donation( $donation ) {
        $this->pending = array();

        // Perform your actions before the donation is loaded with this hook:
        do_action( 'peerraiser_before_setup_donation', $this, $donation );

        // Primary Identifier
        $this->ID = absint( $donation->donation_id );

        // Protected ID (can't be changed)
        $this->_ID = absint( $donation->donation_id);

        // Status and Dates
        $this->date   = $donation->date;
        $this->status = $donation->status;

        // Money related
        $this->total    = $donation->total;
        $this->subtotal = $donation->subtotal;

        // Gateway related
        $this->gateway        = $this->get_meta( 'gateway', true );
        $this->transaction_id = $donation->transaction_id;
        $this->donation_type  = $this->get_meta( 'donation_type', true );

        // User related
        $this->ip             = $donation->ip;
        $this->donor_id       = $donation->donor_id;
        $this->campaign_id    = $donation->campaign_id;
        $this->fundraiser_id  = $donation->fundraiser_id;
        $this->team_id        = $donation->team_id;

        // Donation Notes
        $donation_notes = $this->get_meta( 'notes', true );
        $this->notes = ! empty( $donation_notes ) ? $donation_notes : array();

        // Add your own items to this object via this hook:
        do_action( 'peerraiser_after_setup_donation', $this, $donation );

        return true;
    }

    /**
     * Creates a donation record in the database
     *
     * @since     1.0.0
     *
     * @return    int|\WP_Error    Donation ID
     */
    private function insert_donation() {
        if ( empty( $this->transaction_id ) ) {
            $this->transaction_id = $this->generate_transaction_id();
        }

        if ( empty( $this->donor_id ) ) {
			return new \WP_Error( 'peerraiser_missing_donor_id', __( "A donor ID is required to make a donation", "peerraiser" ) );
        }

        // If no team is currently set, try to see if the fundraiser is part of one
        if ( ! $this->team_id && $this->fundraiser_id ) {
            $fundraiser = new \PeerRaiser\Model\Fundraiser( $this->fundraiser_id );

            if ( $team_id = $fundraiser->team_id ) {
                $this->team_id = $team_id;
                $this->pending['team_id'] = $team_id;
            }
        }

        $this->increase_donor_amounts();
        $this->increase_campaign_amounts();
        $this->increase_fundraiser_amounts();
        $this->increase_team_amounts();

        if ( empty( $this->ip ) ) {
            $this->ip = $this->get_ip_address();
        }

        if ( empty( $this->date ) ) {
            $this->date = current_time( 'mysql' );
        }

        $donation_table = new Donation_Database();
        $donation_id    = $donation_table->add_donation( $this );

        $this->ID  = $donation_id;
        $this->_ID = $donation_id;

        return $this->ID;
    }

    /**
     * Save information to the database
     *
     * @since 1.0.0
     * @return bool  True of the save occurred, false if it failed
     */
    public function save() {
        if ( empty( $this->ID ) ) {
            $this->insert_donation();
        }

        if ( $this->ID !== $this->_ID ) {
            $this->ID = $this->_ID;
        }

        if ( ! empty( $this->pending ) ) {
            foreach ( $this->pending as $key => $value ) {
                switch( $key ) {
                    case 'donation_type' :
                        $this->update_meta( 'donation_type', $this->donation_type );
                        break;

                    case 'gateway' :
                        $this->update_meta( 'gateway', $this->gateway );
                        break;

                    case 'notes' :
                        $this->update_meta( 'notes', $this->notes );
                        break;

                    default :
                        do_action( 'peerraiser_donation_save', $this, $key );
                        break;
                }
            }
        }

        do_action( 'peerraiser_donation_saved', $this->ID, $this );

        $cache_key = md5( 'peerraiser_donation_' . $this->ID );
        wp_cache_set( $cache_key, $this, 'donations' );

        return true;
    }

    /**
     * Delete the donation record
     */
    public function delete() {
        do_action( 'peerraiser_pre_delete_donation', $this );

		$donation_table = new Donation_Database();
		$donation_table->delete( $this->ID );

        $this->decrease_donor_amounts();
        $this->decrease_campaign_amounts();
        $this->decrease_fundraiser_amounts();
        $this->decrease_fundraiser_amounts();
        $this->decrease_team_amounts();

		do_action( 'peerraiser_post_delete_donation', $this );
	}

    /**
     * Add a note to a donation
     *
     * @param string $what The donation note content
     * @param string $when When the note was added
     * @param string $who  Who added the note
     *
     * @return array
     */
	public function add_note( $what = '', $who = 'bot', $when = 'now' ) {
        $notes = $this->notes;

        if ( 'now' === $when ) {
            $when = current_time( 'mysql' );
        }

        if ( 'bot' === $who ) {
            $who = __( 'PeerRaiser Bot', 'peerraiser' );
        }

        $notes[] = array(
            'id'   => md5( $what . time() ),
            'when' => esc_attr( $when ),
            'who'  => esc_attr( $who ),
            'what' => wp_strip_all_tags( $what )
        );

        $this->notes = $notes;
        $this->pending['notes'] = $this->notes;

        return $this->notes;
    }

    /**
     * Gets the IP address
     *
     * Returns the IP address of the current user
     *
     * @since 1.0.0
     * @return string $ip User's IP address
     */
    private function get_ip_address() {

        $ip = '127.0.0.1';

        if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        $ip_array = explode( ',', $ip );
        $ip_array = array_map( 'trim', $ip_array );

        return apply_filters( 'peerraiser_set_ip', $ip_array[0] );
    }

    /**
     * Generate a random transaction ID
     *
     * This is used if a transaction ID wasn't passed by PeerRaiser.com
     * For example, if this donation was added manually.
     *
     * @since     1.0.0
     * @return    string    Random transaction id
     */
    private function generate_transaction_id() {
        return md5( mt_rand() . time() );
    }

    /**
     * Update donation meta
     *
     * @since     1.0.0
     * @param     string    $meta_key      Meta key to update
     * @param     string    $meta_value    Meta value
     * @param     string    $prev_value    Previous value
     * @return    int|bool                 Meta ID if the key didn't exist, true on success, false on failure
     */
    public function update_meta( $meta_key = '', $meta_value = '', $prev_value = '' ) {
        $meta_value = apply_filters( 'peerraiser_update_donation_meta_' . $meta_key, $meta_value, $this->ID );

        $donation_meta = new Donation_Meta_Table();

        return $donation_meta->update_meta( $this->ID, $meta_key, $meta_value, $prev_value);
    }

    /**
     * Get donation meta
     *
     * @param string $meta_key
     * @param bool $single
     * @return mixed
     */
    public function get_meta( $meta_key= '', $single = false ) {
        $donation_meta = new Donation_Meta_Table();
        $result = $donation_meta->get_meta( $this->ID, $meta_key, $single );

        return $result;
    }

    public function get_donations_total() {
        $donation_table = new Donation_Database();
        $total = $donation_table->get_donations_total();

        return $total;
    }

    private function increase_donor_amounts() {
        $donor = new Donor_Model( $this->donor_id );

        $donor->increase_donation_count( 1 );
        $donor->increase_value( $this->total );
    }

    private function decrease_donor_amounts() {
        $donor = new Donor_Model( $this->donor_id );

        $donor->decrease_donation_count( 1 );
        $donor->decrease_value( abs( $this->total ) );
    }

    private function increase_fundraiser_amounts() {
        if ( empty( $this->fundraiser_id ) ) {
            return;
        }

        $fundraiser = new Fundraiser_Model( $this->fundraiser_id );

        $fundraiser->increase_donation_count( 1 );
        $fundraiser->increase_value( $this->total );
    }

    private function decrease_fundraiser_amounts() {
        if ( empty( $this->fundraiser_id ) ) {
            return;
        }

        $fundraiser = new Fundraiser_Model( $this->fundraiser_id );

        $fundraiser->decrease_donation_count( 1 );
        $fundraiser->decrease_value( abs( $this->total ) );
    }

    private function increase_team_amounts() {
        if ( empty( $this->team_id ) ) {
            return;
        }

        $team = new Team_Model( $this->team_id );

        $team->increase_donation_count( 1 );
        $team->increase_value( $this->total );
    }

    private function decrease_team_amounts() {
        if ( empty( $this->team_id ) ) {
            return;
        }

        $team = new Team_Model( $this->team_id );

        $team->decrease_donation_count( 1 );
        $team->decrease_value( abs( $this->total ) );
    }

    private function increase_campaign_amounts() {
        $campaign = new Campaign_Model( $this->campaign_id );

        $campaign->increase_donation_count( 1 );
        $campaign->increase_value( $this->total );
    }

    private function decrease_campaign_amounts() {
        $campaign = new Campaign_Model( $this->campaign_id );

        $campaign->decrease_donation_count( 1 );
        $campaign->decrease_value( abs( $this->total ) );
    }
}
