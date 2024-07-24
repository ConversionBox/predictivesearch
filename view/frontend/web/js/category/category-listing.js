define([
    'jquery', 
    'jquery/ui',
    'uiComponent',
    'Conversionbox_Predictivesearch/js/config/typesenseSearchConfig',
    'Conversionbox_Predictivesearch/js/plugin/pagination/jquery.twbsPagination',
    'Conversionbox_Predictivesearch/js/component/add-to-cart',
    'mage/url',
    'ko',
    'Magento_Catalog/js/price-utils',
    'Conversionbox_Predictivesearch/js/component/add-to-wishlist',
    'Conversionbox_Predictivesearch/js/component/add-to-compare',
    'Conversionbox_Predictivesearch/js/resultpage/component/price',
    'Conversionbox_Predictivesearch/js/component/updateParam',
], function ($, ui, Component, SearchConfig, twbsPagination, addTOCart, urlFormatter, ko, priceUtils, addToWishList, addToCompare, priceComponent, updateParam) {
    'use strict';

    const INDEX_PERFIX = typesenseConfig.general.indexprefix;
    const CATEGORY     = typesenseConfig.category.name;
    const NO_PRODUCTS_PAGE = typesenseConfig.search_result.no_products;
    const sortOptions = typesenseConfig.search_result.sort_options;
    const ADD_To = typesenseConfig.search_result.addto_cart;
    const SEARCHBLE_ATTRIBUTES = typesenseConfig.products.attributes;
    const SHOW_SKU = typesenseConfig.products.display_sku;
    const SHOW_PRICE = typesenseConfig.products.display_price;
    const POPULAR_TERMS = typesenseConfig.search_terms.data;
    const RANKING = typesenseConfig.products.ranking;
    const TYPO_ENABLED = typesenseConfig.typotolerance.enable;
    const WORD_LENGTH = typesenseConfig.typotolerance.word_length;
    const HIGHLIGHTS = typesenseConfig.general.highlights;
    const SLIDER = typesenseConfig.search_result.price_slider;
    const CURRENCY = typesenseConfig.general.store_currency;
    const STORE = typesenseConfig.general.storeCode;
    const PLACEHOLDER = typesenseConfig.general.placeholder;
    const GRID_PRODUCT_COUNT = typesenseConfig.general.grid_per_value;
    const IMAGE_TYPE = typesenseConfig.search_result.image_type;
    const IMAGE_WIDTH = typesenseConfig.search_result.image_width;
    const IMAGE_HEIGHT = typesenseConfig.search_result.image_height;

    let page = 1;
    let keyword = '';
    let sortQuery = '';
    let stableContent = null;
    let selectedFilters = [];
    let selectedIndex = [];
    let filterParam = [];
    let selectedSort = null;
    let facetArr = [];
    let minValue = 0;
    let maxValue = 100;
    let searchResultsArray = [];
    let selectedDisplayvalue = null;
    let priceSlide = null;
    let currentPriceFilter = null;
    let requestParams = null;
    let loadParams = null;
    let pageParam = 1;
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('page')) {
        pageParam = urlParams.get('page');
    }

    let facet = typesenseConfig.search_result.search_filters;
    $.each(facet, function (key, val) {
        facetArr.push(val.filterAttribute);
    });

    let facetParam = facetArr.toString();
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
        sliderCategoryAction();
     });

    return Component.extend({
        initialize: function () {
            this._super();
            $("#category_searchbox").on("keyup", function(e) {
                $('#product_list_pagination').twbsPagination('destroy');
                keyword = e.target.value;
                categoryProductSearch(page, keyword, sortQuery, filterParam);
                if (pageParam) {
                    page = pageParam;
                }
                sliderCategoryAction();
            });
            categoryProductSearch(page, keyword, sortQuery, filterParam);
        }
    });

    function categoryProductSearch (page, keyword, sortQuery, filterParam, priceFilter, perPage = null) {
        priceSlide = priceFilter;
        if ($('#priceRange').val() && priceFilter != null) {
            currentPriceFilter = $('#priceRange').val().split('~');
            currentPriceFilter = currentPriceFilter[0]+'-'+currentPriceFilter[1];
            priceFilter = currentPriceFilter;
        }
        $( document ).ready(function() {
            $("#loader").remove();
        });
        
        try {
            const typsenseClient = SearchConfig.createClient(typesenseConfig);

            if (keyword == undefined) {
                keyword = '';
            }

            if (!page) {
                page = 1;
            }

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
            //preparing search params
            let searchParameters = {
                'q'         : keyword,
                'query_by'  : 'category_ids,'+searchAttributes,
                'per_page'  : NO_PRODUCTS_PAGE,
                'page'      : page,
                'filter_by' : `category_ids:["${CATEGORY}"]`,
                'facet_by'  : facetParam,
                'sort_by'   : ranking,
                'highlight_full_fields':"name",
                'typo_tokens_threshold' : TYPO_ENABLED,
                'num_typos' : 2,
                'min_len_1typo' : WORD_LENGTH,
                'min_len_2typo' : WORD_LENGTH,
            }

            if (perPage || $('#cat_product_count_page').val()) {
                perPage = ($('#cat_product_count_page').val())?$('#cat_product_count_page').val():perPage;
                searchParameters.per_page = perPage;
            }
            
            let currentSort = $('#cat_product_sort').val();
            if (!sortQuery && currentSort) {
                currentSort = currentSort.split('-');
                sortQuery = currentSort[0]+":"+currentSort[1];
            }
       
            if (sortQuery) {
                searchParameters.sort_by = sortQuery;
            }

            let finalRequestParam = {};
            if (filterParam != undefined) {
                for (var key of Object.keys(filterParam)) {
                    if (!$.isArray(filterParam[key])) {
                        const valeArr = filterParam[key].split(',');
                        finalRequestParam[key] = valeArr;
                    }
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
                } else {
                }
            });
        
            if (requestQuery) {
                requestQuery = requestQuery.slice(0, -2);
                requestQuery = requestQuery+`&& category_ids:["${CATEGORY}"]`;
                searchParameters.filter_by = requestQuery;
                requestParams = requestQuery;
            }

            if (priceFilter && SLIDER == 1) {
                priceFilter = priceFilter.split('-');
                if (!requestQuery) {
                    requestQuery = `category_ids:["${CATEGORY}"]`;
                }
                let multiRequestQuery = 'price:=['+priceFilter[0]+'..'+priceFilter[1]+'] &&'+requestQuery;
                multiRequestQuery = multiRequestQuery.slice(0, -2);
                searchParameters.filter_by = multiRequestQuery;
                requestParams = multiRequestQuery;
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

                let totalPage = 0;
                let visiblePage = 0;
                if (searchResults.found > NO_PRODUCTS_PAGE) {
                    $('#product_list_pagination').show();
                    totalPage = searchResults.found/NO_PRODUCTS_PAGE;
                    totalPage = Math.ceil(totalPage);
                    if (searchResults.found > NO_PRODUCTS_PAGE && totalPage == 1) {
                        totalPage = 2;
                    }
        
                    if (totalPage > 4) {
                        visiblePage = 3;
                    } else {
                        visiblePage = totalPage;
                    }
                } else {
                    $('#product_list_pagination').hide();
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

                if (perPage || $('#cat_product_count_page').val()) {
                    loadedProductCount = $('#cat_product_count_page').val()*page;
                    if (loadedProductCount > searchResults.found) {
                        loadedProductCount = searchResults.found;
                    }
                }
                 
                let paginationHtml = `<div>
                    <div>Found ${loadedProductCount} out of ${searchResults.found} Results in ${searchResults.search_time_ms} ms</div>
                </div>`;
                $('#product_count_listing').html(paginationHtml);

                 //implementing sort options
                 let sOptions = '';
                 if (Object.keys(sortOptions).length > 1) {
                     $.each(sortOptions, function (key, item) {
                         sOptions += `  
                             <option value="${item.sortAttribute+'-'+item.sortDirection}">${item.fieldName}</option>
                         `;
                     });
                     let sortDropDown = `<select id="cat_product_sort" name="cat_product_sort">
                         <option value="">Select Option </option>
                             ${sOptions}
                         </select>`;
                     $('#cat_sort_option').html(sortDropDown);
                 }

                 //setting selected sort option while refecting data
                 if (selectedSort) {
                    $('select[name^="cat_product_sort"] option[value='+selectedSort+']').attr("selected","selected");
                 }
 
                 const sortSelect = document.querySelector('#cat_product_sort');
                 if (sortSelect) {
                     sortSelect['onchange'] = function(e) {
                        let dropValue = $('#cat_product_sort').val();
                        if (dropValue) {
                            selectedSort = dropValue;
                            dropValue = dropValue.split('-');
                            sortQuery = dropValue[0]+":"+dropValue[1];
                            categoryProductSearch(page, keyword, sortQuery, filterParam);
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
                         <select id="cat_product_count_page" name="cat_product_count_page">
                            <option value="">Select Option </option>
                                ${productCountOption}
                        </select>
                    <span>Per Page</span>
                            `;
                 $('#cat_product_display_count').html(productPageCount);

                 if (selectedDisplayvalue) {
                     $('select[name^="cat_product_count_page"] option[value='+selectedDisplayvalue+']').attr("selected","selected");
                 }

                 const displayCountSelect = document.querySelector('#cat_product_count_page');
                 if (displayCountSelect) {
                     displayCountSelect['onchange'] = function(e) {
                        let displayValue = $('#cat_product_count_page').val();
                        selectedDisplayvalue = displayValue;
                        totalPage = searchResults.found/displayValue;
                        totalPage = Math.ceil(totalPage);
                        if (totalPage < 5) {
                            visiblePage = totalPage - 1;
                        } else {
                            visiblePage = 5;
                        }

                        if (displayValue) {
                            $('#product_list_pagination').twbsPagination('destroy');
                            paginationAction(totalPage, visiblePage, keyword, sortQuery, filterParam);
                            categoryProductSearch(page, keyword, sortQuery, filterParam, currentPriceFilter, displayValue);
                        }
                    }
                }

                paginationAction(totalPage, visiblePage, keyword, sortQuery, filterParam);
    
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
                            if (highlight == 'product_name') {
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
                            <div class="category-product-wrapper-main">
                                <a href="${val.document.url}" >
                                    <div class="category-product-wrapper">
                                        <div class="category-product-image-div">
                                            <img src="${image}" class="search-product-image" width="${IMAGE_WIDTH}" height="${IMAGE_HEIGHT}"/>
                                        </div>
                                        <div class="category-product_item_wrapper">
                                            <div class="item_name">${name}</div>
                                            ${SHOW_SKU == 1 ? `<div class="item_sku">SKU: ${sku}</div>`: ''}
                                            ${SHOW_PRICE == 1 ? `<div class="item_price">${CURRENCY+priceUtils.formatPrice(price)}</div>`: ''}
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
                $('#category_product_result').html(html);
                renderFilterOptions(searchResults);
                showSelectedFilter(filterParam);
                sliderCategoryAction($('#priceRange').val());

                const cartBtn = document.querySelector('#category_product_result');
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
            });
    
        } catch (error) {
            console.log(error)
        }
    }

    function paginationAction(totalPage, visiblePage, keyword, sortQuery, filterParam) {
        if (totalPage && visiblePage) {
            $('#product_list_pagination').twbsPagination({
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
                    }
                    categoryProductSearch(page, keyword, sortQuery, filterParam);
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
        if (filterParam == undefined) {
            $('#filter-items').html('');
        } else {
            for (var key of Object.keys(filterParam)) {
                let slValues = filterParam[key].toString().split(',');
                let slHtml = '';
                $.each(slValues, function(itemkey, val) {
                    if ((key == 'price' && SLIDER == 1) && val != '') {
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
                    let keyword = $('#search-result-box').val();
                    const typsenseClient = SearchConfig.createClient(typesenseConfig);
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
                    updateParam.updateParams(filterParam, 'listing');
                    categoryProductSearch(1, keyword, sortQuery, filterParam);
                    sliderCategoryAction($('#priceRange').val());
                };
    
            }
        }
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
         * 
         * @param {*} searchData 
         */
    function renderFilterOptions(searchData) {
        let filterArray = searchData.facet_counts.filter((item) => {
             if($.inArray(item.field_name, facetArr) !== -1) {
                return item;
             }
         });
        const typsenseClient = SearchConfig.createClient(typesenseConfig);
        let keyword = $('#search-result-box').val();
         
        let html = '';
        let filterHtml = '';
        searchOptionFilter(filterArray)
        $.each(filterArray, function (key, item) {
            let condition = true;
            let itemOptionsCondition = false;
            let itemOptions = 2;
            filterHtml = renderFilterHtml(item, 2, false, item.field_name);
            let itemLabel = item.field_name.toUpperCase();
            $.each(facet, function (key, value) {
                if (item.field_name == value.filterAttribute) {
                    itemLabel = value.fieldName;
                    itemOptions = value.filterOption;
                }
            });
            if (item.counts.length <= 2) {
                condition = false;
            }
            if (itemOptions == 1) {
                itemOptionsCondition = true;
            }
            if (filterHtml) {
                html += `<div class="filter_main_test" id="${item.field_name}">
                <span class="item_label">${itemLabel}</span>
                <div class="child_main" id="more_option_${item.field_name}">
                    ${itemOptionsCondition ? `
                        <div class="search_bar_option">
                            <input class="search_option_filter" data-attr="${item.field_name}" type="search" id="search_filter_${item.field_name}" placeholder="Search by option">
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
        if (SLIDER == 1) {
            $('#price').html('')
            priceSlider(filterArray);
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
            var filterHtml = renderFilterHtml(singleObjectItemData, itemCount, isReadMore, itemId);
            $('#filtermore_attribute_' + itemId).html(filterHtml);
            $('#toggle_'+itemId).css("display", "block");
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
                requestParams = null;
                sliderCategoryAction();
                $('#clear_all').hide();
                categoryProductSearch(1, keyword, sortQuery,  filterParam, null);

            };
        }
        const filterContainers = document.querySelectorAll('.filter_check');
        if (filterContainers) {
            filterContainers.forEach(filterContainer => {
                filterContainer.addEventListener('click', function(e) {
                    $('#product_list_pagination').twbsPagination('destroy');
                    if (e.target.type === 'checkbox') {
                        let checkField = document.getElementById(e.target.id);
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
                                updateParam.updateParams(filterParam, 'listing');
                            } else {
                                checkField.removeAttribute('checked');
                                if (filterParam[attributeFieldname]) {
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
                                updateParam.updateParams(filterParam, 'listing');
                            }
                            sliderCategoryAction();
                            categoryProductSearch(1, keyword, sortQuery, filterParam);
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
        let html = '';
        let counts = item ? item.counts.slice(0, maxItems) : item.counts.slice(0, 2);
        $.each(counts, function (itemkey, itemValue) {
            if (itemValue.value) {
                html += `
                    <div class="form-check col-md-12 filter_${item.field_name}">
                    <input type="checkbox" class="form-check-input rangeCheck" name="[${item.field_name}]" id="${itemValue.value}" ${$.inArray(itemValue.value, selectedIndex) != -1 ? 'checked' : 'null'}  data-range="${itemValue.value}" data-typename="${item.field_name}" readonly="true">
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
        let expandItems = true;
        let itemData = item.counts;
        if (!filterKeyword) {
            expandItems = false;
            itemData = item.counts.slice(0, 2);
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
        if (!isSlide) {
            $("#priceRange").val(minValue + " - " + maxValue);
        }
    }

    function sliderCategoryAction(currentValue = null) {
        if (SLIDER == 1) {
            let priceData = priceComponent.sliderPrice($('#category_searchbox').val(), 'listing', filterParam);
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
                
                if (value.min != undefined && value.max != undefined) {
                    $("#price-range").slider({
                        step: 1,
                        range: true, 
                        min: parseInt(minValue), 
                        max: parseInt(maxValue), 
                        values: [parseInt(minValue), parseInt(maxValue)], 
                        slide: function(event, ui)
                        {
                            isSlide = true;
                            $("#priceRange").val(ui.values[0] + " - " + ui.values[1]);
                            let priceParam = ui.values[0]+".."+ui.values[1];
                            filterParam['price'] = priceParam;
                            updateParam.updateParams(filterParam, 'listing');
                            categoryProductSearch(1,$('#category_searchbox').val(), '', filterParam,ui.values[0]+'-'+ui.values[1]);
                        }
                    });
                }
              });
            
        }
    }
});
