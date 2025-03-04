<?php

namespace Conversionbox\Predictivesearch\Model\ResourceModel\LandingPage;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Conversionbox\Predictivesearch\Model\LandingPage::class,
            \Conversionbox\Predictivesearch\Model\ResourceModel\LandingPage::class
        );
    }
}
