<?php
ob_start();
?>
<div class="products owl-carousel owl-theme">
    <?php foreach ($related_products as $related_product) : ?>
        <?php $post_object = get_post($related_product->get_id()); ?>
        <?php setup_postdata($GLOBALS['post'] =& $post_object); ?>
        <div class="item">
            <?php wc_get_template_part('content', 'product'); ?>
        </div>
        <?php wp_reset_postdata(); ?>
    <?php endforeach; ?>
</div>
<?php
$html = ob_get_clean();
echo $html;
$product_count = count($related_products);
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
<script>
    jQuery(document).ready(function(){
        var productCount = <?php echo $product_count; ?>;
        console.log("cuantos: ", productCount);
        let loopStatus = productCount >= 3? true : false; 
        jQuery(".owl-carousel").owlCarousel({
            loop: loopStatus,
            margin: 10,
            nav: true,
            responsive: {
                0: {
                    items: 1
                },
                360: {
                    items: 2
                },
                1010: {
                    items: 3
                },
                1150: {
                    items: 4
                }
            }
        });
    });
</script>
