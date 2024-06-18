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
            // Re-enlazar eventos de clic en los botones "Agregar al carrito"
            bindAddToCartEvents();

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

            // Eventos de clic en botones de navegación
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

            // Verificar si el ancho de la ventana es menor o igual a 768px (tabletas)
            if (window.innerWidth <= 768) {
                // Evento de desplazamiento con el ratón (scroll)
                carousel.addEventListener('wheel', (event) => {
                    if (event.deltaY > 0) {
                        nextButton.click();
                    } else {
                        prevButton.click();
                    }
                });

                // Variables para manejo de eventos touch
                let touchStartX = 0;
                let touchEndX = 0;

                carousel.addEventListener('touchstart', (event) => {
                    touchStartX = event.changedTouches[0].screenX;
                });

                carousel.addEventListener('touchmove', (event) => {
                    touchEndX = event.changedTouches[0].screenX;
                });

                carousel.addEventListener('touchend', () => {
                    if (touchEndX < touchStartX) {
                        nextButton.click();
                    } else if (touchEndX > touchStartX) {
                        prevButton.click();
                    }
                });
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            // Manejo de errores
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

    function bindAddToCartEvents() {
        $(".button-addToCart-home").off('click').on('click', function() {
            var productID = this.id;
            $.post('/wp-admin/admin-ajax.php', {
                action: 'add_to_cart_home',
                id_product_card: productID
            }, function(response) {
                $(document.body).trigger('wc_fragment_refresh');
                $(".cfw-side-cart-floating-button").click();
            });
        });
    }
    bindAddToCartEvents();
});
