<?php

declare(strict_types=1);

namespace Conversionbox\Predictivesearch\Block;

use Magento\Framework\Data\CollectionDataSourceInterface;
use Magento\Framework\View\Element\Template;
use Conversionbox\Predictivesearch\Model\ConfigData;
use Magento\Framework\DataObject;
use Conversionbox\Predictivesearch\Model\General;
use Magento\Framework\Registry;
use Magento\Search\Model\ResourceModel\Query\CollectionFactory;
use Magento\Framework\DataObjectFactory;
use Magento\Directory\Model\Currency;
use Magento\Catalog\Helper\ImageFactory as HelperFactory;
use Magento\Framework\View\Asset\Repository;

class Configuration extends Template implements CollectionDataSourceInterface
{
    /**
     * @var ConfigData
     */
    private $configData;

    /**
     * @var General
     */
    private $generaModel;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var Currency
     */
    private $currency;

    /**
     * @var HelperFactory
     */
    private $helperFactory;

    /**
     * @var Repository
     */
    private $assetRepos;

    /**
     * Configuration constructor
     *
     * @param Template\Context $context
     * @param ConfigData $configData
     * @param General $generaModel
     * @param Registry $registry
     * @param CollectionFactory $collectionFactory
     * @param DataObjectFactory $dataObjectFactory
     * @param Currency $currency
     * @param HelperFactory $helperFactory
     * @param Repository $repository
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        ConfigData $configData,
        General $generaModel,
        Registry $registry,
        CollectionFactory $collectionFactory,
        DataObjectFactory $dataObjectFactory,
        Currency $currency,
        HelperFactory $helperFactory,
        Repository $repository,
        array $data = []
    ) {
        $this->configData = $configData;
        $this->generaModel = $generaModel;
        $this->registry = $registry;
        $this->collectionFactory = $collectionFactory;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->currency = $currency;
        $this->assetRepos = $repository;
        $this->helperFactory = $helperFactory;

        parent::__construct($context, $data);
    }

    /**
     * Get configuration data
     *
     * @param void
     * @return object
     */
    public function getConfigurationData()
    {
        $response = [
            'general' => [
                'enabled' => $this->configData->getModuleStatus(),
                'cloudKey' => $this->generaModel->base64Encode($this->configData->getHost()),
                'searchApikey' => $this->generaModel->base64Encode($this->configData->getSearchApiKey()),
                'adminApiKey' => $this->generaModel->base64Encode($this->configData->getAdminApiKey()),
                'indexprefix' => $this->configData->getIndexPrefix(),
                'node' => $this->generaModel->base64Encode($this->configData->getNode()),
                'nearest_node' => $this->generaModel->base64Encode($this->configData->nearestNodes()),
                'unique_id' => $this->configData->getUniqueId(),
                'protocol' => $this->configData->getProtocol(),
                'port' => $this->configData->getPort(),
                'highlights' => $this->configData->isHighlightEnabled(),
                'store_currency' => $this->getStoreCurrency(),
                'storeCode' => $this->getStoreCode(),
                'placeholder' => $this->getPlaceHolderImage(),
                'grid_per_value' => $this->configData->getGridPerValue(),
            ],
            'search_result' => [
                'no_products' => $this->configData->getNoProductsPage(),
                'search_filters' => $this->configData->getSearchFilters(),
                'sort_options' => $this->configData->getSortOptions(),
                'addto_cart' => $this->configData->getEnableAddToCart(),
                'price_slider' => $this->configData->enableSlider(),
                'image_type' => $this->configData->getImageType(),
                'image_height' => $this->configData->getImageHeight(),
                'image_width' => $this->configData->getImageWidth()
            ],
            'auto_complete' => [
                'no_products' => $this->configData->getProductCount(),
                'category_enabled' => $this->configData->getEnableCategorySearch(),
                'category_count' => $this->configData->getCategoryCount(),
                'pages_enabled' => $this->configData->getEnablePageSearch(),
                'pages_count' => $this->configData->getPageCount(),
                'excluded_page' => $this->configData->getExcludedPages(),
                'suggestions' => $this->configData->showSuggestions(),
                'suggestions_count' => $this->configData->getSuggestionsCount(),
                'minimum_char_length' => $this->configData->getMinimumChar(),
            ],
            'products' => [
                'attributes' => $this->configData->getProductAttributeConfig(),
                'display_sku' => $this->configData->getShowSku(),
                'display_price' => $this->configData->getShowPrice(),
                'ranking' => $this->makeRankingQuery()
            ],
            'category' => [
                'name' => $this->getCurrentCategory(),
                'attributes' => $this->configData->getCategoryAttributeConfig(),
                'ranking' => $this->categoryRankingQuery()
            ],
            'search_terms' => [
                'data' => $this->getPopularTerms()
            ],
        'typotolerance' => [
               'enable' => $this->configData->isTypoEnabled(),
                'word_length' => $this->configData->minimumLength()
             ]
        ];

        $responseObject = $this->dataObjectFactory->create();
        $responseObject->setItem($response);

        return $this->generaModel->encodeData($responseObject->getItem());
    }

    /**
     * Get store Currency
     */
    public function getStoreCurrency()
    {
        return $this->currency->getCurrencySymbol();
    }

    /**
     * Get storeCode
     *
     * @param void
     * @return string
     */
    public function getStoreCode()
    {
        return $this->generaModel->getStore()->getCode();
    }

    /**
     * Get popular search terms
     */
    public function getPopularTerms()
    {
        $response = [];
        $queryCollection = $this->collectionFactory->create();
        foreach ($queryCollection as $data) {
            $response[] = $data->getQueryText();
        }
        $response = array_slice($response, -6);
        return $response;
    }

    /**
     * Get Current Category
     *
     * @param void
     * @return string
     */
    public function getCurrentCategory()
    {
        $category = $this->registry->registry('current_category');
        $categoryId = null;
        if ($category) {
            $categoryId = $category->getId();
        }
        return $categoryId;
    }

    /**
     * Make Ranking Query
     */
    public function makeRankingQuery()
    {
        $rankingQuery = '';
        foreach ($this->configData->getRanking() as $item) {
            $rankingQuery .= $item['rankingAttribute'].':'.$item['ascDescOrder'].',';
        }
        $rankingQuery = substr($rankingQuery, 0, -1);
        return $rankingQuery;
    }

    /**
     * Make Category Ranking Query
     */
    public function categoryRankingQuery()
    {
        $rankingQuery = '';
        foreach ($this->configData->getCategoryRanking() as $item) {
            $rankingQuery .= $item['categoryRankingAttr'].':'.$item['categorySortOrder'].',';
        }
        $rankingQuery = substr($rankingQuery, 0, -1);
        return $rankingQuery;
    }

    /**
     * Get PlaceHolder Image
     *
     * @param void
     * @return string
     */
    public function getPlaceHolderImage()
    {
        if ($this->configData->getPlaceHolderImage()) {
            return $this->configData->getPlaceHolderImage();
        } else {
            /** @var \Magento\Catalog\Helper\Image $helper */
            $helper = $this->helperFactory->create();
            return $this->assetRepos->getUrl($helper->getPlaceholder('small_image'));
        }
    }
}
