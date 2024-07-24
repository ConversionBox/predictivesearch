<?php

declare(strict_types=1);

namespace Conversionbox\Predictivesearch\Model;

use Magento\Framework\Model\AbstractModel;
use Conversionbox\Predictivesearch\Model\ResourceModel\TypesenseSearch as TypesenseSearchResource;

class TypesenseSearch extends AbstractModel
{
   /**
    * Constuct
    *
    * @return void
    */
    public function _construct()
    {
        $this->_init(TypesenseSearchResource::class);
    }
}
