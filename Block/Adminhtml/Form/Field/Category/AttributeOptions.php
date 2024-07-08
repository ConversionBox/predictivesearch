<?php

declare(strict_types=1);

namespace Thecommerceshop\Predictivesearch\Block\Adminhtml\Form\Field\Category;

use Magento\Framework\View\Element\Html\Select;
use Magento\Eav\Model\Config;
use Magento\Framework\View\Element\Context;
use Thecommerceshop\Predictivesearch\Model\Schema\CategorySchema;

class AttributeOptions extends Select
{

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var CategorySchema
     */
    private $categorySchema;

    /**
     * CmsPages constructor.
     *
     * @param Context $context
     * @param Config $eavConfig
     * @param CategorySchema $categorySchema
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $eavConfig,
        CategorySchema $categorySchema,
        array $data = []
    ) {
        $this->eavConfig = $eavConfig;
        $this->categorySchema = $categorySchema;
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
        if (isset($this->categoryAttributes)) {
            return $this->categoryAttributes;
        }

        $this->categoryAttributes = [];

        $allAttributes = $this->eavConfig->getEntityAttributeCodes('catalog_category');

        $categoryAttributes = array_merge($allAttributes, ['product_count']);

        $categoryAttributes = array_diff($categoryAttributes, $this->categorySchema->exculdedAttributes());

        foreach ($categoryAttributes as $attributeCode) {
            /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
            $attribute = $this->eavConfig->getAttribute('catalog_category', $attributeCode);
            $this->categoryAttributes[$attributeCode] = $attribute->getData('frontend_label');
        }

        return $this->categoryAttributes;
    }
}
