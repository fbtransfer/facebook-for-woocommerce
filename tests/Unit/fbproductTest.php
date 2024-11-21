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

}
