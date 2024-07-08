<?php

declare(strict_types=1);

namespace Thecommerceshop\Predictivesearch\Block\Adminhtml\Form\Field\Products;

use Magento\Framework\View\Element\Html\Select;
use Magento\Eav\Model\Config;
use Magento\Framework\View\Element\Context;

class RankingOptions extends Select
{

    /**
     * @var Config
     */
    private $eavConfig;
 
    /**
     * CmsPages constructor.
     *
     * @param Context $context
     * @param Config $eavConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $eavConfig,
        array $data = []
    ) {
        $this->eavConfig = $eavConfig;
        parent::__construct($context, $data);
    }
    /**
     * Set "name" for <select> element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Set "id" for <select> element
     *
     * @param int $value
     * @return $this
     */
    public function setInputId($value)
    {
        return $this->setId($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getSourceOptions());
        }
        return parent::_toHtml();
    }

    /**
     * Get Source Options
     *
     * @return array
     */
    public function getSourceOptions(): array
    {
        $attributes = [
            [
               'value' => 'name',
               'label' => __('Name'),
            ],
            [
                'value' => 'created_at',
                'label' => __('Created At'),
            ],
            [
                'value' => 'price',
                'label' => __('Price'),
            ],
            [
                'value' => 'special_price',
                'label' => __('Special Price'),
            ],
            [
                'value' => 'new',
                'label' => __('New'),
            ],
            [
                'value' => 'rating_summary',
                'label' => __('Rating Summary'),
            ],
            [
                'value' => 'stock_qty',
                'label' => __('Stock Qty'),
            ]
        ];
        return $attributes;
    }
}
