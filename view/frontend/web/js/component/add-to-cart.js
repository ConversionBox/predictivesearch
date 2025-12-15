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
            toCart: function(id, buttonElement) {
                try {
                   // Change button text to "Adding..." and disable it
                   if (buttonElement) {
                       buttonElement.textContent = 'Adding...';
                       buttonElement.disabled = true;
                   }
                   
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
                            
                            // Reset button text to "Add to Cart" after success
                            if (buttonElement) {
                                buttonElement.textContent = 'Add to Cart';
                                buttonElement.disabled = false;
                            }
                        } else {
                            if (response.responseJSON.url) {
                                window.location.replace(response.responseJSON.url);
                            }
                            // Reset button on error
                            if (buttonElement) {
                                buttonElement.textContent = 'Add to Cart';
                                buttonElement.disabled = false;
                            }
                        }
                    },
                    error: function (xhr, status, errorThrown) {
                        console.log('Error happens. Try again.');
                        // Reset button on error
                        if (buttonElement) {
                            buttonElement.textContent = 'Add to Cart';
                            buttonElement.disabled = false;
                        }
                    }
                    });
                } catch (error) {
                    console.log(error)
                    // Reset button on exception
                    if (buttonElement) {
                        buttonElement.textContent = 'Add to Cart';
                        buttonElement.disabled = false;
                    }
                }
            }
        };
    }
);

