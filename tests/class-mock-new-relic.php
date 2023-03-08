<?php

namespace Alley\WP_New_Relic_Transactions\Tests;

class Mock_New_Relic implements \Alley\WP_New_Relic_Transactions\With_New_Relic {

	public string $name;
	public array $params = [];

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
}
