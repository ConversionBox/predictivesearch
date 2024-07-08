<?php

declare(strict_types=1);

namespace Thecommerceshop\Predictivesearch\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class GteModel implements ArrayInterface
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
            ['value' => 'ts/gte-small', 'label' => __('ts/gte-small')],
        ];
    }
}
