define(
    [
        'jquery',
    ], function ($) {
        'use strict';
        let priceParam = '';
        let sortParam='';
        return {
            updateParams: function(params, mode = null, page = null,sortQuery=null) {
                const urlParams = new URLSearchParams(window.location.search);
                var keyword = $("#search-result-box").val();
                let queryParam = urlParams.get('q');
                let searchparams = '';
                let filterData = {};
                
                // Build filter parameters
                if (params && typeof params === 'object') {
                    for (let key of Object.keys(params)) {
                        filterData.key = params[key];
                        if (params[key]) {
                            searchparams += '&&'+key+':='+params[key];
                        }
                    }
                }
      
                let finalParam = searchparams+priceParam;
                if (queryParam) {
                    finalParam = queryParam+finalParam;
                }
                let currentUrl = window.location.href;
                let urlParts = currentUrl.split('?');
                let baseUrl = urlParts[0];
                let newUrl = '';
                if (!mode) {
                    newUrl  = baseUrl + '?q=' + finalParam;
                } else {
                    newUrl  = baseUrl + '?'+ finalParam;
                }

                if (page && page != 1) {
                    newUrl = newUrl+'&&page='+page;
                }
                if (sortQuery) {
                    newUrl = newUrl+'&&sort_by='+sortQuery;
                }
                 
                // Update the browser's URL
                window.history.pushState({ path: newUrl }, '', newUrl);
            }
        };
    }
);
