<?php

if ( ! defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

class WC_GZD_Admin {

	/**
	 * Single instance of WooCommerce Germanized Main Class
	 *
	 * @var object
	 */
	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'woocommerce-germanized' ), '1.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'woocommerce-germanized' ), '1.0' );
	}
	
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_legal_page_metabox' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_product_mini_desc' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'settings_page_scroll_top' ) );
		add_action( 'save_post', array( $this, 'save_legal_page_content' ), 10, 3 );
	}

	public function settings_page_scroll_top() {
	
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$assets_path = WC_germanized()->plugin_url() . '/assets/';
		wp_register_style( 'woocommerce-gzd-admin', $assets_path . 'css/woocommerce-gzd-admin' . $suffix . '.css', false, WC_germanized()->version );
		wp_enqueue_style( 'woocommerce-gzd-admin' );
		
		$admin_script_path = $assets_path . 'js/admin/';
		
		if ( isset( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] == 'germanized' )
			wp_enqueue_script( 'wc-gzd-admin', $admin_script_path . 'settings' . $suffix . '.js', array( 'jquery', 'woocommerce_settings' ), WC_GERMANIZED_VERSION, true );
		
		if ( isset( $_GET[ 'section' ] ) && ! empty( $_GET[ 'section' ] ) && strpos( $_GET[ 'section' ], 'gzd' ) !== false )
			wp_enqueue_script( 'wc-gzd-admin-emails', $admin_script_path . 'emails' . $suffix . '.js', array( 'jquery', 'woocommerce_settings' ), WC_GERMANIZED_VERSION, true );

		// Hide delivery time and unit tagsdiv
		if ( version_compare( WC()->version, '2.3', '>=' ) )
			wp_add_inline_style( 'woocommerce-gzd-admin', '#tagsdiv-product_delivery_time, #tagsdiv-product_unit {display: none}' );
	}

	public function add_legal_page_metabox() {
		add_meta_box( 'wc-gzd-legal-page-email-content', __( 'Optional Email Content', 'woocommerce-germanized' ), array( $this, 'init_legal_page_metabox' ), 'page' );
	}

	public function init_legal_page_metabox( $post ) {
		$legal_pages = array( wc_get_page_id( 'revocation' ), wc_get_page_id( 'data_security' ), wc_get_page_id( 'imprint' ), wc_get_page_id( 'terms' ) );
		if ( ! in_array( $post->ID, $legal_pages ) ) {
			echo '<style type="text/css">#wc-gzd-legal-page-email-content { display: none; }</style>';
			return;
		}
		echo '<p class="small">' . __( 'Add content which will be replacing default page content within emails.', 'woocommerce-germanized' ) . '</p>';
		wp_editor( htmlspecialchars_decode( get_post_meta( $post->ID, '_legal_text', true ) ), 'legal_page_email_content', array( 'textarea_name' => '_legal_text', 'textarea_rows' => 5 ) );
	}

	public function add_product_mini_desc() {
		global $post;
		if ( isset( $post ) ) {
			$product = wc_get_product( $post );
			if ( ! $product->is_type( 'variable' ) )
				add_meta_box( 'wc-gzd-product-mini-desc', __( 'Optional Mini Description', 'woocommerce-germanized' ), array( $this, 'init_product_mini_desc' ), 'product', 'advanced', 'high' );
		}
	}

	public function save_legal_page_content( $post_id, $post, $update ) {

		if ( $post->post_type != 'page' )
			return;

		if ( isset( $_POST[ '_legal_text' ] ) && ! empty( $_POST[ '_legal_text' ] ) )
			update_post_meta( $post_id, '_legal_text', esc_html( $_POST[ '_legal_text' ] ) );
		else
			delete_post_meta( $post_id, '_legal_text' );
		
	}

	public function init_product_mini_desc( $post ) {
		echo '<p class="small">' . __( 'This content will be shown as short product description within checkout and emails.', 'woocommerce-germanized' ) . '</p>';
		wp_editor( htmlspecialchars_decode( get_post_meta( $post->ID, '_mini_desc', true ) ), 'wc_gzd_product_mini_desc', array( 'textarea_name' => '_mini_desc', 'textarea_rows' => 5, 'media_buttons' => false ) );
	}

}

WC_GZD_Admin::instance();