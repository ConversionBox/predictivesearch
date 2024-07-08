<?php
namespace Thecommerceshop\Predictivesearch\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class ImageType implements ArrayInterface
{
    /**
     * To Option Array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => '',         'label' => __('Select Image Type')],
            ['value' => 'product_base_image',         'label' => __('Base Image')],
            ['value' => 'product_small_image',        'label' => __('Small Image')],
            ['value' => 'product_thumbnail_image',    'label' => __('Thumbnail')],
        ];
    }
}
