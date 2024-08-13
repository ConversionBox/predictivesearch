<?php

declare(strict_types=1);

namespace Conversionbox\Predictivesearch\Model\DataProcessor;

use Exception;
use Conversionbox\Predictivesearch\Model\ConfigData;
use Conversionbox\Predictivesearch\Model\General;
use Conversionbox\Predictivesearch\Model\Api\TypeSenseApi;
use Conversionbox\Predictivesearch\Logger\Logger;
use Conversionbox\Predictivesearch\Model\Schema\PageSchema;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory;
use Magento\Cms\Helper\Page as PageHelper;
use Magento\Cms\Model\PageFactory;
use Conversionbox\Predictivesearch\Model\Queue\QueueProcessor;
use Conversionbox\Predictivesearch\Api\TypesenseSearchRepositoryInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class PageDataProcessor
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
     * @var PageSchema
     */
    private $pageSchema;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var PageHelper
     */
    private $pageHelper;

    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var QueueProcessor
     */
    private $queueProcessor;

    /**
     * @var TypesenseSearchRepositoryInterface
     */
    private $typesenseSearchRepositoryInterface;

    /**
     * @var TimezoneInterface
     */
    private $timezoneInterface;

    /**
     * page Index Constructor
     *
     * @param ConfigData $configData
     * @param General $general
     * @param TypeSenseApi $typeSenseApi
     * @param Logger $logger
     * @param PageSchema $pageSchema
     * @param CollectionFactory $collectionFactory
     * @param PageHelper $pageHelper
     * @param PageFactory $pageFactory
     * @param QueueProcessor $queueProcessor
     * @param TypesenseSearchRepositoryInterface $typesenseSearchRepositoryInterface
     * @param TimezoneInterface $timezoneInterface
     */
    public function __construct(
        ConfigData $configData,
        General $general,
        TypeSenseApi $typeSenseApi,
        Logger $logger,
        PageSchema $pageSchema,
        CollectionFactory $collectionFactory,
        PageHelper $pageHelper,
        PageFactory $pageFactory,
        QueueProcessor $queueProcessor,
        TypesenseSearchRepositoryInterface $typesenseSearchRepositoryInterface,
        TimezoneInterface $timezoneInterface
    ) {
        $this->configData = $configData;
        $this->general = $general;
        $this->typeSenseApi = $typeSenseApi;
        $this->logger = $logger;
        $this->pageSchema = $pageSchema;
        $this->collectionFactory = $collectionFactory;
        $this->pageHelper = $pageHelper;
        $this->pageFactory = $pageFactory;
        $this->queueProcessor = $queueProcessor;
        $this->typesenseSearchRepositoryInterface = $typesenseSearchRepositoryInterface;
        $this->timezoneInterface = $timezoneInterface;
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

        $this->syncPages($ids);
    }

    /**
     * Sync products
     *
     * @param null||array $ids
     */
    public function syncPages($ids)
    {
        $queueData = [];
        $availableStore = $this->general->getAllStore();
        foreach ($availableStore as $storeData) {
            try {
                $indexName = $storeData->getCode().'-pages';
                if ($this->configData->getIndexPrefix()) {
                    $indexName = $this->configData->getIndexPrefix().$indexName;
                }
                $this->processpageData($indexName, $storeData, $ids);
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }

    /**
     * Process page Data
     *
     * @param string $indexName
     * @param object $storeData
     * @param array $ids
     */
    public function processpageData($indexName, $storeData, $ids)
    {
        //Get collections from typesense and check if the collections exist.
        $collectionData = $this->typeSenseApi->retriveCollectionData();
        if (!in_array($indexName, $collectionData)) {
            // Get Page schema structure
            $pageSchemaData = $this->pageSchema->getPageSchema($indexName);

            //Create schema with structure
            $this->typeSenseApi->createSchema($pageSchemaData);
            
            $stores = $this->general->getStore($storeData->getId());
            $cmsCollection = $this->collectionFactory->create();
            $cmsCollection->addStoreFilter($stores);

            foreach ($cmsCollection as $data) {
                $queueData[] = [
                    'pageId' => $data->getId(),
                    'storeCode' => $stores->getCode(),
                    'storeId' => $stores->getId()
                ];
            }

            if ($this->configData->isCronEnbaled()) {
                $this->queueProcessor->processPageQueue($queueData, $indexName);
            } else {
                $cmsCollectionArray = [];
                foreach ($queueData as $data) {
                    $cmsData = $this->createPageData($data['pageId'], $data['storeCode']);
                    if ($cmsData) {
                        $cmsData = $this->general->encodeData($cmsData);
                        $cmsData = trim($cmsData, '[]');
                        $cmsCollectionArray[] = $cmsData;
                    }
                }
                $cmsCollectionArray = implode(PHP_EOL, $cmsCollectionArray);
                //sync typesense pages here...
                $this->typeSenseApi->importCollectionData($indexName, $cmsCollectionArray);
                //error handling section need to be implemented here....
            }
        }

        if (!empty($ids)) {
            $pageObj = $this->pageFactory->create();
            foreach ($ids as $id) {
                $pageObj->load($id);
                if (!$pageObj->getId()) {
                 //removing deleted pages from typesense
                    $this->typeSenseApi->deleteDocument($indexName, $id);
                } else {
                    $storeIds= $pageObj->getResource()->lookupStoreIds($pageObj->getId());
                    if (in_array($storeData->getId(), $storeIds)) {
                        $updatedDocument = $this->createPageData($pageObj->getId(), $storeData->getCode());
                        //Update or create data to collection
                        $this->typeSenseApi->upsertDocument($indexName, $updatedDocument);
                    } else {
                        $this->typeSenseApi->deleteDocument($indexName, $id);
                    }
                }
            }
        }
    }

    /**
     * Get Page Collection
     *
     * @param int $pageId
     * @param string $storeCode
     * @return array
     */
    public function createPageData($pageId, $storeCode)
    {
        $page = $this->pageFactory->create();
        $page->load($pageId);
        return [
            'id' => $page->getId(),
            'page_id' => $page->getId(),
            'page_title' => $page->getTitle(),
            'url' => $this->pageHelper->getPageUrl($page->getId()),
            'identifier' => $page->getIdentifier(),
            'status' => (int) $page->getIsActive(),
            'created_at' => $page->getCreationTime(),
            'store' => $storeCode
        ];
    }

     /**
      * Sync Page by cron
      *
      * @param array $cmsDataArray
      * @param int $queueId
      * @return void
      */
    public function syncPageByCron($cmsDataArray, $queueId)
    {
        $response = null;
        try {
            $cmsCollection = [];
            $indexName = null;
            foreach ($cmsDataArray as $item) {
                $indexName =  $item['storeCode'].'-pages';
                if ($this->configData->getIndexPrefix()) {
                    $indexName = $this->configData->getIndexPrefix().$indexName;
                }
                $cmsDataItems = $this->createPageData($item['pageId'], $item['storeCode']);
                if ($cmsDataItems) {
                    $cmsData = $this->general->encodeData($cmsDataItems);
                    $cmsData = trim($cmsData, '[]');
                    $cmsCollection[] = $cmsData;
                }
    
                $collectionData = $this->typeSenseApi->retriveCollectionData();
                if (!in_array($indexName, $collectionData)) {
                    //Get product schema structure
                    $productSchemaData = $this->pageSchema->getPageSchema($indexName);
                    //Create schema with structure
                    $this->typeSenseApi->createSchema($productSchemaData);
                }
            }
            $cmsCollection = implode(PHP_EOL, $cmsCollection);
            //sync typesense products here...
            $response = $this->typeSenseApi->importCollectionData($indexName, $cmsCollection);
           
            if ($queueId) {
                $currentQueue = $this->typesenseSearchRepositoryInterface->getById($queueId);
                $currentQueue->setJobStatus(1);
                $currentQueue->setErrors($this->general->encodeData($response));
                $currentQueue->setUpdatedAt($this->timezoneInterface->date()->format('Y-m-d H:i:s'));
                $this->typesenseSearchRepositoryInterface->save($currentQueue);
            }
            //log response
            $this->logger->error($response);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Update Page Data
     *
     * @param int $pageId
     * @param string $storeCode
     * @param int $queueId
     * @return void
     */
    public function updatePagesByCron($pageId, $storeCode, $queueId)
    {
        $response = null;
        $indexName =  $storeCode.'-pages';
        if ($this->configData->getIndexPrefix()) {
            $indexName = $this->configData->getIndexPrefix().$indexName;
        }

        $cmsObj = $this->pageFactory->create();
        $cmsObj->load($pageId);
        if ($cmsObj->getId()) {
            $updatedDocument = $this->createPageData($pageId, $storeCode);
            //Update or create data to collection
            $response = $this->typeSenseApi->upsertDocument($indexName, $updatedDocument);
        } else {
            $response = $this->typeSenseApi->deleteDocument($indexName, $pageId);
        }

        if ($queueId) {
            $currentQueue = $this->typesenseSearchRepositoryInterface->getById($queueId);
            $currentQueue->setJobStatus(1);
            $currentQueue->setErrors($this->general->encodeData($response));
            $currentQueue->setUpdatedAt($this->timezoneInterface->date()->format('Y-m-d H:i:s'));
            $this->typesenseSearchRepositoryInterface->save($currentQueue);
        }
    }
}
