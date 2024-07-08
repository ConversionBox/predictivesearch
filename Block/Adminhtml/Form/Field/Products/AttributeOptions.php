<?php

declare(strict_types=1);

namespace Thecommerceshop\Predictivesearch\Block\Adminhtml\Form\Field\Products;

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
        $addEmptyRow = false;
        if (!isset($this->productAttributes)) {
            $this->productAttributes = [];

            $allAttributes = $this->eavConfig->getEntityAttributeCodes('catalog_product');

            $productAttributes = array_merge([
                'name',
                'path',
                'categories',
                'categories_without_path',
                'description',
                'ordered_qty',
                'total_ordered',
                'stock_qty',
                'rating_summary',
                'media_gallery',
                'in_stock',
            ], $allAttributes);

            $productAttributes = array_diff($productAttributes, $this->categorySchema->exculdedAttributes());

            foreach ($productAttributes as $attributeCode) {
                $this->productAttributes[$attributeCode] = $this->eavConfig
                    ->getAttribute('catalog_product', $attributeCode)
                    ->getFrontendLabel();
            }
        }

        $attributes = $this->productAttributes;
        
        uksort($attributes, function ($a, $b) {
            return strcmp($a, $b);
        });
        return $attributes;
    }
}
