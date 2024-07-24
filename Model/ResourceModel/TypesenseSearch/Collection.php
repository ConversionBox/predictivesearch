<?php

declare(strict_types=1);

namespace Conversionbox\Predictivesearch\Model\ResourceModel\TypesenseSearch;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Primary key
     *
     * @var string
     */
    protected $_idFieldName = 'job_id';

    /**
     * Construct function
     */
    public function _construct()
    {
        $this->_init(
            \Conversionbox\Predictivesearch\Model\TypesenseSearch::class,
            \Conversionbox\Predictivesearch\Model\ResourceModel\TypesenseSearch::class
        );
        $this->_idFieldName = 'job_id';
    }
}
