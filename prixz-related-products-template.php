<?php
ob_start();
?>
<div id="pcrp-carouselExample" class="pcrp-carousel products" data-ride="carousel">
    <div class="pcrp-carousel-container">
        <div class="pcrp-carousel">
            <div class="pcrp-carousel-inner">
                <?php foreach ($related_products as $related_product) : ?>
                    <?php $post_object = get_post($related_product->get_id()); ?>
                    <?php setup_postdata($GLOBALS['post'] =& $post_object); ?>
                    <div class="pcrp-carousel-item">
                        <div class="pcrp-col-md-3">
                            <?php wc_get_template_part('content', 'product'); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <button class="pcrp-prev">&#10094;</button>
    <button class="pcrp-next">&#10095;</button>
</div>


<?php
$html = ob_get_clean();
wp_send_json_success($html); // Enviar el HTML generado
?>
