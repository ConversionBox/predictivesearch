define(
	[
		'jquery',
		'jquery/ui',
		'Thecommerceshop_Predictivesearch/js/config/typesenseSearchConfig',
	],
	function($, ui, searchConfig) {
		const SEARCHBLE_ATTRIBUTES = typesenseConfig.products.attributes;
		const INDEX_PERFIX = typesenseConfig.general.indexprefix;
		const STORE = typesenseConfig.general.storeCode;
		const CATEGORY = typesenseConfig.category.name;

		return {
			/**
			 * 
			 * @param {*} keyword 
			 * @param {*} typsenseClient 
			 */
			sliderPrice: async function(keyword, location = null, filterParam = null) {
				try {
					let searchAttributes = SEARCHBLE_ATTRIBUTES.map((item) => {
						if (item.search == 1) {
							return item.code;
						}
					});
					searchAttributes = searchAttributes.join(',');
					if (keyword) {
						keyword = keyword.replace(/%20/g, " ");
					}
					let searchparams = '';
					if (filterParam) {
						for (let key of Object.keys(filterParam)) {
							if (filterParam[key]) {
								searchparams += '&&' + key + ':=' + filterParam[key];
							}
						}
						searchparams = searchparams.slice(2);
					}

					//preparing search params
					let searchParameters = {
						'q': keyword,
						'query_by': searchAttributes,
						'facet_by': 'price',
						'filter_by': searchparams
					}
					if (location == 'listing') {
						if (searchparams) {
							searchParameters.filter_by = searchparams + `&& category_ids:["${CATEGORY}"]`;
						} else {
							searchParameters.filter_by = `category_ids:["${CATEGORY}"]`;
						}
					}

					const typsenseClient = searchConfig.createClient(typesenseConfig);
					return typsenseClient.collections(INDEX_PERFIX + STORE + '-products').documents().search(searchParameters).then((searchResults) => {
						return searchResults.facet_counts[0].stats;
					});
				} catch (error) {
					console.log(error)
				}
			},
		};
	}
);