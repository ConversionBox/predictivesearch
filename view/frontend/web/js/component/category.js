define(
    [
        'jquery',
    ], function ($) {
        /**
         * Index Prefix
         */
        const INDEX_PERFIX = typesenseConfig.general.indexprefix;

        /**
         * Max Category Display
         */
         const MAX_COUNT = typesenseConfig.auto_complete.category_count;

        /**
         * Typo Tolerance
         */
        const TYPO_ENABLED = typesenseConfig.typotolerance.enable;
        const WORD_LENGTH = typesenseConfig.typotolerance.word_length;

        /**
         * Searchable Attributes
         */
        const SEARCHBLE_ATTRIBUTES = typesenseConfig.category.attributes;
        const RANKING = typesenseConfig.category.ranking;
        const HIGHLIGHTS = typesenseConfig.general.highlights;
        const STORE = typesenseConfig.general.storeCode;

        return {
            /**
             * 
             * @param {*} keyword 
             * @param {*} typsenseClient 
             */
            categorySearch: function(keyword, typsenseClient) {
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
                        'query_by'  : 'category_name,'+searchAttributes,
                        'per_page'  : MAX_COUNT,
                        'filter_by' : `store:["${STORE}"]`,
                        'sort_by'   : ranking,
                        'typo_tokens_threshold' : TYPO_ENABLED,
                        'num_typos' : 2,
                        'min_len_1typo' : WORD_LENGTH,
                        'min_len_2typo' : WORD_LENGTH,
                    }
            
                        typsenseClient.collections(INDEX_PERFIX+STORE+'-categories').documents().search(searchParameters).then((searchResults) => {
                            let html = '';
                            if (searchResults.hits.length < 1) {
                                html = 'No Categories found';
                            }
                            $.each(searchResults.hits, function (key, val) {
                                if (val.document.status == 1) {
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
                            $('#category_section').html(html);
                        })
                        .catch((error) => {
                            console.error(error);
                        });
                } catch (error) {
                    console.log(error)
                }
            }
        };
    }
);
