define(
    [
        'jquery',
        'mage/url',
        'Magento_Customer/js/customer-data'
    ], function ($, UrlBuilder, customerData) {
        'use strict';

        UrlBuilder.setBaseUrl(BASE_URL);
        const CART_URL = UrlBuilder.build('typesense/Add/AddToCart');

        return {
            toCart: function(id) {
                try {
                   $.ajax({
                        url: CART_URL,
                        type: 'GET',
                        dataType: 'json',
                        data: {
                            id: id
                        },
                    complete: function(response) {             
                        if (response.responseJSON.success) {
                            var sections = ['cart'];
                            customerData.invalidate(sections);
                            customerData.reload(sections, true);
                            $('#message_parent').css("display", "block");
                            $('#success_message').html(response.responseJSON.message);
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                        } else {
                            if (response.responseJSON.url) {
                                window.location.replace(response.responseJSON.url);
                            }
                        }
                    },
                    error: function (xhr, status, errorThrown) {
                        console.log('Error happens. Try again.');
                    }
                    });
                } catch (error) {
                    console.log(error)
                }
            }
        };
    }
);
