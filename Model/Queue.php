<?php


declare(strict_types=1);

namespace Thecommerceshop\Predictivesearch\Model;

use Thecommerceshop\Predictivesearch\Model\ResourceModel\TypesenseSearch\CollectionFactory;
use Thecommerceshop\Predictivesearch\Model\DataProcessor\ProductDataProcessor;
use Magento\Framework\Serialize\Serializer\Json;
use Thecommerceshop\Predictivesearch\Model\DataProcessor\CategoryDataProcessor;
use Thecommerceshop\Predictivesearch\Model\DataProcessor\PageDataProcessor;

class Queue
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ProductDataProcessor
     */
    private $productDataProcessor;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var CategoryDataProcessor
     */
    private $categoryDataProcessor;

    /**
     * @var PageDataProcessor
     */
    private $pageDataProcessor;

    /**
     * Queue constructor
     *
     * @param CollectionFactory $collectionFactory
     * @param ProductDataProcessor $productDataProcessor
     * @param Json $json
     * @param CategoryDataProcessor $categoryDataProcessor
     * @param PageDataProcessor $pageDataProcessor
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        ProductDataProcessor $productDataProcessor,
        Json $json,
        CategoryDataProcessor $categoryDataProcessor,
        PageDataProcessor $pageDataProcessor
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->productDataProcessor = $productDataProcessor;
        $this->json = $json;
        $this->categoryDataProcessor = $categoryDataProcessor;
        $this->pageDataProcessor = $pageDataProcessor;
    }

    /**
     * Product Data processor
     *
     * @param void
     * @return void
     */
    public function productDataSyncProcessor()
    {
        $collectionFactory = $this->collectionFactory->create();
        $collectionFactory->addFieldToFilter('job_status', 0);
        $collectionFactory->addFieldToFilter('job_type', ['in' => ['productIndex', 'productUpdateIndex']]);
        $firstItem = $collectionFactory->getFirstItem();

        if ($firstItem->getJobId()) {
            $item = $this->json->unserialize($firstItem->getJobData());
            if ($firstItem->getJobType() == 'productUpdateIndex') {
                $this->productDataProcessor->updateProductCron(
                    $item,
                    $firstItem->getJobId(),
                    $firstItem->getJobIndex()
                );
            } else {
                $this->productDataProcessor->syncProductByCron(
                    $item,
                    $firstItem->getJobId(),
                    $firstItem->getJobIndex()
                );
            }
        }
    }

    /**
     * Category Data processor
     *
     * @param void
     * @return void
     */
    public function categoryDataSyncProcessor()
    {
        $collectionFactory = $this->collectionFactory->create();
        $collectionFactory->addFieldToFilter('job_status', 0);
        $collectionFactory->addFieldToFilter('job_type', ['in' => ['categoryIndex', 'categoryUpdateIndex']]);
        $firstItem = $collectionFactory->getFirstItem();
        if ($firstItem->getJobId()) {
            $item = $this->json->unserialize($firstItem->getJobData());
            if ($firstItem->getJobType() == 'categoryUpdateIndex') {
                $this->categoryDataProcessor->updateCategoryByCron(
                    $item,
                    $firstItem->getJobId(),
                    $firstItem->getJobIndex()
                );
            } else {
                $this->categoryDataProcessor->syncCategoryByCron(
                    $item,
                    $firstItem->getJobId(),
                    $firstItem->getJobIndex()
                );
            }
        }
    }

    /**
     * Cms Data processor
     *
     * @param void
     * @return void
     */
    public function cmsDataSyncProcessor()
    {
        $collectionFactory = $this->collectionFactory->create();
        $collectionFactory->addFieldToFilter('job_status', 0);
        $collectionFactory->addFieldToFilter('job_type', ['in' => ['pageIndex', 'pageUpdateIndex']]);
        $firstItem = $collectionFactory->getFirstItem();

        if ($firstItem->getJobId()) {
            if ($firstItem->getJobType() == 'pageUpdateIndex') {
                $item = $this->json->unserialize($firstItem->getJobData());
                $this->pageDataProcessor->updatePagesByCron(
                    $item['pageId'],
                    $item['storeCode'],
                    $firstItem->getJobId()
                );
            } else {
                $this->pageDataProcessor->syncPageByCron(
                    $this->json->unserialize($firstItem->getJobData()),
                    $firstItem->getJobId()
                );
            }
        }
    }
}
