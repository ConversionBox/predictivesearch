<?php

declare(strict_types=1);

namespace Thecommerceshop\Predictivesearch\Observer\Product;

use Exception;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Thecommerceshop\Predictivesearch\Model\DataProcessor\ProductDataProcessor;
use Thecommerceshop\Predictivesearch\Logger\Logger;
use Magento\Framework\App\RequestInterface;
use Thecommerceshop\Predictivesearch\Model\ConfigData;
use Thecommerceshop\Predictivesearch\Model\Queue\QueueProcessor;
use Magento\Store\Api\StoreRepositoryInterface;

class ProductChange implements ObserverInterface
{
    /**
     * @var ProductDataProcessor
     */
    private $productDataProcessor;

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
     * Product Observer
     *
     * @param ProductDataProcessor $productDataProcessor
     * @param Logger $logger
     * @param RequestInterface $requestInterface
     * @param ConfigData $configData
     * @param QueueProcessor $queueProcessor
     * @param StoreRepositoryInterface $storeRepositoryInterface
     */
    public function __construct(
        ProductDataProcessor $productDataProcessor,
        Logger $logger,
        RequestInterface $requestInterface,
        ConfigData $configData,
        QueueProcessor $queueProcessor,
        StoreRepositoryInterface $storeRepositoryInterface
    ) {
        $this->productDataProcessor = $productDataProcessor;
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

        $storeId = $this->requestInterface->getParam('store', 0);
        $product = $observer->getProduct();
        try {
            //update page data to typesense after page save
            if ($product->getId()) {
                if ($this->configData->isCronEnbaled()) {
                    if ($storeId == 0) {
                        $storeId = 1;
                    }
                    $storeData = $this->storeRepositoryInterface->getById($storeId);
                    $this->queueProcessor->processProductUpdateQueue(
                        $product->getId(),
                        $storeData->getCode(),
                        $storeId
                    );
                } else {
                    $this->productDataProcessor->importDataToTypeSense([$product->getId()], $storeId);
                }
            }
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
