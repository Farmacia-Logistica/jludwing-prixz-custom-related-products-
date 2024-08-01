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
add_action('woocommerce_after_single_product_summary', 'prixz_custom_related_products_container', 10);

function prixz_custom_related_products_container()
{
    echo '<h2 class="woorelated-title" style="display:block;margin-top:25px;"> También puedes comprar </h2>';
    echo '<div id="prixz-custom-related-products-container">';
    prixz_display_related_products();
    echo '</div>'; // Contenedor donde se cargarán los productos relacionados
}

// Cargar y mostrar los productos relacionados directamente
function prixz_display_related_products() {
    global $product;

    if (!is_object($product) || !$product->get_id()) {
        return;
    }

    $product_id = $product->get_id();
    $api_url = sprintf('%s/wp-json/wc-product-info-bought-together/v1/product/%d', get_site_url(), $product_id);

    // Hacer la solicitud GET a la API interna
    $response = wp_remote_get($api_url);

    if (is_wp_error($response)) {
        echo 'Error: ' . $response->get_error_message();
        return;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (!$data || !isset($data['payload']) || !is_array($data['payload'])) {
        echo 'Invalid API response';
        return;
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
        'stock_status' => 'instock', // Solo productos en stock
    ));

    // Doble verificación: filtrar productos válidos, en stock, publicados y visibles
    $related_products = array_filter($related_products, function($product) {
        return $product
            && $product->get_status() === 'publish'
            && $product->get_catalog_visibility() !== 'hidden';
    });

    if (!empty($related_products)) {
        include 'prixz-related-products-template.php';
    }
}

// Añadir estilos y scripts si es necesario
add_action('woocommerce_before_single_product', 'prixz_enqueue_scripts');

function prixz_enqueue_scripts() {
    if (!is_product()) {
        return;
    }

    wp_enqueue_style(
        'prixz-style',
        plugin_dir_url(__FILE__) . 'style.css',
        array(),
        '0.0.1'
    );
}
