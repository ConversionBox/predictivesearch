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
          $autocomplete = $this->configData->getAutocompleteEnabled();
        if ($this->request->isXmlHttpRequest() || strpos($this->request->getPathInfo(), '/rest/') !== false) {
            return;
        }
        if ($this->configData->getModuleStatus()) {
            $category = "";
            $category = $this->layerResolver->get()->getCurrentCategory();
            if (($this->request->getFullActionName() ==='catalog_category_view') &&(($category->getData('enable_conversion_category') == 1) || ($this->configData->getCategorypageEnabled() == 1))) {
                $layout = $observer->getData('layout');
                $layout->getUpdate()->addHandle('typesense_category_handle');
            }
            else {
                if($this->configData->getModuleStatus() && $this->configData->getAutocompleteEnabled() == 1 &&
    !in_array($this->request->getFullActionName(), [
        'weltpixel_quickview_catalog_product_view'
    ]) ) {
                $layout = $observer->getData('layout');
                $layout->getUpdate()->addHandle('typsense_search_handle');
            }
        }
    }
}
}
