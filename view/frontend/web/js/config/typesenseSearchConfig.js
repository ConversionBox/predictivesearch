define(
    [
        'jquery',
        'Thecommerceshop_Predictivesearch/js/typesense/typesense',
    ], function ($, Typesense) {
        return {
            /**
             * 
             * @param {*} typesenseConfigData 
             * @returns 
             */
            createClient: function(typesenseConfigData) {
                try {
                    let host = atob(this.replaceSpcharacters(typesenseConfigData.general.node));
                   
                    let port = typesenseConfigData.general.port;
                    let protocol = typesenseConfigData.general.protocol;
                    let apiKey = atob(this.replaceSpcharacters(typesenseConfigData.general.searchApikey));
                    let client = {
                        'nodes': [{
                            'host': host,
                            'port': port,
                            'protocol': protocol
                        }],
                        'apiKey': apiKey,
                        'connectionTimeoutSeconds': 2
                    };

                   
console.log(client);
                    return new Typesense.Client(client);
                } catch (error) {
                    console.log(error)
                }
            },

            /**
             * 
             * @param {*} data 
             * @returns 
             */
            replaceSpcharacters: function (data) {
                if (data) {
                    var replacements = {
                        ',': '',
                        '~': '='
                    };
                    var stringWithMultipleReplacements = data.replace(/[,~]/g, function(match) {
                        return replacements[match];
                    });
                    return stringWithMultipleReplacements;
                }
            }
        };
    }
);
