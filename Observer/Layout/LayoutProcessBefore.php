<?php
declare(strict_types=1);

namespace Conversionbox\Predictivesearch\Observer\Layout;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Conversionbox\Predictivesearch\Model\ConfigData;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Layer\Resolver;

class LayoutProcessBefore implements ObserverInterface
{
    /**
     * @var ConfigData
     */
    private $configData;
    protected $layerResolver;

    /**
     * Layout constructor
     *
     * @param ConfigData $configData
     */
    public function __construct(
        ConfigData $configData,
        Resolver $layerResolver
    ) {
        $this->configData = $configData;
        $this->layerResolver = $layerResolver;
    }

    /**
     * Execute function
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if ($this->configData->getModuleStatus()) {

            $category = $this->layerResolver->get()->getCurrentCategory();
            if ($category && $category->getData('enable_conversion_category') == 1) {
                $layout = $observer->getData('layout');
                $layout->getUpdate()->addHandle('typesense_category_handle');
            }
            elseif(!$category && $this->configData->getAdminApiKey() ) {
                $layout = $observer->getData('layout');
                $layout->getUpdate()->addHandle('typsense_search_handle');
            }
        }
    }
}
