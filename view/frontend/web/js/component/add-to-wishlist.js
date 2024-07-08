define(
    [
        'jquery',
        'mage/url',
        'Magento_Customer/js/customer-data'
    ], function ($, UrlBuilder, customerData) {
        'use strict';

        UrlBuilder.setBaseUrl(BASE_URL);
        const WHISLIST_URL = UrlBuilder.build('typesense/Add/AddToWishlist');

        return {
            toWishlist: function(productId) {
                try {
                   var customer = customerData.get('customer');
                   var isCustomerLoggedIn = customer().firstname ? true : false;
                   $.ajax({
                        url: WHISLIST_URL,
                        type: 'GET',
                        dataType: 'json',
                        data: {
                            id: productId,
                            isCustomerLoggedIn: isCustomerLoggedIn,
                        },
                        success: function (response) {
                            if (customer().firstname) {
                             if (response.success) {
                            let sections = ['wishlist'];
                            customerData.invalidate(sections);
                            customerData.reload(sections, true);
                                    window.location.href = '/wishlist/index/index';
                                }
                            } else {
                                window.location.href = '/customer/account/login';
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
