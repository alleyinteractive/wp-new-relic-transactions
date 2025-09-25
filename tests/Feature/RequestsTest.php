<?php
namespace Alley\WP_New_Relic_Transactions\Tests\Feature;

use Alley\WP_New_Relic_Transactions\Tests\TestCase;

/**
 * Visit {@see https://mantle.alley.co/testing/test-framework.html} to learn more.
 */
class RequestsTest extends TestCase {
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
		$this->expectApplied( 'wp_new_relic_transactions_custom_parameters' )
		     ->once()
		     ->with( [
				'HTTP_REFERER'    => '',
				'HTTP_USER_AGENT' => '',
				'HTTPS'           => false,
				'logged-in'      => false,
		     ] );
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
				'HTTP_REFERER'    => '',
				'HTTP_USER_AGENT' => '',
				'HTTPS'           => false,
				 'post_id'        => $post->ID,
				 'logged-in'      => false,
		     ] );
		$this->get( $post );

		$this->assertSame( 'post', $this->nr->name );
		$this->assertSame( $post->ID, $this->nr->params['post_id'] );
	}

	public function test_logged_in(): void {
		$this->acting_as( 'administrator' );

		$this->expectApplied( 'wp_new_relic_transactions_name' )
		     ->once()
		     ->with( 'homepage' );

		$this->expectApplied( 'wp_new_relic_transactions_custom_parameters' )
		     ->once()
		     ->with( [
				'HTTP_REFERER'    => '',
				'HTTP_USER_AGENT' => '',
				'HTTPS'           => true,
				'logged-in'       => true,
		     ] );

		$this->with_https()->get( '/' );

		$this->assertSame( 'homepage', $this->nr->name );
		$this->assertTrue( $this->nr->params['HTTPS'] );
		$this->assertTrue( $this->nr->params['logged-in'] );
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
