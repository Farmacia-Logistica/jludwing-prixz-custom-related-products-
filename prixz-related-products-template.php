<?php
ob_start();
?>
<div class="owl-carousel owl-theme">
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
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
<script>
    jQuery(document).ready(function(){
        jQuery(".owl-carousel").owlCarousel({
            loop: true,
            margin: 10,
            nav: true,
            responsive: {
                0: {
                    items: 1
                },
                600: {
                    items: 3
                },
                1000: {
                    items: 4
                }
            }
        });
    });
</script>
