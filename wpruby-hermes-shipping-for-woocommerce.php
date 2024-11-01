<?php
/**
 * Plugin Name: Shipping Method for Hermes Germany and WooCommerce
 * Plugin URI: https://wpruby.com
 * Description: Shipping Calculator for Hermes Germany and WooCommerce
 * Version: 1.0.3
 * WC requires at least: 3.0
 * WC tested up to: 8.4
 * Author: WPRuby
 * Author URI: https://waseem.blog
 * License: GPLv2 or later
 * License URI:  http://www.gnu.org/licenses/gpl-2.0.html
 * Domain Path: /language
 * Text Domain: shipping-method-for-hermes-germany-and-wc
 */


define("WPRUBY_HERMES_PLUGIN_ID", "hermes-germany");

require_once plugin_dir_path(__FILE__) . '/vendor/autoload.php';


class WPRuby_Hermes_Shipping_For_WooCommerce {
	public static $_instance;

	/**
	 * @return WPRuby_Hermes_Shipping_For_WooCommerce
	 */
	public static function get_instance() {
		if (self::$_instance) {
			return self::$_instance;
		}

		return new self();
	}

	/**
	 * WPRuby_Hermes_Shipping_For_WooCommerce constructor.
	 */
	public function __construct() {
		if (! $this->is_woocommerce_active()) {
			return;
		}

		add_action( 'init', [$this, 'load_plugin_textdomain'] );
		add_action('woocommerce_shipping_init', [$this, 'hermes_germany_shipping']);
		add_action('woocommerce_shipping_init', [$this, 'hermes_germany_shipping']);
		add_filter('woocommerce_shipping_methods', [$this, 'add_hermes_germany_shipping_method']);
	}

	/**
	 * @return bool
	 */
	private function is_woocommerce_active() {
		$active_plugins = (array) get_option( 'active_plugins', array() );

		if ( is_multisite() )
			$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );

		return in_array( 'woocommerce/woocommerce.php', $active_plugins ) || array_key_exists( 'woocommerce/woocommerce.php', $active_plugins );
	}

	public function hermes_germany_shipping()
	{
		require_once plugin_dir_path(__FILE__) . '/includes/class-wpruby-hermes-calculate-shipping-price.php';
		require_once plugin_dir_path(__FILE__) . '/class-hermes-germany-shipping.php';
	}

	public function add_hermes_germany_shipping_method($methods)
	{
		$methods[WPRUBY_HERMES_PLUGIN_ID] = 'Hermes_Germany_Shipping';
		return $methods;
	}

	public function load_plugin_textdomain() {
		$domain = 'shipping-method-for-hermes-germany-and-wc';
		$mo_file = dirname(  __FILE__ ) . '/language/' . $domain . '-' . get_locale() . '.mo';

		load_textdomain( $domain, $mo_file );
		load_plugin_textdomain( $domain, false, dirname( plugin_basename( __FILE__ ) ) . '/language/' );
	}

}

WPRuby_Hermes_Shipping_For_WooCommerce::get_instance();