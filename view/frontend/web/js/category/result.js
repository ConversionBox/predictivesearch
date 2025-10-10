define(
	[
		'jquery',
		'uiComponent',
		'Conversionbox_Predictivesearch/js/config/typesenseSearchConfig',
		'Conversionbox_Predictivesearch/js/category/category-listing',
		'mage/url',
		'ko'
	],
	function($, Component, searchConfig, productResult, url, ko) {
		'use strict';

		let page = 1;
		let keyword = null;
		let filterValue = null;
		let sortParam = null;
		//initialize the typsense client
		const typsenseClient = searchConfig.createClient(typesenseConfig);
		const SLIDER = typesenseConfig.category.price_slider;
		const urlParams = new URLSearchParams(window.location.search);
		const queryParam = urlParams.get('q');
		$('#search-result-box').val(queryParam);

		return Component.extend({
			initialize: function() {
                let queryParam='';
				var self = this;
				this._super();

				/** return if configuration value not set **/
				if (typeof typesenseConfig === 'undefined' || !typesenseConfig.general.enabled) {
					return;
				}

				$(document).ready(function() {
					if (queryParam) {
                                               if (productResult && typeof productResult.performSearch === 'function') {
						productResult.performSearch(queryParam, page, typsenseClient, filterValue); 
                                          }
					}
                      if (productResult && typeof productResult.performSearch === 'function') {
                    productResult.performSearch(queryParam, page, typsenseClient, filterValue);
                       }
					upadteUrl(keyword);
				});

				$("#search-result-box").on("keyup", function(e) {
					keyword = e.target.value;
					upadteUrl(keyword);
					productResult.performSearch(keyword, page, typsenseClient, filterValue);
					if(SLIDER == 1){
					productResult.sliderComponent(keyword);
					}
				});
			},
		});

		/**
		 * 
		 * @param {*} keyword 
		 */
		function upadteUrl(keyword) {
			var currentUrl = window.location.href+'?q=';
			// if (keyword !="") {
			// 	// If the URL already has query parameters, add "&q"
			// 	// Otherwise, add "?q" to start query parameters
			// 	var newUrl = currentUrl.includes("?") ? currentUrl + "&q=" : currentUrl + "?q=";
				
			// 	// Update the URL without reloading (optional)
			// 	window.history.replaceState(null, "", newUrl);
			
			// 	console.log("Updated URL:", newUrl); // Debugging
			// }
			// Parse query string parameters
			var urlParts = currentUrl.split('?');
			var baseUrl = urlParts[0];
			var queryString = urlParts[1];
			var queryParams = queryString.split('&');

			// Create an object to hold updated query parameters
			var updatedParams = {};

			// Replace or modify the value of a specific parameter
			queryParams.forEach(function(param) {
				var paramParts = param.split('=');
				var paramName = paramParts[0];
				var paramValue = paramParts[1];

				if (paramName === 'q') {
					// Replace the value of 'exampleParam'
					paramValue = keyword;
				}

				updatedParams[paramName] = paramValue;
			});

			var newQueryString = $.param(updatedParams);
			var newUrl = baseUrl + '?' + newQueryString;
			// Update the browser's URL
			window.history.pushState({
				path: newUrl
			}, '', newUrl);
		}
	});
