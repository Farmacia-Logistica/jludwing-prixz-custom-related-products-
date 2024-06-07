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
