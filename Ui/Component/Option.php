<?php
namespace Conversionbox\Predictivesearch\Ui\Component;
use Magento\Framework\Option\ArrayInterface;

class Option implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['label' => 'Searchable', 'value' => '1'],
            ['label' => 'Not Searchable', 'value' => '2'],
            ['label' => 'Filter Only', 'value' => '3'],
        ];
    }
}