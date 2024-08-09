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
    $related_products = prixz_display_related_products();
    
    if (empty($related_products)) {
        echo '<style> #content-wrapper #woorelated-title-id.woorelated-title{ display:none; } </style>';
    } else {
        echo '<h2 id="woorelated-title-id" class="woorelated-title" > También puedes comprar </h2>';
        echo '<div id="prixz-custom-related-products-container">';
        echo $related_products;  // Mostrar los productos si existen
        echo '</div>';
    }
}

// Cargar y mostrar los productos relacionados directamente
function prixz_display_related_products($max_products = 8) {
    global $product;

    if (!is_object($product) || !$product->get_id()) {
        return array();  // Retorna un array vacío si no hay producto
    }

    $product_id = $product->get_id();

    $transient_key = 'prixz_related_products_' . $product_id;

    $api_url = sprintf('%s/wp-json/wc-product-info-bought-together/v1/product/%d', get_site_url(), $product_id);
    $response = wp_remote_get($api_url);
    if (is_wp_error($response)) {
        echo 'Error: ' . $response->get_error_message();
        return array();  // Retorna un array vacío si hay un error en la API
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    if (!$data || !isset($data['payload']) || !is_array($data['payload'])) {
        echo 'Invalid API response';
        return array();  // Retorna un array vacío si la respuesta es inválida
    }

    $related_product_ids = array();
    if (empty($data['payload'])) {
        return array();  // Retorna un array vacío si el payload está vacío
    }

    foreach ($data['payload'] as $related_product) {
        if (isset($related_product['productTwo'])) {
            $related_product_ids[] = $related_product['productTwo'];
        }
    }

    $related_products = wc_get_products(array(
        'include' => $related_product_ids,
        'stock_status' => 'instock',
    ));

    $related_products = array_filter($related_products, function($product) {
        return $product && $product->get_status() === 'publish' && $product->get_catalog_visibility() !== 'hidden';
    });

    set_transient($transient_key, $related_products, DAY_IN_SECONDS);

    if (!empty($related_products)) {
        ob_start();  // Inicia la captura de salida
        include 'prixz-related-products-template.php';
        return ob_get_clean();  // Retorna el contenido capturado
    }

    return array();  // Retorna un array vacío si no hay productos relacionados
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
        '0.0.2'
    );
}
