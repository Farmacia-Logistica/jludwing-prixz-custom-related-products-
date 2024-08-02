<?php
/*
Plugin Name: Prixz Custom Related Products
Description: Muestra productos relacionados obtenidos de una API interna debajo del producto.
Version: 1.0
Author: Prixz Woo Team
*/

// Evitar el acceso directo al archivo.
if (!defined('ABSPATH')) {
    exit;
}

// Añadir el contenedor para los productos relacionados a la página del producto
add_action('woocommerce_after_single_product_summary', 'prixz_custom_related_products_container', 10);

function prixz_custom_related_products_container()
{
    echo '<h2 class="woorelated-title" style="display:block;margin-top:25px;"> También puedes comprar </h2>';
    echo '<div id="prixz-custom-related-products-container">';
    prixz_display_related_products();
    echo '</div>';
}

// Cargar y mostrar los productos relacionados directamente
function prixz_display_related_products() {
    global $product;

    if (!is_object($product) || !$product->get_id()) {
        return;
    }

    $product_id = $product->get_id();

    $transient_key = 'prixz_related_products_' . $product_id;  // Clave única para el transient

    // Intentar recuperar los productos relacionados desde el transient
    $related_products = get_transient($transient_key);

    // Verifica si los productos están ya almacenados en el transient
    if ($related_products !== false) {
        if (!empty($related_products)) {
            include 'prixz-related-products-template.php';
            return;
        }
    }


    $api_url = sprintf('%s/wp-json/wc-product-info-bought-together/v1/product/%d', get_site_url(), $product_id);
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

    $related_product_ids = array_slice($related_product_ids, 0, 4);

    // Obtener los productos relacionados de WooCommerce Solo productos en stock
    $related_products = wc_get_products(array(
        'include' => $related_product_ids,
        'stock_status' => 'instock',
    ));

    // Doble verificación: filtrar productos válidos, en stock, publicados y visibles
    $related_products = array_filter($related_products, function($product) {
        return $product
            && $product->get_status() === 'publish'
            && $product->get_catalog_visibility() !== 'hidden';
    });

    // Guardar los productos relacionados en un transient por un día
    set_transient($transient_key, $related_products, DAY_IN_SECONDS);

    if (!empty($related_products)) {
        include 'prixz-related-products-template.php';
    }
}

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
