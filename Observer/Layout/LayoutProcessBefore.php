<?php
declare(strict_types=1);

namespace Thecommerceshop\Predictivesearch\Observer\Layout;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Thecommerceshop\Predictivesearch\Model\ConfigData;

class LayoutProcessBefore implements ObserverInterface
{
    /**
     * @var ConfigData
     */
    private $configData;

    /**
     * Layout constructor
     *
     * @param ConfigData $configData
     */
    public function __construct(
        ConfigData $configData
    ) {
        $this->configData = $configData;
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
            if ($this->configData->getAdminApiKey() ) {
                $layout = $observer->getData('layout');
                $layout->getUpdate()->addHandle('typsense_search_handle');
            }
        }
    }
}
