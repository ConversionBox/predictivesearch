define(
    [
        'jquery',
        'Magento_Catalog/js/price-utils',
    ], function ($, priceUtils) {
        /**
         * Index Prefix
         */
        const INDEX_PERFIX = typesenseConfig.general.indexprefix;
        
        /**
         * Max Product Display
         */
        const MAX_COUNT = typesenseConfig.auto_complete.no_products;

        /**
         * Searchable Attributes
         */
        const SEARCHBLE_ATTRIBUTES = typesenseConfig.products.attributes;
        const RANKING = typesenseConfig.products.ranking;

        /**
         * Typo Tolerance
         */
        const TYPO_ENABLED = typesenseConfig.typotolerance.enable;
        const WORD_LENGTH = typesenseConfig.typotolerance.word_length;

        /**
         * Highlights
         */
        const HIGHLIGHTS = typesenseConfig.general.highlights;
        const CURRENCY = typesenseConfig.general.store_currency;
        const STORE = typesenseConfig.general.storeCode;
        const PLACEHOLDER = typesenseConfig.general.placeholder;

        return {
            /**
             * 
             * @param {*} keyword 
             * @param {*} typsenseClient 
             */
            producSearch: function(keyword, typsenseClient) {
                let searchUrl = BASE_URL+'catalogsearch/result/?q='+keyword;
                try {
                    let searchAttributes = SEARCHBLE_ATTRIBUTES.map((item) => {
                        if (item.search == 1) {
                            return item.code;
                        }
                    });
                    searchAttributes = searchAttributes.join(',');
                    let ranking = null;
                    if (RANKING !== false && RANKING) {
                        ranking = RANKING;
                    }
                    let searchParameters = {
                        'q'         : keyword,
                        'query_by'  : searchAttributes,
                        'per_page'  : MAX_COUNT,
                        'sort_by'   : ranking,
                        'filter_by' : `storeCode:["${STORE}"]`,
                        'typo_tokens_threshold' : TYPO_ENABLED,
                        'num_typos' : 2,
                        'min_len_1typo' : WORD_LENGTH,
                        'min_len_2typo' : WORD_LENGTH,
                    }
                    typsenseClient.collections(INDEX_PERFIX+STORE+'-products').documents().search(searchParameters).then((searchResults) => {
                        let html = '';
                        if (searchResults.hits.length < 1) {
                            html = 'No products found';
                        }
                        
                        let count = 0;
                        $('#auto_search_time').html(
                            `<div>Found <a href="${searchUrl}">${searchResults.found}</a> out of ${searchResults.out_of} Results in ${searchResults.search_time_ms} ms</div>`
                        );
                        $.each(searchResults.hits, function (key, val) {
                            if (val.document.product_status == 1) {
                                let price = val.document.price;
                                if (val.document.special_price) {
                                    let currentDate = new Date();
                                    let startDate = new Date(val.document.special_from_date);
                                    let endDate = new Date(val.document.special_to_date);
                                    if (startDate <= currentDate && endDate >= currentDate) {
                                        price = `<span class="special_price">${val.document.special_price}</span>
                                             <span class="normal_price">${val.document.price}</span>
                                        `;
                                    }
                                }
                                var name = val.document.product_name;
                                var sku = val.document.sku;
                                if (typeof val.highlights[0] !== 'undefined'&& HIGHLIGHTS == 1) {
                                    var highlight = val.highlights[0].field || false;
                                    if (highlight == 'name') {
                                        var name = val.highlight.name.snippet;
                                    } else if (highlight == 'price') {
                                        price  = val.highlight.price.snippet;
                                    } else if (highlight == 'sku') {
                                        var sku =  val.highlight.sku.snippet;
                                    }
                                }

                                let image = null;
                                if (val.document.thumbnail) {
                                    image = val.document.thumbnail;
                                } else {
                                    image = PLACEHOLDER;
                                }
                                html += `
                                    <div class="product-item">
                                        <a href="${val.document.url}" >
                                            <div class="product-wrapper">
                                                <div class="product-image-div">
                                                    <img src="${image}" class="product-image"/>
                                                </div>
                                                <div class="predictive-product_container">
                                                    <div class="predictive-product_heading">${name}</div>
                                                    <div class="predictive-product_sku">Sku: ${sku}</div>
                                                    <div class="predictive-product_price">${CURRENCY+priceUtils.formatPriceLocale(price)}</div>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                `;
                                count++;
                                if (count == MAX_COUNT) {
                                    $('.product-viewall').html('View All '+searchResults.found+' Products')
                                    $('.product-viewall').show();
                                    return false;
                                }
                            }
                        });

                        if (count < MAX_COUNT) {
                            $('.product-viewall').hide();
                        }
                        $('#product_section').html(html);
                    })
                    .catch((error) => {
                        $('#product_section').html('Configuration issues try again');
                        console.error(error);
                    });
                } catch (error) {
                    console.log(error)
                }
            }
        };
    }
);
