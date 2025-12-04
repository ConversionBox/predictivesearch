define(
    [
        'jquery',
        'mage/url',
        'Magento_Catalog/js/price-utils',
    ], function ($, urlFormatter, priceUtils) {
        /**  Index Prefix */
        const INDEX_PERFIX = typesenseConfig.general.indexprefix;

        /** Product Config */
        const PRODUCT_MAX_COUNT = typesenseConfig.auto_complete.no_products;
        const PROD_SEARCHBLE_ATTRIBUTES = typesenseConfig.products.attributes;
        const PROD_RANKING = typesenseConfig.products.ranking;
        const CURRENCY = typesenseConfig.general.store_currency;
        /** Category Config */
        const CAT_MAX_COUNT = typesenseConfig.auto_complete.category_count;
        const CAT_SEARCHBLE_ATTRIBUTES = typesenseConfig.category.attributes;
        const CAT_RANKING = typesenseConfig.category.ranking;

        /** Page Config */
        const PAGE_MAX_COUNT = typesenseConfig.auto_complete.pages_count;
        const SHOW_PRICE = typesenseConfig.auto_complete.show_price;
        const SHOW_SKU = typesenseConfig.auto_complete.show_sku;
        const SHOW_DESCRIPTION = typesenseConfig.auto_complete.show_description;
        const Max_DESCRIPTION_LINE = typesenseConfig.auto_complete.max_description_line;
        const  SEE_ALL_BUTTON = typesenseConfig.auto_complete.see_all_button;
        const SHOW_OUT_OF_STOCK = typesenseConfig.auto_complete.show_out_of_stock;
        /** Typo Tolerance */
        const TYPO_ENABLED = typesenseConfig.typotolerance.enable;
        const WORD_LENGTH = typesenseConfig.typotolerance.word_length;

        /** Excluded Pages         */
        const EXCUDED_PAGE = typesenseConfig.auto_complete.excluded_page;

        /** General Config */
        const HIGHLIGHTS = typesenseConfig.general.highlights;
        const PLACEHOLDER = typesenseConfig.general.placeholder;
        const STORE = typesenseConfig.general.storeCode;
        const POPULAR_TERMS = typesenseConfig.search_terms.data;
        const UNIQUEID = typesenseConfig.general.unique_id;
        const CATEGORY_SECTION = typesenseConfig.auto_complete.category_enabled;
       	const PAGE_SECTION = typesenseConfig.auto_complete.pages_enabled;
	    const SUGGESTION_SECTION = typesenseConfig.auto_complete.suggestions;
        let excludedPageArr = [];
        let analyticsURL= typesenseConfig.general.analytic_url;
        if (Object.keys(EXCUDED_PAGE).length >= 1) {
            $.each(EXCUDED_PAGE, function (key, item) {
                excludedPageArr.push(item.page)
            });
        }
        
        // Track latest search request ID to prevent race conditions
        let latestSearchId = 0;

        return {
            /**
             * 
             * @param {*} keyword 
             * @param {*} typsenseClient 
             * @param {*} callback - Optional callback function to execute after search completes
             */
            multiSearch: function(keyword, typsenseClient,callback) {
                try {
                    // Generate unique ID for this search request
                    const currentSearchId = ++latestSearchId;
                    
                    let searches = [getProductAttributes(keyword)];
                    
                    if (CATEGORY_SECTION == 1) {
                        searches.push(getCategoryAttributes(keyword));
                    }
                    
                    if (PAGE_SECTION == 1) {
                        searches.push(getPageAttributes(keyword));
                    }
                    
                    if (SUGGESTION_SECTION == 1) {
                        searches.push(getQuerySuggestions(keyword));
                    }
                    
                    let searchRequests = {
                        'searches': searches
                    }
                    let commonSearchParams = {}
                    $.when(typsenseClient.multiSearch.perform(searchRequests, commonSearchParams)).done(function(searchResults) {
                        // Only process results if this is still the latest search
                        if (currentSearchId !== latestSearchId) {
                            console.log('Ignoring outdated search results (ID: ' + currentSearchId + ', Latest: ' + latestSearchId + ')');
                            return;
                        }
                        
                        $.each(searchResults.results, function(index, value) {
                            if (value.request_params.collection_name === INDEX_PERFIX+STORE+'-products') {
                                renderProducts(value.hits, value.found,keyword,value.out_of,value.search_time_ms);
                            }
                            if (CATEGORY_SECTION == 1 && value.request_params.collection_name === INDEX_PERFIX+STORE+'-categories') {
                                renderCategory(value.hits);
                            }
                            if (PAGE_SECTION == 1 && value.request_params.collection_name === INDEX_PERFIX+STORE+'-pages') {
                                renderPages(value.hits);
                            }
                            if (SUGGESTION_SECTION == 1 && value.request_params.collection_name === INDEX_PERFIX+STORE+'-suggestions') {
                                renderSuggestions(value.hits);
                            }
                        });
                       hitSearchAnalytics(keyword,searchResults.results);
                        // Execute callback if provided
                       if (typeof callback === 'function') {
                           callback();
                       }
                    });
                } catch (error) {
                    console.log(error)
                }
            }
        };
         function hitSearchAnalytics(keyword,searchResults) {
            setTimeout(function () {
          try {
          const postData = {
        uniqueId: UNIQUEID,
        searchKey: keyword,
        searchResult: searchResults,
        sessionId: $.cookie("_conversion_box_track_id")
       };
    $.ajax({
        url: analyticsURL+`api/v1/analytics/autocompleteLog`,
        type: 'POST',
        contentType: 'application/json',
        dataType: 'json',
        data: JSON.stringify(postData),
        success: function(data) {
            if (!data.ok) {
                    console.log('Error:Network response was not ok');
                }
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
        }
    });

} catch (error) {
    console.error('Error:', error);
}
       }, 6000);

                    
                    }

        /**
         * 
         * @param string keyword 
         */
          function getQuerySuggestions(keyword) {
    
            let suggestions = {
                'collection': INDEX_PERFIX+STORE+'-suggestions',
                'q'         : keyword,
                'query_by'  : 'q',
                'per_page'  : typesenseConfig.auto_complete.suggestions_count
            }            

            return suggestions;
        }


        /**
         * 
         * @param string keyword 
         */
        function getProductAttributes(keyword) {
            let productSearchAttributes = PROD_SEARCHBLE_ATTRIBUTES.map((item) => {
                if (item.search == 1) {
                    return item.code;
                }
            });
            productSearchAttributes = productSearchAttributes.join(',');
            let ranking = null;
            if (PROD_RANKING !== false && PROD_RANKING) {
                ranking = PROD_RANKING;
            }
            let productSearchParameters = {
                'collection': INDEX_PERFIX+STORE+'-products',
                'q'         : keyword,
                'query_by'  : productSearchAttributes,
                'per_page'  : PRODUCT_MAX_COUNT,
                'sort_by'   : ranking,
                'filter_by' : `storeCode:["${STORE}"]`
            }    
             if(SHOW_OUT_OF_STOCK == 0){
                    productSearchParameters.filter_by += ` && stock_status:=true`;
                }        
            $.each(productSearchParameters, function (key, val) {
                if (!val) {
                    delete productSearchParameters[key];
                }
            })
            return productSearchParameters;
        }
                /**
         * 
         * @param {*} hits 
         */
        function renderSuggestions(hits) {
            let html = '';
            
            if (hits.length < 1) {
                // Hide suggestion section when no hits
                    $('#suggestion_section').parent().hide();
                    return;
            }

            // Add Suggestions heading when there are hits
            let htmlhead = '<span class="autocomplete_head">Popular Searches</span>';
            $.each(hits, function (key, val) {
                let name = val.document.q;
                if (HIGHLIGHTS == 1) {
                    name = val.highlight.q.snippet;
                }
                html += ` <div class="suggestion_container">
                    <a href="${urlFormatter.build('catalogsearch/result/?q='+val.document.q)}">
                        <div class="suggest_category">${name}</div>
                    </a>
                </div>`;
            });
            html = htmlhead + html;
            $('#suggestion_section').html(html);
            $('#suggestion_section').parent().show();
        }
        /**
         * 
         * @param {*} hits 
         */
        function renderProducts(hits, found,keyword,out_of,search_time_ms) {
          let searchUrl = BASE_URL+'catalogsearch/result/?q='+keyword;
            html = '';
            if (hits.length < 1) {
                html = 'No products found';
            }

            let count = 0;
            $.each(hits, function (key, val) {
                let price =  CURRENCY + parseFloat(val.document.price).toFixed(2);
                if(val.document.type_id == 'bundle'){
                      price = formatPriceRange(val.document.price_range);
                      }
                  let priceval = Number(price);
                  priceval = Math.floor(priceval * 100) / 100; // truncate instead of round
                  if (val.document.special_price) {
                        let currentDate = new Date();
                        let startDate = new Date(val.document.special_from_date);
                        let endDate = new Date(val.document.special_to_date);
                         if (isDateInRange(new Date(), new Date(val.document.special_from_date), new Date(val.document.special_to_date))) {
                            let splval = Number(val.document.special_price);
                               splval = Math.floor(splval * 100) / 100;
                               price = CURRENCY + parseFloat(val.document.special_price).toFixed(2);

                        }
                    }

                    var name = val.document.product_name;
                    var proddescription =  val.document.description;
                    var description = proddescription.replace(/<\/?[^>]+(>|$)/g, "")
                    var sku = val.document.sku;
                     if (typeof val.highlight !== 'undefined'&& HIGHLIGHTS == 1) {
                        var highlight = val.highlight.name;
                        if (val.highlight.name) {
                            var name = val.highlight.name.snippet;
                        } else if (highlight == 'sku') {
                            var sku =  val.highlight.sku.snippet;
                        }
                    }
                    let image = null;
                    if (val.document.thumbnail) {
                        image = val.document.thumbnail;
                    } else if(typeof image === 'undefined' || image === null) {
                        image = BASE_URL+`media/catalog/product/placeholder/`+PLACEHOLDER;
                    }
                     html += `
                        <div class="product-item">
                            <a href="${val.document.url}" >
                                <div class="product-wrapper">
                                    <div class="product-image-div">
                                        <img src="${image}" class="product-image"/>
                                    </div>
                                    <div class="predictive-product_container">
                                        <div class="predictive-product_heading">${name}</div>`;
                                          if(SHOW_DESCRIPTION == 1){
                                               html += `<div class="predictive-product_description" style="-webkit-line-clamp:${Max_DESCRIPTION_LINE};">${description}</div>`;
                                           }
                                           if(SHOW_SKU == 1){
                                        html += `<div class="predictive-product_sku">SKU: ${sku}</div>`;
                                           }
                                          if(SHOW_PRICE == 1){
                                         html += `<div class="predictive-product_price" >`;
                                         html+=`${price}`;
                                         html +=`</div>`;
                                        }
                                   html +=`</div>
                                </div>
                            </a>
                        </div>
                    `;                    count++;
                   /* if (count == PRODUCT_MAX_COUNT) {
                        $('.product-viewall').html('View All '+found+' Products')
                        $('.product-viewall').show();
                        return false;
                    }*/
                   if (count == PRODUCT_MAX_COUNT) {
                                return false;
                    }

            });

            if (SEE_ALL_BUTTON == 0 || hits.length < 1) {
                $('.product-viewall').hide();
            }else if(hits.length > 1 && SEE_ALL_BUTTON == 1){
                $('.product-viewall').html('View All '+found+' Products');
                $('.product-viewall').show();
             }            
            $('#product_section').html(html);
        }
        
      function normalizeDate(date) {
            return new Date(date.getFullYear(), date.getMonth(), date.getDate());
            }
    function formatPriceRange(priceRange) {
        // Remove all $ signs
        let cleaned = priceRange.replace(/\$/g, "").trim();
        const format = (value) => {
        let num = parseFloat(value);
        if (isNaN(num)) return "0.00";
        return num.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    };

        // Check if it's a range (contains "-")
        if (cleaned.includes("-")) {
            let [min, max] = cleaned.split("-");
               return `$${format(min)} - $${format(max)}`;
        } else {
                return `$${format(cleaned)}`;
        }
        }

        function isDateInRange(current, start, end) {
            const c = normalizeDate(current);
            const s = normalizeDate(start);
            const e = normalizeDate(end);
            return c >= s && c <= e;
            }

        /**
         * 
         * @param string keyword 
         */
        function getCategoryAttributes(keyword) {
            let catSearchAttributes = CAT_SEARCHBLE_ATTRIBUTES.map((item) => {
                if (item.search == 1) {
                    return item.code;
                }
            });

            catSearchAttributes = catSearchAttributes.join(',');
            let ranking = null;
            if (CAT_RANKING !== false && CAT_RANKING) {
                ranking = CAT_RANKING;
            }
            let catSearchParameters = {
                'collection': INDEX_PERFIX+STORE+'-categories',
                'q'         : keyword,
                'query_by'  : 'category_name,'+catSearchAttributes,
                'per_page'  : CAT_MAX_COUNT,
                'filter_by' : `store:["${STORE}"]`,
                'sort_by'   : ranking,
            }
            $.each(catSearchParameters, function (key, val) {
                if (!val) {
                    delete catSearchParameters[key];
                }
            })
            return catSearchParameters;
        }

        /**
         * 
         * @param {*} hits 
         */
        function renderCategory(hits) {
            if (hits.length < 1) {
                $('#category_section').parent().hide();
                return;
            }
            
            let html = '';
            let hasValidCategory = false;
            $.each(hits, function (key, val) {
                if (val.document.status == 1) {
                    hasValidCategory = true;
                    var path = val.document.path;
                    if (HIGHLIGHTS == 1) {
                        if (typeof val.highlight.path !== 'undefined' && typeof val.highlight.path.snippet !== 'undefined') {
                            var path = path.replace(val.document.path,val.highlight.path.snippet);
                        }
                    }
                    html += `
                        <div>
                            <a href="${val.document.url}">${path}</a>
                        </div>
                    `;
                }
            });
            
            if (hasValidCategory && html.trim() !== '') {
                // Add heading when there are valid categories
                let fullHtml = '<span class="autocomplete_head">Categories</span>' + html;
                $('#category_section').html(fullHtml);
                $('#category_section').parent().show();
            } else {
                $('#category_section').parent().hide();
            }
        }

        /**
         * 
         * @param string keyword 
         */
        function getPageAttributes(keyword) {
            let pageSearchParameters = {
                'collection': INDEX_PERFIX+STORE+'-pages',
                'q'         : keyword,
                'query_by'  : 'page_title',
                'per_page'  : PAGE_MAX_COUNT,
                'filter_by' : `store:["${STORE}"]`
            }
            $.each(pageSearchParameters, function (key, val) {
                if (!val) {
                    delete pageSearchParameters[key];
                }
            })
            return pageSearchParameters;
        }
        
        /**
         * 
         * @param {*} hits 
         */
        function renderPages(hits) {
            if (hits.length < 1) {
                $('#cms_section').parent().hide();
                return;
            }

            let html = '';
            let hasValidPage = false;
            $.each(hits, function (key, val) {
                var title =  val.document.page_title;
                if (HIGHLIGHTS == 1 && typeof(val.highlights[0].field) != "undefined" && val.highlights[0].field == 'page_title') {
                    var title = val.highlight.page_title.snippet;
                }
                if (val.document.status == 1 && $.inArray(val.document.identifier, excludedPageArr) === -1) {
                    hasValidPage = true;
                    html += `
                        <div>
                            <a href="${val.document.url}">${title}</a>
                        </div>
                    `;
                }
            });
            
            if (hasValidPage && html.trim() !== '') {
                // Add heading when there are valid pages
                let fullHtml = '<span class="autocomplete_head">Pages</span>' + html;
                $('#cms_section').html(fullHtml);
                $('#cms_section').parent().show();
            } else {
                $('#cms_section').parent().hide();
            }
        }

    }
);
