<?php
declare(strict_types=1);

namespace Conversionbox\Predictivesearch\Model\DataProcessor;

use Exception;
use Conversionbox\Predictivesearch\Model\ConfigData;
use Conversionbox\Predictivesearch\Model\General;
use Conversionbox\Predictivesearch\Model\Api\TypeSenseApi;
use Conversionbox\Predictivesearch\Logger\Logger;
use Conversionbox\Predictivesearch\Model\Types\TypesenseTypes;
use Magento\Framework\HTTP\Client\Curl;

class SuggestionDataProcessor
{
    /**
     * @var ConfigData
     */         
    private $configData;

    /**
     * @var General
     */
    private $general;

    /**
     * @var TypeSenseApi
     */
    private $typeSenseApi;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var Curl
     */
    private $curl;

    /**
     * page Index Constructor
     *
     * @param ConfigData $configData
     * @param General $general
     * @param TypeSenseApi $typeSenseApi
     * @param Logger $logger
     * @param Curl $curl
     */
    public function __construct(
        ConfigData $configData,
        General $general,
        TypeSenseApi $typeSenseApi,
        Logger $logger,
        Curl $curl
    ) {
        $this->configData = $configData;
        $this->general = $general;
        $this->typeSenseApi = $typeSenseApi;
        $this->logger = $logger;
        $this->curl = $curl;
    }

    /**
     * Perform indexing action to Typesense
     *
     * @param null||array $ids
     */
    public function importDataToTypeSense($ids)
    {
        if (!$this->configData->getModuleStatus()) {
            return;
        }

        if (!$this->configData->getAdminApiKey() ||
                !$this->configData->getNode() ||
                !$this->configData->getProtocol()
            ) {
            return;
        }

        $this->syncSuggestions($ids);
    }

    /**
     * Sync products
     *
     * @param null||array $ids
     */
    public function syncSuggestions($ids)
    {
        $queueData = [];
        $availableStore = $this->general->getAllStore();
        $collectionData = $this->typeSenseApi->retriveCollectionData();

        //create suggestion collection
        foreach ($availableStore as $storeData) {
            try {
                $indexName = $storeData->getCode().'-suggestions';
                $source = $storeData->getCode().'-products';
                $aggregation = $storeData->getCode().'-queries_aggregation';
                if ($this->configData->getIndexPrefix()) {
                    $indexName = $this->configData->getIndexPrefix().$indexName;
                    $source = $this->configData->getIndexPrefix().$source;
                    $aggregation = $this->configData->getIndexPrefix().$aggregation;
                }
                if (!in_array($indexName, $collectionData)) {
                    $indexParam = $this->generateCollectionParam($indexName);
                    $this->typeSenseApi->createSchema($indexParam);
                }
             
                if (array_search($source, $collectionData) !== false) {
                    //generate analytic rule based on collection
                    $this->generateAnalyticRules($indexName, $source, $aggregation);
                }
               
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }

    /**
     * Suggetions Collection Param
     *
     * @param string $indexName
     * @return string
     */
    public function generateCollectionParam($indexName)
    {
        return [
            'name' => $indexName,
            'fields' => [
                ['name' => 'q', 'type' => TypesenseTypes::STRING],
                ['name' => 'count', 'type' => TypesenseTypes::INTEGER]
            ]
        ];
    }

    /**
     * Generate Analytics Rule
     *
     * @param string $indexName
     * @param string $source
     * @param string $aggregation
     * @return null
     */
    public function generateAnalyticRules($indexName, $source, $aggregation)
    {
        try {
            $url = 'https://'.$this->configData->getNode().'/analytics/rules/'.$aggregation;
            $paramsData = [
                "type" => "popular_queries",
                "params" => [
                    "source" => [
                        "collections" => [$source]
                    ],
                    "destination" => [
                        "collection" => $indexName
                    ],
                    "limit" => 1000
                ],
            ];
            $paramsData = $this->general->encodeData($paramsData);
            $this->curl->put($url, $this->configData->getAdminApiKey(), $paramsData);

        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
