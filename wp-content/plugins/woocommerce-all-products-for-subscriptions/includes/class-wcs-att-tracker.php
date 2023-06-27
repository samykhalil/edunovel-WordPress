<?php
/**
 * WCS_ATT_Tracker class
 *
 * @package  WooCommerce All Products For Subscriptions
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Product Bundle Helper Functions.
 *
 * @class    WCS_ATT_Tracker
 * @version  3.3.0
 */
class WCS_ATT_Tracker {

	/**
	 * Initialize the Tracker.
	 */
	public static function init() {
		if ( 'yes' === get_option( 'woocommerce_allow_tracking', 'no' ) ) {
			add_filter( 'woocommerce_tracker_data', array( __CLASS__, 'add_tracking_data' ), 10 );
		}
	}

	/**
	 * Adds APFS data to the WC tracked data.
	 *
	 * @param array $data
	 * @return array all the tracking data.
	 */
	public static function add_tracking_data( $data ) {

		$data[ 'extensions' ][ 'wc_apfs' ][ 'settings' ] = self::get_settings();

		return $data;
	}

	/**
	 * Gets WC settings data.
	 *
	 * @return array Subscriptions options data.
	 */
	private static function get_settings() {

		$cart_level_schemes = get_option( 'wcsatt_subscribe_to_cart_schemes', array() );

		return array(
			'cart_plans'                    => ! empty( $cart_level_schemes ) && is_array( $cart_level_schemes ) ? count( $cart_level_schemes ) : 0,
			'add_products_to_subscriptions' => 'off' === get_option( 'wcsatt_add_product_to_subscription', 'off' ) ? 'off' : 'on',
			'add_cart_to_subscriptions'     => 'off' === get_option( 'wcsatt_add_cart_to_subscription', 'off' ) ? 'off' : 'on',
		);
	}
}

WCS_ATT_Tracker::init();
