<?php
// phpcs:ignoreFile
/**
 * Copyright (c) Facebook, Inc. and its affiliates. All Rights Reserved
 *
 * This source code is licensed under the license found in the
 * LICENSE file in the root directory of this source tree.
 *
 * @package FacebookCommerce
 */

namespace WooCommerce\Facebook\Api\Catalog\Product_Group\Products\Read;

defined( 'ABSPATH' ) or exit;

use WooCommerce\Facebook\Api;

/**
 * Response object for the API endpoint that returns a list of Product Items in a particular Product Group.
 *
 * @since 2.0.0
 */
class Response extends Api\Response {


	use Api\Traits\Paginated_Response;


	/**
	 * Gets the Product Item IDs indexed by the retailer ID.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_ids() {

		$product_item_ids = array();

		foreach ( $this->get_data() as $entry ) {
			$product_item_ids[ $entry->retailer_id ] = $entry->id;
		}

		return $product_item_ids;
	}


}