define(
	[
		'jquery',
		'uiComponent',
		'Conversionbox_Predictivesearch/js/config/typesenseSearchConfig',
		'Conversionbox_Predictivesearch/js/resultpage/component/product-result',
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

		const urlParams = new URLSearchParams(window.location.search);
		const queryParam = urlParams.get('q');
		$('#search-result-box').val(queryParam);

		return Component.extend({
			initialize: function() {
				var self = this;
				this._super();

				/** return if configuration value not set **/
				if (typeof typesenseConfig === 'undefined' || !typesenseConfig.general.enabled) {
					return;
				}

				$(document).ready(function() {
					if (queryParam) {
						productResult.performSearch(queryParam, page, typsenseClient, filterValue);
					}
				});

				$("#search-result-box").on("keyup", function(e) {
					keyword = e.target.value;
					upadteUrl(keyword);
					productResult.performSearch(keyword, page, typsenseClient, filterValue);
					productResult.sliderComponent(keyword);
				});
			},
		});

		/**
		 * 
		 * @param {*} keyword 
		 */
		function upadteUrl(keyword) {
			var currentUrl = window.location.href;

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
