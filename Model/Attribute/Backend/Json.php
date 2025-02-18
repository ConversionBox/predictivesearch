<?php
namespace Conversionbox\Predictivesearch\Model\Attribute\Backend;

use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;

class Json extends AbstractBackend
{
   /**
    *  After load custom attribute data in json format
    */
    public function afterLoad($object)
    {
        parent::afterLoad($object);
        $value = $object->getData($this->getAttribute()->getAttributeCode());
        if ($value && is_string($value)) {
            $object->setData($this->getAttribute()->getAttributeCode(), json_decode($value, true));
        }
    
    }
}
