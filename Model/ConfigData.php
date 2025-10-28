<?php
declare(strict_types=1);

namespace Conversionbox\Predictivesearch\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Conversionbox\Predictivesearch\Model\General;
use Magento\Framework\Registry;

class ConfigData
{
    /**
     * Search Enable Status
     */
    private const IS_ENABLED = 'typesense_general/credentials/enable_frontend';

    /**
     * Cloud Key
     */
    private const HOST = 'typesense_general/credentials/host';

    /**
     * Search Only Api Key
     */
    private const SEARCH_API_KEY = 'typesense_general/credentials/search_only_api_key';

    /**
     * Admin Api Key
     */
    private const ADMIN_API_KEY = 'typesense_general/credentials/admin_api_key';

    /**
     * Index Prefix
     */
    private const INDEX_PERFIX = 'typesense_general/credentials/index_prefix';

    /**
     * Node
     */
    private const NODE = 'typesense_general/credentials/node';

    /**
     * Nearest Node
     */
    private const NEAREST_NODE = 'typesense_general/credentials/nearest_node';

    /**
     * Protocol
     */
    private const PROTOCOL = 'typesense_general/credentials/protocol';

    /**
     * Port
     */
    private const PORT = 'typesense_general/credentials/port';

    /**
     * Search Result page Status
     */
    private const RESULT_PAGE = 'typesense_search_result/instant_search_result/enable_result_page';

    /**
     * No of products per page
     */
    private const NO_PRODUCTS = 'typesense_search_result/instant_search_result/page_per_product';

    /**
     * Search Filter Attributes
     */
    private const SEARCH_FILTERS = 'typesense_search_result/instant_search_result/search_filters';

    /**
     * Sort Attributes
     */
    private const SORT_ATTRIBUTES = 'typesense_search_result/instant_search_result/sort_options';

    /**
     * Enable Addto cart
     */
    private const ADD_CART = 'typesense_search_result/instant_search_result/enable_addtocart';
   /**
    *  show price Search result page
    **/  
   private const SEARCH_SHOW_PRICE = 'typesense_search_result/instant_search_result/show_price';
   /**
    *  show sku Search result page
    **/  
   private const SEARCH_SHOW_SKU = 'typesense_search_result/instant_search_result/show_sku';
   /**
    *  Maximum Title Lines Search result page
    **/  
   private const SEARCH_MAX_TITLE_LINE = 'typesense_search_result/instant_search_result/max_title_lines';
    /**
    *  Flip Image On Hover Search result page
    **/  
   private const SEARCH_FLIP_IMG_HOVER = 'typesense_search_result/instant_search_result/flip_image_on_hover';
   /**
    *  Show description Search result page
    **/  
   private const SEARCH_SHOW_DESC = 'typesense_search_result/instant_search_result/show_description';
    /**
    *  Maximum  description line search result page
    **/  
   private const SEARCH_MAX_DESC_LINE = 'typesense_search_result/instant_search_result/max_description_lines';
    /**
    *  Show out of stock product in  search result page
    **/  
    private const SEARCH_OUT_OF_STOCK = 'typesense_search_result/instant_search_result/show_outof_stock';
    /**
    *  Show out of stock product in  category page
    **/  
    private const CATEGORY_OUT_OF_STOCK = 'typesense_categories/categories/show_outof_stock';
   /**
    *  Show  Price  autocomplete
    */
    private const AUTOCOMPLTE_SHOW_PRICE = "typesense_autocomplete/autocomplete/show_price";
   /**
    * Show SKU autocomplete
    */
    private const AUTOCOMPLETE_SHOW_SKU = "typesense_autocomplete/autocomplete/show_sku";
    /**
     * Show description autocomplete
     */
      private const AUTOCOMPLETE_SHOW_DESC = "typesense_autocomplete/autocomplete/show_description";
   /**
    * See All Button
    */
   private const SEE_ALL_BUTTON = "typesense_autocomplete/autocomplete/see_all_button";
   /**
    *  Maximum description Line
    */
    private const AUTOCOMPLETE_MAX_DESC_LINE = "typesense_autocomplete/autocomplete/max_description_lines";
    /**
     * Enable Category Search
     */
    private const CARTGORY_SEARCH = 'typesense_autocomplete/autocomplete/enable_Category';

    /**
     * Enable page Search
     */
    private const PAGE_SEARCH = 'typesense_autocomplete/autocomplete/enable_page';

    /**
     * Category count
     */
    private const CATEGORY_COUNT = 'typesense_autocomplete/autocomplete/nb_of_categories_suggestions';

    /**
     * Page count
     */
    private const PAGE_COUNT = 'typesense_autocomplete/autocomplete/nb_of_pages_suggestions';

    /**
     * Product count
     */
    private const PRODUCT_COUNT = 'typesense_autocomplete/autocomplete/nb_of_products_suggestions';

    /**
     * Excluded Pages
     */
    private const EXCLUDED_PAGES = 'typesense_autocomplete/autocomplete/excluded_pages';

    /**
     * Product Attribute Config
     */
    private const ADDITONAL_ATTRIBUTES = 'typesense_products/products/product_additional_attributes';

    /**
     * Display Sku
     */
    private const SHOW_SKU = 'typesense_products/products/show_sku';

    /**
     * Display Price
     */
    private const SHOW_PRICE = 'typesense_products/products/show_price';

    /**
     * Show Suggestions
     */
    private const SHOW_SUGGESTIONS = 'typesense_autocomplete/autocomplete/enable_query_suggestions';

    /**
     * Ranking
     */
    private const RANKING = 'typesense_products/products/custom_ranking_product_attributes';
    /**
     * Enable Logging
     */
    private const LOGGING_ENABLED = 'typesense_general/credentials/debug';

    /**
     * Enable Typo Tolerance
     */
    private const TYPO_ENABLED = 'typotolerance/typotolerance_group/enable_typotolerance';

    /**
     * Enable Word Length
     */
    private const WORD_LENGTH = 'typotolerance/typotolerance_group/word_length';

    /**
     * Category Attributes
     */
    private const CATEGORY_ATTRIBUTES = 'typesense_categories/categories/category_additional_attributes';

    /**
     * Category Ranking
     */
    private const CATEGORY_RANKING_ATTR = 'typesense_categories/categories/custom_ranking_category_attributes';

    /**
     * Search Filter Attributes
     */
    private const CATEGORY_FILTERS = 'typesense_categories/categories/search_filters';

    /**
     * Sort Attributes
     */
    private const CATEGORY_SORT_ATTRIBUTES = 'typesense_categories/categories/sort_options';

    /**
     * Enable Highlights
     */
    private const HIGHLIGHT_ENABLED = 'typesense_general/credentials/highlights';

    /**
     * Enable Slider
     */
    private const SLIDER_ENABLED = 'typesense_search_result/instant_search_result/enable_price_slider';

    /**
     * Placeholder iamges
     */
    private const PLACEHOLDER = 'catalog/placeholder/small_image_placeholder';

    /**
     * Grid Per value
     */
    private const GRID_PER_VALUE = 'catalog/frontend/grid_per_page_values';

    /**
     * Suggestion Item Count
     */
    private const SUGGESTIONS_COUNT = 'typesense_autocomplete/autocomplete/nb_of_query_suggestions';

     /**
      * Image Type
      */
    private const IMG_TYPE = 'typesense_search_result/image/type';

     /**
      * Image Height
      */
    private const IMG_HIEHT = 'typesense_search_result/image/height';

    /**
     * Image Width
     */
    private const IMG_WIDTH = 'typesense_search_result/image/width';
    
    /**
     * Cron Status
     */
    private const CRON_STATUS = 'typesense_queue/queue/active';

    /**
     * Cron Time
     */
    private const CRON_TIME = 'typesense_queue/queue/cron_time';

    /**
     * Batch Size
     */
    private const BATCH_SIZE = 'typesense_queue/queue/batch_size';

    /**
     * Clear records of cron data
     */
    private const CLEAR_RECORDS = 'typesense_queue/queue/clear';
    /***
     * Minimum char length
     */
    private const MINIMUM_LENGTH = 'typesense_autocomplete/autocomplete/minimum_char_length';


    private const UNIQUEID ='typesense_general/credentials/unique_id';
    /**
    *  Display No of product
    **/  
   private const CATEGORY_NO_OF_PRODUCT = 'typesense_categories/categories/nb_of_products_shown';
     /**
    *  show price Search result page
    **/  
   private const CATEGORY_SHOW_PRICE = 'typesense_categories/categories/show_price';
   /**
    *  show sku Search result page
    **/  
   private const CATEGORY_SHOW_SKU = 'typesense_categories/categories/show_sku';
   /**
    *  Maximum Title Lines Search result page
    **/  
   private const CATEGORY_MAX_TITLE_LINE = 'typesense_categories/categories/max_title_lines';
    /**
    *  Flip Image On Hover Search result page
    **/  
   private const CATEGORY_FLIP_IMG_HOVER = 'typesense_categories/categories//flip_image_on_hover';
   /**
    *  Show description Search result page
    **/  
   private const CATEGORY_SHOW_DESC = 'typesense_categories/categories/show_description';
    /**
    *  Maximum  description line search result page
    **/  
   private const CATEGORY_MAX_DESC_LINE = 'typesense_categories/categories/max_description_lines';
   private const CATEGORY_PAGE_ENABLED ='typesense_categories/categories/enable_Category';
   private const AUTOCOMPLETE_OUT_OF_STOCK ='typesense_autocomplete/autocomplete/show_outof_stock';
   private const AUTOCOMPLETE_ENABLED ='typesense_autocomplete/autocomplete/enable_frontend';
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfigInterface;

    /**
     * @var General
     */
    private $generalModel;
        /**
     * @var Registry
     */
    private $registry;

    /**
     * Config Data Provider
     *
     * @param ScopeConfigInterface $scopeConfigInterface
     * @param General $generalModel
     */
    public function __construct(
        ScopeConfigInterface $scopeConfigInterface,
        General $generalModel,
        Registry $registry
    ) {
        $this->scopeConfigInterface = $scopeConfigInterface;
        $this->generalModel = $generalModel;
        $this->registry = $registry;
    }

    /**
     * Get Module Status
     *
     * @param void
     * @return string
     */
    public function getModuleStatus()
    {
        return $this->getSystemConfigValues(self::IS_ENABLED);
    }

    /**
     * Get Cloud Key
     *
     * @param void
     * @return string
     */
    public function getHost()
    {
        return $this->getSystemConfigValues(self::HOST);
    }

    /**
     * Get Search Api Key
     *
     * @param void
     * @return string
     */
    public function getSearchApiKey()
    {
        return $this->getSystemConfigValues(self::SEARCH_API_KEY);
    }

    /**
     * Get Admin Api Key
     *
     * @param void
     * @return string
     */
    public function getAdminApiKey()
    {
        return $this->getSystemConfigValues(self::ADMIN_API_KEY);
    }
    
    /**
     * Conversion category page Enabled
     *
     * @param void
     * @return string
     */
    public function getCategorypageEnabled()
    {
        return $this->getSystemConfigValues(self::CATEGORY_PAGE_ENABLED);
    }
    /**
     * Conversion autocomplete search Enabled
     *
     * @param void
     * @return string
     */
    public function getAutocompleteEnabled()
    {
        return $this->getSystemConfigValues(self::AUTOCOMPLETE_ENABLED);
    }
    /**
     * Get Index Prefix
     *
     * @param void
     * @return string
     */
    public function getIndexPrefix()
    {
        return $this->getSystemConfigValues(self::INDEX_PERFIX);
    }

    /**
     * Get Node
     *
     * @param void
     * @return string
     */
    public function getNode()
    {
        return $this->getSystemConfigValues(self::NODE);
    }

    /**
     * Get Protocol
     *
     * @param void
     * @return string
     */
    public function getProtocol()
    {
        return $this->getSystemConfigValues(self::PROTOCOL);
    }

    /**
     * Get Port
     *
     * @param void
     * @return string
     */
    public function getPort()
    {
        return $this->getSystemConfigValues(self::PORT);
    }

    /**
     * Get System config values
     *
     * @param string $configPath
     */
    public function getSystemConfigValues($configPath)
    {
        return $this->scopeConfigInterface->getValue($configPath, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Number of Products per search result page
     *
     * @param void
     * @return string
     */
    public function getNoProductsPage()
    {
        return $this->getSystemConfigValues(self::NO_PRODUCTS);
    }

    /**
     * Custom search result page
     *
     * @param void
     * @return string
     */
    public function getCustomResultPage()
    {
        return $this->getSystemConfigValues(self::RESULT_PAGE);
    }

    /**
     * Search Filters
     *
     * @param void
     * @return string
     */
    public function getSearchFilters()
    {
        $filters = $this->getSystemConfigValues(self::SEARCH_FILTERS);
        if ($filters) {
            $filters = $this->generalModel->decodeData($filters);
            return $filters;
        }
        return [];
    }

    /**
     * Sort Options
     *
     * @param void
     * @return string
     */
    public function getSortOptions()
    {
        $sortOption = $this->getSystemConfigValues(self::SORT_ATTRIBUTES);
        if ($sortOption) {
            $sortOption = $this->generalModel->decodeData($sortOption);
            return $sortOption;
        }

        return [];
    }

    /**
     * Enable Addto cart
     *
     * @param void
     * @return string
     */
    public function getEnableAddToCart()
    {
        return $this->getSystemConfigValues(self::ADD_CART);
    }

    /**
     * Category section Status
     *
     * @param void
     * @return string
     */
    public function getEnableCategorySearch()
    {
        return $this->getSystemConfigValues(self::CARTGORY_SEARCH);
    }

    /**
     * Page section Status
     *
     * @param void
     * @return string
     */
    public function getEnablePageSearch()
    {
        return $this->getSystemConfigValues(self::PAGE_SEARCH);
    }

    /**
     * Category Section Count
     *
     * @param void
     * @return string
     */
    public function getCategoryCount()
    {
        return $this->getSystemConfigValues(self::CATEGORY_COUNT);
    }

    /**
     * Page Section Count
     *
     * @param void
     * @return string
     */
    public function getPageCount()
    {
        return $this->getSystemConfigValues(self::PAGE_COUNT);
    }

    /**
     * Product Section Count
     *
     * @param void
     * @return string
     */
    public function getProductCount()
    {
        return $this->getSystemConfigValues(self::PRODUCT_COUNT);
    }

    /**
     * Excluded pages
     *
     * @param void
     * @return array
     */
    public function getExcludedPages()
    {
        $excludedPages = $this->getSystemConfigValues(self::EXCLUDED_PAGES);
        if ($excludedPages) {
            $excludedPages = $this->generalModel->decodeData($excludedPages);
            return $excludedPages;
        }
        return [];
    }

    /**
     * Excluded pages
     *
     * @param void
     * @return array
     */
    public function getProductAttributeConfig()
    {
        $productAttribute = [];
        $productAttributeConfig = $this->getSystemConfigValues(self::ADDITONAL_ATTRIBUTES);
        if ($productAttributeConfig) {
            $productAttributeConfig = $this->generalModel->decodeData($productAttributeConfig);
            foreach ($productAttributeConfig as $item) {
                $productAttribute[] = [
                    'code' => $item['productAttribute'],
                    'search' => $item['searchable'],
                ];
            }
            return $productAttribute;
        }
        return [];
    }

    /**
     * Display Sku
     *
     * @param void
     * @return string
     */
    public function getShowSku()
    {
        return $this->getSystemConfigValues(self::SHOW_SKU);
    }

    /**
     * Display Price
     *
     * @param void
     * @return string
     */
    public function getShowPrice()
    {
        return $this->getSystemConfigValues(self::SHOW_PRICE);
    }

    /**
     * Show Suggestions
     *
     * @param void
     * @return string
     */
    public function showSuggestions()
    {
        return $this->getSystemConfigValues(self::SHOW_SUGGESTIONS);
    }

    /**
     * Ranking
     *
     * @param void
     * @return array
     */
    public function getRanking()
    {
        $ranking = $this->getSystemConfigValues(self::RANKING);
        if ($ranking) {
            $ranking = $this->generalModel->decodeData($ranking);
            return $ranking;
        }
        return [];
    }
    public function getMinimumChar(){
        
        return $this->getSystemConfigValues(self::MINIMUM_LENGTH);

    }
    public function getUniqueId(){
        
        return $this->getSystemConfigValues(self::UNIQUEID);

    }
    /**
     * Enable Logging
     *
     * @param void
     * @return string
     */
    public function isLoggingEnabled()
    {
        return $this->getSystemConfigValues(self::LOGGING_ENABLED);
    }

    /**
     * Enable Typo Tolerance
     *
     * @param void
     * @return string
     */
    public function isTypoEnabled()
    {
        return $this->getSystemConfigValues(self::TYPO_ENABLED);
    }

    /**
     * Typo Tolerance Word Length
     *
     * @param void
     * @return string
     */
    public function minimumLength()
    {
        return $this->getSystemConfigValues(self::WORD_LENGTH);
    }

    /**
     * Category Attributes
     *
     * @param void
     * @return array
     */
    public function getCategoryAttributeConfig()
    {
        $categoryAttribute = [];
        $categoryAttributeConfig = $this->getSystemConfigValues(self::CATEGORY_ATTRIBUTES);
        if ($categoryAttributeConfig) {
            $categoryAttributeConfig = $this->generalModel->decodeData($categoryAttributeConfig);
            foreach ($categoryAttributeConfig as $item) {
                $categoryAttribute[] = [
                    'code' => $item['categoryAttribute'],
                    'search' => $item['categorySearch'],
                ];
            }
            return $categoryAttribute;
        }
        return [];
    }

    /**
     * Category Ranking
     *
     * @param void
     * @return array
     */
    public function getCategoryRanking()
    {
        $ranking = $this->getSystemConfigValues(self::CATEGORY_RANKING_ATTR);
        if ($ranking) {
            $ranking = $this->generalModel->decodeData($ranking);
            return $ranking;
        }
        return [];
    }

    /**
     * Enable Highlights
     *
     * @param void
     * @return string
     */
    public function isHighlightEnabled()
    {
        return $this->getSystemConfigValues(self::HIGHLIGHT_ENABLED);
    }

    /**
     * Enable Slider
     *
     * @param void
     * @return string
     */
    public function enableSlider()
    {
        $filterCollection = $this->getSearchFilters();
        foreach($filterCollection as $filter){
            if($filter['facet'] == 'slider' && $filter['filterAttribute'] == 'price'){
                return 1;
            }else{
                return 0;
            }
        }
    }

    /**
     * Get PlaceHolder Image
     *
     * @param void
     * @return string
     */
    public function getPlaceHolderImage()
    {
        return $this->getSystemConfigValues(self::PLACEHOLDER);
    }

    /**
     * Fet Grid Per value
     *
     * @param void
     * @return string
     */
    public function getGridPerValue()
    {
        return $this->getSystemConfigValues(self::GRID_PER_VALUE);
    }

    /**
     * Suggestion Section Count
     *
     * @param void
     * @return string
     */
    public function getSuggestionsCount()
    {
        return $this->getSystemConfigValues(self::SUGGESTIONS_COUNT);
    }

    /**
     * Image Type
     *
     * @param void
     * @return string
     */
    public function getImageType()
    {
        return $this->getSystemConfigValues(self::IMG_TYPE);
    }

    /**
     * Image Height
     *
     * @param void
     * @return string
     */
    public function getImageHeight()
    {
        return $this->getSystemConfigValues(self::IMG_HIEHT);
    }

    /**
     * Image Width
     *
     * @param void
     * @return string
     */
    public function getImageWidth()
    {
        return $this->getSystemConfigValues(self::IMG_WIDTH);
    }
    public function getAutocompletesku(){
      return $this->getSystemConfigValues(self::AUTOCOMPLETE_SHOW_SKU);
     }
    public function getAutocompleteprice(){
     return $this->getSystemConfigValues(self::AUTOCOMPLTE_SHOW_PRICE);
    }
    public function getAutocompletedesc(){
     return $this->getSystemConfigValues(self::AUTOCOMPLETE_SHOW_DESC);
    }
     public function getAutocompletemaxdescline(){
     return $this->getSystemConfigValues(self::AUTOCOMPLETE_MAX_DESC_LINE);
     }
    public function getSeeall(){
     return $this->getSystemConfigValues(self::SEE_ALL_BUTTON);
    }
     public function getAutocompleteOutofstock(){
     return $this->getSystemConfigValues(self::AUTOCOMPLETE_OUT_OF_STOCK);
    }
    public function getInstantsku(){
      return $this->getSystemConfigValues(self::SEARCH_SHOW_SKU);
     }
    public function getInstantprice(){
     return $this->getSystemConfigValues(self::SEARCH_SHOW_PRICE);
    }
    public function getInstantdesc(){
     return $this->getSystemConfigValues(self::SEARCH_SHOW_DESC);
    }
     public function getInstantmaxdescline(){
     return $this->getSystemConfigValues(self::SEARCH_MAX_DESC_LINE);
     }
    public function getInstanttitle(){
     return $this->getSystemConfigValues(self::SEARCH_MAX_TITLE_LINE);
     }
     public function getInstantflipImghover(){
     return $this->getSystemConfigValues(self::SEARCH_FLIP_IMG_HOVER);
     }
     public function getCategoryInstantsku(){
        return $this->getSystemConfigValues(self::CATEGORY_SHOW_SKU);
       }
      public function getCategoryInstantprice(){
       return $this->getSystemConfigValues(self::CATEGORY_SHOW_PRICE);
      }
      public function getCategoryInstantdesc(){
       return $this->getSystemConfigValues(self::CATEGORY_SHOW_DESC);
      }
       public function getCategoryInstantmaxdescline(){
       return $this->getSystemConfigValues(self::CATEGORY_MAX_DESC_LINE);
       }
      public function getCategoryInstanttitle(){
       return $this->getSystemConfigValues(self::CATEGORY_MAX_TITLE_LINE);
      }
      public function getCategoryInstantflipImghover(){
        return $this->getSystemConfigValues(self::CATEGORY_FLIP_IMG_HOVER);
       }
       public function getNoProductShown(){
        return $this->getSystemConfigValues(self::CATEGORY_NO_OF_PRODUCT);
       }
       public function getCategoryShowoutofStock(){
        return $this->getSystemConfigValues(self::CATEGORY_OUT_OF_STOCK);
       }
       public function getInstantShowoutofStock(){
        return $this->getSystemConfigValues(self::SEARCH_OUT_OF_STOCK);
       }
       public function getCategorySearchFilters(){
        $category = $this->registry->registry('current_category');
        $filters = null;
        $categoryId = null;
        if ($category) {
            $categoryId = $category->getId();
        }
        if($categoryId && $category->getData('enable_conversion_category') == 1 ){
            $filters = $category->getData('conversion_categories_facet');
            if($filters){
            $outputArray = [];
            foreach ($filters as $filter) {
                $uniqueKey = "_" .round(microtime(true) * 1000) . '_'. rand(100, 999);
                $outputArray[$uniqueKey] = [
                    "filterAttribute" => $filter["filterAttribute"],
                    "facet" => $filter["facet"],
                    "fieldName" => $filter["fieldName"],
                    "filterOption" => (string) $filter["filterOption"] // Ensure it's a string
                ];
            }
            return $outputArray;
           }
            }
        else{
        $filters = $this->getSystemConfigValues(self::CATEGORY_FILTERS);
        if ($filters) {
            $filters = $this->generalModel->decodeData($filters);
            return $filters;
        }
         }
        return [];
       }
       public function enableCategorySlider(){
        $category = $this->registry->registry('current_category');
        $filters = null;
        $categoryId = null;
        if ($category) {
            $categoryId = $category->getId();
        }
        if($categoryId && $category->getData('enable_conversion_category') == 1 ){
         $filterCollection = $category->getData('conversion_categories_facet');
         if($filterCollection){
            foreach($filterCollection as $filter){
                if($filter['facet'] == 'slider' && $filter['filterAttribute'] == 'price'){
                    return 1;
                }else{
                    return 0;
                }
            }
         }
        }else{
        $filterCollection = $this->getCategorySearchFilters();
        foreach($filterCollection as $filter){
            if($filter['facet'] == 'slider' && $filter['filterAttribute'] == 'price'){
                return 1;
            }else{
                return 0;
            }
        }
       }
       return 0;
    }
       public function getCategorySortOptions(){
        $category = $this->registry->registry('current_category');
        $filters = null;
        $categoryId = null;
        if ($category) {
            $categoryId = $category->getId();
        }
        if($categoryId && $category->getData('enable_conversion_category') == 1 ){
            $filters = $category->getData('conversion_categories_sortorder');
            if($filters){
            $outputArray = [];
             foreach ($filters as $filter) {
                 $uniqueKey = "_" .round(microtime(true) * 1000) . '_'. rand(100, 999);
                 $outputArray[$uniqueKey] = [
                     "sortAttribute" => $filter["sortAttribute"],
                     "sortDirection" => $filter["sortDirection"],
                     "fieldName" => $filter["fieldName"],
                     "position" => (string) $filter["position"] // Ensure it's a string
                 ];
             }
             return $outputArray;
            }
            }else{
            $sortOption = $this->getSystemConfigValues(self::CATEGORY_SORT_ATTRIBUTES);
            if ($sortOption) {
                $sortOption = $this->generalModel->decodeData($sortOption);
               // print_r($sortOption);exit(0);
                return $sortOption;
            }
           }
        return [];
       }
    /**
     * Cron staus
     *
     * @param void
     * @return string
     */
    public function isCronEnbaled()
    {
        return $this->getSystemConfigValues(self::CRON_STATUS);
    }

    /**
     * Cron Time
     *
     * @param void
     * @return string
     */
    public function getCronTime()
    {
        return $this->getSystemConfigValues(self::CRON_TIME);
    }

    /**
     * Batch Size
     *
     * @param void
     * @return string
     */
    public function getBatchSize()
    {
        return $this->getSystemConfigValues(self::BATCH_SIZE);
    }

    /**
     * Clear records
     *
     * @param void
     * @return string
     */
    public function clearRecords()
    {
        return $this->getSystemConfigValues(self::CLEAR_RECORDS);
    }

    /**
     * Nearest Node
     *
     * @param void
     * @return string
     */
    public function nearestNodes()
    {
        return $this->getSystemConfigValues(self::NEAREST_NODE);
    }
}
