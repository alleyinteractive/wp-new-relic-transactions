<?php
namespace Alley\WP_New_Relic_Transactions\Tests\Feature;

use Alley\WP_New_Relic_Transactions\Tests\TestCase;
use Alley\WP_New_Relic_Transactions\WP_New_Relic_Transactions;
use PHPUnit\Framework\Attributes\DataProvider;

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

	#[DataProvider( 'rest_route_to_transaction_name_dataprovider' )]
	public function test_rest_route_to_transaction_name( string $route, string $expected ): void {
		$this->assertEquals(
			$expected,
			WP_New_Relic_Transactions::rest_route_to_transaction_name( $route )
		);
	}

	public static function rest_route_to_transaction_name_dataprovider(): array {
		return [
			[
				'/no-params/here',
				'/no-params/here',
			],
			[
				'/wp/v2/posts',
				'/wp/v2/posts',
			],
			[
				'/wp/v2/posts/(?P<id>[\d]+)',
				'/wp/v2/posts/<id>',
			],
			[
				'/vendor/v1.3/posts/ids/(?P<ids>(?:[^/]+))',
				'/vendor/v1.3/posts/ids/<ids>',
			],
			[
				'/vendor/v1.3/posts/ids/(?P<ids>(?:[^/]+))/count/(?P<count>(?:[^/]+))',
				'/vendor/v1.3/posts/ids/<ids>/count/<count>',
			],
			[
				'/complex/(?P<one>[^/]+)/(?P<two>[^/]+)/(?P<three>[^/]+)/end',
				'/complex/<one>/<two>/<three>/end',
			],

			[
				'/mixed/(?P<param1>[^/]+)/static/(?P<param2>[^/]+)',
				'/mixed/<param1>/static/<param2>',
			],
			[
				'/nested/(?P<outer>(?:[^/]+/)?(?P<inner>[^/]+))/end',
				'/nested/<outer>/end',
			],
			[
				'/complex-regex/(?P<param>[a-zA-Z0-9_-]{3,})',
				'/complex-regex/<param>',
			],
			[
				'/plugin/placement-strategy/(?P<player>(?:[^/]+))?',
				'/plugin/placement-strategy/<player>',
			],
			[
				'/(?P<path>(media))/(?P<endpoint>(?:[^/]+))?',
				'/<path>/<endpoint>',
			],
		];
	}
}
