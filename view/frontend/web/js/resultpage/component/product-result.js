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
	],
	function($, ui, twbsPagination, searchConfig, addTOCart, urlFormatter, priceUtils, addToWishList, addToCompare, priceComponent, updateParam) {
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
		const urlParams = new URLSearchParams(window.location.search);
		let refine = 'Refine';
		let analyticsURL = 'https://devbackend.conversionbox.io/';
		const queryParam = urlParams.get('q');
		const UNIQUEID = typesenseConfig.general.unique_id;
		let pageParam = 1;
		if (urlParams.get('page')) {
			pageParam = urlParams.get('page');
		}

		let selectedFilters = [];
		let selectedRadio = [];
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
		const typsenseClient = searchConfig.createClient(typesenseConfig);
		$.each(facet, function(key, val) {
			facetArr.push(val.filterAttribute);
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
			/* Mobile filter toggle */
			$('body').on('click', '#refine-toggle', function() {
				$('.filter_main').toggleClass('hidden-sm').toggleClass('hidden-xs');
				if ($(this).html().trim()[0] === '+')
					$(this).html('- ' + refine);
				else
					$(this).html('+ ' + refine);
			});
			/* Mobile slider issue */
			$(document).on('touchstart', '#price-range .ui-slider-handle', function(e) {
				let t = e.touches[0] || e;
				jQuery(this).addClass('ui-state-hover').addClass('ui-state-active').addClass('ui-state-focus')
				var newEvent = new MouseEvent('mousedown', {
					screenX: t.screenX,
					screenY: t.screenY,
					clientX: t.clientX,
					clientY: t.clientY,
					relatedTarget: t.target,
				})
				Object.defineProperty(newEvent, 'target', {
					value: t.target,
					enumerable: true
				});
				Object.defineProperty(newEvent, 'currentTarget', {
					value: t.target,
					enumerable: true
				});
				$(this).parent().slider("instance")._mouseDown(newEvent)
			});
			$(document).on('touchend', '#price-range .ui-slider-handle', function(e) {
				let t = e.touches[0] || e;
				jQuery(this).removeClass('ui-state-hover').removeClass('ui-state-active').removeClass('ui-state-focus')
				var newEvent = new MouseEvent('mouseup', {
					screenX: t.screenX,
					screenY: t.screenY,
					clientX: t.clientX,
					clientY: t.clientY,
					relatedTarget: t.target,
				})
				Object.defineProperty(newEvent, 'target', {
					value: t.target,
					enumerable: true
				});
				Object.defineProperty(newEvent, 'currentTarget', {
					value: t.target,
					enumerable: true
				});
				$(this).parent().slider("instance")._mouseUp(newEvent)
			});
			$(document).on('touchmove', '#price-range .ui-slider-handle', function(e) {
				let t = e.touches[0] || e;
				var newEvent = new MouseEvent('mousemove', {
					screenX: t.screenX,
					screenY: t.screenY,
					clientX: t.clientX,
					clientY: t.clientY,
					relatedTarget: t.target,
					'bubbles': true,
					'cancelable': true,
				});
				Object.defineProperty(newEvent, 'target', {
					value: t.target,
					enumerable: true
				});
				Object.defineProperty(newEvent, 'currentTarget', {
					value: t.target,
					enumerable: true
				});
				$(this).parent().slider("instance")._mouseMove(newEvent);
			});
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

		};

		/**
		 * Product Search
		 * 
		 * @param {*} keyword 
		 * @param {*} page 
		 * @param {*} typsenseClient 
		 */
		function productSearch(keyword, page, typsenseClient, filterValue, sortQuery, priceFilter, perPage = null) {
			priceSlide = priceFilter;
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
					'q': keyword,
					'query_by': searchAttributes,
					'per_page': NO_PRODUCTS_PAGE,
					'page': page,
					'facet_by': facetParam,
					'sort_by': ranking,
					'typo_tokens_threshold': TYPO_ENABLED,
					'num_typos': 2,
					'min_len_1typo': WORD_LENGTH,
					'min_len_2typo': WORD_LENGTH,
				}

				if (perPage || $('#product_count_page').val()) {
					perPage = ($('#product_count_page').val()) ? $('#product_count_page').val() : perPage;
					searchParameters.per_page = perPage;
				}

				let finalRequestParam = {};
				for (var key of Object.keys(filterParam)) {
					if (!$.isArray(filterParam[key])) {
						const valeArr = filterParam[key].split(',');
						finalRequestParam[key] = valeArr;
					}
				}

				if (selectedIndex.length >= 1 || selectedRadio.length >= 1) {
					$('#clear_all').show();
				} else {
					$('#clear_all').hide();
				}

				//make request query
				let requestQuery = '';
				$.each(finalRequestParam, function(key, val) {
					if (val != '') {
						requestQuery += key + ':=[' + val + '] &&';
					}
				});

				if (requestQuery) {
					searchParameters.query_by = searchAttributes;
					requestQuery = requestQuery.slice(0, -2);
					if (SLIDER == 1 && (tmin && tmax)) {
						requestQuery = requestQuery;
					}
				}
				searchParameters.filter_by = requestQuery;

				if (priceFilter && SLIDER == 1) {
					priceFilter = priceFilter.split('-');
					let multiRequestQuery = '';
					if (SLIDER != 1) {
						multiRequestQuery = 'price:=[' + priceFilter[0] + '..' + priceFilter[1] + '] &&' + requestQuery;
						multiRequestQuery = multiRequestQuery.slice(0, -2);
						searchParameters.filter_by = multiRequestQuery;
					}
				}

				let currentSort = $('#product_sort').val();
				if (!sortQuery && currentSort) {
					currentSort = currentSort.split('-');
					sortQuery = currentSort[0] + ":" + currentSort[1];
				}

				if (sortQuery) {
					searchParameters.sort_by = sortQuery;
				}
				typsenseClient.collections(INDEX_PERFIX + STORE + '-products').documents().search(searchParameters).then((searchResults) => {
						//   sliderAction(keyword,searchParameters,searchResults.facet_counts[0].stats);

						searchResultsArray.push(searchResults);
						let html = '';
						if (searchResults.hits.length < 1) {
							$('.filter_main').show();
							let htmlhead = '<div class="popular_search_head"> No product Found </div>';
								html += `Try clearing the filters or changing your input`
							html = htmlhead + html;
						} else {
							$('.filter_main').show();
						}

						perPage = $('#product_count_page').val() ? $('#product_count_page').val() : perPage;
						if (searchResults.found > NO_PRODUCTS_PAGE) {
							$('#product-pagination').show();
							totalPage = searchResults.found / NO_PRODUCTS_PAGE;
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
							loadedProductCount = $('#product_count_page').val() * page;
							if (loadedProductCount > searchResults.found) {
								loadedProductCount = searchResults.found;
							}
						}
						productCount = searchResults.found;
						let paginationHtml = `<div>
                        <div>Found ${searchResults.found} Results in ${searchResults.search_time_ms} ms</div>
                    </div>`;
						$('#product_count').html(paginationHtml);

						//implementing sort options
						let sOptions = '';
						if (Object.keys(sortOptions).length > 1) {
							$.each(sortOptions, function(key, item) {
								sOptions += `  
                                <option value="${item.sortAttribute+'-'+item.sortDirection}">${item.fieldName}</option>
                            `;
							});
							let sortDropDown = `<span class="sort_by">Sort By</span><select id="product_sort" name="product_sort">
                            <option value="">Select Option </option>
                                ${sOptions}
                            </select>`;
							$('#sort_option').html(sortDropDown);
						}

						//setting selected sort option while refecting data
						if (selectedSort) {
							$('select[name^="product_sort"] option[value=' + selectedSort + ']').attr("selected", "selected");
						}

						const sortSelect = document.querySelector('#product_sort');
						if (sortSelect) {
							sortSelect['onchange'] = function(e) {
								let dropValue = $('#product_sort').val();
								if (dropValue) {
									selectedSort = dropValue;
									dropValue = dropValue.split('-');
									let sortQuery = dropValue[0] + ':' + dropValue[1];
									$('#product-pagination').twbsPagination('destroy');
									paginationAction(totalPage, visiblePage, keyword, productCount);
									productSearch(keyword, page, typsenseClient, null, sortQuery);
								}
							}
						}

						//product display count
						let productCountOption = '';
						$.each(GRID_PRODUCT_COUNT.split(','), function(key, val) {
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
							$('select[name^="product_count_page"] option[value=' + selectedDisplayvalue + ']').attr("selected", "selected");
						}

						const displayCountSelect = document.querySelector('#product_count_page');
						if (displayCountSelect) {
							displayCountSelect['onchange'] = function(e) {
								let displayValue = $('#product_count_page').val();
								selectedDisplayvalue = displayValue;
								totalPage = searchResults.found / displayValue;
								totalPage = Math.ceil(totalPage);
								if (totalPage < 5) {
									visiblePage = totalPage - 1;
								} else {
									visiblePage = 5;
								}

								if (displayValue) {
									$('#product-pagination').twbsPagination('destroy');
									paginationAction(totalPage, visiblePage, keyword, productCount);
									productSearch(keyword, page, typsenseClient, null, sortQuery, null, displayValue);
								}
							}
						}

						paginationAction(totalPage, visiblePage, keyword, productCount);

						$.each(searchResults.hits, function(key, val) {
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
									price = val.highlight.price.snippet;
								} else if (highlight == 'sku') {
									var sku = val.highlight.sku.snippet;
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

						});
						$('#product_result').html(html);
						renderFilterOptions(searchResults);
						showSelectedFilter(filterParam)
						if (searchParameters.filter_by == "") {
							sliderAction(location.search.split('=')[1], filterParam);
						} else {
							sliderAction(keyword, filterParam, searchResults.facet_counts[0].stats);
						}

						hitSearchAnalytics(searchParameters, searchResults)
						const cartBtn = document.querySelector('#product_result');
						if (cartBtn) {
							cartBtn.addEventListener('click', function(e) {
								const target = e.target;
								if (target.classList.contains('cart_btn')) {
									const productId = target.id;
									addTOCart.toCart(productId);
									e.stopImmediatePropagation();
								} else if (target.classList.contains('towishlist')) {
									e.preventDefault();
									const productId = target.id;
									addToWishList.toWishlist(productId);
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

			if (totalPage && visiblePage) {
				$('#product-pagination').twbsPagination({
					totalPages: totalPage,
					visiblePages: visiblePage,
					startPage: parseInt(pageParam),
					first: false,
					last: false,
					prev: '<<',
					next: '>>',
					onPageClick: function(event, page) {
						updateParam.updateParams(filterParam, null, page);
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
					if ((key == 'price' && SLIDER == 1) && val != '') {
						val = val.split('..');
						val = CURRENCY + val[0] + '-' + CURRENCY + val[1];
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
							//const itemIndex = selectedRadio.indexOf(selectArr[1]);
							const itemIndex = selectedIndex.indexOf(selectArr[1]);
							if (itemIndex > -1) {
								selectedIndex.splice(itemIndex, 1);
							}
							const itemRadioindex = selectedRadio.indexOf(selectArr[1]);
							if (itemRadioindex > -1) {
								selectedRadio.splice(itemIndex, 1);
							}
							if (selectArr[0] != 'price') {
								stableContent = $('#' + selectArr[0])[0].outerHTML;
							}
							if (filterParam == '') {
								selectedRadio = [];
								$('#clear_all').hide();
							}
							selectedFilters.push({
								key: selectArr[0],
								content: stableContent
							});
						}
					}
					updateParam.updateParams(filterParam);
					productSearch(keyword, 1, typsenseClient, filterParam, '');

				};

			}
		}

		/**
		 * 
		 * @param {*} searchData 
		 */
		function renderFilterOptions(searchData) {
			let filterArray = searchData.facet_counts.filter((item) => {
				if ($.inArray(item.field_name, facetArr) !== -1) {
					return item;
				}
			});
			if (SLIDER == 1) {
				$('#price').html('')
				priceSlider(filterArray);
			}
			const typsenseClient = searchConfig.createClient(typesenseConfig);
			let keyword = $('#search-result-box').val();

			let html = '';
			let filterHtml = '';
			searchOptionFilter(filterArray)
			$.each(filterArray, function(key, item) {
				let condition = true;
				let itemOptionsCondition = false;
				let itemOptions = 6;
				filterHtml = renderFilterHtml(item, 6, false, item.field_name);
				let itemLabel = item.field_name.toUpperCase();
				$.each(facet, function(key, value) {
					if (item.field_name == value.filterAttribute) {
						itemLabel = value.fieldName;
						itemOptions = value.filterOption;
					}
				});
				if (item.counts.length <= 6) {
					condition = false;
				}
				if (itemOptions == 1) {
					itemOptionsCondition = true;
				}
				if (filterHtml) {
					html += `<div class="filter_main_test" id="price"></div><div class="filter_main_test" id="${item.field_name}">
                    <span class="item_label">${itemLabel}</span>
                    <div class="child_main" id="more_option_${item.field_name}">
                        ${itemOptionsCondition ? `
                            <div class="search_bar_option">
                                <input class="search_option_filter" data-attr="${item.field_name}" type="search" id="search_filter_${item.field_name}" placeholder="Search by option">
                            </div>` : ''}
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

			// Read more Toggle
			$(document).on('click', '.read_toggle_link', function(e) {
				let $button = $(this);
				let itemId = $button.data('info');
				let itemCount = $button.data('count');
				let toggleState = $button.data('toggle-state');
				let filterArr = [];
				searchResultsArray[searchResultsArray.length - 1].facet_counts.filter((item) => {
					if (item.field_name === itemId) {
						filterArr.push(item);
					}
				});
				const singleObjectItemData = filterArr[0];
				var isReadMore = true;
				$('#toggle_' + itemId).text("Read Less");
				$('#toggle_' + itemId).attr('data-toggle-state', 'less');
				$('#toggle_' + itemId).removeClass('read_toggle_link');
				$('#toggle_' + itemId).addClass('read_less');
				var filterHtml = renderFilterHtml(singleObjectItemData, itemCount, isReadMore, itemId);
				$('#filtermore_attribute_' + itemId).html(filterHtml);
				$('#toggle_' + itemId).css("display", "block");
			});
			// Read less Toggle
			$(document).on('click', '.read_less', function(e) {
				let $button = $(this);
				let itemId = $button.data('info');
				let toggleState = $button.data('toggle-state');
				let filterArr = [];
				searchResultsArray[searchResultsArray.length - 1].facet_counts.filter((item) => {
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
				var filterHtml = renderFilterHtml(singleObjectItemData, 6, isReadMore, itemId);
				$('#filtermore_attribute_' + itemId).html(filterHtml);
			});
			const resetbutton = document.querySelector('#clear_all');

			if (resetbutton) {
				resetbutton['onclick'] = function(e) {
					filterParam = [];
					selectedFilters = [];
					selectedIndex = [];
					selectedRadio = [];
					$('#clear_all').hide();
					sliderAction(keyword, filterParam);
					productSearch(keyword, 1, typsenseClient, null);
				};
			}

			const filterContainers = document.querySelectorAll('.filter_check');
			if (filterContainers) {
				filterContainers.forEach(filterContainer => {
					filterContainer.addEventListener('click', function(e) {
						$('#product-pagination').twbsPagination('destroy');
						if (e.target.type === 'checkbox') {
							var checkField = document.getElementById(e.target.id);
							if (checkField) {
								let attributeFieldname = checkField.getAttribute('data-typename');
								if (checkField.checked) {
									checkField.setAttribute("checked", "checked");
									if (filterParam[attributeFieldname]) {
										filterParam[attributeFieldname] += ',' + e.target.id;
									} else {
										filterParam[attributeFieldname] = e.target.id;
									}

									if ($.inArray(attributeFieldname, selectedFilters) === -1) {
										stableContent = $('#' + attributeFieldname)[0].outerHTML;
										selectedFilters.push({
											key: attributeFieldname,
											content: stableContent
										});
									}

									if ($.inArray(e.target.id, selectedIndex) === -1) {
										selectedIndex.push(e.target.id);
									}
									updateParam.updateParams(filterParam);
								} else {
									checkField.removeAttribute('checked');
									const currentarray = filterParam[attributeFieldname].toString().split(',');
									currentarray.splice($.inArray(e.target.id, currentarray), 1);
									filterParam[attributeFieldname] = currentarray.toString();

									stableContent = $('#' + attributeFieldname)[0].outerHTML;
									const index = selectedIndex.indexOf(e.target.id);
									if (index > -1) {
										selectedIndex.splice(index, 1);
									}
									selectedFilters.push({
										key: attributeFieldname,
										content: stableContent
									});
									updateParam.updateParams(filterParam);
								}
								productSearch(keyword, 1, typsenseClient, filterParam);
							}
						} else {
							if (e.target.type === 'radio') {
								var checkField = document.getElementById(e.target.id);

								if (checkField) {
									let attributeFieldname = checkField.getAttribute('data-typename');

									// Get all radio buttons with the same data-typename attribute
									let allRadioButtons = document.querySelectorAll(`input[type='radio'][data-typename='${attributeFieldname}']`);

									// Uncheck all radio buttons in the same group
									allRadioButtons.forEach(radio => {
										radio.removeAttribute("checked");
									});

									if (checkField.checked) {
										// Handle the checked state
										checkField.setAttribute("checked", "checked");
										filterParam[attributeFieldname] = e.target.id; // Store the selected radio button ID
										stableContent = $('#' + attributeFieldname)[0].outerHTML;
										selectedFilters.push({
											key: attributeFieldname,
											content: stableContent
										});
										selectedRadio = [];
										// Update selectedIndex if necessary
										selectedRadio.push(e.target.id);
										if (selectedRadio >= 1) {
											$('#clear_all').show();
										}
									}

									updateParam.updateParams(filterParam);
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
		function renderFilterHtml(item, maxItems = 6, isReadMore = true, fieldName) {
			$.each(facet, function(key, value) {
				if (fieldName == value.filterAttribute) {
					itemFacetType = value.facet;
				}
			});
			let html = '';
			let counts = item ? item.counts.slice(0, maxItems) : item.counts.slice(0, 2);

			$.each(counts, function(itemkey, itemValue) {
				if (itemValue.value && itemFacetType == 'disjunctive') {
					html += `
                        <div class="form-check col-md-12 filter_${item.field_name}">
                        <input type="checkbox" class="form-check-input rangeCheck" name="[${item.field_name}]" id="${itemValue.value}" ${$.inArray(itemValue.value, selectedIndex) != -1 ? 'checked' : 'null'}  data-range="${itemValue.value}" data-typename="${item.field_name}" readonly="true">
                        <label class="form-check-label" for="${itemValue.value}">${itemValue.value} (${itemValue.count})</label>
                        </div>
                    `;
				} else if (itemValue.value && itemFacetType == 'conjunctive') {
					html += `
                        <div class="form-check col-md-12 filter_${item.field_name}">
                        <input type="radio" class="form-check-input radioCheck" name="[${item.field_name}]" id="${itemValue.value}" data-range="${itemValue.value}" data-typename="${item.field_name}" ${$.inArray(itemValue.value, selectedRadio) != -1 ? 'checked' : 'null'}  readonly="true">
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
			$.each(item.counts, function(itemkey, itemValue) {
				if (itemValue.value) {
					values.push(itemValue.value);
				}
			});
			if (values) {
				filteredArray = filterSearch(filterKeyword, values);
			}
			if ($.isEmptyObject(filteredArray)) {
				html += `
                <div class="form-check col-md-12 searchOption_${item.field_name}">
                    No Data Found
                </div>
                `;
				return html;
			}
			$.each(item.counts, function(itemkey, itemValue) {
				if (itemValue.value) {
					if (itemValue.value && $.inArray(itemValue.value, filteredArray) > -1 && item.field_name == filterId) {
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
		function searchOptionFilter(filterArray) {
			$(document).on('keyup', '.search_option_filter', function(e) {
				let filterKeyword = e.target.value;
				let filterId = $(this).attr("data-attr");
				$('#toggle_' + filterId).hide();
				let filterItem = '';
				$('.filter_' + filterId).hide();
				$.each(filterArray, function(key, item) {
					if (item.field_name === filterId) {
						filterItem = item;
					}
				});
				const searchOptionsContainer = document.getElementById('filtermore_attribute_' + filterId);
				const generatedHTML = searchOpitonHtml(filterItem, filterKeyword, filterId);
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
			$.each(item.counts, function(itemkey, itemValue) {
				if (itemValue.value) {
					values.push(itemValue.value);
				}
			});
			if (values) {
				filteredArray = filterSearch(filterKeyword, values);
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
			$.each(itemData, function(itemkey, itemValue) {
				if (itemValue.value) {
					if (itemValue.value && $.inArray(itemValue.value, filteredArray) > -1 && item.field_name == filterId) {
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
				html = html + `<div class="read_more_less_buttons">
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
					console.log(item);
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
			let i = 0;
			if (!isSlide) {
				$("#price-range>span:eq(0)").html("<span class='point'>$" + Math.floor(minValue) + "</span>");
				$("#price-range>span:eq(1)").html("<span class='point'>$" + Math.ceil(maxValue) + "</span>");

			}
			i++;
		}

		function hitSearchAnalytics(searchParameters, searchResults) {
			setTimeout(function() {
				try {
					const postData = {
						uniqueId: UNIQUEID,
						searchKey: searchParameters.q,
						searchResult: searchResults,
						sortValue: searchParameters.sort_by,
						facetValue: searchParameters.filter_by,
						page: searchParameters.page,
						sessionId: $.cookie("_conversion_box_track_id")
					};
					$.ajax({
						url: analyticsURL + `api/v1/analytics/instantSearchLog`,
						type: 'POST',
						contentType: 'application/json',
						dataType: 'json',
						data: JSON.stringify(postData),
						success: function(data) {
							if (!data.ok) {
								console.log('Network response was not ok');
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
		/** Slider Handling  */
		function sliderAction(keyword, filterParamData = null, currentValue = null) {
			if (SLIDER == 1 && location.search) {
				if (!keyword) {
					keyword = location.search.split('=')[1];
				}
				if (keyword.match(/&&/g)) {
					keyword = keyword.substring(0, keyword.indexOf('&&'));
				}

				let minValue, maxValue;
				let priceData = priceComponent.sliderPrice(keyword, filterParamData);
				priceData.then((value) => {
					if (currentValue) {
						minValue = currentValue.min;
						maxValue = currentValue.max;
					} else {
						minValue = value.min;
						maxValue = value.max;
					}
					// Reset slider values to default if no values are passed
					if (filterParamData.price != undefined) {
						let priceRange = filterParamData.price.split("..");
						if (priceRange != '') {
							minValue = parseInt(priceRange[0]);
							maxValue = parseInt(priceRange[1]);
						}
					}
					if (filterParamData.price == undefined || filterParamData.price == "") {
						min = parseInt(minValue);
						max = parseInt(maxValue);
					} else {
						min = parseInt(value.min);
						max = parseInt(value.max);
					}
					$("#price-range").slider({
						step: 1,
						range: true,
						min: min,
						max: max,
						values: [parseInt(minValue), parseInt(maxValue)],
						slide: function(event, ui) {
							ui.handle.innerHTML = '<span class="point">$' + ui.value + '</span>';
							isSlide = true;
							tmin = ui.values[0];
							tmax = ui.values[1];

						},
						stop: function(event, ui) {
							let priceParam = ui.values[0] + ".." + ui.values[1];
							filterParam['price'] = priceParam;
							//     updateParam.updateParams(filterParam);
							if (filterParam['price'] && isSlide == 1) {
								productSearch($('#search-result-box').val(), 1, searchConfig.createClient(typesenseConfig), '', '', ui.values[0] + '-' + ui.values[1]);
							}
						}
					});
					updateParam.updateParams(filterParam);
					let sliderHandles = $("#price-range").find(".ui-slider-handle");
					sliderHandles.eq(0).html("<span class='point'>$" + Math.floor(minValue) + "</span>");
					sliderHandles.eq(1).html("<span class='point'>$" + Math.ceil(maxValue) + "</span>");
				});

			}
		}
	}
);
