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
            const itemCount = document.querySelectorAll('.pcrp-carousel-item').length;
            let counter = 0;
            let stepPercentage = getStepPercentage(window.innerWidth);

            // Ocultar las flechas si hay 4 o menos productos
            if (itemCount <= 4) {
                prevButton.style.display = 'none';
                nextButton.style.display = 'none';
            }

            // Evento de clic en botón siguiente
            nextButton.addEventListener('click', () => {
                if (counter < itemCount - 4) {
                    counter++;
                } else {
                    counter = 0; // Reiniciar el contador
                }
                carousel.style.transform = `translateX(${-counter * stepPercentage}%)`;
            });

            // Evento de clic en botón anterior
            prevButton.addEventListener('click', () => {
                if (counter > 0) {
                    counter--;
                } else {
                    counter = itemCount - 4; // Retroceder al último conjunto de elementos
                }
                carousel.style.transform = `translateX(${-counter * stepPercentage}%)`;
            });

            // Verificar si el ancho de la ventana es menor o igual a 768px (tabletas)
            if (window.innerWidth <= 768) {
                // Evento de desplazamiento con el ratón (scroll)
                carousel.addEventListener('wheel', (event) => {
                    event.preventDefault(); // Evitar el scroll predeterminado
                    if (event.deltaY > 0 && counter < itemCount - 4) { // Avanzar solo si no estamos en el último elemento
                        counter++;
                    } else if (event.deltaY < 0 && counter > 0) { // Retroceder solo si no estamos en el primer elemento
                        counter--;
                    }
                    carousel.style.transform = `translateX(${-counter * stepPercentage}%)`;
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
                    if (touchEndX < touchStartX && counter < itemCount - 4) { // Avanzar solo si no estamos en el último elemento
                        counter++;
                    } else if (touchEndX > touchStartX && counter > 0) { // Retroceder solo si no estamos en el primer elemento
                        counter--;
                    }
                    carousel.style.transform = `translateX(${-counter * stepPercentage}%)`;
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
