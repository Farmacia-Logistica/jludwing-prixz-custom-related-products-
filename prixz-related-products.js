jQuery(document).ready(function($) {
    var product_id = prixz_ajax.product_id;
    var ajax_url = prixz_ajax.ajax_url;
    if (ajax_url && product_id) {
        $.ajax({
            url: ajax_url,
            type: 'POST',
            data: {
                action: 'prixz_get_related_products',
                product_id: product_id
            },
            success: function(response) {
                if (response.success) {
                    $('#prixz-custom-related-products-container').html(response.data);
                    const carousel = document.querySelector('.pcrp-carousel');
                    const prevButton = document.querySelector('.pcrp-prev');
                    const nextButton = document.querySelector('.pcrp-next');
                    
                    let counter = 0;
                    const itemCount = document.querySelectorAll('.pcrp-carousel-item').length;
                    let stepPercentage = 25; // Porcentaje de avance predeterminado

                    // Verificar si el dispositivo es un tablet o un móvil
                    if (window.innerWidth <= 768 && window.innerWidth >= 426 ) { // Ajusta este valor según tus necesidades para dispositivos móviles y tabletas
                        stepPercentage = 50; // Cambia el porcentaje de avance para dispositivos móviles y tabletas
                    }else{
                        if (window.innerWidth <= 425) { // Ajusta este valor según tus necesidades para dispositivos móviles y tabletas
                            stepPercentage = 80; // Cambia el porcentaje de avance para dispositivos móviles y tabletas
                        }
                    }

                    // Ocultar las flechas si hay 4 o menos productos
                    if (itemCount <= 4) {
                        prevButton.style.display = 'none';
                        nextButton.style.display = 'none';
                    }

                    nextButton.addEventListener('click', () => {
                        counter++;
                        carousel.style.transform = `translateX(${-counter * stepPercentage}%)`;
                        if (counter === itemCount - 3) {
                            setTimeout(() => {
                                carousel.style.transition = 'transform 0.5s ease';
                                counter = 0;
                                carousel.style.transform = `translateX(${-counter * stepPercentage}%)`;
                            }, 500);
                            setTimeout(() => {
                                carousel.style.transition = 'transform 0.5s ease';
                            }, 550);
                        }
                    });
                    
                    prevButton.addEventListener('click', () => {
                        if (counter > 0) {
                            counter--;
                            carousel.style.transform = `translateX(${-counter * stepPercentage}%)`;
                        }
                    });
                } else {
                    console.log('Prixz-Custom-Related-Error fetching related products:', response.data);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log('Prixz-Custom-Related-AJAX error:', textStatus, errorThrown);
            }
        });
    } else {
        console.log('Invalid AJAX URL or Product ID');
    }
});
