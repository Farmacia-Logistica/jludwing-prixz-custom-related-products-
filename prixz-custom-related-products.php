<?php
/*
Plugin Name: Prixz Custom Related Products
Description: Muestra productos relacionados obtenidos de una API interna debajo del producto.
Version: 1.0
Author: Prixz Woo Team
*/

// Evitar el acceso directo al archivo.
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Añadir el contenedor para los productos relacionados a la página del producto
add_action('woocommerce_after_single_product_summary', 'prixz_custom_related_products_container', 20);

function prixz_custom_related_products_container()
{
    echo '<h2 class="woorelated-title" style="display:block;margin-top:25px;"> Productos Relacionados </h2>';
    echo '<div id="prixz-custom-related-products-container" style="margin-top: 25px; width: 95%; overflow: hidden;margin: 0 auto; "></div>'; // Contenedor donde se cargarán los productos relacionados
}

// Engancharse en el gancho 'wp_enqueue_scripts' para cargar el script solo en la página del producto actual
add_action('woocommerce_before_single_product', 'prixz_enqueue_scripts');

function prixz_enqueue_scripts()
{
    global $product;
    if (!is_product() || !is_object($product)) {
        return;
    }
    $product_id = $product->get_id();
    wp_enqueue_script('prixz-related-products', plugin_dir_url(__FILE__) . 'prixz-related-products.js', array('jquery'), '0.0.1', true);
    wp_localize_script('prixz-related-products', 'prixz_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'product_id' => $product_id,
    ));

    wp_enqueue_style( 'style', plugin_dir_url(__FILE__) . 'style.css' );

}


// Manejar la solicitud AJAX
add_action('wp_ajax_nopriv_prixz_get_related_products', 'prixz_get_related_products');
add_action('wp_ajax_prixz_get_related_products', 'prixz_get_related_products');

function prixz_get_related_products()
{
    if (!isset($_POST['product_id'])) {
        wp_send_json_error('No product_id in POST request');
    }

    $product_id = intval($_POST['product_id']);
    $api_url = sprintf('%s/wp-json/wc-product-info/v1/product/%d', get_site_url(), $product_id);

    // Hacer la solicitud GET a la API interna
    $response = wp_remote_get($api_url);

    if (is_wp_error($response)) {
        wp_send_json_error('WP_Error: ' . $response->get_error_message()); // Return an error
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (!$data || !isset($data['payload']) || !is_array($data['payload'])) {
        wp_send_json_error('Invalid API response');
    }

    $related_product_ids = array();

    // Obtener los IDs de los productos relacionados
    foreach ($data['payload'] as $related_product) {
        if (isset($related_product['productTwo'])) {
            $related_product_ids[] = $related_product['productTwo'];
        }
    }

    // Limitar a 8 productos relacionados
    $related_product_ids = array_slice($related_product_ids, 0, 8);

    // Obtener los productos relacionados de WooCommerce
    $related_products = wc_get_products(array(
        'include' => $related_product_ids,
    ));

    include 'prixz-related-products-template.php';
}
?>
