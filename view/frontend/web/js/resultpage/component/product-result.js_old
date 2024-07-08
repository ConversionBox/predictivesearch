define(
    [
        'jquery',
        'jquery/ui',
        'Thecommerceshop_Predictivesearch/js/plugin/pagination/jquery.twbsPagination',
        'Thecommerceshop_Predictivesearch/js/config/typesenseSearchConfig',
        'Thecommerceshop_Predictivesearch/js/component/add-to-cart',
        'mage/url',
        'Magento_Catalog/js/price-utils',
        'Thecommerceshop_Predictivesearch/js/component/add-to-wishlist',
        'Thecommerceshop_Predictivesearch/js/component/add-to-compare',
        'Thecommerceshop_Predictivesearch/js/resultpage/component/price',
        'Thecommerceshop_Predictivesearch/js/component/updateParam',
    ], function ($, ui, twbsPagination, searchConfig, addTOCart, urlFormatter, priceUtils, addToWishList, addToCompare, priceComponent, updateParam) {
        /**
         * Index Prefix
         */
        const INDEX_PERFIX = typesenseConfig.general.indexprefix;

        /**
         * Total number per page
         */
        const NO_PRODUCTS_PAGE = typesenseConfig.search_result.no_products;
        const sortOptions = typesenseConfig.search_result.sort_options;
        const ADD_To = typesenseConfig.search_result.addto_cart;
        const SEARCHBLE_ATTRIBUTES = typesenseConfig.products.attributes;
        const POPULAR_TERMS = typesenseConfig.search_terms.data;
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
        const SLIDER = typesenseConfig.search_result.price_slider;
        const CURRENCY = typesenseConfig.general.store_currency;
        const STORE = typesenseConfig.general.storeCode;
        const PLACEHOLDER = typesenseConfig.general.placeholder;
        const GRID_PRODUCT_COUNT = typesenseConfig.general.grid_per_value;
        const IMAGE_TYPE = typesenseConfig.search_result.image_type;
        const IMAGE_WIDTH = typesenseConfig.search_result.image_width;
        const IMAGE_HEIGHT = typesenseConfig.search_result.image_height;
        const SEMANTIC_SEARCH = typesenseConfig.additional_section.semantic_status;
        const HYBRID_SEARCH = typesenseConfig.additional_section.hybrid_search;
        const urlParams = new URLSearchParams(window.location.search);
        const queryParam = urlParams.get('q');
        let pageParam = 1;
        if (urlParams.get('page')) {
            pageParam = urlParams.get('page');
        }
        let sortParam = '';
        let selectedFilters = [];
        let selectedIndex = [];
        let filterParam = [];
        let stableContent = null;
        let selectedSort = null;
        let selectedDisplayvalue = null;
        let facetArr = [];
        let minValue = 0;
        let maxValue = 100;
        let searchResultsArray = [];
        let priceSlide = null;
        let facet = typesenseConfig.search_result.search_filters;
        let totalPage = 0;
        let visiblePage = 0;
        let productCount = 0;
        let loadParams = null;
        $.each(facet, function (key, val) {
           // console.log(val);
            facetArr.push(val.filterAttribute);
            //facetArr.push(val.facet);
        });
    
        let facetParam = facetArr.toString();

        let tmin = 0;
        let tmax = 0;
        let isSlide = false;

        $(document).ready(function() {
           loadParams = location.search.slice(location.search.indexOf('&&') + 2);
            let filterparamArr = [];
            loadParams.split('&&').forEach(function(param) {
                let stringArr = param.split(':=');
                if (stringArr[1] != undefined) {
                    let filterText = stringArr[1].replace("%20", " ");
                    filterparamArr[stringArr[0]] = filterText;
                }
            });
            filterParam = filterparamArr;
            sliderAction(location.search.split('=')[1], filterParam); 
        });

        return {
            /**
             * 
             * @param {*} keyword 
             * @param {*} typsenseClient 
             */
            performSearch: function(keyword, page, typsenseClient, filterValue) {
                $('#product-pagination').twbsPagination('destroy');
                if (pageParam) {
                    page = pageParam;
                }
                productSearch(keyword, page, typsenseClient, filterValue);
            },

            /**
             * 
             * @param {*} keyword 
             */
            sliderComponent: function(keyword) {
                //sliderAction(keyword, filterParam);
            },
        };

        /**
         * Product Search
         * 
         * @param {*} keyword 
         * @param {*} page 
         * @param {*} typsenseClient 
         */
        function productSearch (keyword, page, typsenseClient, filterValue, sortQuery, priceFilter, perPage = null ) {
            priceSlide = priceFilter;
            $( document ).ready(function() {
                $("#loader").remove();
            });
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
                    'per_page'  : NO_PRODUCTS_PAGE,
                    'page'      : page,
                    'facet_by'  : facetParam,
                    'sort_by'   : ranking,
                    'typo_tokens_threshold' : TYPO_ENABLED,
                    'num_typos' : 2,
                    'min_len_1typo' : WORD_LENGTH,
                    'min_len_2typo' : WORD_LENGTH,
                }

                if (perPage || $('#product_count_page').val()) {
                    perPage = ($('#product_count_page').val())?$('#product_count_page').val():perPage;
                    searchParameters.per_page = perPage;
                }

                let finalRequestParam = {};
                for (var key of Object.keys(filterParam)) {
                    if (!$.isArray(filterParam[key])) {
                        const valeArr = filterParam[key].split(',');
                        finalRequestParam[key] = valeArr;
                    }
                }

                if (selectedIndex.length >= 1) {
                    $('#clear_all').show();
                } else {
                    $('#clear_all').hide();
                }

                //make request query
                let requestQuery = '';
                $.each(finalRequestParam, function (key, val) {
                    if (val != '') {
                        requestQuery += key+':=['+val+'] &&';
                    }
                });
            
                if (requestQuery) {
                    searchParameters.query_by = searchAttributes;
                    requestQuery = requestQuery.slice(0, -2);
                    if ( (tmin && tmax)) {
                        requestQuery = requestQuery+'&& price:['+tmin+'..'+tmax+']';
                    }
                } else {
                    if (tmin && tmax) {
                        requestQuery = 'price:['+tmin+'..'+tmax+']';
                    }
                }

                searchParameters.filter_by = requestQuery+&&product_status:1;

                if (priceFilter ) {
                    priceFilter = priceFilter.split('-');
                    let multiRequestQuery = '';
                    if (SLIDER != 1) {
                        multiRequestQuery = 'price:=['+priceFilter[0]+'..'+priceFilter[1]+'] &&'+requestQuery;
                        multiRequestQuery = multiRequestQuery.slice(0, -2);
                        searchParameters.filter_by = multiRequestQuery;
                    }
                }

                let currentSort = $('#product_sort').val();
                if (!sortQuery && currentSort) {
                    currentSort = currentSort.split('-');
                    sortQuery = currentSort[0]+":"+currentSort[1];
                }
       
                if (sortQuery) {
                    searchParameters.sort_by = sortQuery;
                    
                }

                if (SEMANTIC_SEARCH == 1) {
                    searchParameters.query_by = 'embedding';
                    searchParameters.vector_query = 'embedding:([], k:200';
                    if (HYBRID_SEARCH == 1) {
                        searchParameters.query_by = 'embedding,'+searchAttributes;
                    }
                }

                typsenseClient.collections(INDEX_PERFIX+STORE+'-products').documents().search(searchParameters).then((searchResults) => {
                    searchResultsArray.push(searchResults);
                    let html = '';
                    if (searchResults.hits.length < 1) {
                        $('.filter_main').hide();
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
                    } else {
                        $('.filter_main').show();
                    }
                    
                    perPage = $('#product_count_page').val()?$('#product_count_page').val():perPage;  
                    if (searchResults.found > NO_PRODUCTS_PAGE) {
                        $('#product-pagination').show();
                        totalPage = searchResults.found/NO_PRODUCTS_PAGE;
                        totalPage = Math.ceil(totalPage);
                        if (totalPage > 4) {
                            visiblePage = 3;
                        } else {
                            visiblePage = totalPage;
                        }
                    } else {
                        $('#product-pagination').hide();
                    }

                    if (perPage > searchResults.found) {
                        $('#product-pagination').hide();
                    } else {
                        if (searchResults.found > NO_PRODUCTS_PAGE) {
                            $('#product-pagination').show();
                        }
                    }

                    let loadedProductCount = 0;
                    if (totalPage == searchResults.page) {
                        loadedProductCount = searchResults.found;
                    } else if (searchResults.found < NO_PRODUCTS_PAGE) {
                        loadedProductCount = searchResults.found;
                    } else {
                        loadedProductCount = (NO_PRODUCTS_PAGE * searchResults.page);
                    }

                    if (perPage || $('#product_count_page').val()) {
                        loadedProductCount = $('#product_count_page').val()*page;
                        if (loadedProductCount > searchResults.found) {
                            loadedProductCount = searchResults.found;
                        }
                    }
                    productCount = searchResults.found;
                    let paginationHtml = `<div>
                        <div>Found ${loadedProductCount} out of ${searchResults.found} Results in ${searchResults.search_time_ms} ms</div>
                    </div>`;
                    $('#product_count').html(paginationHtml);

                    //implementing sort options
                   let sOptions = '';
                    if (Object.keys(sortOptions).length > 1) {
                        $.each(sortOptions, function (key, item) {
                            sOptions += `  
                                <option value="${item.sortAttribute+'-'+item.sortDirection}">${item.fieldName}</option>
                            `;
                        });
                        let sortDropDown = `<select id="product_sort" name="product_sort">
                            <option value="">Select Option </option>
                                ${sOptions}
                            </select>`;
                        $('#sort_option').html(sortDropDown);
                    }

                    //setting selected sort option while refecting data
                    if (selectedSort) {
                        $('select[name^="product_sort"] option[value='+selectedSort+']').attr("selected","selected");
                    }

                    const sortSelect = document.querySelector('#product_sort');
                    if (sortSelect) {
                        sortSelect['onchange'] = function(e) {
                            let dropValue = $('#product_sort').val();
                            if (dropValue) {
                                selectedSort = dropValue;
                                dropValue = dropValue.split('-');
                                let sortQuery = dropValue[0]+':'+dropValue[1];
                                $('#product-pagination').twbsPagination('destroy');
                                paginationAction(totalPage, visiblePage, keyword, productCount);
                                productSearch(keyword, page, typsenseClient, null, sortQuery);
                            }
                        }
                    }

                    //product display count
                    let productCountOption  = '';
                    $.each(GRID_PRODUCT_COUNT.split(','), function (key, val) {
                        productCountOption += ` <option value="${val}">${val}</option>`;
                    });
                   
                    let productPageCount = `
                        <span>Show</span>
                        <select id="product_count_page" name="product_count_page">
                            <option value="">Select Option </option>
                                ${productCountOption}
                            </select>
                        <span>Per Page</span>
                            `;
                    $('#product_display_count').html(productPageCount);

                    if (selectedDisplayvalue) {
                        $('select[name^="product_count_page"] option[value='+selectedDisplayvalue+']').attr("selected","selected");
                    }

                    const displayCountSelect = document.querySelector('#product_count_page');
                    if (displayCountSelect) {
                        displayCountSelect['onchange'] = function(e) {
                            let displayValue = $('#product_count_page').val();
                            selectedDisplayvalue = displayValue;
                            totalPage = searchResults.found/displayValue;
                            totalPage = Math.ceil(totalPage);
                            if (totalPage < 5) {
                                visiblePage = totalPage - 1;
                            } else {
                                visiblePage = 5;
                            }
                           
                            if (displayValue) {
                                $('#product-pagination').twbsPagination('destroy');
                                paginationAction(totalPage, visiblePage, keyword, productCount);
                                productSearch(keyword, page, typsenseClient, null, sortQuery, null,displayValue);
                            }
                        }
                    }

                    paginationAction(totalPage, visiblePage, keyword, productCount);
                 
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
                            if (typeof val.highlights[0] !== 'undefined' && HIGHLIGHTS == 1) {
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
                            if (IMAGE_TYPE == 'product_base_image') {
                                image = val.document.image_url;
                            } else if (IMAGE_TYPE == 'product_small_image') {
                                image = val.document.small_image;
                            } else if (IMAGE_TYPE == 'product_thumbnail_image') {
                                image = val.document.thumbnail;
                            } else {
                                image = PLACEHOLDER;
                            }

                            html += `
                                <div class="product-wrapper-main">
                                    <a href="${val.document.url}" >
                                        <div class="product-wrapper">
                                            <div class="product-image-div">
                                                <img src="${image}" class="search-product-image" width="${IMAGE_WIDTH}" height="${IMAGE_HEIGHT}">
                                            </div>
                                            <div class="product_item_wrapper">
                                                <div class="item_name">${name}</div>
                                                <div class="item_sku">SKU: ${sku}</div>
                                                <div class="item_price">${CURRENCY+priceUtils.formatPriceLocale(price)}</div>
                                            </div>
                                        </div>
                                    </a>
                                    ${ADD_To == 1 ? `<div class="cartbutton_wrapper"><div class="btn_conatiner" id="btn_conatiner">
                                        <button class="cart_btn" id="${val.document.id}">Add to Cart</button>
                                    </div>`:''}
                                    <div class="whishlist_wrapper_main product-item">
                                        <a href="#" id="${val.document.id}" class="action towishlist wishlist_wrapper" data-wishlist-url="/wishlist/index/add/product/${val.document.id}" title="Add to Wish List" aria-label="Add to Wish List" data-action="add-to-wishlist" role="button">
                                        </a>
                                        <a href="#" id="${val.document.id}" class="action tocompare compare_wrapper" data-compare-url="/catalog/product_compare/add/product/${val.document.id}"   title="Add to Compare" aria-label="Add to Compare" data-action="add-to-compare" role="button">
                                        </a>
                                    </div></div>
                                </div>
                            `;
                        }
                    });
                    $('#product_result').html(html);
                    renderFilterOptions(searchResults);
                    showSelectedFilter(filterParam)
                    sliderAction(keyword, filterParam, $('#priceRange').val());

                    const cartBtn = document.querySelector('#product_result');
                    if (cartBtn) {
                        cartBtn.addEventListener('click', function (e) {
                            const target = e.target;
                            if (target.classList.contains('cart_btn')) {
                                const productId = target.id;
                                addTOCart.toCart(productId);
                                e.stopImmediatePropagation();
                            } else if (target.classList.contains('towishlist')) {
                                e.preventDefault();
                                const productId = target.id;
                                addToWishList. toWishlist(productId);
                            } else if (target.classList.contains('tocompare')) {
                                e.preventDefault();
                                const productId = target.id;
                                addToCompare.toCompare(productId);
                            }
                        });
                    }
                })
                .catch((error) => {
                    $('#product_result').html('Configuration issues try again');
                    console.error(error);
                });
            } catch (error) {
                console.log(error)
            }
        }

        function paginationAction(totalPage, visiblePage, keyword, productCount) { 
            const typsenseClient = searchConfig.createClient(typesenseConfig); 
            if (totalPage && visiblePage) {
                $('#product-pagination').twbsPagination({
                    totalPages: totalPage,
                    visiblePages: visiblePage,
                    startPage: parseInt(pageParam),
                    first:false,
                    last:false,
                    prev: '<<',
                    next: '>>',
                    onPageClick: function (event, page) {
                        if (page > 1) {
                            updateParam.updateParams(filterParam, null,page);
                        }else{
                            updateParam.updateParams(filterParam, null,page=1);
                        }
                        productSearch(keyword, page, typsenseClient);
                    }
                });
            }
        }

        /**
         * 
         * @param {*} filterParam 
         */
        function showSelectedFilter(filterParam) {
            let selectedHtml = '';
            for (var key of Object.keys(filterParam)) {
                let slValues = filterParam[key].toString().split(',');
                let slHtml = '';
                $.each(slValues, function(itemkey, val) {
                    if ((key == 'price' ) && val != '') {
                        val = val.split('..');
                        val = CURRENCY+val[0]+'-'+CURRENCY+val[1];
                    }
                    if (val != '') {
                        slHtml += `<div class="clear_filter_main">
                            <div id="clear-filter">${val}<button id="${key+'-'+val}" class="remove_button">x</button></div>
                        </div>`;
                    }
                })
                let label = key.toUpperCase();
                if (slHtml != '') {
                    selectedHtml += `
                        <div class="sl_main">
                            <div class="sl_label">${label+" :"}</div>
                            <div class="sl_value">${slHtml}</div>
                        </div>
                    `;
                }
            }
            $('#filter-items').html(selectedHtml);

            const clearButton = document.querySelector('#filter-items');
            if (clearButton) {
                clearButton['onclick'] = function(e) {
                    $('#product-pagination').twbsPagination('destroy');
                    let keyword = $('#search-result-box').val();
                    const typsenseClient = searchConfig.createClient(typesenseConfig);
                    let selectedId = e.target.id;
                    let selectArr = selectedId.split('-');
                    if (selectArr.length > 1) {
                        if (filterParam[selectArr[0]]) {
                            let currentValue = filterParam[selectArr[0]].toString().split(',');
                            var selectFiled = document.getElementById(selectArr[1]);
                            if (selectFiled) {
                                selectFiled.removeAttribute('checked');
                            } else {
                                currentValue.splice(0, 1);
                            }
                            const index = currentValue.indexOf(selectArr[1]);
                            if (index > -1) {
                                currentValue.splice(index, 1);
                            }
                            filterParam[selectArr[0]] = currentValue.toString();
    
                            const itemIndex = selectedIndex.indexOf(selectArr[1]);
                            if (itemIndex > -1) {
                                selectedIndex.splice(itemIndex, 1);
                            }
                            stableContent = $('#'+selectArr[0])[0].outerHTML;
                            selectedFilters.push({
                                key:selectArr[0],
                                content:stableContent
                            });
                        }
                    }
                    updateParam.updateParams(filterParam);
                    productSearch(keyword, 1, typsenseClient, filterParam);
                    sliderAction(keyword, filterParam, $('#priceRange').val()); 
                };
    
            }
        }

        /**
         * 
         * @param {*} searchData 
         */
        function renderFilterOptions(searchData) {
            let filterArray = searchData.facet_counts.filter((item) => {
                 if($.inArray(item.field_name, facetArr) !== -1) {
                    return item;
                 }
             });
            const typsenseClient = searchConfig.createClient(typesenseConfig);
            let keyword = $('#search-result-box').val();
             
            let html = '';
            let filterHtml = '';
            searchOptionFilter(filterArray)
            $.each(filterArray, function (key, item) {
              
                let condition = true;
                let itemOptionsCondition = false;
                let itemOptions = 2;
                filterHtml = renderFilterHtml(item,item.counts.length, false, item.field_name);
                let itemLabel = item.field_name.toUpperCase();
            
                $.each(facet, function (key, value) {
                    if (item.field_name == value.filterAttribute) {
                        itemLabel = value.fieldName;
                        itemOptions = value.filterOption;
                        itemFacetType =  value.facet;                    }
                });
                 if (item.counts.length <= 2) {
                    condition = false;
                }
                if (itemOptions == 1) {
                    itemOptionsCondition = true;
                }
              // itemFacetType = true;
                if (filterHtml) {
                    html += `<div class="filter_main_test" id="${item.field_name}">
                    <span class="item_label">${itemLabel}</span>
                    <div class="child_main" id="more_option_${item.field_name}">
                        ${itemOptionsCondition ? `
                            <div class="search_bar_option">
                                <input class="search_option_filter" data-attr="${item.field_name}" type="search" id="search_filter_${item.field_name}" placeholder="Search by option">
                            </div><br></br>` : ''}
                              ${itemFacetType ? `
                            <div class="search_bar_facet">
                                <input class="search_option_filter" data-attr="${itemFacetType}" type="search" id="search_facet_${itemFacetType}" placeholder="${itemFacetType}">
                            </div><br></br>` : ''}
                        
                        <div id="filtermore_attribute_${item.field_name}" class="filter_check">${filterHtml}</div>
                        <div class="read_more_less_buttons">
                        ${condition ? `<button data-info="${item.field_name}" data-count="${item.counts.length}" data-toggle-state="more" id="toggle_${item.field_name}" class="read_toggle_link">Read More</button>` : ''}
                        </div>
                    </div>
                </div>`;
                }
            });
            
            $('#filter_container').html(html);
            
            //setting data after refresh
            for (let key of Object.keys(filterParam)) {
                let paramValues = filterParam[key].split(',');
                paramValues.forEach(function(value) {
                    if (document.getElementById(value)) {
                        document.getElementById(value).setAttribute('checked', 'checked');
                    }
                });
            }
    

            $(document).on('click', '.read_toggle_link', function(e) {
                let $button = $(this);
                let itemId = $button.data('info');
                let itemCount = $button.data('count');
                let toggleState = $button.data('toggle-state');
                let filterArr = [];
                searchResultsArray[searchResultsArray.length-1].facet_counts.filter((item) => {
                    if (item.field_name === itemId) {
                        filterArr.push(item);
                    }
                });
                const singleObjectItemData = filterArr[0];
                var isReadMore = true;
                $('#toggle_'+itemId).text("Read Less");
                $('#toggle_' + itemId).attr('data-toggle-state', 'less');
                $('#toggle_' + itemId).removeClass('read_toggle_link');
                $('#toggle_' + itemId).addClass('read_less');
                var filterHtml = renderFilterHtml(singleObjectItemData, itemCount, isReadMore, itemId,itemFacetType);
                $('#filtermore_attribute_' + itemId).html(filterHtml);
                $('#toggle_'+itemId).css("display","block");
            });

            $(document).on('click', '.read_less', function(e) {
                let $button = $(this);
                let itemId = $button.data('info');
                let toggleState = $button.data('toggle-state');
                let filterArr = [];
                searchResultsArray[searchResultsArray.length-1].facet_counts.filter((item) => {
                    if (item.field_name === itemId) {
                        filterArr.push(item);
                    }
                });
                const singleObjectItemData = filterArr[0];
                var isReadMore = toggleState === "less";
                $button.text("Read More");
                $button.attr('data-toggle-state', 'more');
                $button.removeClass('read_less');
                $('#toggle_' + itemId).addClass('read_toggle_link');
                var filterHtml = renderFilterHtml(singleObjectItemData, 2, isReadMore, itemId);
                $('#filtermore_attribute_' + itemId).html(filterHtml);
            });

            //first filter reassigning....
            let selectedFiltersElem = selectedFilters[selectedFilters.length-1];
            if (selectedFiltersElem != undefined) {
                $('#'+selectedFiltersElem.key).html(selectedFiltersElem.content);
            }

            const resetbutton = document.querySelector('#clear_all');

            if (resetbutton) {
                resetbutton['onclick'] = function(e) {
                    filterParam = [];
                    selectedFilters = [];
                    selectedIndex = [];
                    sliderAction(keyword, filterParam);
                    $('#clear_all').hide();
                    productSearch(keyword, 1, typsenseClient, null);
                };
            }

            const filterContainers = document.querySelectorAll('.filter_check');
            if (filterContainers) {
                filterContainers.forEach(filterContainer => {
                    filterContainer.addEventListener('click', function(e) {
                        $('#product-pagination').twbsPagination('destroy');
                        if (e.target.type === 'checkbox'  ) {
                            var checkField = document.getElementById(e.target.id);
                            if (checkField) {
                                let attributeFieldname = checkField.getAttribute('data-typename');
                                if (checkField.checked) {
                                    checkField.setAttribute("checked", "checked");
                                    if (filterParam[attributeFieldname]) {
                                        filterParam[attributeFieldname] += ','+e.target.id;
                                    } else {
                                        filterParam[attributeFieldname] = e.target.id;
                                    }

                                    if($.inArray(attributeFieldname, selectedFilters) === -1) {
                                        stableContent = $('#'+attributeFieldname)[0].outerHTML;
                                        selectedFilters.push({
                                            key:attributeFieldname,
                                            content:stableContent
                                        });
                                    }

                                    if($.inArray(e.target.id, selectedIndex) === -1) {
                                        selectedIndex.push(e.target.id);
                                    }
                                    updateParam.updateParams(filterParam);
                                } else {
                                    checkField.removeAttribute('checked');
                                    const currentarray = filterParam[attributeFieldname].toString().split(',');
                                    currentarray.splice($.inArray(e.target.id, currentarray), 1);
                                    filterParam[attributeFieldname] = currentarray.toString();

                                    stableContent = $('#'+attributeFieldname)[0].outerHTML;
                                    const index = selectedIndex.indexOf(e.target.id);
                                    if (index > -1) {
                                        selectedIndex.splice(index, 1);
                                    }
                                    selectedFilters.push({
                                        key:attributeFieldname,
                                        content:stableContent
                                    });
                                    updateParam.updateParams(filterParam);
                                }
                                productSearch(keyword, 1, typsenseClient, filterParam);
                            }
                        }
                        else{
                            if(e.target.type === 'radio'){
                                   var checkField = document.getElementById(e.target.id);
                          if (checkField) {
                              let attributeFieldname = checkField.getAttribute('data-typename');
                              if (checkField.checked) {
                                  checkField.setAttribute("checked", "checked");
                                  if (filterParam[attributeFieldname]) {
                                      filterParam[attributeFieldname] = e.target.id;
                                  } else {
                                      filterParam[attributeFieldname] = e.target.id;
                                  }
                                 console.log(filterParam);
                                  if($.inArray(attributeFieldname, selectedFilters) === -1) {
                                      stableContent = $('#'+attributeFieldname)[0].outerHTML;
                                      selectedFilters.push({
                                          key:attributeFieldname,
                                          content:stableContent
                                      });
                                  }

                                  /*if($.inArray(e.target.id, selectedIndex) === -1) {
                                      selectedIndex.push(e.target.id);
                                  } */

                              } else {
                                  checkField.removeAttribute('checked');
                                  const currentarray = filterParam[attributeFieldname].toString().split(',');
                                  currentarray.splice($.inArray(e.target.id, currentarray), 1);
                                  filterParam[attributeFieldname] = currentarray.toString();

                                  stableContent = $('#'+attributeFieldname)[0].outerHTML;
                                  const index = selectedIndex.indexOf(e.target.id);
                                  if (index > -1) {
                                      selectedIndex.splice(index, 1);
                                  }
                                  selectedFilters.push({
                                      key:attributeFieldname,
                                      content:stableContent
                                  });
                              }
                              productSearch(keyword, 1, typsenseClient, filterParam);
                          }
                          }
                          }
                    });
                });
            }
        }

        /**
         * Creating filer Html
         * 
         * @param {*} item 
         * @returns 
         */
        function renderFilterHtml(item, maxItems = 2, isReadMore = true, fieldName) {
              $.each(facet, function (key, value) {
                    if (fieldName == value.filterAttribute) {
                        itemFacetType =  value.facet;                    
                    }
                 });

            let html = '';
            let counts = item.counts;
            $.each(counts, function (itemkey, itemValue) {
                console.log(itemFacetType);
                if (itemValue.value && itemFacetType =='disjunctive' ) {
                    html += `
                        <div class="form-check col-md-12 filter_${item.field_name}">
                        <input type="checkbox" class="form-check-input rangeCheck" name="[${item.field_name}]" id="${itemValue.value}" ${$.inArray(itemValue.value, selectedIndex) != -1 ? 'checked' : 'null'}  data-range="${itemValue.value}" data-typename="${item.field_name}" readonly="true">
                        <label class="form-check-label" for="${itemValue.value}">${itemValue.value} (${itemValue.count})</label>
                        </div>
                    `;
                }
                else if (itemValue.value && itemFacetType =='conjunctive'){
                     html += `
                        <div class="form-check col-md-12 filter_${item.field_name}">
                        <input type="radio" class="form-check-input radioCheck" name="[${item.field_name}]" id="${itemValue.value}" data-range="${itemValue.value}" data-typename="${item.field_name}" readonly="true">
                        <label class="form-check-label" for="${itemValue.value}">${itemValue.value} (${itemValue.count})</label>
                        </div>
                    `;

                }
            });
            return html;
        }

        /**
         * Search Option filer Html
         *
         * @param {*} item
         * @param {*} filterKeyword
         * @returns
         */
        function searchOpitonHtml(item, filterKeyword, filterId) {
            let html = '';
            let values = [];
            let filteredArray = [];
            $.each(item.counts, function (itemkey, itemValue) {
                if (itemValue.value) {
                    values.push(itemValue.value);
                }
            });
            if (values) {
                filteredArray  = filterSearch(filterKeyword,values);
            }
            if ($.isEmptyObject(filteredArray)) {
                html += `
                <div class="form-check col-md-12 searchOption_${item.field_name}">
                    No Data Found
                </div>
                `;
                return html;
            }
            $.each(item.counts, function (itemkey, itemValue) {
                if (itemValue.value) {
                    if (itemValue.value && $.inArray(itemValue.value, filteredArray) > -1 && item.field_name == filterId ) {
                        html += `
                        <div class="form-check col-md-12 searchOption_${item.field_name}">
                            <input type="checkbox" class="form-check-input rangeCheck" name="[${item.field_name}]" id="${itemValue.value}" ${$.inArray(itemValue.value, selectedIndex) != -1? 'checked' : 'null'} data-range="${itemValue.value}" data-typename="${item.field_name}" readonly="true">
                            <label class="form-check-label" for="range1">${itemValue.value} (${itemValue.count})</label>
                        </div>
                        `;
                    }
                }
            });
            return html;
        }

        /**
         *
         * @param {*} filterArray
         */
        function searchOptionFilter(filterArray)
        {
            $(document).on('keyup', '.search_option_filter', function(e) {
                let filterKeyword =e.target.value;
                let filterId = $(this).attr("data-attr");
                $('#toggle_'+filterId).hide();
                let filterItem = '';
                $('.filter_'+filterId).hide();
                $.each(filterArray, function (key, item) {
                    if (item.field_name === filterId) {
                        filterItem =  item;
                    }
                });
                const searchOptionsContainer = document.getElementById('filtermore_attribute_'+filterId);
                const generatedHTML =  searchOpitonHtml(filterItem, filterKeyword, filterId);
                searchOptionsContainer.innerHTML = generatedHTML;
            });
        }

        /**
         * Search Option filer Html
         *
         * @param {*} item
         * @param {*} filterKeyword
         * @returns
         */
        function searchOpitonHtml(item, filterKeyword, filterId) {
            let html = '';
            let values = [];
            let filteredArray = [];
            $.each(item.counts, function (itemkey, itemValue) {
                if (itemValue.value) {
                    values.push(itemValue.value);
                }
            });
            if (values) {
                filteredArray  = filterSearch(filterKeyword,values);
            }
            if ($.isEmptyObject(filteredArray)) {
                html += `
                <div class="form-check col-md-12 searchOption_${item.field_name}">
                    No Data Found
                </div>
                `;
                return html;
            }
            
            let expandItems = true;
            let itemData = item.counts;
            if (!filterKeyword) {
                expandItems = true;
                itemData = item.counts;
            }
            $.each(itemData, function (itemkey, itemValue) {
                if (itemValue.value) {
                    if (itemValue.value && $.inArray(itemValue.value, filteredArray) > -1 && item.field_name == filterId ) {
                        html += `
                        <div class="form-check col-md-12 searchOption_${item.field_name}">
                            <input type="checkbox" class="form-check-input rangeCheck" name="[${item.field_name}]" id="${itemValue.value}" ${$.inArray(itemValue.value, selectedIndex) != -1? 'checked' : 'null'} data-range="${itemValue.value}" data-typename="${item.field_name}" readonly="true">
                            <label class="form-check-label" for="range1">${itemValue.value} (${itemValue.count})</label>
                        </div>
                        `;
                    }
                }
            });
            if (!expandItems) {
                html = html+`<div class="read_more_less_buttons">
                    <button data-info="${item.field_name}" data-count="${item.counts.length}" data-toggle-state="more" id="toggle_${item.field_name}" class="read_toggle_link">Read More</button>
                </div>`
            }
            return html;
        }

        /**
         * Filter Search
         *
         * @param {*} keyword
         * @param {*} array
         * @returns
         */
        function filterSearch(keyword, array) {
            const escapedKeyword = keyword.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
            const regex = new RegExp(escapedKeyword, 'i');
            const filteredArray = $.grep(array, function(item) {
            return regex.test(item);
            });
            return filteredArray;
        }

        function priceSlider(filterArray) {
            const priceArr = filterArray.filter((item) => {
                if (item.field_name == 'price') {
                    return item.counts;
                }
            });
            const priceArrItem = priceArr.filter((val) => {
                return val.stats;
            });
    
            if (priceArrItem[0].stats != undefined) {
                if (!priceSlide) {
                    minValue = priceArrItem[0].stats.min;
                    maxValue = priceArrItem[0].stats.max;
                }
            }

            tmin = minValue;
            
            if (!isSlide) {
                $("#priceRange").val(minValue + " - " + maxValue);
            }
        }

        function sliderAction(keyword, filterParamData = null, currentValue = null) {
            if (location.search) {
                if (!keyword) {
                    keyword = location.search.split('=')[1];
                }
                if (keyword.match(/&&/g)) {
                    keyword = keyword.substring(0, keyword.indexOf('&&'));
                }

                let priceData = priceComponent.sliderPrice(keyword, '', filterParamData);
                priceData.then((value) => {
                    if (currentValue) {
                        currentValue = currentValue.split('-');
                        minValue = $.trim(currentValue[0]);
                        maxValue = $.trim(currentValue[1]);
                    } else {
                        minValue = value.min;
                        maxValue = value.max;
                    }

                    if (window.location.search) {
                        $.each(window.location.search.split('&&'), function (key, val) {
                           if (val.indexOf('price') > -1) {
                                val = val.substring(val.indexOf(":=") + 2);
                                val = val.split('..');
                                minValue = val[0];
                                maxValue = val[1];
                                $("#priceRange").val(minValue + " - " + maxValue);
                            }
                        });
                    }

                    $("#price-range").slider({
                        step: 1,
                        range: true, 
                        min: parseInt(minValue), 
                        max: parseInt(maxValue), 
                        values: [parseInt(minValue), parseInt(maxValue)], 
                        slide: function(event, ui)
                        {
                            isSlide = true;
                            tmin = ui.values[0];
                            tmax = ui.values[1];
                            let priceParam = ui.values[0]+".."+ui.values[1];
                            filterParam['price'] = priceParam;
                            updateParam.updateParams(filterParam);
                            $("#priceRange").val(ui.values[0] + " - " + ui.values[1]);
                            if (queryParam) {
                                productSearch($('#search-result-box').val(), 1,searchConfig.createClient(typesenseConfig), '','',ui.values[0]+'-'+ui.values[1]);
                            }
                        }
                    });
                });
            }
        }
    }
);
