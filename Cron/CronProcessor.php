<?php

declare(strict_types=1);

namespace Conversionbox\Predictivesearch\Cron;

use Conversionbox\Predictivesearch\Model\Queue;
use Conversionbox\Predictivesearch\Model\ConfigData;
use Conversionbox\Predictivesearch\Model\ResourceModel\TypesenseSearch\CollectionFactory;
use Conversionbox\Predictivesearch\Api\TypesenseSearchRepositoryInterface;

class CronProcessor
{
    /**
     * @var Queue
     */
    private $queue;

    /**
     * @var ConfigData
     */
    private $configData;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var TypesenseSearchRepositoryInterface
     */
    private $typesenseSearchRepositoryInterface;

    /**
     * Cron constructor
     *
     * @param Queue $queue
     * @param ConfigData $configData
     * @param CollectionFactory $collectionFactory
     * @param TypesenseSearchRepositoryInterface $typesenseSearchRepositoryInterface
     */
    public function __construct(
        Queue $queue,
        ConfigData $configData,
        CollectionFactory $collectionFactory,
        TypesenseSearchRepositoryInterface $typesenseSearchRepositoryInterface
    ) {
        $this->queue = $queue;
        $this->configData = $configData;
        $this->collectionFactory = $collectionFactory;
        $this->typesenseSearchRepositoryInterface = $typesenseSearchRepositoryInterface;
    }

    /**
     * Execute function
     *
     * @param void
     * @return void
     */
    public function execute()
    {

        if ($this->configData->clearRecords()) {
            $this->clearData();
        }    
        $this->queue->categoryDataSyncProcessor();
        $this->queue->cmsDataSyncProcessor();
        $this->queue->productDataSyncProcessor();
    }

    /**
     * Clear records
     *
     * @param void
     * @return void
     */
    public function clearData()
    {
        $collectionFactory = $this->collectionFactory->create();
        $collectionFactory->addFieldToFilter('job_status', 1);
        foreach ($collectionFactory as $data) {
            $this->typesenseSearchRepositoryInterface->deleteById($data->getId());
        }
    }
}
