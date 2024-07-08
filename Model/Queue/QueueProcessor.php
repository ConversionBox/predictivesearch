<?php

declare(strict_types=1);

namespace Thecommerceshop\Predictivesearch\Model\Queue;

use Exception;
use Thecommerceshop\Predictivesearch\Logger\Logger;
use Thecommerceshop\Predictivesearch\Model\ConfigData;
use Thecommerceshop\Predictivesearch\Model\General;
use Thecommerceshop\Predictivesearch\Model\Api\Data\TypesenseSearchInterfaceFactory;
use Thecommerceshop\Predictivesearch\Model\Api\TypesenseSearchRepositoryInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Thecommerceshop\Predictivesearch\Model\ResourceModel\TypesenseSearch\CollectionFactory;

class QueueProcessor
{
    /**
     * Index for product
     *
     * @var string
     */
    private $indexName = 'productIndex';

    /**
     * Product Index for Update
     *
     * @var string
     */
    private $productUpdateIndex = 'productUpdateIndex';

    /**
     * Categoery Index
     *
     * @var string
     */
    private $categoryIndex = 'categoryIndex';

    /**
     * Category Index for Update
     *
     * @var string
     */
    private $categoryUpdateIndex = 'categoryUpdateIndex';

    /**
     * Index for page
     *
     * @var string
     */
    private $pageIndex = 'pageIndex';

    /**
     * Update Index for Page
     *
     * @var string
     */
    private $pageUpdateIndex = 'pageUpdateIndex';

    /**
     * @var ConfigData
     */
    private $configData;

    /**
     * @var General
     */
    private $general;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var TypesenseSearchInterfaceFactory
     */
    private $typesenseSearchInterfaceFactory;

    /**
     * @var TypesenseSearchRepositoryInterface
     */
    private $typesenseSearchRepositoryInterface;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var TimezoneInterface
     */
    private $timezoneInterface;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * Queue Index Constructor
     *
     * @param ConfigData $configData
     * @param General $general
     * @param Logger $logger
     * @param TypesenseSearchInterfaceFactory $typesenseSearchInterfaceFactory
     * @param TypesenseSearchRepositoryInterface $typesenseSearchRepositoryInterface
     * @param Json $json
     * @param TimezoneInterface $timezoneInterface
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        ConfigData $configData,
        General $general,
        Logger $logger,
        TypesenseSearchInterfaceFactory $typesenseSearchInterfaceFactory,
        TypesenseSearchRepositoryInterface $typesenseSearchRepositoryInterface,
        Json $json,
        TimezoneInterface $timezoneInterface,
        CollectionFactory $collectionFactory
    ) {
        $this->configData = $configData;
        $this->general = $general;
        $this->logger = $logger;
        $this->typesenseSearchInterfaceFactory = $typesenseSearchInterfaceFactory;
        $this->typesenseSearchRepositoryInterface = $typesenseSearchRepositoryInterface;
        $this->json = $json;
        $this->timezoneInterface = $timezoneInterface;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Process Product Data
     *
     * @param array $productData
     * @param string $index
     * @return void
     */
    public function processProductQueue($productData, $index)
    {
        $batchSize = ($this->configData->getBatchSize())?$this->configData->getBatchSize() :10;
        $arrayChunk = array_chunk($productData, (int)$batchSize);
        $collectionFactory = $this->collectionFactory->create();
        $collectionFactory->addFieldToFilter('job_type', $this->indexName);

        foreach ($arrayChunk as $data) {
            $queueData = $this->typesenseSearchInterfaceFactory->create();
            $queueData->setJobType($this->indexName);
            $queueData->setJobIndex($index);
            $queueData->setJobData($this->json->serialize($data));
            $queueData->setJobStatus(0);
            $queueData->setErrors('');
            $queueData->setCreatedAt($this->timezoneInterface->date()->format('Y-m-d H:i:s'));
            $queueData->setUpdatedAt('');
            $this->typesenseSearchRepositoryInterface->save($queueData);
        }
    }

     /**
      * Process Product Update Data
      *
      * @param int $productId
      * @param string $storeCode
      * @param int $storeId
      * @return void
      */
    public function processProductUpdateQueue($productId, $storeCode, $storeId)
    {
        $data = [
            'productId' => $productId,
            'storeCode' => $storeCode,
            'storeId' => $storeId
        ];
        $indexName =  $storeCode.'-products';
        if ($this->configData->getIndexPrefix()) {
            $indexName = $this->configData->getIndexPrefix().$indexName;
        }
        $queueData = $this->typesenseSearchInterfaceFactory->create();
        $queueData->setJobType($this->productUpdateIndex);
        $queueData->setJobData($this->json->serialize($data));
        $queueData->setJobStatus(0);
        $queueData->setErrors('');
        $queueData->setCreatedAt($this->timezoneInterface->date()->format('Y-m-d H:i:s'));
        $queueData->setUpdatedAt('');
        $queueData->setJobIndex($indexName);
        $this->typesenseSearchRepositoryInterface->save($queueData);
    }

    /**
     * Process Product Data
     *
     * @param array $categoryData
     * @param string $indexName
     * @return void
     */
    public function processCataegoryQueue($categoryData, $indexName)
    {
        $batchSize = ($this->configData->getBatchSize())?$this->configData->getBatchSize():10;
        $arrayChunk = array_chunk($categoryData, (int)$batchSize);
        $collectionFactory = $this->collectionFactory->create();
        $collectionFactory->addFieldToFilter('job_type', $this->categoryIndex);

        foreach ($arrayChunk as $data) {
            $queueData = $this->typesenseSearchInterfaceFactory->create();
            $queueData->setJobType($this->categoryIndex);
            $queueData->setJobIndex($indexName);
            $queueData->setJobData($this->json->serialize($data));
            $queueData->setJobStatus(0);
            $queueData->setErrors('');
            $queueData->setCreatedAt($this->timezoneInterface->date()->format('Y-m-d H:i:s'));
            $queueData->setUpdatedAt('');
            $this->typesenseSearchRepositoryInterface->save($queueData);
        }
    }

    /**
     * Process Category Update Data
     *
     * @param int $categoryId
     * @param string $storeCode
     * @param int $storeId
     * @return void
     */
    public function processCategoryUpdateQueue($categoryId, $storeCode, $storeId)
    {
        $data = [
            'categoryId' => $categoryId,
            'storeCode' => $storeCode,
            'storeId' => $storeId
        ];

        $indexName =  $storeCode.'-categories';
        if ($this->configData->getIndexPrefix()) {
            $indexName = $this->configData->getIndexPrefix().$indexName;
        }
        
        $queueData = $this->typesenseSearchInterfaceFactory->create();
        $queueData->setJobType($this->categoryUpdateIndex);
        $queueData->setJobData($this->json->serialize($data));
        $queueData->setJobStatus(0);
        $queueData->setErrors('');
        $queueData->setCreatedAt($this->timezoneInterface->date()->format('Y-m-d H:i:s'));
        $queueData->setUpdatedAt('');
        $queueData->setJobIndex($indexName);
        $this->typesenseSearchRepositoryInterface->save($queueData);
    }

    /**
     * Process Page Data
     *
     * @param array $pageData
     * @param string $indexName
     * @return void
     */
    public function processPageQueue($pageData, $indexName)
    {
        $batchSize = $this->configData->getBatchSize();
        $arrayChunk = array_chunk($pageData, (int)$batchSize);
        $collectionFactory = $this->collectionFactory->create();
        $collectionFactory->addFieldToFilter('job_type', $this->pageIndex);

        foreach ($arrayChunk as $data) {
            $queueData = $this->typesenseSearchInterfaceFactory->create();
            $queueData->setJobType($this->pageIndex);
            $queueData->setJobData($this->json->serialize($data));
            $queueData->setJobStatus(0);
            $queueData->setErrors('');
            $queueData->setCreatedAt($this->timezoneInterface->date()->format('Y-m-d H:i:s'));
            $queueData->setJobIndex($indexName);
            $queueData->setUpdatedAt('');
            $this->typesenseSearchRepositoryInterface->save($queueData);
        }
    }

    /**
     * Process Page Update Data
     *
     * @param int $pageId
     * @param string $storeCode
     * @return void
     */
    public function processPageUpdateQueue($pageId, $storeCode)
    {
        $data = [
            'pageId' => $pageId,
            'storeCode' => $storeCode,
            'storeId' => null
        ];

        $indexName =  $storeCode.'-pages';
        if ($this->configData->getIndexPrefix()) {
            $indexName = $this->configData->getIndexPrefix().$indexName;
        }
        
        $queueData = $this->typesenseSearchInterfaceFactory->create();
        $queueData->setJobType($this->pageUpdateIndex);
        $queueData->setJobData($this->json->serialize($data));
        $queueData->setJobStatus(0);
        $queueData->setErrors('');
        $queueData->setCreatedAt($this->timezoneInterface->date()->format('Y-m-d H:i:s'));
        $queueData->setJobIndex($indexName);
        $queueData->setUpdatedAt('');
        $this->typesenseSearchRepositoryInterface->save($queueData);
    }
}
