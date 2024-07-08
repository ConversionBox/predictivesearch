define(
    [
        'jquery',
    ], function ($) {
        /**
         * Index Prefix
         */
        const INDEX_PERFIX = typesenseConfig.general.indexprefix;

        /**
         * Max Page Display
         */
         const MAX_COUNT = typesenseConfig.auto_complete.pages_count;

        /**
         * Excluded Pages
         */
         const EXCUDED_PAGE = typesenseConfig.auto_complete.excluded_page;

        /**
         * Typo Tolerance
         */
        const TYPO_ENABLED = typesenseConfig.typotolerance.enable;
        const WORD_LENGTH = typesenseConfig.typotolerance.word_length;

        /**
         * Highlights
         */
        const HIGHLIGHTS = typesenseConfig.general.highlights;
        const STORE = typesenseConfig.general.storeCode;

         let excludedPageArr = [];
         if (Object.keys(EXCUDED_PAGE).length >= 1) {
            $.each(EXCUDED_PAGE, function (key, item) {
                excludedPageArr.push(item.page)
            });
        }
        
        return {
            /**
             * 
             * @param {*} keyword 
             * @param {*} typsenseClient 
             */
            cmsSearch: function(keyword, typsenseClient) {
                try {
                    let searchParameters = {
                        'q'         : keyword,
                        'query_by'  : 'page_title',
                        'per_page'  : MAX_COUNT,
                        'filter_by' : `store:["${STORE}"]`,
                        'typo_tokens_threshold' : TYPO_ENABLED,
                        'num_typos' : 2,
                        'min_len_1typo' : WORD_LENGTH,
                        'min_len_2typo' : WORD_LENGTH,
                    }
            
                        typsenseClient.collections(INDEX_PERFIX+STORE+'-pages').documents().search(searchParameters).then((searchResults) => {
                            let html = '';
                            if (searchResults.hits.length < 1) {
                                html = 'No Pages found';
                            }

                            $.each(searchResults.hits, function (key, val) {
                                var title =  val.document.page_title;
                                if (HIGHLIGHTS == 1 && typeof(val.highlights[0].field) != "undefined" && val.highlights[0].field == 'page_title') {
                                    var title = val.highlight.page_title.snippet;
                                }
                                if (val.document.status == 1 && $.inArray(val.document.identifier, excludedPageArr) === -1) {
                                    html += `
                                        <div>
                                            <a href="${val.document.url}">${title}</a>
                                        </div>
                                    `;
                                } else {
                                    html = 'No Pages found';
                                }
                            });
                           $('#cms_section').html(html);
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
