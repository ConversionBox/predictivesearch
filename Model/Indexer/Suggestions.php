<?php
declare(strict_types=1);

namespace Conversionbox\Predictivesearch\Model\Indexer;

use Conversionbox\Predictivesearch\Model\DataProcessor\SuggestionDataProcessor;

class Suggestions implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    /**
     * @var SuggestionDataProcessor
     */
    private $suggestionDataProcessor;

    /**
     * Indexer Constructor
     *
     * @param SuggestionDataProcessor $suggestionDataProcessor
     */
    public function __construct(
        SuggestionDataProcessor $suggestionDataProcessor
    ) {
        $this->suggestionDataProcessor = $suggestionDataProcessor;
    }

    /**
     * Used by mview, allows process indexer in the "Update on schedule" mode
     *
     * @param array $ids
     * @return void
     */
    public function execute($ids)
    {
        $this->suggestionDataProcessor->importDataToTypeSense($ids);
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
