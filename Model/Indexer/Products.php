<?php
declare(strict_types=1);

namespace Thecommerceshop\Predictivesearch\Model\Indexer;

use Thecommerceshop\Predictivesearch\Model\DataProcessor\ProductDataProcessor;

class Products implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    /**
     * @var ProductDataProcessor
     */
    private $productDataProcessor;

    /**
     * Indexer Constructor
     *
     * @param ProductDataProcessor $productDataProcessor
     */
    public function __construct(
        ProductDataProcessor $productDataProcessor
    ) {
        $this->productDataProcessor = $productDataProcessor;
    }

    /**
     * Allows process indexer in the "Update on schedule" mode
     *
     * @param array $ids
     * @return void
     */
    public function execute($ids)
    {
        $this->productDataProcessor->importDataToTypeSense($ids);
    }

    /**
     * Will take all of the data and reindex
     *
     * @param void
     * @return void
     */
    public function executeFull()
    {
        $this->execute(null);
    }

    /**
     * Works with a set of entity changed (may be massaction)
     *
     * @param array $ids
     * @return void
     */
    public function executeList(array $ids)
    {
        $this->execute($ids);
    }

    /**
     * Works in runtime for a single entity using plugins
     *
     * @param int $id
     * @return void
     */
    public function executeRow($id)
    {
        $this->execute([$id]);
    }
}
