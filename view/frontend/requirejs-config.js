var config = {
    map: {
        '*': {
            'typesenseSearchConfig': 'Conversionbox_Predictivesearch/js/config/typesenseSearchConfig',
            'multiSearch': 'Conversionbox_Predictivesearch/js/component/multi-search',
           'searchResult': 'Conversionbox_Predictivesearch/js/resultpage/result',
            'productResult': 'Conversionbox_Predictivesearch/js/resultpage/component/product-result',
        }
    },
	paths: {
		'typesense': 'Conversionbox_Predictivesearch/typesense/typesense.min',
	},
    config: {
        mixins: {
            'Magento_Ui/js/core/app': {
                'Conversionbox_Predictivesearch/js/resultpage/component/product-result': false
            }
        }
    },
    priority: [
        'Conversionbox_Predictivesearch/js/config/typesenseSearchConfig',
        'Conversionbox_Predictivesearch/js/resultpage/result',
        'Conversionbox_Predictivesearch/js/resultpage/component/product-result'
    ]
};
