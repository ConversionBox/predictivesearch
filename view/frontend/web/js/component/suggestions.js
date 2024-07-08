define(
    [
        'jquery',
        'mage/url',
    ], function ($, urlFormatter) {
        /**
         * Index Prefix
         */
        const INDEX_PERFIX = typesenseConfig.general.indexprefix;
    
        /**
         * Searchable Attributes
         */
        const SEARCHBLE_ATTRIBUTES = typesenseConfig.products.attributes;

        const POPULAR_TERMS = typesenseConfig.search_terms.data;
        const HIGHLIGHTS = typesenseConfig.general.highlights;
        const STORE = typesenseConfig.general.storeCode;
        const MAX_COUNT = typesenseConfig.auto_complete.suggestions_count;

        return {
            /**
             * 
             * @param {*} keyword 
             * @param {*} typsenseClient 
             */
            suggestions: function(keyword, typsenseClient) {
                let searchAttributes = SEARCHBLE_ATTRIBUTES.map((item) => {
                    if (item.search == 1) {
                        return item.code;
                    }
                });
                searchAttributes = searchAttributes.join(',');

                try {

                    //preparing search params
                    let searchParameters = {
                        'q'         : keyword,
                        'query_by'  : 'product_name',
                        'per_page'  : MAX_COUNT,
                    }

                    typsenseClient.collections(INDEX_PERFIX+STORE+'-products').documents().search(searchParameters).then((searchResults) => {
                        let html = '';
                        if (searchResults.hits.length < 1) {
                            let htmlhead = '<div class="popular_search_head">Popular Searches </div>';
                            $.each(POPULAR_TERMS, function (key, val) {
                                let valkeyword = val.split(" ")[0];
                                html += `
                                    <a href="${urlFormatter.build('catalogsearch/result/?q='+valkeyword)}">
                                        <div class="popular_items">${val.toUpperCase()}</div>
                                    </a>
                                `
                            });
                            html = htmlhead+html;
                        }
                        $.each(searchResults.hits, function (key, val) {
                            var name = val.document.name;
                            if (HIGHLIGHTS == 1) {
                                name = val.highlight.product_name.snippet;
                            }
                            let keyword = val.document.name.split(" ")[0];
                                html += `
                                    <div class="suggestion_container">
                                        <a href="${urlFormatter.build('catalogsearch/result/?q='+keyword)}">
                                            <div>${name} ${val.document.category.length >= 1 ? `<span class="suggest_category">${' in '+val.document.category[0]}</span>`: ''}</div>
                                        </a>
                                    </div>
                            `;
                        });
                        $('#suggestion_section').html(html);
                       
                    })
                    .catch((error) => {
                    });
                } catch (error) {
                    console.log(error)
                }
            }
        };
    }
);
