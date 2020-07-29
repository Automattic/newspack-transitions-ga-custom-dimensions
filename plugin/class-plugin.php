<?php
/**
 * Main plugin class.
 *
 * @package Newspack
 */

namespace Newspack_Transitions_Ga_Custom_Dimensions;

/**
 * Newspack Transitions GA Custom Dimensions main Plugin class.
 */
class Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var Plugin|null
	 */
	private static $instance;

	/**
	 * Plugin constructor.
	 */
	private function __construct() {
	}

	/**
	 * Singleton get.
	 *
	 * @return Plugin
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Registers all plugin's functionality and components.
	 */
	public function register_plugin() {
		add_action( 'init', [ $this, 'register_dimensions_tracking' ] );
	}

	/**
	 * Registers custom dimensions tracking.
	 */
	public function register_dimensions_tracking() {
		// Get dimensions' IDs.
		$team_membership_dimension_id = defined( 'NEWSPACK_TRANSITIONS_GA_CUSTOM_DIMENSIONS_DIMENSION_TEAM_MEMBERSHIP_ID' )
			? NEWSPACK_TRANSITIONS_GA_CUSTOM_DIMENSIONS_DIMENSION_TEAM_MEMBERSHIP_ID
			: null;
		$user_dimension_id            = defined( 'NEWSPACK_TRANSITIONS_GA_CUSTOM_DIMENSIONS_DIMENSION_USER_ID' )
			? NEWSPACK_TRANSITIONS_GA_CUSTOM_DIMENSIONS_DIMENSION_USER_ID
			: null;
		if ( ! $team_membership_dimension_id || ! $user_dimension_id ) {
			return;
		}

		// Get tracked info: current User's ID and Team Membership ID which they are a Member of.
		$current_user_id = get_current_user_id();
		$team            = $this->get_users_team_membership( $current_user_id );
		if ( ! $team ) {
			return;
		}

		$team_membership_dimension_value = $team->get_name() . ' (' . $team->get_id() . ')';

		// Set both Team ID and User ID as custom dimensions.
		add_action(
			'wp_enqueue_scripts',
			function() use ( $user_dimension_id, $current_user_id ) {
				$this->set_ga_dimension( $user_dimension_id, $current_user_id );
			}
		);
		add_action(
			'wp_enqueue_scripts',
			function() use ( $team_membership_dimension_id, $team_membership_dimension_value ) {
				$this->set_ga_dimension( $team_membership_dimension_id, $team_membership_dimension_value );
			}
		);
	}

	/**
	 * Tell Site Kit to report the custom dimensions.
	 * More about custom dimensions:
	 * https://support.google.com/analytics/answer/2709828.
	 *
	 * @param string $dimension_id Custom dimension ID.
	 * @param string $dimension_value Custom dimension value.
	 */
	public function set_ga_dimension( $dimension_id, $dimension_value ) {

		// Non-AMP.
		add_filter(
			'googlesitekit_gtag_opt',
			function ( $gtag_opt ) use ( $dimension_id, $dimension_value ) {
				$gtag_opt[ $dimension_id ] = $dimension_value;
				return $gtag_opt;
			}
		);
		// AMP.
		add_filter(
			'googlesitekit_amp_gtag_opt',
			function ( $gtag_amp_opt ) use ( $dimension_id, $dimension_value ) {
				$tracking_id = $gtag_amp_opt['vars']['gtag_id'];
				$gtag_amp_opt['vars']['config'][ $tracking_id ][ $dimension_id ] = $dimension_value;
				return $gtag_amp_opt;
			}
		);
	}

	/**
	 * Get the Team of which this User is a Member.
	 *
	 * @param int $user_id User ID.
	 *
	 * @return \SkyVerge\WooCommerce\Memberships\Teams\Team|null
	 */
	public function get_users_team_membership( $user_id ) {
		$teams = $this->get_all_teams();
		if ( empty( $teams ) ) {
			return null;
		}

		foreach ( $teams as $team ) {
			if ( $team->is_user_member( $user_id ) ) {
				return $team;
			}
		}

		return null;
	}

	/**
	 * Fetches all WC Memberships Teams.
	 *
	 * @return \SkyVerge\WooCommerce\Memberships\Teams\Team[]
	 */
	private function get_all_teams() {
		$teams = [];

		$args  = array(
			'numberposts' => -1,
			'post_type'   => 'wc_memberships_team',
		);
		$query = new \WP_Query( $args );
		if ( ! $query->have_posts() ) {
			return $teams;
		}

		$teams_ids = [];
		while ( $query->have_posts() ) {
			$query->the_post();
			$teams_ids[] = get_the_ID();
		}
		if ( empty( $teams_ids ) ) {
			return $teams;
		}

		foreach ( $teams_ids as $team_id ) {
			$teams[] = wc_memberships_for_teams_get_team( $team_id );
		}

		return $teams;
	}
}
