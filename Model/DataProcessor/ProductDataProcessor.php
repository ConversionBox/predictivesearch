<?php
declare(strict_types=1);

namespace Conversionbox\Predictivesearch\Model\DataProcessor;

use Exception;
use Conversionbox\Predictivesearch\Model\ConfigData;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Conversionbox\Predictivesearch\Model\General;
use Conversionbox\Predictivesearch\Model\Api\TypeSenseApi;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Catalog\Model\ProductCategoryList;
use Conversionbox\Predictivesearch\Model\Schema\ProductSchema;
use Conversionbox\Predictivesearch\Logger\Logger;
use Magento\Catalog\Model\ProductFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as FilterableAttributes;
use Magento\Catalog\Model\Product\Attribute\Repository as ProductAttributeRespository;
use Magento\Review\Model\ReviewFactory;
use Magento\Sales\Model\ResourceModel\Report\Bestsellers\CollectionFactory as BestsellerCollection;
use Conversionbox\Predictivesearch\Model\Queue\QueueProcessor;
use Conversionbox\Predictivesearch\Model\Api\TypesenseSearchRepositoryInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Filter\FilterManager;

class ProductDataProcessor
{
    /**
     * @var ConfigData
     */
    private $configData;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var General
     */
    private $generalModel;

    /**
     * @var TypeSenseApi
     */
    private $typeSenseApi;

    /**
     * @var Data
     */
    private $priceHelper;

    /**
     * @var ProductCategoryList
     */
    private $productCategoryList;

    /**
     * @var ProductSchema
     */
    private $productSchema;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var Configurable
     */
    private $configurableProductType;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepositoryInterface;

    /**
     * @var FilterableAttributes
     */
    private $filterableAttributes;

    /**
     * @var ProductAttributeRespository
     */
    private $productAttributeRespository;

    /**
     * @var ReviewFactory
     */
    private $reviewFactory;

    /**
     * @var BestsellerCollection
     */
    private $bestsellerCollection;

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
     * @var FilterManager
     */
    private $filterManager;

    /**
     * ProductData processing Constructor
     *
     * @param ConfigData $configData
     * @param CollectionFactory $collectionFactory
     * @param General $generalModel
     * @param TypeSenseApi $typeSenseApi
     * @param Data $priceHelper
     * @param ProductCategoryList $productCategoryList
     * @param ProductSchema $productSchema
     * @param Logger $logger
     * @param ProductFactory $productFactory
     * @param Configurable $configurableProductType
     * @param CategoryRepositoryInterface $categoryRepositoryInterface
     * @param FilterableAttributes $filterableAttributes
     * @param ProductAttributeRespository $productAttributeRespository
     * @param ReviewFactory $reviewFactory
     * @param BestsellerCollection $bestsellerCollection
     * @param QueueProcessor $queueProcessor
     * @param TypesenseSearchRepositoryInterface $typesenseSearchRepositoryInterface
     * @param TimezoneInterface $timezoneInterface
     * @param FilterManager $filterManager
     */
    public function __construct(
        ConfigData $configData,
        CollectionFactory $collectionFactory,
        General $generalModel,
        TypeSenseApi $typeSenseApi,
        Data $priceHelper,
        ProductCategoryList $productCategoryList,
        ProductSchema $productSchema,
        Logger $logger,
        ProductFactory $productFactory,
        Configurable $configurableProductType,
        CategoryRepositoryInterface $categoryRepositoryInterface,
        FilterableAttributes $filterableAttributes,
        ProductAttributeRespository $productAttributeRespository,
        ReviewFactory $reviewFactory,
        BestsellerCollection $bestsellerCollection,
        QueueProcessor $queueProcessor,
        TypesenseSearchRepositoryInterface $typesenseSearchRepositoryInterface,
        TimezoneInterface $timezoneInterface,
        FilterManager $filterManager
    ) {
        $this->configData = $configData;
        $this->collectionFactory = $collectionFactory;
        $this->generalModel = $generalModel;
        $this->typeSenseApi = $typeSenseApi;
        $this->priceHelper = $priceHelper;
        $this->productCategoryList = $productCategoryList;
        $this->productSchema = $productSchema;
        $this->logger = $logger;
        $this->productFactory = $productFactory;
        $this->configurableProductType = $configurableProductType;
        $this->categoryRepositoryInterface = $categoryRepositoryInterface;
        $this->filterableAttributes = $filterableAttributes;
        $this->productAttributeRespository = $productAttributeRespository;
        $this->reviewFactory = $reviewFactory;
        $this->bestsellerCollection = $bestsellerCollection;
        $this->queueProcessor = $queueProcessor;
        $this->typesenseSearchRepositoryInterface = $typesenseSearchRepositoryInterface;
        $this->timezoneInterface = $timezoneInterface;
        $this->filterManager = $filterManager;
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

        $this->syncAllProducts($ids, $storeId);
    }

    /**
     * Sync products
     *
     * @param null||array $ids
     * @param int $storeId
     * @param string $mode
     */
    public function syncAllProducts($ids, $storeId, $mode = null)
    {
        if ($this->configData->isCronEnbaled() && !empty($ids)) {
            return;
        }

        if (!empty($ids)) {
            $storeCode = '';
            if ($storeId == 0) {
                $storeId = 1;
            }
            $stores = $this->generalModel->getStore($storeId);
            $storeCode = $stores->getCode();
            $indexName  =  $this->getStoreCode($storeCode);
            $productObj = $this->productFactory->create();
            foreach ($ids as $id) {
                $productObj->load($id);
                if (!$productObj->getId()) {
                    //removing deleted product from typesense
                    $this->typeSenseApi->deleteDocument($indexName, $id);
                } else {
                    if (in_array($storeId, $productObj->getStoreIds()) || $storeId == 0) {
                        $updatedDocument = $this->createProductData($id, $storeCode, $storeId);
                        //Update or create data to collection
                        $response = $this->typeSenseApi->upsertDocument($indexName, $updatedDocument);
                    }
                    if (!in_array($storeId, $productObj->getStoreIds())) {
                        $this->typeSenseApi->deleteDocument($indexName, $id);
                    }
                }
            }
            return;
        }

        $availableStore = $this->generalModel->getAllStore();
        foreach ($availableStore as $storeData) {
            $prdCollection = [];
            try {
                $storeCode = $storeData->getCode();
                $indexName =  $this->getStoreCode($storeCode);
                //Get collections from typesense and check if the collections exist.
                $collectionData = $this->typeSenseApi->retriveCollectionData();
                if (!in_array($indexName, $collectionData) || $mode) {
                    //Get product schema structure
                    $productSchemaData = $this->productSchema->getProductSchema($indexName);
                    //Create schema with structure
                    $this->typeSenseApi->createSchema($productSchemaData);

                    $collection = $this->collectionFactory->create();
                    $collection->addAttributeToSelect('*');
                    $collection->addStoreFilter($storeData->getId());
                    
                    foreach ($collection as $data) {
                        $productData = $this->createProductData(
                            $data->getId(),
                            $storeData->getCode(),
                            $storeData->getId()
                        );
                        if ($productData) {
                            $productData = $this->generalModel->encodeData($productData);
                            $productData = trim($productData, '[]');
                            $prdCollection[] = $productData;
                        }
                    }
    
                }

                if ($this->configData->isCronEnbaled() || $mode == 'cron') {
                    $this->queueProcessor->processProductQueue($prdCollection, $indexName);
                } else {
                    $prdCollection = implode(PHP_EOL, $prdCollection);
                    //sync typesense products here...
                    $response = $this->typeSenseApi->importCollectionData($indexName, $prdCollection);
        
                    //log response
                    $this->logger->error($response);
                    //error handling section need to be implemented here....
                }
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
            }
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
        $indexName =  $storeCode.'-products';
        if ($this->configData->getIndexPrefix()) {
            $indexName = $this->configData->getIndexPrefix().$indexName;
        }
        return $indexName;
    }

    /**
     * Create product Data array
     *
     * @param int $productId
     * @param string $storeCode
     * @param int $storeId
     * @return array
     */
    public function createProductData($productId, $storeCode, $storeId)
    {
        $response = [];
        $stockStatus = false;
        $stockQty = 0;
        $stock = $this->generalModel->getStockInfo($productId);
        if ($stock) {
            $stockStatus = $stock->getIsInStock();
            $stockQty = $stock->getQty();
        }
      
        $product = $this->generalModel->getProductData($productId, $storeId);
        $attributesArray = [];
        $productAttCode = [];
        $attributes = $product->getAttributes();
        $filterableData = $this->getFilterableAttributes();
        foreach ($attributes as $data) {
            if ($data->getIsFilterable()) {
                $attributeCode = $data->getAttributeCode();
                $attributeValue = $product->getData($attributeCode);
                if ($attributeValue) {
                        $productAttCode[] = $data->getAttributeCode();
                        $value = $product->getResource()->getAttribute($attributeCode)->getFrontend()
                                ->getValue($product);
                        $multiListArr = ['multiselect', 'dropdown', 'select'];
                    if (in_array($data->getFrontendInput(), $multiListArr)) {
                        if ($data->getFrontendInput() == 'multiselect') {
                            $value = str_replace(",", " ", "$value");
                        }
                        $attributesArray[$attributeCode] = [$value];
                    } else {
                        $attributesArray[$attributeCode] = $value;
                    }
                            
                    if ($product->getTypeId() === Configurable::TYPE_CODE) {
                        $attributesArray = $this->handlingConfigData($product);
                    }
                }
            }
        }
        $attrDiffArr = [];
        foreach ($filterableData as $data) {
            if (!in_array($data, $productAttCode)) {
                $attributeData = $this->productAttributeRespository->get($data);
                $multiListArr = ['multiselect', 'dropdown', 'select'];
                if (in_array($attributeData->getFrontendInput(), $multiListArr)) {
                    $attrDiffArr[$data] = [];
                } else {
                    $attrDiffArr[$data] = '';
                }
            }
        }
        $finalAtrArray = array_merge($attrDiffArr, $attributesArray);
        if ($product->getVisibility() != 1) {
            $image = null;
            if ($product->getImage()) {
                $image = $this->generalModel->getMediaUrl().'catalog/product'.$product->getImage();
            }
    
            $thumbNailImage = null;
            if ($product->getThumbnail()) {
                $thumbNailImage = $this->generalModel->getMediaUrl().'catalog/product'.$product->getThumbnail();
            }

            $smallImage = null;
            if ($product->getSmallImage()) {
                $smallImage = $this->generalModel->getMediaUrl().'catalog/product'.$product->getSmallImage();
            }
    
            $categoryIds = $this->productCategoryList->getCategoryIds($product->getId());
            $category = [];
            if ($categoryIds) {
                foreach (array_unique($categoryIds) as $catData) {
                    $category[] = $catData;
                }
            }
            $categoryNameArr = $this->getCategoryNameArr($category);
            $categoryUrlPath = $this->getCategoryUrlPath($category);

            $price = $product->getPrice();
            if ($product->getTypeId() === Configurable::TYPE_CODE) {
                $childProducts = $this->configurableProductType->getUsedProducts($product);
                $lowestPrice = null;
                $childAttributeData = [];
                foreach ($childProducts as $childProduct) {
                    $childPrice = $childProduct->getPrice();
                    if ($lowestPrice === null || $childPrice < $lowestPrice) {
                        $lowestPrice = $childPrice;
                    }
                }
                $price = $lowestPrice;
            }
    
            if ($product->getTypeId() == 'grouped') {
                $groupChildren = $product->getTypeInstance(true) ->getAssociatedProducts($product);
                $lowestPrice = null;
                foreach ($groupChildren as $childProduct) {
                    $childPrice = $childProduct->getPrice();
                    if ($lowestPrice === null || $childPrice < $lowestPrice) {
                        $lowestPrice = $childPrice;
                    }
                }
                $price = $lowestPrice;
            }

            $this->reviewFactory->create()->getEntitySummary($product, $this->generalModel->getStore()->getId());
            $ratingSummary = $product->getRatingSummary()->getRatingSummary();
            
            $productStore = '';
            if (in_array($storeId, $product->getStoreIds())) {
                $productStore = $storeCode;
            }
            $spAmount = $product->getSpecialPrice();
            $spPrice = ($spAmount)?$this->priceHelper->currency($spAmount, true, false):'';

            if($product->getStatus() == 1 && ($stockStatus == 1 || $stockStatus == true)){
            $response = [
                'id' => $product->getId(),
                'product_id' => $product->getId(),
                'product_name' => $product->getName(),
                'name' => $product->getName(),
                'sku' => $product->getSku(),
                'url' => $product->getProductUrl(),
                'image_url' => $image,
                'small_image' => $smallImage,
                'thumbnail' => $thumbNailImage,
                'price' => $price,
                'type_id' => $product->getTypeId(),
                'visibility' => $product->getVisibility(),
                'category' => $categoryNameArr,
                'url_path' => $categoryUrlPath,
                'stock_status' => $stockStatus,
                'product_status' => $product->getStatus(),
                'created_at' => $product->getCreatedAt(),
                'stock_qty' => $stockQty,
                'special_price' => $spPrice,
                'rating_summary' => ($ratingSummary)? $ratingSummary: '',
                'special_from_date' => ($product->getSpecialFromDate())?$product->getSpecialFromDate():'',
                'special_to_date' => ($product->getSpecialToDate())?$product->getSpecialToDate():'',
                'storeCode' => $productStore,
                'bestseller' => $this->getBestSellerQty($product->getId(), $storeId),
                'category_ids' => $categoryIds,
                'description' => $this->removeHtmlTags($product->getDescription()),
                'short_description' => $this->removeHtmlTags($product->getShortDescription()),
                'price_search' => round((float)$price, 2),
            ];
            }
            $productArray = array_merge($finalAtrArray, $response);
            return $productArray;
        }
    }

    /**
     * Remove Html Tags
     *
     * @param string $data
     * @return string
     */
    public function removeHtmlTags($data)
    {
        $params = ['allowableTags' => null, 'escape' => false];
        if ($data) {
            return $this->filterManager->stripTags($data, $params);
        }
        return '';
    }

    /**
     * Get Bestseller Qty
     *
     * @param int $productId
     * @param int $storeId
     */
    public function getBestSellerQty($productId, $storeId)
    {
        $collection = $this->bestsellerCollection->create();
        $collection->setPeriod('day');
        $collection->addStoreFilter($storeId);
        $collection->addFieldToFilter('product_id', $productId);

        if ($collection->getFirstItem()) {
            return 1;
        }
        return 0;
    }

    /**
     * Handling Config product Data
     *
     * @param object $product
     * @return array
     */
    public function handlingConfigData($product)
    {
        $childProducts = $this->configurableProductType->getUsedProducts($product);
        $childArrayAttributes = [];
        foreach ($childProducts as $childProduct) {
            $childAttributes = $childProduct->getAttributes();
            foreach ($childAttributes as $item) {
                if ($item->getIsFilterable()) {
                    $productAttCode[] = $item->getAttributeCode();
                    $value = $childProduct->getResource()->getAttribute($item->getAttributeCode())->getFrontend()
                            ->getValue($childProduct);
                    if ($item->getFrontendInput() == 'multiselect') {
                        $value = str_replace(",", " ", "$value");
                    }

                    $childArrayAttributes[$item->getAttributeCode()][] = $value;
                }
            }
        }
        foreach ($childArrayAttributes as $key => $data) {
            $attributeData = $this->productAttributeRespository->get($key);
            $multiListArr = ['multiselect', 'dropdown', 'select'];
            if (in_array($attributeData->getFrontendInput(), $multiListArr)) {
                $uniqueArray = array_values(array_unique($data));
                $childArrayAttributes[$key] = $uniqueArray;
            } elseif ($key == 'price') {
                $childArrayAttributes[$key] = min($data);
            } else {
                $childArrayAttributes[$key] = end($data);
            }
        }
        return $childArrayAttributes;
    }

    /**
     * Get category name by categoryId
     *
     * @param array $category
     * @return array
     */
    public function getCategoryNameArr($category)
    {
        $response = [];
        foreach ($category as $item) {
            $categoryData = $this->categoryRepositoryInterface->get($item, null);
            if ($categoryData->getLevel() > 1) {
                $response[] = $categoryData->getName();
            }
        }
        return $response;
    }

    /**
     * Get category url path by categoryId
     *
     * @param array $category
     * @return array
     */
    public function getCategoryUrlPath($category)
    {
        $response = [];
        foreach ($category as $item) {
            $categoryData = $this->categoryRepositoryInterface->get($item, null);
            if ($categoryData->getUrlPath()) {
                $urlpath = str_replace('/', '-', $categoryData->getUrlPath());
                $response[] = $urlpath;
            }
        }
        return $response;
    }

    /**
     * Get Filterable Attributes of product
     */
    public function getFilterableAttributes()
    {
        $response = [];
        $productAttributes = $this->filterableAttributes->create();
        $productAttributes->addFieldToFilter(
            ['is_filterable', 'is_filterable_in_search'],
            [[1, 2], 1]
        );

        foreach ($productAttributes as $attributes) {
            $response[] = $attributes->getAttributeCode();
        }
        return $response;
    }

    /**
     * Sync product by cron
     *
     * @param array $productDataArray
     * @param int $queueId
     * @param string $index
     * @return void
     */
    public function syncProductByCron($productDataArray, $queueId, $index)
    {
        try {
            $prdCollection = implode(PHP_EOL, $productDataArray);
            $response = $this->typeSenseApi->importCollectionData($index, $prdCollection);
            $this->logger->error($response);
            $success = true;
            if ($response) {
                $parts = explode(',', substr($response, 1, -1));
                if (isset($parts[0])) {
                    $responseData = explode(":", $parts[0]);
                    $key = str_replace('"', '', $responseData[0]);
                    if ($key == 'code') {
                        $success = false;
                    }
                    if ($key == 'success' && $responseData[1] == 'false') {
                        $success = false;
                    }
                }
            }

            if ($queueId) {
                $currentQueue = $this->typesenseSearchRepositoryInterface->getById($queueId);
                if ($success) {
                    $currentQueue->setJobStatus(1);
                }
                $currentQueue->setErrors($this->generalModel->encodeData($response));
                $currentQueue->setUpdatedAt($this->timezoneInterface->date()->format('Y-m-d H:i:s'));
                $this->typesenseSearchRepositoryInterface->save($currentQueue);
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Update Product Data
     *
     * @param array $productData
     * @param int $queueId
     * @param string $index
     * @return void
     */
    public function updateProductCron($productData, $queueId, $index)
    {
        if (!empty($productData)) {
            try {
                $productObj = $this->productFactory->create();
                $productObj->load($productData['productId']);
                if ($productObj->getId()) {
                    $updatedDocument = $this->createProductData(
                        $productData['productId'],
                        $productData['storeCode'],
                        $productData['storeId']
                    );
                    $response = $this->typeSenseApi->upsertDocument($index, $updatedDocument);
                } else {
                    $response = $this->typeSenseApi->deleteDocument($index, $productData['productId']);
                }
        
                if ($queueId) {
                    $currentQueue = $this->typesenseSearchRepositoryInterface->getById($queueId);
                    $currentQueue->setJobStatus(1);
                    $currentQueue->setErrors($this->generalModel->encodeData($response));
                    $currentQueue->setUpdatedAt($this->timezoneInterface->date()->format('Y-m-d H:i:s'));
                    $this->typesenseSearchRepositoryInterface->save($currentQueue);
                }
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }
}
