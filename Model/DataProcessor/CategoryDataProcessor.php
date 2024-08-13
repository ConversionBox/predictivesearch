<?php
declare(strict_types=1);

namespace Conversionbox\Predictivesearch\Model\DataProcessor;

use Exception;
use Conversionbox\Predictivesearch\Model\ConfigData;
use Conversionbox\Predictivesearch\Model\General;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Conversionbox\Predictivesearch\Model\Api\TypeSenseApi;
use Conversionbox\Predictivesearch\Logger\Logger;
use Conversionbox\Predictivesearch\Model\Schema\CategorySchema;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category\Attribute\CollectionFactory as FilterableAttributes;
use Magento\Catalog\Api\Data\CategoryAttributeInterface;
use Magento\Catalog\Api\CategoryAttributeRepositoryInterface;
use Conversionbox\Predictivesearch\Model\Queue\QueueProcessor;
use Conversionbox\Predictivesearch\Api\TypesenseSearchRepositoryInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class CategoryDataProcessor
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
     * @var CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var TypeSenseApi
     */
    private $typeSenseApi;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var CategorySchema
     */
    private $categorySchema;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepositoryInterface;

    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var FilterableAttributes
     */
    private $filterableAttributes;

    /**
     * @var CategoryAttributeRepositoryInterface
     */
    private $categoryAttributeInfo;

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
     * Constructor
     *
     * @param ConfigData $configData
     * @param General $general
     * @param CollectionFactory $collectionFactory
     * @param TypeSenseApi $typeSenseApi
     * @param Logger $logger
     * @param CategorySchema $categorySchema
     * @param CategoryRepositoryInterface $categoryRepositoryInterface
     * @param CategoryFactory $categoryFactory
     * @param FilterableAttributes $filterableAttributes
     * @param CategoryAttributeRepositoryInterface $categoryAttributeInfo
     * @param QueueProcessor $queueProcessor
     * @param TypesenseSearchRepositoryInterface $typesenseSearchRepositoryInterface
     * @param TimezoneInterface $timezoneInterface
     */
    public function __construct(
        ConfigData $configData,
        General $general,
        CollectionFactory $collectionFactory,
        TypeSenseApi $typeSenseApi,
        Logger $logger,
        CategorySchema $categorySchema,
        CategoryRepositoryInterface $categoryRepositoryInterface,
        CategoryFactory $categoryFactory,
        FilterableAttributes $filterableAttributes,
        CategoryAttributeRepositoryInterface $categoryAttributeInfo,
        QueueProcessor $queueProcessor,
        TypesenseSearchRepositoryInterface $typesenseSearchRepositoryInterface,
        TimezoneInterface $timezoneInterface
    ) {
        $this->configData = $configData;
        $this->general = $general;
        $this->collectionFactory = $collectionFactory;
        $this->typeSenseApi = $typeSenseApi;
        $this->logger = $logger;
        $this->categorySchema = $categorySchema;
        $this->categoryRepositoryInterface = $categoryRepositoryInterface;
        $this->categoryFactory = $categoryFactory;
        $this->filterableAttributes = $filterableAttributes;
        $this->categoryAttributeInfo = $categoryAttributeInfo;
        $this->queueProcessor = $queueProcessor;
        $this->typesenseSearchRepositoryInterface = $typesenseSearchRepositoryInterface;
        $this->timezoneInterface = $timezoneInterface;
    }

    /**
     * Perform indexing action to Typesense
     *
     * @param null||array $ids
     * @param int $storeId
     */
    public function importDataToTypeSense($ids, $storeId = null)
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

        $this->syncCategory($ids, $storeId);
    }

    /**
     * Sync products
     *
     * @param null||array $ids
     * @param int $storeId
     * @param string $mode
     */
    public function syncCategory($ids, $storeId, $mode = null)
    {
        try {
            if ($this->configData->isCronEnbaled() && !empty($ids)) {
                return;
            }
    
            if (!empty($ids)) {
                $storeCode = '';
                if ($storeId == 0) {
                    $storeId = 1;
                }
                $stores = $this->general->getStore($storeId);
                $storeCode = $stores->getCode();
                $indexName  =  $this->getStoreCode($storeCode);
                $categoryObj = $this->categoryFactory->create()->setStoreId($storeId);
                foreach ($ids as $id) {
                      $categoryObj->load($id);
                    if (!$categoryObj->getId()) {
                        //removing deleted category from typesense
                        $this->typeSenseApi->deleteDocument($indexName, $id);
                    } else {
                        if (in_array($storeId, $categoryObj->getStoreIds()) || $storeId == 0) {
                            $updatedDocument = $this->createCategoryData($categoryObj, $storeCode, $storeId);
                            //Update or create data to collection
                            $this->typeSenseApi->upsertDocument($indexName, $updatedDocument);
                        } if (!in_array($storeId, $categoryObj->getStoreIds())) {
                            $this->typeSenseApi->deleteDocument($indexName, $id);
                        }
                    }
                }
            }

            $availableStore = $this->general->getAllStore();
            foreach ($availableStore as $storeData) {
                try {
                    $storeCode = $storeData->getCode();
                    if ($storeId) {
                        $stores = $this->general->getStore($storeId);
                        $storeCode = $stores->getCode();
                    }
                    $indexName = $this->getStoreCode($storeCode);
                    ;
                    //Get collections from typesense and check if the collections exist.
                    $collectionData = $this->typeSenseApi->retriveCollectionData();
                    $stores = $this->general->getStore($storeData->getId());
                    if (!in_array($indexName, $collectionData) || $mode = 'cron') {
                       // Get category schema structure
                        $categorySchemaData = $this->categorySchema->getCategorySchema($indexName);
        
                        //Create schema with structure
                        $this->typeSenseApi->createSchema($categorySchemaData);
                    }
                    $categoryCollection = $this->collectionFactory->create()
                        ->addAttributeToSelect('*')
                        ->setStore($stores)
                        ->addAttributeToFilter('level', ['gt' => 1]);
                         
                    $catCollection = [];
                    foreach ($categoryCollection as $data) {
                        $categoryData = $this->createCategoryData($data, $stores->getCode(), $stores->getId());
                        if ($categoryData) {
                            $categoryData = $this->general->encodeData($categoryData);
                            $categoryData = trim($categoryData, '[]');
                            $catCollection[] = $categoryData;
                        }
                    }

                    if ($this->configData->isCronEnbaled() || $mode == 'cron') {
                        $this->queueProcessor->processCataegoryQueue($catCollection, $indexName);
                    } else {
                        $catCollection = implode(PHP_EOL, $catCollection);
                        //sync typesense categoreis here...
                        $response = $this->typeSenseApi->importCollectionData($indexName, $catCollection);
                        $this->logger->error($response);
                        //error handling section need to be implemented here....
                    }
                } catch (Exception $e) {
                    $this->logger->error($e->getMessage());
                }
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Get Index name
     *
     * @param string $storeCode
     * @return string
     */
    public function getStoreCode($storeCode)
    {
        $indexName =  $storeCode.'-categories';
        if ($this->configData->getIndexPrefix()) {
            $indexName = $this->configData->getIndexPrefix().$indexName;
        }
        return $indexName;
    }

    /**
     * Get Category Collection
     *
     * @param object $category
     * @param string $storeCode
     * @param int $storeId
     * @return array
     */
    public function createCategoryData($category, $storeCode, $storeId)
    {
         //ignoring setting admin storeId
        if ($storeId == 0) {
            $storeId = 1;
        }

        $category->setStoreId($storeId);
        $filterableData = $this->getFilterableAttributes($category);
        $categoryArray =   [
            'id' => $category->getId(),
            'category_id' => $category->getId(),
            'category_name' => $category->getName(),
            'path' => $this->getCatgorypath($category->getPath(), $storeId),
            'include_in_menu' => $category->getIncludeInMenu(),
            'level' => $category->getLevel(),
            'product_count' => $category->getProductCount(),
            'url' => $category->getUrl(),
            'status' => $category->getIsActive(),
            'created_at' => $category->getCreatedAt(),
            'store' => $storeCode,
        ];
        $finalAtrArray = array_merge($filterableData, $categoryArray);
        return $finalAtrArray;
    }

    /**
     * Get category Path
     *
     * @param string $pathIds
     * @param int $storeId
     * @return string
     */
    public function getCatgorypath($pathIds, $storeId)
    {
        $pathIds = explode('/', $pathIds);
        $path = '';
        foreach ($pathIds as $categoryId) {
            if ($path !== '') {
                $path .= ' / ';
            }

            $category = $this->categoryRepositoryInterface->get($categoryId, $storeId);
            if ($category->getLevel() > 1) {
                $path .= $category->getName();
            }
        }
        return $path;
    }

    /**
     * Get Category Filterable Attributes
     *
     * @param array $category
     * @return void
     */
    public function getFilterableAttributes($category)
    {
        $response = [];
        $categoryAttributes = $this->filterableAttributes->create();
        foreach ($categoryAttributes as $attributes) {
            $categoryData = $category->getData();
            $multiListArr = ['multiselect', 'dropdown', 'select'];
            if (!in_array($attributes->getAttributeCode(), $this->categorySchema->exculdedAttributes())) {
                if (isset($categoryData[$attributes->getAttributeCode()])) {
                    $attrValue = $categoryData[$attributes->getAttributeCode()];
                    if (in_array($attributes->getFrontendInput(), $multiListArr)) {
                        $response[$attributes->getAttributeCode()] = !empty($attrValue)  ? [$attrValue] : [];
                    } else {
                        $response[$attributes->getAttributeCode()] = !empty($attrValue)  ? $attrValue : '';
                    }
                } elseif (in_array($attributes->getFrontendInput(), $multiListArr)) {
                    $response[$attributes->getAttributeCode()] = [];
                } else {
                    $response[$attributes->getAttributeCode()] = '';
                }
            }
        }
        return $response;
    }

    /**
     * Sync Category by cron
     *
     * @param array $categoryDataArray
     * @param int $queueId
     * @param string $index
     * @return void
     */
    public function syncCategoryByCron($categoryDataArray, $queueId, $index)
    {
        try {
            $categoryCollection = implode(PHP_EOL, $categoryDataArray);
            $response = $this->typeSenseApi->importCollectionData($index, $categoryCollection);
            if ($queueId) {
                $currentQueue = $this->typesenseSearchRepositoryInterface->getById($queueId);
                $currentQueue->setJobStatus(1);
                $currentQueue->setErrors($this->general->encodeData($response));
                $currentQueue->setUpdatedAt($this->timezoneInterface->date()->format('Y-m-d H:i:s'));
                $this->typesenseSearchRepositoryInterface->save($currentQueue);
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Update Category Data
     *
     * @param array $categoryData
     * @param int $queueId
     * @param string $index
     * @return void
     */
    public function updateCategoryByCron($categoryData, $queueId, $index)
    {
        if (!empty($categoryData)) {
            try {
                $categoryObj = $this->categoryFactory->create();
                $categoryObj->load($categoryData['categoryId']);
                if ($categoryObj->getId()) {
                    $updatedDocument = $this->createCategoryData(
                        $categoryObj,
                        $categoryData['storeCode'],
                        $categoryData['storeId']
                    );
                    $response = $this->typeSenseApi->upsertDocument($index, $updatedDocument);
                } else {
                    $response = $this->typeSenseApi->deleteDocument($index, $productData['categoryId']);
                }
        
                if ($queueId) {
                    $currentQueue = $this->typesenseSearchRepositoryInterface->getById($queueId);
                    $currentQueue->setJobStatus(1);
                    $currentQueue->setErrors($this->general->encodeData($response));
                    $currentQueue->setUpdatedAt($this->timezoneInterface->date()->format('Y-m-d H:i:s'));
                    $this->typesenseSearchRepositoryInterface->save($currentQueue);
                }
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }
}
