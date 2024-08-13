<?php

declare(strict_types=1);

namespace Conversionbox\Predictivesearch\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;
use Conversionbox\Predictivesearch\Api\Data\TypesenseSearchInterface;

interface TypesenseSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get Items.
     *
     * @return TypesenseSearchInterface[]
     */
    public function getItems();

    /**
     * Set Items.
     *
     * @param TypesenseSearchInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
