<?php
declare(strict_types=1);

namespace Thecommerceshop\Predictivesearch\Observer\Page;

use Exception;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Thecommerceshop\Predictivesearch\Model\DataProcessor\PageDataProcessor;
use Thecommerceshop\Predictivesearch\Logger\Logger;
use Thecommerceshop\Predictivesearch\Model\ConfigData;
use Thecommerceshop\Predictivesearch\Model\Queue\QueueProcessor;
use Magento\Store\Api\StoreRepositoryInterface;

class PageChangeAfter implements ObserverInterface
{
    /**
     * @var PageDataProcessor
     */
    private $pageDataProcessor;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var ConfigData
     */
    private $configData;

    /**
     * @var QueueProcessor
     */
    private $queueProcessor;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepositoryInterface;

    /**
     * Page Observer
     *
     * @param PageDataProcessor $pageDataProcessor
     * @param Logger $logger
     * @param ConfigData $configData
     * @param QueueProcessor $queueProcessor
     * @param StoreRepositoryInterface $storeRepositoryInterface
     */
    public function __construct(
        PageDataProcessor $pageDataProcessor,
        Logger $logger,
        ConfigData $configData,
        QueueProcessor $queueProcessor,
        StoreRepositoryInterface $storeRepositoryInterface
    ) {
        $this->pageDataProcessor = $pageDataProcessor;
        $this->logger = $logger;
        $this->configData = $configData;
        $this->queueProcessor = $queueProcessor;
        $this->storeRepositoryInterface = $storeRepositoryInterface;
    }

    /**
     * Execute
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $page = $observer->getEvent()->getObject();
        try {
            //update page data to typesense after page save
            if ($page->getId()) {
                if ($this->configData->isCronEnbaled()) {
                    $storeId = $page->getStoreId()[0];
                    if ($page->getStoreId()[0] == 0) {
                        $storeId = 1;
                    }
                    $storeData = $this->storeRepositoryInterface->getById($storeId);
                    $this->queueProcessor->processPageUpdateQueue($page->getId(), $storeData->getCode());
                } else {
                    $this->pageDataProcessor->importDataToTypeSense([$page->getId()]);
                }
            }
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
