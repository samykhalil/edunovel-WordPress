<?php
/**
 * WCS_ATT_Stripe_Compatibility class
 *
 * @package  WooCommerce All Products For Subscriptions
 * @since    3.1.30
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stripe Compatibility.
 *
 * @version  3.1.30
 */
class WCS_ATT_Stripe_Compatibility {

	public static function init() {
		add_filter( 'wc_stripe_hide_payment_request_on_product_page', array( __CLASS__, 'hide_stripe_quickpay' ), 10, 2 );
	}

	/**
	 * Hide Stripe Quick-pay buttons for products with Subscription plans.
	 *
	 * @since 3.1.30
	 *
	 */
	public static function hide_stripe_quickpay( $hide_button, $post ) {

		global $product;

		$the_product = $product && is_a( $product, 'WC_Product' ) ? $product : wc_get_product( $post->ID );

		if ( ! $the_product ) {
			return $hide_button;
		}

		if ( WCS_ATT_Product_Schemes::has_subscription_schemes( $product ) ) {
			$hide_button = true;
		}

		return $hide_button;
	}
}

WCS_ATT_Stripe_Compatibility::init();
