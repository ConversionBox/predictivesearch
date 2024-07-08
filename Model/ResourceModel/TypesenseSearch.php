<?php

declare(strict_types=1);

namespace Thecommerceshop\Predictivesearch\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class TypesenseSearch extends AbstractDb
{
    /**
     * Construct function
     */
    public function _construct()
    {
        $this->_init('typesense_job_queue', 'job_id');
    }
}
