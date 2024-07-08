<?php
declare(strict_types=1);

namespace Thecommerceshop\Predictivesearch\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Thecommerceshop\Predictivesearch\Model\General;

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
    private const SHOW_SUGGESTIONS = 'typesense_autocomplete/autocomplete/enable_suggestions';

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
    private const SUGGESTIONS_COUNT = 'typesense_autocomplete/autocomplete/nb_of_suggestions_count';

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
     * @var ScopeConfigInterface
     */
    private $scopeConfigInterface;

    /**
     * @var General
     */
    private $generalModel;

    /**
     * Config Data Provider
     *
     * @param ScopeConfigInterface $scopeConfigInterface
     * @param General $generalModel
     */
    public function __construct(
        ScopeConfigInterface $scopeConfigInterface,
        General $generalModel
    ) {
        $this->scopeConfigInterface = $scopeConfigInterface;
        $this->generalModel = $generalModel;
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
