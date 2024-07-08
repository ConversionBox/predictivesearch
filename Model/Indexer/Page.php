<?php

declare(strict_types=1);

namespace Thecommerceshop\Predictivesearch\Model\Indexer;

use Thecommerceshop\Predictivesearch\Model\DataProcessor\PageDataProcessor;

class Page implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    /**
     * @var PageDataProcessor
     */
    private $pageDataProcessor;

    /**
     * Indexer Constructor
     *
     * @param PageDataProcessor $pageDataProcessor
     */
    public function __construct(
        PageDataProcessor $pageDataProcessor
    ) {
        $this->pageDataProcessor = $pageDataProcessor;
    }

    /**
     * Used by mview, allows process indexer in the "Update on schedule" mode
     *
     * @param array $ids
     * @return void
     */
    public function execute($ids)
    {
        $this->pageDataProcessor->importDataToTypeSense($ids);
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
     *  Works in runtime for a single entity using plugins
     *
     * @param int $id
     * @return void
     */
    public function executeRow($id)
    {
        $this->execute([$id]);
    }
}
