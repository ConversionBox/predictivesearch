<?php
namespace Conversionbox\Predictivesearch\Model\Attribute\Backend;

use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;

class Sort extends AbstractBackend
{
    public function beforeSave($object)
    {
        $attributeCode = $this->getAttribute()->getAttributeCode(); 

        $value = $object->getData($this->getAttribute()->getAttributeCode());

        if (is_array($value) && ($value[$attributeCode][0]['record_id'] == 0 &&  $value[$attributeCode][0]['label'] == "") ) {
        $object->setData($attributeCode, json_encode([])); 
    } else {
        $object->setData($attributeCode, json_encode($value));
    }
        return parent::beforeSave($object);
    }

    public function afterLoad($object)
    {
        $value = $object->getData($this->getAttribute()->getAttributeCode());
        if ($value && is_string($value)) {
            $object->setData($this->getAttribute()->getAttributeCode(), json_decode($value, true));
        }
        return parent::afterLoad($object);
    }
}
