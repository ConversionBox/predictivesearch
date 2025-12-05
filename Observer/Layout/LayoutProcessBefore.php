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
            'weltpixel_quickview_catalog_product_view'
        ];
        
        // Handle category page with Typesense
        if ($fullActionName === 'catalog_category_view') {
            $category = $this->layerResolver->get()->getCurrentCategory();
            if (($category && $category->getData('enable_conversion_category') == 1) || 
                ($this->configData->getCategorypageEnabled() == 1)) {
                $layout->getUpdate()->addHandle('typesense_category_handle');
            }
        }
        
        // Load search result handle only on search result page for page speed optimization
        // This loads the Typesense configuration and search result components
        if ($fullActionName === 'catalogsearch_result_index') {
            $layout->getUpdate()->addHandle('typesense_search_result_handle');
        }
     if( $this->configData->getAutocompleteEnabled() == 1 && 
-            !in_array($fullActionName, $excludedActions)) {
                $layout = $observer->getData('layout');
                $layout->getUpdate()->addHandle('typsense_search_handle');
            }
    }
}
