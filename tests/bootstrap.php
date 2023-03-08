<?php
/**
 * wp-new-relic-transactions Test Bootstrap
 */

// Load Composer dependencies.
use Alley\WP_New_Relic_Transactions\Tests\Mock_New_Relic;

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

/*
 * Visit {@see https://mantle.alley.co/testing/test-framework.html} to learn more.
 */
\Mantle\Testing\manager()
	->maybe_rsync_plugin()
	// Load the main file of the plugin.
	->loaded( function() {
		require_once __DIR__ . '/../plugin.php';

		$GLOBALS['mock_new_relic'] = new Mock_New_Relic();

		add_filter( 'wp_new_relic_transactions_wrapper', fn () => $GLOBALS['mock_new_relic'] );

		// Load the plugin class into a global so the tests can manipulate it.
		add_action( 'wp_new_relic_transactions_init', function( $plugin ) {
			$GLOBALS['wp_new_relic_transactions_plugin'] = $plugin;
		} );
	} )
	->install();
