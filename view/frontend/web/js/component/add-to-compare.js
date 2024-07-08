define(
    [
        'jquery',
        'mage/url',
        'Magento_Customer/js/customer-data'
    ], function ($, UrlBuilder, customerData) {
        'use strict';
        UrlBuilder.setBaseUrl(BASE_URL);
        return {
            toCompare: function(productId) {
                const COMPARE_URL = UrlBuilder.build('typesense/Add/AddToCompare');
                try {
                    $.ajax({
                        url: COMPARE_URL,
                        type: 'GET',
                        dataType: 'json',
                        data: {
                            id: productId,
                        },
                        success: function (response) {
                            let sections = ['compare-products'];
                            customerData.invalidate(sections);
                            customerData.reload(sections, true);
                            if (response.success == true) {
                                $('#message_parent').css("display", "block");
                                $('#success_message').html(response.message);
                                window.scrollTo({ top: 0, behavior: 'smooth' });
                            } else {
                                window.location.href = '/catalog/product_compare/';
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error(error);
                        }
                    });
                } catch (error) {
                    console.log(error)
                }
            }
        };
    }
);
