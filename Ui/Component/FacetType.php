<?php
namespace Conversionbox\Predictivesearch\Ui\Component;

use Magento\Framework\Option\ArrayInterface;

class FacetType implements ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['label' => 'Conjunctive', 'value' => 'conjunctive'],
            ['label' => 'Disjunctive', 'value' => 'disjunctive'],
            ['label' => 'Slider', 'value' => 'slider'],
        ];
    }
}