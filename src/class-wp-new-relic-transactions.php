<?php
/**
 * WP_New_Relic_Transactions class file
 *
 * @package wp-new-relic-transactions
 */

namespace Alley\WP_New_Relic_Transactions;

use WP;
use WP_Post;
use WP_REST_Request;
use WP_Term;

use function is_user_logged_in;

/**
 * Manages the New Relic transaction data.
 */
class WP_New_Relic_Transactions {

	/**
	 * Flag if the transaction was already named to prevent duplicates.
	 *
	 * @var bool
	 */
	public bool $named = false;

	/**
	 * Create the object.
	 *
	 * @param With_New_Relic $new_relic New Relic dependency.
	 */
	public function __construct(
		protected With_New_Relic $new_relic
	) {
	}

	/**
	 * Boot the plugin and add hooks if it's supported.
	 */
	public function boot(): void {
		if ( ! $this->new_relic->is_supported() ) {
			return;
		}

		remove_action( 'rest_dispatch_request', 'wpcom_vip_rest_routes_for_newrelic' );
		add_filter( 'rest_dispatch_request', [ $this, 'rest_routes' ], 10, 4 );
		add_action( 'wp', [ $this, 'process_wp' ] );
	}

	/**
	 * Name the transaction.
	 *
	 * @param string $name Transaction name.
	 * @return bool Returns true if the transaction name was successfully changed.
	 *              If false is returned, check the agent log for more information.
	 */
	public function name_transaction( string $name ): bool {
		/**
		 * Filter the transaction name before setting it.
		 *
		 * @param string $name Transaction name.
		 */
		$name        = apply_filters( 'wp_new_relic_transactions_name', $name );
		$this->named = $this->new_relic->name_transaction( $name );
		return $this->named;
	}

	/**
	 * Add custom parameters to the new relic transaction.
	 *
	 * @param array $params Custom params as key => value pairs.
	 * @return bool Returns true if all parameters were added successfully.
	 */
	public function add_custom_parameters( array $params ): bool {
		/**
		 * Filter the custom params before setting them.
		 *
		 * @param array $params Custom params, as key => value pairs.
		 */
		$params = apply_filters( 'wp_new_relic_transactions_custom_parameters', $params );
		$result = true;
		foreach ( $params as $key => $value ) {
			$result = $this->new_relic->add_custom_parameter( $key, $value ) && $result;
		}

		return $result;
	}

	/**
	 * Add New Relic data for REST requests.
	 *
	 * This replaces and improves upon `wpcom_vip_rest_routes_for_newrelic` by:
	 *
	 *     1. Only naming transactions that are actual REST API requests (vs non-
	 *        REST API requests that use the REST API internally)
	 *     2. Unhooking itself so it only runs once
	 *
	 * @param mixed           $dispatch_result Dispatch result, will be used if not
	 *                                         empty.
	 * @param WP_REST_Request $request         Request used to generate the
	 *                                         response.
	 * @param string          $route           Route matched for the request.
	 * @param array           $handler         Route handler used for the request.
	 * @return mixed Unaltered `$dispatch_result`.
	 */
	public function rest_routes( $dispatch_result, $request, $route, $handler ) {
		if (
			! $this->named
			&& defined( 'REST_REQUEST' )
			&& true === REST_REQUEST
			&& ! empty( $GLOBALS['wp']->query_vars['rest_route'] )
		) {
			$path = $GLOBALS['wp']->query_vars['rest_route'];
			if ( preg_match( '@^' . $route . '@i', $path ) ) {
				$name = $request->get_method() . ' ' . preg_replace(
					'/\(\?P(<\w+?>).*?\)/',
					'$1',
					$route
				);
				$this->name_transaction( $name );
				$this->add_custom_parameters(
					[
						'wp-api'       => 'true',
						'wp-api-route' => $route,
					]
				);
			}
		}

		return $dispatch_result;
	}

	/**
	 * Handle the 'wp' event to set the transaction name.
	 *
	 * @param WP $wp Global WP object.
	 */
	public function process_wp( WP $wp ): void {
		if ( $this->named ) {
			return;
		}

		if (
			! empty( $wp->query_vars['rest_route'] )
			|| is_admin()
			|| wp_doing_cron()
			|| ( defined( 'WP_CLI' ) && WP_CLI )
		) {
			return;
		}

		$name   = '';
		$params = [];

		switch ( true ) {
			case is_feed():
				$feed_type = get_query_var( 'feed' );
				if ( 'feed' !== $feed_type ) {
					$name = "feed.{$feed_type}";
				} else {
					$name = 'feed';
				}

				$params['feed'] = 'true';
				break;
			case is_embed():
				$name            = 'embed';
				$params['embed'] = get_query_var( 'embed' );
				break;
			case is_404():
				$name = 'error.404';
				break;
			case is_search():
				$name        = 'search';
				$params['s'] = get_query_var( 's' );
				break;
			case is_front_page():
			case is_home():
				$name = 'homepage';
				break;
			case is_privacy_policy():
				$name = 'privacy_policy';
				break;
			case is_post_type_archive():
				$name = 'archive.post_type.' . get_query_var( 'post_type' );
				break;
			case is_tax():
			case is_category():
			case is_tag():
				$name = 'taxonomy';
				$term = get_queried_object();
				if ( $term instanceof WP_Term ) {
					$name             .= ".{$term->taxonomy}";
					$params['term_id'] = $term->term_id;
					$params['slug']    = $term->slug;
				}
				break;
			case is_attachment():
				$name = 'attachment';
				break;
			case is_single():
			case is_page():
			case is_singular():
				$name = 'post';
				$post = get_queried_object();
				if ( $post instanceof WP_Post ) {
					if ( 'post' !== $post->post_type ) {
						$name .= ".{$post->post_type}";
					}

					$params['post_id'] = $post->ID;
				}
				break;
			case is_author():
				$name = 'archive.author';
				break;
			case is_date():
				$name = 'archive.date';
				break;
			case is_archive():
				$name = 'archive';
				break;
		}

		$params['logged-in'] = is_user_logged_in();

		if ( is_paged() ) {
			$params['paged'] = 'true';
			$params['page']  = get_query_var( 'paged' );
		}

		if ( ! empty( $name ) ) {
			$this->name_transaction( $name );
		}

		if ( ! empty( $params ) ) {
			$this->add_custom_parameters( $params );
		}
	}
}
