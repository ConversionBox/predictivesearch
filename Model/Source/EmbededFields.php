<?php

declare(strict_types=1);

namespace Thecommerceshop\Predictivesearch\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class EmbededFields implements ArrayInterface
{
    /**
     * To Option Array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'product_name',         'label' => __('Product Name')],
            ['value' => 'sku', 'label' => __('Sku')],
            ['value' => 'description', 'label' => __('Description')],
            ['value' => 'short_description', 'label' => __('Short Description')],
        ];
    }
}
