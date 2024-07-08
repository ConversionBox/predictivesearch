<?php

declare(strict_types=1);

namespace Thecommerceshop\Predictivesearch\Block\Adminhtml\Form\Field\Category;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Thecommerceshop\Predictivesearch\Block\Adminhtml\Form\Field\SortDirection;
use Thecommerceshop\Predictivesearch\Block\Adminhtml\Form\Field\Category\AttributeOptions;

class CustomRankingCategory extends AbstractFieldArray
{
    /**
     * @var SortDirection
     */
    private $orderType;

    /**
     * @var AttributeOptions
     */
    private $attributeOptions;

    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    protected function _prepareToRender()
    {
        $this->addColumn('categoryRankingAttr', [
            'label' => __('Attribute'),
            'renderer' => $this->getAttributeRenderer()
        ]);
        $this->addColumn('categorySortOrder', [
            'label' => __('Order'),
            'renderer' => $this->orderRenderer()
        ]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];
        $order = $row->getCategorySortAttr();
        if ($order !== null) {
            $options['option_' . $this->orderRenderer()->calcOptionHash($order)] = 'selected="selected"';
        }
        $categoryAttr = $row->getCategoryRankingAttr();
        if ($categoryAttr !== null) {
            $options['option_' . $this->getAttributeRenderer()->calcOptionHash($categoryAttr)] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }

    /**
     * Order Renderer
     *
     * @return SortDirection
     * @throws LocalizedException
     */
    private function orderRenderer()
    {
        if (!$this->orderType) {
            $this->orderType = $this->getLayout()->createBlock(
                SortDirection::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->orderType;
    }

    /**
     * Get Attribute Renderer
     *
     * @return AttributeOptions
     * @throws LocalizedException
     */
    private function getAttributeRenderer()
    {
        if (!$this->attributeOptions) {
            $this->attributeOptions = $this->getLayout()->createBlock(
                AttributeOptions::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->attributeOptions;
    }
}
