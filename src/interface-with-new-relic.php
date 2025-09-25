<?php
/**
 * With_New_Relic Interface
 *
 * @package wp-new-relic-transactions
 */

namespace Alley\WP_New_Relic_Transactions;

interface With_New_Relic {
	/**
	 * Check if the plugin is supported.
	 *
	 * @return bool
	 */
	public function is_supported(): bool;

	/**
	 * Set custom name for current transaction.
	 *
	 * @param string $name Transaction name.
	 * @return bool Returns true if the transaction name was successfully changed.
	 *              If false is returned, check the agent log for more information.
	 */
	public function name_transaction( string $name ): bool;

	/**
	 * Attaches a custom attribute (key/value pair) to the current transaction and the current span (if enabled).
	 *
	 * @param string                $key   Attribute name.
	 * @param bool|float|int|string $value Attribute value.
	 * @return bool Returns true if the parameter was added successfully.
	 */
	public function add_custom_parameter( string $key, bool|float|int|string $value ): bool;

	/**
	 * Marks the current transaction as a background job.
	 *
	 * @param bool $flag Whether to mark the current transaction as a background job.
	 *                   If false is passed, the transaction is marked as a web transaction.
	 */
	public function background_job( bool $flag ): void;
}
