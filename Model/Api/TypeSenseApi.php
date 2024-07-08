<?php

declare(strict_types=1);

namespace Thecommerceshop\Predictivesearch\Model\Api;

use Exception;
use Typesense\Client;
use Thecommerceshop\Predictivesearch\Model\ConfigData;
use Thecommerceshop\Predictivesearch\Logger\Logger;
class TypeSenseApi
{
    /**
     * @var ConfigData
     */
    protected $configData;
     
     /**
     * @var Logger
     */
    private $logger;

    /**
     * Typesense Api
     *
     * @param ConfigData $configData
     * @param Logger $logger
     */
    public function __construct(
        ConfigData $configData,
        Logger $logger
    ) {
        $this->configData = $configData;
        $this->logger = $logger;
    }

    /**
     * Create Client
     *
     * @param void
     * @return object
     */
    public function createClient()
    {
        try {
            $client = [
                'api_key'      => $this->configData->getAdminApiKey(),
                'nodes'        => [
                [
                    'host'     => $this->configData->getHost(),
                    'port'     => $this->configData->getPort(),
                    'protocol' => $this->configData->getProtocol(),
                ],
                ],
                'connection_timeout_seconds' => 2,
            ];
            return new Client(
                $client
            );
        }  catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
    /**
     * Create Collection shema
     *
     * @param array $schema
     */
    public function createSchema($schema)
    {
        try {
            $client = $this->createClient();
            return $client->collections->create($schema);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
     /**
     * Retrive Collection Data
     *
     * @param void
     * @return array $collectionDataArr
     */
    public function retriveCollectionData()
    {
        try {
            $client = $this->createClient();
            $collectionData = $client->collections->retrieve();
    
            $collectionDataArr = [];
            foreach ($collectionData as $collectionItem) {
                $collectionDataArr[] = $collectionItem['name'];
            }
            return $collectionDataArr;
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Import collection Data
     *
     * @param string $indexName
     * @param array $data
     */
    public function importCollectionData($indexName, $data)
    {
        try {
            $client = $this->createClient();
            return $client->collections[$indexName]->documents->import($data);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Update collection Data
     *
     * @param string $indexName
     * @param int $itemId
     * @param array $document
     */
    public function updateCollection($indexName, $itemId, $document)
    {
        try {
            $client = $this->createClient();
            return $client->collections[$indexName]->documents[$itemId]->update($document);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Function will update the product data if exist or create new document
     *
     * @param string $indexName
     * @param array $document
     */
    public function upsertDocument($indexName, $document)
    {
        try {
            $client = $this->createClient();
            return $client->collections[$indexName]->documents->upsert($document);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Delete Document by Id
     *
     * @param string $indexName
     * @param int $id
     */
    public function deleteDocument($indexName, $id)
    {
        try {
            $client = $this->createClient();
            return $client->collections[$indexName]->documents[$id]->delete();
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Perform search
     *
     * @param array $searchParameters
     * @param string $indexName
     * @return object
     */
    public function performSearch($searchParameters, $indexName)
    {
        try {
            $client = $this->createClient();
            return $client->collections[$indexName]->documents->search($searchParameters);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Create MultiWay Synonyms
     *
     * @param string $indexName
     * @param string $id
     * @param array $synonyms
     * @return object
     */
    public function createMultiWaySynonyms($indexName, $id, $synonyms)
    {
        try {
            $client = $this->createClient();
            $synonym = [
                'synonyms' => $synonyms,
              ];
            $response =  $client->collections[$indexName]->synonyms->upsert($id, $synonym);
            return $response;
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Create OneWay Synonyms
     *
     * @param string $indexName
     * @param string $id
     * @param string $root
     * @param array $synonyms
     * @return object
     */
    public function createOneWaySynonyms($indexName, $id, $root, $synonyms)
    {
        try {
            $client = $this->createClient();
            $synonym = [
                'root' => $root,
                'synonyms' => $synonyms,
            ];
            $response =  $client->collections[$indexName]->synonyms->upsert($id, $synonym);
            return $response;
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Retreive Synonyms
     *
     * @param string $id
     * @param string $indexName
     * @return object
     */
    public function retreiveSynonyms($id, $indexName)
    {
        try {
            $client = $this->createClient();
            $response =  $client->collections[$indexName]->synonyms[$id]->retrieve();
            return $response;
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Retreive All Synonyms
     *
     * @param array $indexName
     * @return object
     */
    public function retreiveAllSynonyms($indexName)
    {
        try {
            $client = $this->createClient();
            $response = $client->collections[$indexName]->synonyms->retrieve();
            return $response;
           
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Delete Synonyms
     *
     * @param string $indexName
     * @param string $synonymsId
     * @return object
     */
    public function deleteSynonyms($indexName, $synonymsId)
    {
        try {
            $client = $this->createClient();
            $collectionData = $this->retriveCollectionData();
            if (!empty($collectionData) && in_array($indexName, $collectionData)) {
                $response = $client->collections[$indexName]->synonyms[$synonymsId]->delete();
                return $response;
            }
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Delete Collection
     *
     * @param string $indexName
     * @return void
     */
    public function deleteCollection($indexName)
    {
        $client = $this->createClient();
        try {
            $client->collections[$indexName]->delete();
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
