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
                let searchparams = '';
                let filterData = {};
                for (let key of Object.keys(params)) {
                    filterData.key = params[key];
                    if (params[key]) {
                        searchparams += '&&'+key+':='+params[key];
                    }
                }
      
                let finalParam = searchparams+priceParam;
                let currentUrl = window.location.href;
                let urlParts = currentUrl.split('?');
                let baseUrl = urlParts[0];
                let newUrl = '';
                 if(finalParam){
                    finalParam = finalParam.replace(/^&&/, "?");
                    newUrl  = baseUrl + finalParam;
                 }else{
                    newUrl  = baseUrl;
                 }  
                    let activePage = $(".page-item.active .page-link").text().trim();
                    if(activePage > 1){
                        newUrl = newUrl + '&&page='+ activePage;
                    }                  

                if (sortQuery) {
                    newUrl = newUrl+'&&sort_by='+sortQuery;
                }
                if (!newUrl.includes("?")) {
                    newUrl = newUrl.replace("&&", "?");
                }
                // Update the browser's URL
                window.history.pushState({ path: newUrl }, '', newUrl);
            }
        };
    }
);
