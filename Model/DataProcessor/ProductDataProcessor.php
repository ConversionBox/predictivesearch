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
use Conversionbox\Predictivesearch\Api\TypesenseSearchRepositoryInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Filter\FilterManager;
use Magento\CatalogInventory\Api\StockRegistryInterface;
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
     * @var $stockRegistry;
     */
      protected $stockRegistry;
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
        FilterManager $filterManager,
        StockRegistryInterface   $stockRegistry
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
        $this->stockRegistry = $stockRegistry;
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
            // Optimize single product updates
            $this->processIndividualProducts($ids, $storeId);
            return;
        }

        $availableStore = $this->generalModel->getAllStore();
        foreach ($availableStore as $storeData) {
            $prdCollection = [];
            try {
                $storeCode = $storeData->getCode();
                $indexName = $this->getStoreCode($storeCode);
                
                // Cache collection data to avoid repeated API calls
                static $collectionDataCache = [];
                if (!isset($collectionDataCache[$indexName])) {
                    $collectionDataCache[$indexName] = $this->typeSenseApi->retriveCollectionData();
                }
                $collectionData = $collectionDataCache[$indexName];
                
                if (!in_array($indexName, $collectionData) || $mode) {
                    // Create schema only if needed
                    $productSchemaData = $this->productSchema->getProductSchema($indexName);
                    $this->typeSenseApi->createSchema($productSchemaData);

                    // Optimize collection by limiting attributes and using batch processing
                    $collection = $this->getOptimizedProductCollection($storeData->getId());
                    
                    // Process in batches to avoid memory issues
                    $batchSize = 100; // Adjust based on your server capacity
                    $currentBatch = [];
                    $batchCount = 0;
                    
                    foreach ($collection as $data) {
                        $productData = $this->createProductData(
                            $data->getId(),
                            $storeData->getCode(),
                            $storeData->getId()
                        );
                        
                        if ($productData) {
                            $productData = $this->generalModel->encodeData($productData);
                            $productData = trim($productData, '[]');
                            $currentBatch[] = $productData;
                            $batchCount++;
                            
                            // Process batch when it reaches the batch size
                            if ($batchCount >= $batchSize) {
                                $this->processBatch($currentBatch, $indexName, $mode);
                                $currentBatch = [];
                                $batchCount = 0;
                            }
                        }
                    }
                    
                    // Process any remaining products
                    if (!empty($currentBatch)) {
                        $this->processBatch($currentBatch, $indexName, $mode);
                    }
                }
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }
    
    /**
     * Process individual product updates
     * 
     * @param array $ids
     * @param int $storeId
     * @return void
     */
    private function processIndividualProducts($ids, $storeId)
    {
        $storeCode = '';
        if ($storeId == 0) {
            $storeId = 1;
        }
        $stores = $this->generalModel->getStore($storeId);
        $storeCode = $stores->getCode();
        $indexName = $this->getStoreCode($storeCode);
        
        // Use repository pattern instead of creating new product instances
        $productRepository = \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
            
        foreach ($ids as $id) {
            try {
                $productObj = $productRepository->getById($id, false, $storeId);
                if (in_array($storeId, $productObj->getStoreIds()) || $storeId == 0) {
                    $updatedDocument = $this->createProductData($id, $storeCode, $storeId);
                    $this->typeSenseApi->upsertDocument($indexName, $updatedDocument);
                } else {
                    $this->typeSenseApi->deleteDocument($indexName, $id);
                }
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                // Product doesn't exist, remove from index
                $this->typeSenseApi->deleteDocument($indexName, $id);
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }
    /**
     * Get optimized product collection
     * 
     * @param int $storeId
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    private function getOptimizedProductCollection($storeId)
    {
        $collection = $this->collectionFactory->create();
        
        // Only select necessary attributes instead of '*'
        $collection->addAttributeToSelect(['entity_id', 'name', 'sku', 'price', 'type_id', 'visibility', 'status']);
        $collection->addStoreFilter($storeId);
        $collection->addAttributeToFilter(
            'status',
            \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
        );
        $collection->addAttributeToFilter(
            'visibility',
            ['neq' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE]
        );
        return $collection;
    }
    
    /**
     * Process a batch of products
     * 
     * @param array $batch
     * @param string $indexName
     * @param string|null $mode
     * @return void
     */
    private function processBatch($batch, $indexName, $mode = null)
    {
        if ($this->configData->isCronEnbaled() || $mode == 'cron') {
            $this->queueProcessor->processProductQueue($batch, $indexName);
        } else {
            $batchData = implode(PHP_EOL, $batch);
            $response = $this->typeSenseApi->importCollectionData($indexName, $batchData);
            
            // Only log errors, not successful responses
            if (isset($response['error'])) {
                $this->logger->error(json_encode($response));
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
        $minimalPrice = "";
        $min="";
        $max="";
        $response = [];
        $stockStatus = false;
        $stockQty = 0;
        $stock = $this->generalModel->getStockInfo($productId);
        $stockQty = $this->getProductQty($productId);
        if ($stockQty > 0  && $stock->getIsInStock()) {
            $stockStatus = true;
        }

        $stockQty = $this->getProductQty($productId);
        $product = $this->generalModel->getProductData($productId, $storeId);
        if ($product->getTypeId() === Configurable::TYPE_CODE) {
            $childProducts = $this->configurableProductType->getUsedProducts($product);
             $isInStock = false;
            foreach ($childProducts as $childProduct) {
            $stock = $this->generalModel->getStockInfo($childProduct->getId());
            $stockqty = $this->getProductQty($childProduct->getId());
               if ($stockqty > 0  && $stock->getIsInStock()) {
                   $isInStock = true;
                   break;
            }
        }
        $stockStatus = $isInStock;
        }
        if ($product->getTypeId() == 'grouped') {
            $groupChildren = $product->getTypeInstance(true) ->getAssociatedProducts($product);
               $isInStock = false;
            foreach ($groupChildren as $childProduct) {
               $stock = $this->generalModel->getStockInfo($childProduct->getId());
               if ($stock && $stock->getIsInStock()) {
                   $isInStock = true;
                   break;
            }
            
            }
            $stockStatus = $isInStock;
        }
        if ($product->getTypeId() == 'bundle') {
                /** @var \Magento\Bundle\Model\Product\Type $typeInstance */
                $typeInstance = $product->getTypeInstance();
                $optionCollection = $typeInstance->getOptionsCollection($product);
                $selectionCollection = $typeInstance->getSelectionsCollection(
                    $typeInstance->getOptionsIds($product),
                    $product
                );

               // Group selections by option
    $selectionsByOption = [];
    foreach ($selectionCollection as $selection) {
        $selectionsByOption[$selection->getOptionId()][] = $selection;
    }

    $isInStock = true; // assume true until proven otherwise

    foreach ($optionCollection as $option) {
        if (!$option->getRequired()) {
            // Skip non-required options
            continue;
        }

        $hasInStockSelection = false;

        if (isset($selectionsByOption[$option->getOptionId()])) {
            foreach ($selectionsByOption[$option->getOptionId()] as $selection) {
                $stock = $this->generalModel->getStockInfo($selection->getId());
                if ($stock && $stock->getIsInStock()) {
                    $hasInStockSelection = true;
                    break;
                }
            }
        }

                // If a required option has no in-stock selection → bundle not salable
                if (!$hasInStockSelection) {
                    $isInStock = false;
                    break;
                }
            }

             $stockStatus = $isInStock;
            }
        $attributesArray = [];
        $productAttCode = [];
        $attributes = $product->getAttributes();
        $filterableData = $this->getFilterableAttributes();
         if ($product->getTypeId() === Configurable::TYPE_CODE) {
             $attributesArray = $this->handlingConfigData($product);
        }
        if ($product->getTypeId() == 'bundle') {
            $attributesArray = $this->handlingBundleData($product);
        } 
        foreach ($attributes as $data) {
            if ($data->getIsFilterable()) {
                $attributeCode = $data->getAttributeCode();
                $attributeValue = $product->getData($attributeCode);
                if ($attributeValue) {
                        $productAttCode[] = $data->getAttributeCode();
                        $value = $product->getResource()->getAttribute($attributeCode)->getFrontend()
                                ->getValue($product);
                            $multiListArr = ['multiselect', 'dropdown', 'select','swatch_visual'];
                    if (in_array($data->getFrontendInput(), $multiListArr)) {
                        if ($data->getFrontendInput() == 'multiselect') {
                            $value = str_replace(",", " ", "$value");
                        }
                        $attributesArray[$attributeCode] = $value ? [$value] : [];
                    } else {
                        $attributesArray[$attributeCode] = $value  ? $value : '';
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
                $price = $lowestPrice === null ? 0 : $lowestPrice;
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
                $price = $lowestPrice === null ? 0 : $lowestPrice;
            }

            if ($product->getTypeId() == 'bundle') {
                $priceModel = $product->getPriceModel();
                list($minPrice, $maxPrice) = $product->getPriceModel()->getTotalPrices($product, null, true);
                $priceType =  $product->getPriceType();
                if($priceType == 1){
                $min = $minPrice;
                $max = $maxPrice;
                if($minPrice == $maxPrice){
                $minimalPrice = "$".$minPrice;
                }else{
                $minimalPrice = "$".$minPrice ."-". "$".$maxPrice; 
                }
                $price = $minPrice;
                }
                else
                {
                $min = $product->getPriceInfo()->getPrice('final_price')->getMinimalPrice()->getValue();
                $max = $product->getPriceInfo()->getPrice('final_price')->getMaximalPrice()->getValue();
                if($min == $max){
                $minimalPrice =  "$".$min;
                }else{
                $minimalPrice = "$".$min ."-". "$".$max;
                }
                $price = $min;
                }
            }

            $this->reviewFactory->create()->getEntitySummary($product, $this->generalModel->getStore()->getId());
            $ratingSummary = $product->getRatingSummary()->getRatingSummary();
            
            $productStore = '';
            if (in_array($storeId, $product->getStoreIds())) {
                $productStore = $storeCode;
            }
            $spAmount = $product->getSpecialPrice();
            $spPrice = ($spAmount)?$spAmount:'';
            $priceRange = ($minimalPrice)?:'';
            $priceMin =($min)?:'';
            $priceMax=($max)?:'';
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
                'price' => $price??0,
                'price_range' => $priceRange,
                'price_min' => $priceMin,
                'price_max' => $priceMax,
                'type_id' => $product->getTypeId(),
                'visibility' => $product->getVisibility(),
                'category' => $categoryNameArr,
                'url_path' => $categoryUrlPath,
                'stock_status' => $stockStatus,
                'product_status' => $product->getStatus(),
                'created_at' => $product->getCreatedAt(),
                'stock_qty' => $stockQty,
                'special_price' => round((float)$spPrice, 2),
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
            $productArray = array_merge($finalAtrArray, $response);
            return $productArray;
    }


public function getProductQty($productId)
{
    $stockItem = $this->stockRegistry->getStockItem($productId);
    return $stockItem->getQty(); // returns decimal quantity
}
    /**
     * Remove Html Tags
     *
     * @param string $data
     * @return string
     */
    public function removeHtmlTags($data)
    {
        if ($data) {
              // Decode entities like &lt;br&gt; into <br>
        $decoded = html_entity_decode($data);

        // Remove <style> blocks and PageBuilder inline styles
        $decoded = preg_replace('#<style\b[^>]*>(.*?)</style>#is', '', $decoded);

        // Remove PageBuilder data-pb-style attributes
        $decoded = preg_replace('/#html-body\s*\[data-pb-style=.*?\}\s*/', '', $decoded);

        // Strip any remaining tags
        return strip_tags($decoded);
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
            $multiListArr = ['multiselect', 'dropdown', 'select','swatch_visual'];
            if (in_array($attributeData->getFrontendInput(), $multiListArr)) {
                // Filter out false values before creating unique array
                $filteredData = array_filter($data, function($value) {
                    return $value !== false;
                });
                $uniqueArray = array_values(array_unique($filteredData));
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
     * Handling Bundle product Data
     *
     * @param object $product
     * @return array
     */
    public function handlingBundleData($product)
    {
        /** @var \Magento\Bundle\Model\Product\Type $typeInstance */
        $typeInstance = $product->getTypeInstance();
        $optionCollection = $typeInstance->getOptionsCollection($product);
        $selectionCollection = $typeInstance->getSelectionsCollection(
            $typeInstance->getOptionsIds($product),
            $product
        );

        $childArrayAttributes = [];
        foreach ($selectionCollection as $selection) {
            $childProduct = $selection;
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
                 $filteredData = array_filter($data, function($value) {
                    return $value !== false;
                });
                $uniqueArray = array_values(array_unique($filteredData));
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
}
