<?php

declare(strict_types=1);

namespace Thecommerceshop\Predictivesearch\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\View\Element\Context;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;

class AttributeList extends Select
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * Attribute Constructor
     *
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;

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
     * @param string $value
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
     * Get Source option
     *
     * @param void
     * @return array
     */
    private function getSourceOptions(): array
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $productAttributes */
        $productAttributes = $this->collectionFactory->create();
        $productAttributes->addFieldToFilter(
            ['is_filterable', 'is_filterable_in_search'],
            [[1, 2], 1]
        );

        $response = [];
        foreach ($productAttributes as $item) {
            if ($item->getData('attribute_code') == 'category_gear') {
                $attibuteCode = 'category';
                $categoryLabel = 'Category';
            } else {
                $attibuteCode = $item->getData('attribute_code');
                $categoryLabel = $item->getData('frontend_label');
            }
            
            $response[] = [
                'label' => $categoryLabel,
                'value' => $attibuteCode
            ];
        }

        return $response;
    }
}
