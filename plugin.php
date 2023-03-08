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
require_once WP_NEW_RELIC_TRANSACTIONS_DIR . '/src/class-wp-new-relic-transactions.php';

/**
 * Instantiate the plugin.
 */
function main() {
	$app = new WP_New_Relic_Transactions();
	/**
	 * Announce that the plugin has been initialized and share the instance.
	 *
	 * @param WP_New_Relic_Transactions $app Plugin class instance.
	 */
	do_action( 'wp_new_relic_transactions_init', $app );
}
add_action( 'after_setup_theme', __NAMESPACE__ . '\main' );
