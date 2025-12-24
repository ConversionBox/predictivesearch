define(
	[
		'jquery',
		'uiComponent',
		'Conversionbox_Predictivesearch/js/config/typesenseSearchConfig',
		'Conversionbox_Predictivesearch/js/landing/landing-listing',
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
		const initialQuery = typesenseConfig.general.query;
		const urlParams = new URLSearchParams(window.location.search);
		const queryParam = urlParams.get('q');
		$('#search-result-box').val(queryParam);

		return Component.extend({
			initialize: function() {
                let queryParam='';
				var self = this;
				this._super();
                 let filterby='';
				/** return if configuration value not set **/
				if (typeof typesenseConfig === 'undefined' || !typesenseConfig.general.enabled || !typesenseConfig.landingPage.isLandingpage) {
					return;
				}
                if(typesenseConfig.landingPage.configuration !== ''){
                    eval(typesenseConfig.landingPage.configuration);
                    if(window.landingMerchandising.query != ''){
                        queryParam = window.landingMerchandising.query;

                    }
                    if(window.landingMerchandising.filter != ''){
                        filterby = window.landingMerchandising.filter;

                    }
                    if(queryParam || filterby ){
                    productResult.performSearch(queryParam, page, typsenseClient, filterValue);
                    }
                   
                }
			},
		});

	
	});
