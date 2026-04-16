<?php
namespace Alley\WP_New_Relic_Transactions\Tests\Feature;

use Alley\WP_New_Relic_Transactions\Tests\TestCase;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use WP_REST_Response;

/**
 * Tests for REST API route transaction naming.
 *
 * Runs in separate processes because each test defines the REST_REQUEST constant,
 * which cannot be redefined once set in PHP.
 *
 * Visit {@see https://mantle.alley.co/testing/test-framework.html} to learn more.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class RestApiTest extends TestCase {
	/**
	 * Mock New Relic instance for testing transaction naming.
	 *
	 * @var \Alley\WP_New_Relic_Transactions\Tests\MockNewRelic
	 */
	protected $nr;

	protected function setUp(): void {
		parent::setUp();

		$GLOBALS['wp_new_relic_transactions_plugin']->named = false;
		$this->nr = $GLOBALS['mock_new_relic'];
		$this->nr->reset();

		// REST_REQUEST must be defined for the plugin's rest_routes() to fire,
		// since Mantle replaces rest_api_loaded() which is normally responsible
		// for defining it.
		define( 'REST_REQUEST', true );

		// Register test REST routes. These callbacks run when Mantle fires
		// rest_api_init() during each request's replace_rest_api() call.
		add_action(
			'rest_api_init',
			function () {
				register_rest_route(
					'test-plugin/v1',
					'/items/(?P<id>[\d]+)',
					[
						'methods'             => [ 'GET', 'POST' ],
						'callback'            => fn() => new WP_REST_Response( [ 'id' => 1 ], 200 ),
						'permission_callback' => '__return_true',
					],
				);

				register_rest_route(
					'test-plugin/v1',
					'/posts/(?P<post_id>[\d]+)/comments/(?P<comment_id>[\d]+)',
					[
						'methods'             => 'GET',
						'callback'            => fn() => new WP_REST_Response( [], 200 ),
						'permission_callback' => '__return_true',
					],
				);

				register_rest_route(
					'test-plugin/v1',
					'/media/(?P<path>(?:[^/]+/)*(?:[^/]+))',
					[
						'methods'             => 'GET',
						'callback'            => fn() => new WP_REST_Response( [], 200 ),
						'permission_callback' => '__return_true',
					],
				);

				register_rest_route(
					'test-plugin/v1',
					'/placement/(?P<player>(?:[^/]+))?',
					[
						'methods'             => 'GET',
						'callback'            => fn() => new WP_REST_Response( [], 200 ),
						'permission_callback' => '__return_true',
					],
				);

				register_rest_route(
					'test-plugin/v1',
					'/(?P<type>(posts|pages))/(?P<slug>[^/]+)',
					[
						'methods'             => 'GET',
						'callback'            => fn() => new WP_REST_Response( [], 200 ),
						'permission_callback' => '__return_true',
					],
				);
			},
		);
	}

	/**
	 * Test that a simple route with a single ID parameter is named correctly.
	 */
	public function test_single_param_route_naming(): void {
		$this->get( '/wp-json/test-plugin/v1/items/123' );

		$this->assertSame( 'GET /test-plugin/v1/items/<id>', $this->nr->name );
		$this->assertSame( '/test-plugin/v1/items/(?P<id>[\d]+)', $this->nr->params['wp-api-route'] );
		$this->assertSame( 'true', $this->nr->params['wp-api'] );
	}

	/**
	 * Test that a route with multiple parameters is named correctly.
	 */
	public function test_multiple_params_route_naming(): void {
		$this->get( '/wp-json/test-plugin/v1/posts/5/comments/42' );

		$this->assertSame( 'GET /test-plugin/v1/posts/<post_id>/comments/<comment_id>', $this->nr->name );
		$this->assertSame( '/test-plugin/v1/posts/(?P<post_id>[\d]+)/comments/(?P<comment_id>[\d]+)', $this->nr->params['wp-api-route'] );
	}

	/**
	 * Test that a route with nested parentheses in the regex is named correctly.
	 */
	public function test_nested_parentheses_route_naming(): void {
		$this->get( '/wp-json/test-plugin/v1/media/uploads/2024/image.jpg' );

		$this->assertSame( 'GET /test-plugin/v1/media/<path>', $this->nr->name );
	}

	/**
	 * Test that a route with an optional parameter and nested parens is named correctly.
	 */
	public function test_optional_nested_param_route_naming(): void {
		$this->get( '/wp-json/test-plugin/v1/placement/my-player' );

		$this->assertSame( 'GET /test-plugin/v1/placement/<player>', $this->nr->name );
	}

	/**
	 * Test that a route with a nested alternation group is named correctly.
	 */
	public function test_alternation_group_route_naming(): void {
		$this->get( '/wp-json/test-plugin/v1/posts/hello-world' );

		$this->assertSame( 'GET /test-plugin/v1/<type>/<slug>', $this->nr->name );
	}

	/**
	 * Test that a POST request to a route is named with the correct HTTP method.
	 */
	public function test_post_method_route_naming(): void {
		$this->post( '/wp-json/test-plugin/v1/items/7' );

		$this->assertSame( 'POST /test-plugin/v1/items/<id>', $this->nr->name );
	}

	/**
	 * Test that a route without any parameters is named as-is.
	 */
	public function test_route_without_params_naming(): void {
		add_action(
			'rest_api_init',
			function () {
				register_rest_route(
					'test-plugin/v1',
					'/status',
					[
						'methods'             => 'GET',
						'callback'            => fn() => new WP_REST_Response( [ 'ok' => true ], 200 ),
						'permission_callback' => '__return_true',
					],
				);
			},
		);

		$this->get( '/wp-json/test-plugin/v1/status' );

		$this->assertSame( 'GET /test-plugin/v1/status', $this->nr->name );
	}

	/**
	 * Test that the transaction is only named once even if multiple REST routes
	 * could match (plugin should only set the name on the first dispatch).
	 */
	public function test_transaction_named_only_once(): void {
		$this->get( '/wp-json/test-plugin/v1/items/123' );

		// The 'named' flag on the plugin should be true after the first dispatch.
		$this->assertTrue( $GLOBALS['wp_new_relic_transactions_plugin']->named );
	}
}
