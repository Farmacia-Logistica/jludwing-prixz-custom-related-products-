<?php
ob_start();
?>
<div class="pcrp-carousel-arrows">
    <div id="pcrp-carouselExample" class="pcrp-carousel products" data-ride="carousel">
        <div class="pcrp-carousel-container">
            <div class="pcrp-carousel-inner">
                <?php foreach ($related_products as $related_product) : ?>
                    <?php $post_object = get_post($related_product->get_id()); ?>
                    <?php setup_postdata($GLOBALS['post'] =& $post_object); ?>
                    <div class="pcrp-carousel-item">
                        <?php wc_get_template_part('content', 'product'); ?>
                    </div>
                    <?php wp_reset_postdata(); // Importante para limpiar el postdata después del loop ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?php
$html = ob_get_clean();
echo $html; // Imprimir el HTML generado directamente
?>
