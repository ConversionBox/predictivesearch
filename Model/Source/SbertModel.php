<?php

declare(strict_types=1);

namespace Thecommerceshop\Predictivesearch\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class SbertModel implements ArrayInterface
{
    /**
     * To Option Array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => '',         'label' => __('Select Model')],
            ['value' => 'ts/all-MiniLM-L12-v2', 'label' => __('ts/all-MiniLM-L12-v2')],
        ];
    }
}
