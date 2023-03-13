<?php
namespace Alley\WP_New_Relic_Transactions\Tests\Feature;

use Alley\WP_New_Relic_Transactions\Tests\Test_Case;

/**
 * Visit {@see https://mantle.alley.co/testing/test-framework.html} to learn more.
 */
class Requests_Test extends Test_Case {
	protected $nr;

	protected function setUp(): void {
		parent::setUp();
		$GLOBALS['wp_new_relic_transactions_plugin']->named = false;
		$this->nr = $GLOBALS['mock_new_relic'];
		$this->nr->reset();
	}

	public function test_homepage() {
		$this->expectApplied( 'wp_new_relic_transactions_name' )
		     ->once()
		     ->with( 'homepage' );
		$this->get( '/' );

		$this->assertSame( 'homepage', $this->nr->name );
	}

	public function test_post() {
		$post = static::factory()->post->create_and_get();

		$this->expectApplied( 'wp_new_relic_transactions_name' )
		     ->once()
		     ->with( 'post' );
		$this->expectApplied( 'wp_new_relic_transactions_custom_parameters' )
		     ->once()
		     ->with( [
				 'post_id' => $post->ID,
				 'logged-in' => false,
		     ] );
		$this->get( $post );

		$this->assertSame( 'post', $this->nr->name );
		$this->assertSame( $post->ID, $this->nr->params['post_id'] );
	}

	public function test_page() {
		$page = static::factory()->page->create_and_get();

		$this->expectApplied( 'wp_new_relic_transactions_name' )
		     ->once()
		     ->with( 'post.page' );
		$this->get( $page );

		$this->assertSame( 'post.page', $this->nr->name );
		$this->assertSame( $page->ID, $this->nr->params['post_id'] );
	}
}
