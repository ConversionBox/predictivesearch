<?php
namespace Conversionbox\Predictivesearch\Ui\Component;
use Magento\Framework\Option\ArrayInterface;

class Sortorder implements ArrayInterface
{
    public function toOptionArray()
    {
        $response = [
            [
               'value' => 'asc',
               'label' => __('Ascending'),
            ],
            [
                'value' => 'desc',
                'label' => __('Descending'),
             ]
        ];
     
        return $response;
    }
}