<?php

declare(strict_types=1);

namespace Thecommerceshop\Predictivesearch\Model\Source;

use Magento\Framework\Option\ArrayInterface;
use Thecommerceshop\Predictivesearch\Model\Api\TypeSenseApi;

class IndexList implements ArrayInterface
{
    /**
     * Option List
     *
     * @var array
     */
    private $options;
    
    /**
     * @var TypeSenseApi
     */
    private $typeSenseApi;

    /**
     * Constructor
     *
     * @param TypeSenseApi $typeSenseApi
     */
    public function __construct(
        TypeSenseApi $typeSenseApi
    ) {
        $this->typeSenseApi = $typeSenseApi;
    }

    /**
     * To Option Array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $indexCollection = $this->typeSenseApi->retriveCollectionData();
        $this->options = [['label' => 'select Index', 'value' => '']];
        if (is_iterable($indexCollection)) {
            foreach ($indexCollection as $key => $value) {
                $this->options[] = [
                    'label' => __($value),
                    'value' => $value
                ];
            }
        }
        return $this->options;
    }
}
