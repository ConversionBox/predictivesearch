<?php

declare(strict_types=1);

namespace Thecommerceshop\Predictivesearch\Observer\Category;

use Exception;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Thecommerceshop\Predictivesearch\Model\DataProcessor\CategoryDataProcessor;
use Thecommerceshop\Predictivesearch\Logger\Logger;
use Magento\Framework\App\RequestInterface;
use Thecommerceshop\Predictivesearch\Model\ConfigData;
use Thecommerceshop\Predictivesearch\Model\Queue\QueueProcessor;
use Magento\Store\Api\StoreRepositoryInterface;

class CategoryChangeAfter implements ObserverInterface
{
    /**
     * @var CategoryDataProcessor
     */
    private $categoryDataProcessor;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var RequestInterface
     */
    private $requestInterface;

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
     * Category Observer
     *
     * @param CategoryDataProcessor $categoryDataProcessor
     * @param Logger $logger
     * @param RequestInterface $requestInterface
     * @param ConfigData $configData
     * @param QueueProcessor $queueProcessor
     * @param StoreRepositoryInterface $storeRepositoryInterface
     */
    public function __construct(
        CategoryDataProcessor $categoryDataProcessor,
        Logger $logger,
        RequestInterface $requestInterface,
        ConfigData $configData,
        QueueProcessor $queueProcessor,
        StoreRepositoryInterface $storeRepositoryInterface
    ) {
        $this->categoryDataProcessor = $categoryDataProcessor;
        $this->logger = $logger;
        $this->requestInterface = $requestInterface;
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
        if (!$this->configData->getModuleStatus()) {
            return;
        }

        if (!$this->configData->getAdminApiKey() ||
                !$this->configData->getNode() ||
                !$this->configData->getProtocol()
            ) {
                return;
        }

        $category = $observer->getEvent()->getCategory();
        $storeId = $this->requestInterface->getParam('store_id', 0);
        try {
            //update category data to typesense after category save
            if ($category->getId()) {
                if ($this->configData->isCronEnbaled()) {
                    if ($storeId == 0) {
                        $storeId = 1;
                    }
                    $storeData = $this->storeRepositoryInterface->getById($storeId);
                    $this->queueProcessor->processCategoryUpdateQueue(
                        $category->getId(),
                        $storeData->getCode(),
                        $storeId
                    );
                } else {
                    $this->categoryDataProcessor->importDataToTypeSense([$category->getId()], $storeId);
                }
            }
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
