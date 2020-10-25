<?php
function my_theme_enqueue_styles()
{
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
}

add_action('wp_enqueue_scripts', 'my_theme_enqueue_styles');


/*
 * Redirects vendors to the wcvendor dashboard after login
 * */
add_filter('woocommerce_login_redirect', 'login_redirect', 10, 2);
function login_redirect($redirect_to, $user)
{
// WCV dashboard — Uncomment the 3 lines below if using WC Vendors Free instead of WC Vendors Pro
    if (class_exists('WCV_Vendors') && WCV_Vendors::is_vendor($user->ID)) {
        $redirect_to = get_permalink(get_option('wcvendors_vendor_dashboard_page_id'));
    }

    return $redirect_to;
}

/*
 * Replace no shipping options available text
 * */
add_filter( 'woocommerce_cart_no_shipping_available_html', 'change_noship_message' );
add_filter( 'woocommerce_no_shipping_available_html', 'change_noship_message' );
function change_noship_message() {
    print "Aya's market is not available in your area yet. We are expanding rapidly, please check back soon or call customer support.";
}