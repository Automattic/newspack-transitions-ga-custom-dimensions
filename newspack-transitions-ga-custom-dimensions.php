<?php
/**
 * Plugin Name: Newspack Transitions Google Analytics Custom Dimensions
 * Description: Inserts Transitions Online custom dimensions for Google Analytics. Requires Google Site Kit plugin.
 * Version: 0.1
 * Author: Automattic
 * Author URI: https://newspack.blog/
 * License: GPL2
 *
 * @package Newspack_Transitions_Ga_Custom_Dimensions
 */

if ( ! defined( 'NEWSPACK_TRANSITIONS_GA_CUSTOM_DIMENSIONS_PLUGIN_FILE' ) ) {
	define( 'NEWSPACK_TRANSITIONS_GA_CUSTOM_DIMENSIONS_PLUGIN_FILE', __FILE__ );
}

// The GA custom dimensions' IDs with value `sprintf( "ga:dimension%d", $dimension_id )`, e.g. 'ga:dimension1'.
if ( ! defined( 'NEWSPACK_TRANSITIONS_GA_CUSTOM_DIMENSIONS_DIMENSION_TEAM_MEMBERSHIP_ID' ) ) {
	define( 'NEWSPACK_TRANSITIONS_GA_CUSTOM_DIMENSIONS_DIMENSION_TEAM_MEMBERSHIP_ID', 'ga:dimension2' );
}
if ( ! defined( 'NEWSPACK_TRANSITIONS_GA_CUSTOM_DIMENSIONS_DIMENSION_USER_ID' ) ) {
	define( 'NEWSPACK_TRANSITIONS_GA_CUSTOM_DIMENSIONS_DIMENSION_USER_ID', 'ga:dimension3' );
}

require_once dirname( NEWSPACK_TRANSITIONS_GA_CUSTOM_DIMENSIONS_PLUGIN_FILE ) . '/plugin/class-plugin.php';
( \Newspack_Transitions_Ga_Custom_Dimensions\Plugin::get_instance() )->register_plugin();
