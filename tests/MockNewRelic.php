<?php

namespace Alley\WP_New_Relic_Transactions\Tests;

class MockNewRelic implements \Alley\WP_New_Relic_Transactions\With_New_Relic {

	public string $name;
	public array $params = [];
	public bool $is_background_job = false;

	/**
	 * @inheritDoc
	 */
	public function is_supported(): bool {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function name_transaction( string $name ): bool {
		$this->name = $name;
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function add_custom_parameter( string $key, float|bool|int|string $value ): bool {
		$this->params[ $key ] = $value;
		return true;
	}

	/**
	 * Reset the mock's data.
	 */
	public function reset(): void {
		unset( $this->name );
		$this->params = [];
	}

	/**
	 * Marks the current transaction as a background job.
	 *
	 * @param bool $flag Whether to mark the current transaction as a background job.
	 *                   If false is passed, the transaction is marked as a web transaction.
	 */
	public function background_job( bool $flag ): void {
		$this->is_background_job = $flag;
	}

	/**
	 * Ignore the current transaction in Apdex calculations.
	 */
	public function ignore_apdex(): void {
		// No-op.
	}
}
