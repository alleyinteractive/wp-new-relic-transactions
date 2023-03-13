<?php
/**
 * Plugin Name: WP New Relic Transactions
 * Plugin URI: https://github.com/alleyinteractive/wp-new-relic-transactions
 * Description: A companion plugin when using New Relic with WordPress, to improve the recorded transaction data.
 * Version: 0.1.0
 * Author: Matthew Boynes
 * Author URI: https://github.com/alleyinteractive/wp-new-relic-transactions
 * Requires at least: 5.9
 * Tested up to: 6.1.1
 *
 * Text Domain: wp-new-relic-transactions
 * Domain Path: /languages/
 *
 * @package wp-new-relic-transactions
 */

namespace Alley\WP_New_Relic_Transactions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Root directory to this plugin.
 *
 * @var string
 */
define( 'WP_NEW_RELIC_TRANSACTIONS_DIR', __DIR__ );

// Load the plugin's main files.
require_once WP_NEW_RELIC_TRANSACTIONS_DIR . '/src/interface-with-new-relic.php';
require_once WP_NEW_RELIC_TRANSACTIONS_DIR . '/src/class-new-relic.php';
require_once WP_NEW_RELIC_TRANSACTIONS_DIR . '/src/class-wp-new-relic-transactions.php';

/**
 * Instantiate the plugin.
 */
function main(): void {
	/**
	 * Create the NR wrapper. Filter this to allow the dependency to be swapped.
	 *
	 * @param With_New_Relic $new_relic New Relic wrapper.
	 */
	$new_relic = apply_filters( 'wp_new_relic_transactions_wrapper', new New_Relic() );

	// Create the core plugin object.
	$plugin = new WP_New_Relic_Transactions( $new_relic );

	/**
	 * Announce that the plugin has been initialized and share the instance.
	 *
	 * @param WP_New_Relic_Transactions $plugin Plugin class instance.
	 */
	do_action( 'wp_new_relic_transactions_init', $plugin );

	$plugin->boot();
}
add_action( 'after_setup_theme', __NAMESPACE__ . '\main' );
