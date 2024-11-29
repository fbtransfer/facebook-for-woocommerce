<?php
declare(strict_types=1);


class fbproductTest extends WP_UnitTestCase {
	private $parent_fb_product;

	/**
	 * Test it gets description from post meta.
	 * @return void
	 */
	public function test_get_fb_description_from_post_meta() {
		$product = WC_Helper_Product::create_simple_product();

		$facebook_product = new \WC_Facebook_Product( $product );
		$facebook_product->set_description( 'fb description' );
		$description = $facebook_product->get_fb_description();

		$this->assertEquals( $description, 'fb description');
	}

	/**
	 * Test it gets description from parent product if it is a variation.
	 * @return void
	 */
	public function test_get_fb_description_variable_product() {
		$variable_product = WC_Helper_Product::create_variation_product();
		$variable_product->set_description('parent description');
		$variable_product->save();

		$parent_fb_product = new \WC_Facebook_Product($variable_product);
		$variation         = wc_get_product($variable_product->get_children()[0]);

		$facebook_product = new \WC_Facebook_Product( $variation, $parent_fb_product );
		$description      = $facebook_product->get_fb_description();
		$this->assertEquals( $description, 'parent description' );

		$variation->set_description( 'variation description' );
		$variation->save();

		$description = $facebook_product->get_fb_description();
		$this->assertEquals( $description, 'variation description' );
	}

	/**
	 * Tests that if no description is found from meta or variation, it gets description from post
	 *
	 * @return void
	 */
	public function test_get_fb_description_from_post_content() {
		$product = WC_Helper_Product::create_simple_product();

		// Gets description from title
		$facebook_product = new \WC_Facebook_Product( $product );
		$description      = $facebook_product->get_fb_description();

		$this->assertEquals( $description, get_post( $product->get_id() )->post_title );

		// Gets description from excerpt (product short description)
		$product->set_short_description( 'short description' );
		$product->save();

		$description = $facebook_product->get_fb_description();
		$this->assertEquals( $description, get_post( $product->get_id() )->post_excerpt );

		// Gets description from content (product description)
		$product->set_description( 'product description' );
		$product->save();

		$description = $facebook_product->get_fb_description();
		$this->assertEquals( $description, get_post( $product->get_id() )->post_content );

		// Gets description from excerpt ignoring content when short mode is set
		add_option(
			WC_Facebookcommerce_Integration::SETTING_PRODUCT_DESCRIPTION_MODE,
			WC_Facebookcommerce_Integration::PRODUCT_DESCRIPTION_MODE_SHORT
		);

		$facebook_product = new \WC_Facebook_Product( $product );
		$description      = $facebook_product->get_fb_description();
		$this->assertEquals( $description, get_post( $product->get_id() )->post_excerpt );
	}

	/**
	 * Test it filters description.
	 * @return void
	 */
	public function test_filter_fb_description() {
		$product = WC_Helper_Product::create_simple_product();
		$facebook_product = new \WC_Facebook_Product( $product );
		$facebook_product->set_description( 'fb description' );

		add_filter( 'facebook_for_woocommerce_fb_product_description', function( $description ) {
			return 'filtered description';
		});

		$description = $facebook_product->get_fb_description();
		$this->assertEquals( $description, 'filtered description' );

		remove_all_filters( 'facebook_for_woocommerce_fb_product_description' );

		$description = $facebook_product->get_fb_description();
		$this->assertEquals( $description, 'fb description' );

	}

		
	/**
	 * Test it gets rich text description from post meta.
	 * @return void
	 */
	public function test_get_rich_text_description_from_post_meta() {
		$product = WC_Helper_Product::create_simple_product();

		$facebook_product = new \WC_Facebook_Product( $product );
		$facebook_product->set_rich_text_description( 'rich text description' );
		$rich_text_description = $facebook_product->get_rich_text_description();

		$this->assertEquals( $rich_text_description,  'rich text description' );
	}	
	
	/**
	 * Test html tags preservation for rich text description
	 * @return void
	 */
	public function test_html_preservation_for_rich_text_description() {
    // Create a simple product
    $product = WC_Helper_Product::create_simple_product();

    // Create a Facebook product instance
    $facebookProduct = new \WC_Facebook_Product($product);

    // Set the rich text description with HTML content
    $htmlContent = '<html>
        <p>Unisex cotton T-shirt with 3/4 length sleeves in royal blue. Great for everyday casual wear. Features graphic print of logo in white on upper left sleeve.</p>
        <ul>
            <li>100% Cotton</li>
            <li>Relaxed Fit</li>
        </ul>
    </html>';
    $facebookProduct->set_rich_text_description($htmlContent);

    // Get the rich text description
    $richTextDescription = $facebookProduct->get_rich_text_description();

    // Assert that the HTML content is preserved
    $this->assertEquals($htmlContent, $richTextDescription);
	}

	/**
	 * Tests for get_rich_text_description() method
	 */
	public function test_get_rich_text_description() {
		// Test 1: Gets rich text description from fb_description if set
		$product = WC_Helper_Product::create_simple_product();
		$facebook_product = new \WC_Facebook_Product($product);
		$facebook_product->set_description('fb description test');
		
		$description = $facebook_product->get_rich_text_description();
		$this->assertEquals('fb description test', $description);

		// Test 2: Gets rich text description from rich_text_description if set
		$facebook_product->set_rich_text_description('<p>rich text description test</p>');
		$description = $facebook_product->get_rich_text_description();
		$this->assertEquals('<p>rich text description test</p>', $description);

		// Test 3: Gets rich text description from post meta
		update_post_meta($product->get_id(), \WC_Facebook_Product::FB_RICH_TEXT_DESCRIPTION, '<p>meta description test</p>');
		$new_facebook_product = new \WC_Facebook_Product($product); // Create new instance to clear cached values
		$description = $new_facebook_product->get_rich_text_description();
		$this->assertEquals('<p>meta description test</p>', $description);

		// Test 4: For variations, gets description from variation first
		$variable_product = WC_Helper_Product::create_variation_product();
		$variation = wc_get_product($variable_product->get_children()[0]);
		$variation->set_description('<p>variation description</p>');
		$variation->save();
		
		$parent_fb_product = new \WC_Facebook_Product($variable_product);
		$facebook_product = new \WC_Facebook_Product($variation, $parent_fb_product);
		$description = $facebook_product->get_rich_text_description();
		$this->assertEquals('<p>variation description</p>', $description);

		// Test 5: Falls back to post content if no other description is set
		$product = WC_Helper_Product::create_simple_product();
		$product->set_description('<p>product content description</p>');
		$product->save();
		
		$facebook_product = new \WC_Facebook_Product($product);
		$description = $facebook_product->get_rich_text_description();
		$this->assertEquals('<p>product content description</p>', $description);

		// Test 6: Falls back to post excerpt if content is empty and sync_short_description is true
		add_option(
			WC_Facebookcommerce_Integration::SETTING_PRODUCT_DESCRIPTION_MODE,
			WC_Facebookcommerce_Integration::PRODUCT_DESCRIPTION_MODE_SHORT
		);
		
		$product->set_description('');
		$product->set_short_description('<p>short description test</p>');
		$product->save();
		
		$facebook_product = new \WC_Facebook_Product($product);
		$description = $facebook_product->get_rich_text_description();
		$this->assertEquals('<p>short description test</p>', $description);

		// Test 7: Applies filters
		add_filter('facebook_for_woocommerce_fb_rich_text_description', function($description) {
			return '<p>filtered description</p>';
		});
		
		$description = $facebook_product->get_rich_text_description();
		$this->assertEquals('<p>filtered description</p>', $description);
		
		// Cleanup
		remove_all_filters('facebook_for_woocommerce_fb_rich_text_description');
		delete_option(WC_Facebookcommerce_Integration::SETTING_PRODUCT_DESCRIPTION_MODE);
	}

	/**
	 * Test HTML preservation in rich text description
	 */
	public function test_rich_text_description_html_preservation() {
		$product = WC_Helper_Product::create_simple_product();
		$facebook_product = new \WC_Facebook_Product($product);

		$html_content = '
			<div class="product-description">
				<h2>Product Features</h2>
				<p>This is a <strong>premium</strong> product with:</p>
				<ul>
					<li>Feature 1</li>
					<li>Feature 2</li>
				</ul>
				<table>
					<tr>
						<th>Size</th>
						<th>Color</th>
					</tr>
					<tr>
						<td>Large</td>
						<td>Blue</td>
					</tr>
				</table>
			</div>
		';

		$facebook_product->set_rich_text_description($html_content);
		$description = $facebook_product->get_rich_text_description();
		
		// Test HTML structure is preserved
		$this->assertStringContainsString('<div class="product-description">', $description);
		$this->assertStringContainsString('<h2>', $description);
		$this->assertStringContainsString('<strong>', $description);
		$this->assertStringContainsString('<ul>', $description);
		$this->assertStringContainsString('<li>', $description);
		$this->assertStringContainsString('<table>', $description);
		$this->assertStringContainsString('<tr>', $description);
		$this->assertStringContainsString('<th>', $description);
		$this->assertStringContainsString('<td>', $description);
	}

	/**
	 * Test empty rich text description fallback behavior
	 */
	public function test_empty_rich_text_description_fallback() {
		$product = WC_Helper_Product::create_simple_product();
		$facebook_product = new \WC_Facebook_Product($product);
		
		// Ensure rich_text_description is empty
		$facebook_product->set_rich_text_description('');
		
		// Test fallback to post meta
		update_post_meta($product->get_id(), \WC_Facebook_Product::FB_RICH_TEXT_DESCRIPTION, '<p>fallback description</p>');
		$description = $facebook_product->get_rich_text_description();
		$this->assertEquals('<p>fallback description</p>', $description);
		
		// Test behavior when both rich_text_description and post meta are empty
		delete_post_meta($product->get_id(), \WC_Facebook_Product::FB_RICH_TEXT_DESCRIPTION);
		$description = $facebook_product->get_rich_text_description();
		$this->assertEquals('', $description);
	}

	/**
	 * Test rich text description handling for variable products and variations
	 */
	public function test_rich_text_description_variants() {
		// Create variable product with variation
		$variable_product = WC_Helper_Product::create_variation_product();
		$variation = wc_get_product($variable_product->get_children()[0]);
		
		// Set up parent product
		$parent_fb_product = new \WC_Facebook_Product($variable_product);
		
		// Set the rich text description using post meta for the parent
		update_post_meta($variable_product->get_id(), \WC_Facebook_Product::FB_RICH_TEXT_DESCRIPTION, '<p>parent rich text</p>');
		
		// Test 1: Variation inherits parent's rich text description when empty
		$facebook_product = new \WC_Facebook_Product($variation, $parent_fb_product);
		$description = $facebook_product->get_rich_text_description();
		$this->assertEquals('<p>parent rich text</p>', $description);
		
		// Test 2: Variation uses its own rich text description when set
		$variation_fb_product = new \WC_Facebook_Product($variation, $parent_fb_product);
		$variation_fb_product->set_rich_text_description('<p>variation rich text</p>');
		$description = $variation_fb_product->get_rich_text_description();
		$this->assertEquals('<p>variation rich text</p>', $description);
		
		// // Test 3: Variation uses its post meta when set
		// update_post_meta($variation->get_id(), \WC_Facebook_Product::FB_RICH_TEXT_DESCRIPTION, '<p>variation meta rich text</p>');
		// $new_variation_product = new \WC_Facebook_Product($variation, $parent_fb_product);
		// $description = $new_variation_product->get_rich_text_description();
		// $this->assertEquals('<p>variation meta rich text</p>', $description);
		
		// Test 4: Fallback chain for variations
		delete_post_meta($variation->get_id(), \WC_Facebook_Product::FB_RICH_TEXT_DESCRIPTION);
	}
}
