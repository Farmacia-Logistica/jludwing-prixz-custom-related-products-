jQuery(document).ready(function($) {
    var product_id = prixz_ajax.product_id;
    var ajax_url = prixz_ajax.ajax_url;

    if (!ajax_url || !product_id) {
        return; // Si no hay URL de AJAX o ID de producto, no hacemos nada
    }

    $.ajax({
        url: ajax_url,
        type: 'POST',
        data: {
            action: 'prixz_get_related_products',
            product_id: product_id
        },
        success: function(response) {
            if (!response.success) {
                return; // Si la respuesta no es exitosa, salimos de la función
            }

            $('#prixz-custom-related-products-container').html(response.data);
            // Re-inicializar los scripts de WooCommerce después de cargar el contenido
            initWooCommerceScripts();

            const carousel = document.querySelector('.pcrp-carousel');
            const prevButton = document.querySelector('.pcrp-prev');
            const nextButton = document.querySelector('.pcrp-next');
            
            let counter = 0;
            const itemCount = document.querySelectorAll('.pcrp-carousel-item').length;
            let stepPercentage = getStepPercentage(window.innerWidth);

            // Ocultar las flechas si hay 4 o menos productos
            if (itemCount <= 4) {
                prevButton.style.display = 'none';
                nextButton.style.display = 'none';
            }

            nextButton.addEventListener('click', () => {
                counter++;
                carousel.style.transform = `translateX(${-counter * stepPercentage}%)`;
                if (counter === itemCount - 3) {
                    resetCarousel(carousel, counter, stepPercentage);
                    counter = 0;
                }
            });
            
            prevButton.addEventListener('click', () => {
                if (counter > 0) {
                    counter--;
                    carousel.style.transform = `translateX(${-counter * stepPercentage}%)`;
                }
            });
        },
        error: function(jqXHR, textStatus, errorThrown) {
        }
    });

    function getStepPercentage(windowWidth) {
        if (windowWidth <= 425) {
            return 80; // Avance para dispositivos móviles
        }
        if (windowWidth <= 768) {
            return 50; // Avance para tabletas
        }
        return 25; // Avance predeterminado para dispositivos grandes
    }

    function resetCarousel(carousel, counter, stepPercentage) {
        setTimeout(() => {
            carousel.style.transition = 'transform 0.5s ease';
            carousel.style.transform = `translateX(${-counter * stepPercentage}%)`;
        }, 500);
        setTimeout(() => {
            carousel.style.transition = 'transform 0.5s ease';
        }, 550);
    }

    function initWooCommerceScripts() {
        // Re-enlazar eventos de WooCommerce para agregar al carrito
        $( document.body ).trigger( 'wc_fragment_refresh' );
        // Forzar la actualización de scripts de WooCommerce
        if (typeof wc_add_to_cart_variation_params !== 'undefined') {
            $('.variations_form').each(function() {
                $(this).wc_variation_form();
            });
        }
        if (typeof $.fn.wc_add_to_cart !== 'undefined') {
            $('.single_add_to_cart_button').each(function() {
                $(this).wc_add_to_cart();
            });
        }
    }
});
