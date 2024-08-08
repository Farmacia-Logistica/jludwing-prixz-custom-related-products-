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
    echo '<h2 id="woorelated-title-id" class="woorelated-title" > También puedes comprar </h2>';
    echo '<div id="prixz-custom-related-products-container">';
    if(empty(prixz_display_related_products())){
        echo '<style> .woorelated-title{ display:none; } </style>';
    };
    echo '</div>';
}

// Cargar y mostrar los productos relacionados directamente
function prixz_display_related_products($max_products = 8) {
    global $product;

    if (!is_object($product) || !$product->get_id()) {
        return;
    }

    $product_id = $product->get_id();

    $transient_key = 'prixz_related_products_' . $product_id;
    //echo $transient_key;

    //delete_transient($transient_key);

   /*
   se puede comentar para traer los productos del endpoint, en lugar del trasient
   */
   /* $related_products = get_transient($transient_key);
    if ($related_products !== false) {
        if (!empty($related_products)) {
            include 'prixz-related-products-template.php';
            return;
        }
    }*/

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
    //var_dump($data);
    $related_product_ids = array();

    foreach ($data['payload'] as $related_product) {
        if (isset($related_product['productTwo'])) {
            $related_product_ids[] = $related_product['productTwo'];
        }
    }

    //$related_product_ids = array_slice($related_product_ids, 0, $max_products); 

    $related_products = wc_get_products(array(
        'include' => $related_product_ids,
        'stock_status' => 'instock',
    ));

    $related_products = array_filter($related_products, function($product) {
        return $product && $product->get_status() === 'publish' && $product->get_catalog_visibility() !== 'hidden';
    });

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
        '0.0.2'
    );
}
