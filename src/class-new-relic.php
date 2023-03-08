<?php

namespace Alley\WP_New_Relic_Transactions;

class New_Relic implements With_New_Relic {

	/**
	 * Check if the plugin is supported.
	 *
	 * @return bool
	 */
	public function is_supported(): bool {
		return extension_loaded( 'newrelic' )
		       && function_exists( 'newrelic_add_custom_parameter' )
		       && function_exists( 'newrelic_name_transaction' );
	}

	/**
	 * Set custom name for current transaction.
	 *
	 * @param string $name Transaction name.
	 * @return bool Returns true if the transaction name was successfully changed.
	 *              If false is returned, check the agent log for more information.
	 */
	public function name_transaction( string $name ): bool {
		return newrelic_name_transaction( $name );
	}

	/**
	 * Attaches a custom attribute (key/value pair) to the current transaction and the current span (if enabled).
	 *
	 * @param string                $key   Attribute name.
	 * @param bool|float|int|string $value Attribute value.
	 * @return bool Returns true if the parameter was added successfully.
	 */
	public function add_custom_parameter( string $key, bool|float|int|string $value ): bool {
		return newrelic_add_custom_parameter( $key, $value );
	}
}
