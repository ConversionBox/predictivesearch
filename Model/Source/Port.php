<?php
namespace Thecommerceshop\Predictivesearch\Model\Source;

use Magento\Framework\Option\ArrayInterface;
use Thecommerceshop\Predictivesearch\Model\Api\TypeSenseApi;
class Port implements ArrayInterface
{
    protected $typeSenseApi;
    /**
     * Config Data Provider
     *
     * @param ScopeConfigInterface $scopeConfigInterface
     * @param General $generalModel
     */
    public function __construct(
        TypeSenseApi $typeSenseApi,
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
        return [
            ['value' => '','label' => __('Select Port')],
            ['value' => 'http',         'label' => __('http')],
            ['value' => 'https',        'label' => __('https')],
        ];
    }
}