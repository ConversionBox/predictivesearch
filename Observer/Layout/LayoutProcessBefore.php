<?php
declare(strict_types=1);

namespace Conversionbox\Predictivesearch\Observer\Layout;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Conversionbox\Predictivesearch\Model\ConfigData;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\App\RequestInterface;

class LayoutProcessBefore implements ObserverInterface
{
    /**
     * @var ConfigData
     */
    private $configData;
    protected $layerResolver;
    /**
     * @var RequestInterface ;
     */
    protected $request;


    /**
     * Layout constructor
     *
     * @param ConfigData $configData
     */
    public function __construct(
        ConfigData $configData,
        Resolver $layerResolver,
        RequestInterface $request
    ) {
        $this->configData = $configData;
        $this->layerResolver = $layerResolver;
        $this->request = $request;
    }

    /**
     * Execute function
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        // Skip AJAX and REST API requests
        if ($this->request->isXmlHttpRequest() || strpos($this->request->getPathInfo(), '/rest/') !== false) {
            return;
        }
        
        // Check if module is enabled
        if (!$this->configData->getModuleStatus()) {
            return;
        }
        
        $layout = $observer->getData('layout');
        $fullActionName = $this->request->getFullActionName();
        
        // Excluded actions where we don't want to load search
        $excludedActions = [
            'weltpixel_quickview_catalog_product_view',
            'checkout_cart_index',
            'checkout_index_index',
            'checkout_onepage_success'
        ];
        
        // Handle category page with Typesense
        if ($fullActionName === 'catalog_category_view') {
            $category = $this->layerResolver->get()->getCurrentCategory();
            if (($category && $category->getData('enable_conversion_category') == 1) || 
                ($this->configData->getCategorypageEnabled() == 1)) {
                $layout->getUpdate()->addHandle('typesense_category_handle');
            }
        }
        
        // Load search handle for autocomplete on all pages (except excluded actions)
        // This loads the searchAutocomplete.js and multi-search.js components
        if ($this->configData->getAutocompleteEnabled() == 1 && 
            !in_array($fullActionName, $excludedActions)) {
            $layout->getUpdate()->addHandle('typsense_search_handle');
        }
    }
}
