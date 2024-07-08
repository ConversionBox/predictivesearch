require(
    [
        'jquery',
        'Magento_Ui/js/modal/alert'
    ],
    function ($, alert) {
        $('#delete_index').click(function(e) {
            e.preventDefault();
            let indexValue = $('#typesense_queue_queue_index_type').val();
            if (!indexValue) {
                alert({
                    content: 'Select index to be deleted.'
                })
                return false;
            }

            alert({
               content: 'Do you want to continue.',
               buttons: [{
                    text: $.mage.__('OK'),
                    class: 'action-primary action-accept',
                    click: function () {
                        $.ajax({
                            showLoader: true, 
                            url: window.baseDomian+'typesense/index/deleteIndex',
                            data: {
                                'index': indexValue
                            },
                            type: "GET", 
                            dataType: 'json'
                        }).done(function (data) { 
                            $('#response_message').delay(1000).fadeIn();
                            if (data.success) {
                                $('#response_message').html('Index deleted Successfully');
                                $('#response_message').css('color', '#097009');
                                $('#response_message').css('background', '#f3f310');
                                $('#response_message').delay(1000).fadeOut();
                            } else {
                                $('#response_message').html('Something Went Wrong try again');
                                $('#response_message').css('color', '#097009');
                                $('#response_message').css('background', '#f3f310');
                                $('#response_message').delay(1000).fadeOut();
                            }
                           location.reload();
                        });
                        this.closeModal(true);
                    }
                }]
            })
        });

        $('#reindex_data').click(function(e) {
            e.preventDefault();

            alert({
                content: 'Continue with',
                buttons: [{
                        text: $.mage.__('Full Reindex'),
                        class: 'action-primary action-accept',
                        click: function () {
                            callAjax('fullindex');
                            this.closeModal(true);
                        }
                    },
                    {
                        text: $.mage.__('Sync With Cron'),
                        class: 'action-primary action-accept',
                        click: function () {
                           callAjax('cron')
                           this.closeModal(true);
                       }
                    }
                ]
            })
        });

        function callAjax(param) {
            $.ajax({
                showLoader: true, 
                url: window.baseDomian+'typesense/index/adminReindex',
                data: {
                    'mode': param
                },
                type: "GET", 
                dataType: 'json'
            }).done(function (data) { 
                location.reload();
            });
        }
    }
);
