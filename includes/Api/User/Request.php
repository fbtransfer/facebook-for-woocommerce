<?php
declare( strict_types=1 );

namespace WooCommerce\Facebook\Api\User;

defined( 'ABSPATH' ) || exit;

use WooCommerce\Facebook\Api;

/**
 * Request object for the User API.
 */
class Request extends Api\Request {
	/**
	 * API request constructor.
	 *
	 * @param string $user_id Facebook User ID.
	 */
	public function __construct( string $user_id = '' ) {
		$path = $user_id ? "/{$user_id}" : '/me';
		parent::__construct( $path, 'GET' );
	}
}