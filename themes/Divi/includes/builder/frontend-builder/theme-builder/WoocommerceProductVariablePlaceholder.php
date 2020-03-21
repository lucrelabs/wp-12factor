<?php

/**
 * Class ET_Theme_Builder_Woocommerce_Product_Variable_Placeholder
 *
 * Variable product class extension for displaying WooCommerce placeholder on Theme Builder
 */
class ET_Theme_Builder_Woocommerce_Product_Variable_Placeholder extends WC_Product_Variable {
	/**
	 * Cached upsells id
	 *
	 * @since 4.0.10
	 *
	 * @var array
	 */
	protected static $tb_upsells_id;

	/**
	 * Cached product category ids
	 *
	 * @since 4.0.10
	 *
	 * @var array
	 */
	protected static $tb_category_ids;

	/**
	 * Cached product tag ids
	 *
	 * @since 4.0.10
	 *
	 * @var array
	 */
	protected static $tb_tag_ids;

	/**
	 * Cached attributes
	 *
	 * @since 4.0.10
	 *
	 * @var array
	 */
	protected static $tb_attributes;

	/**
	 * Create pre-filled WC Product (variable) object which acts as placeholder generator in TB
	 *
	 * @since 4.0.10 Instead of empty product object that is set later, pre-filled default data properties
	 *
	 * @param int|WC_Product|object $product Product to init.
	 */
	public function __construct( $product = 0 ) {
		// Pre-filled default data with placeholder value so everytime this product class is
		// initialized, it already has sufficient data to be displayed on Theme Builder
		$this->data = array(
			'name'               => esc_html( 'Product name', 'et_builder' ),
			'slug'               => 'product-name',
			'date_created'       => current_time( 'timestamp' ),
			'date_modified'      => null,
			'status'             => 'publish',
			'featured'           => false,
			'catalog_visibility' => 'visible',
			'description'        => esc_html( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris bibendum eget dui sed vehicula. Suspendisse potenti. Nam dignissim at elit non lobortis. Cras sagittis dui diam, a finibus nibh euismod vestibulum. Integer sed blandit felis. Maecenas commodo ante in mi ultricies euismod. Morbi condimentum interdum luctus. Mauris iaculis interdum risus in volutpat. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Praesent cursus odio eget cursus pharetra. Aliquam lacinia lectus a nibh ullamcorper maximus. Quisque at sapien pulvinar, dictum elit a, bibendum massa. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Mauris non pellentesque urna.', 'et_builder' ),
			'short_description'  => esc_html( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris bibendum eget dui sed vehicula. Suspendisse potenti. Nam dignissim at elit non lobortis.', 'et_builder' ),
			'sku'                => 'product-name',
			'price'              => '75',
			'regular_price'      => '80',
			'sale_price'         => '65',
			'date_on_sale_from'  => null,
			'date_on_sale_to'    => null,
			'total_sales'        => '0',
			'tax_status'         => 'taxable',
			'tax_class'          => '',
			'manage_stock'       => true,
			'stock_quantity'     => 50,
			'stock_status'       => 'instock',
			'backorders'         => 'no',
			'low_stock_amount'   => 2,
			'sold_individually'  => false,
			'weight'             => 2,
			'length'             => '',
			'width'              => 2,
			'height'             => 2,
			'upsell_ids'         => array(),
			'cross_sell_ids'     => array(),
			'parent_id'          => 0,
			'reviews_allowed'    => true,
			'purchase_note'      => '',
			'attributes'         => array(),
			'default_attributes' => array(),
			'menu_order'         => 0,
			'post_password'      => '',
			'virtual'            => false,
			'downloadable'       => false,
			'category_ids'       => array(),
			'tag_ids'            => array(),
			'shipping_class_id'  => 0,
			'downloads'          => array(),
			'image_id'           => '',
			'gallery_image_ids'  => array(),
			'download_limit'     => -1,
			'download_expiry'    => -1,
			'rating_counts'      => array(
				4 => 2,
			),
			'average_rating'     => '4.00',
			'review_count'       => 2,
			'recent_product_ids' => null,
		);

		parent::__construct( $product );
	}

	/**
	 * Get internal type.
	 * Define custom internal type so custom data store can be used to bypass database value retrieval
	 *
	 * @since 4.0.10
	 *
	 * @return string
	 */
	public function get_type() {
		return 'tb-placeholder';
	}

	/**
	 * Add to cart's <select> requires variable product type and get_available_variations() method
	 * outputting product->children value. Filtering get_available_variations() can't be done so
	 * extending WC_Product_Variable and set fixed value for get_available_variations() method
	 *
	 * @since 4.0.1
	 *
	 * @return array
	 */
	function get_available_variations() {
		// Use `ET_Theme_Builder_Woocommerce_Product_Variable_Placeholder` as variations because
		// this product type has custom data store which bypass database value retrieval when
		// product object is re-initialized using `wc_get_product()` in TB. This is necessary to
		// avoid error since WC add-ons tend to do this instead of using global $product object
		$variation_1 = new ET_Theme_Builder_Woocommerce_Product_Variable_Placeholder();

		return array( $variation_1 );
	}

	/**
	 * Display Divi's placeholder image in WC image in TB
	 *
	 * @since 4.0.10
	 *
	 * @param string not used but need to be declared to prevent incompatible declaration error
	 * @param array  not used but need to be declared to prevent incompatible declaration error
	 * @param bool   not used but need to be declared to prevent incompatible declaration error
	 *
	 * @return string
	 */
	public function get_image( $size = 'woocommerce_thumbnail', $attr = array(), $placeholder = true ) {
		return et_builder_wc_placeholder_img();
	}

	/**
	 * Set product upsells id for TB's woocommerceComponent. This can't be called during class
	 * initialization and need to be called BEFORE `woocommerce_product_class` filter callback
	 * to avoid infinite loop
	 *
	 * @since 4.0.10
	 *
	 * @param array $args
	 */
	public static function set_tb_upsells_ids( $args = array() ) {
		$defaults = array(
			'limit' => 4,
		);
		$args = wp_parse_args( $args, $defaults );

		// Get recent products for upsells product; Any product will do since its purpose is
		// for visual preview only
		$recent_products_query = new WC_Product_Query( $args );
		$recent_product_ids    = array();

		foreach( $recent_products_query->get_products() as $recent_product ) {
			$recent_product_ids[] = $recent_product->get_id();
		}

		// Set up upsells id product
		self::$tb_upsells_id = $recent_product_ids;
	}

	/**
	 * Get upsells id
	 *
	 * @since 4.0.10
	 *
	 * @param string not used but need to be declared to prevent incompatible declaration error
	 *
	 * @return array
	 */
	public function get_upsell_ids( $context = 'view' ) {
		// Bypass database value retrieval and simply pulled cached value from property
		return is_array( self::$tb_upsells_id ) ? self::$tb_upsells_id : array();
	}

	/**
	 * Get attributes
	 *
	 * @since 4.0.10
	 *
	 * @param string not used but need to be declared to prevent incompatible declaration error
	 *
	 * @return array
	 */
	public function get_attributes( $context = 'view' ) {
		if ( ! is_null( self::$tb_attributes ) ) {
			return self::$tb_attributes;
		}

		// Initialize color attribute
		$colors = new WC_Product_Attribute();
		$colors->set_id( 1 );
		$colors->set_name( 'color' );
		$colors->set_options( array( 'Black', 'White', 'Gray' ) );
		$colors->set_position( 1 );
		$colors->set_visible( 1 );
		$colors->set_variation( 1 );

		// Initialize size attribute
		$sizes = new WC_Product_Attribute();
		$sizes->set_id( 2 );
		$sizes->set_name( 'size' );
		$sizes->set_options( array( 'S', 'M', 'L', 'XL' ) );
		$sizes->set_position( 1 );
		$sizes->set_visible( 1 );
		$sizes->set_variation( 1 );

		self::$tb_attributes = array(
			'pa_color' => $colors,
			'pa_size'  => $sizes,
		);

		return self::$tb_attributes;
	}

	/**
	 * Get variation price
	 *
	 * @since 4.0.10
	 *
	 * @param bool not used but need to be declared to prevent incompatible declaration error
	 *
	 * @return array
	 */
	public function get_variation_prices( $for_display = false ) {
		return array(
			'price'         => array( $this->data['price'] ),
			'regular_price' => array( $this->data['regular_price'] ),
			'sale_price'    => array( $this->data['sale_price'] ),
		);
	}
}

/**
 * Render default product variable add to cart UI for tb-placeholder product type
 *
 * @since 4.0.10
 */
add_action( 'woocommerce_tb-placeholder_add_to_cart', 'woocommerce_variable_add_to_cart', 30 );

