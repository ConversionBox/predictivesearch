<?php
declare(strict_types=1);

namespace Thecommerceshop\Predictivesearch\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Thecommerceshop\Predictivesearch\Block\Adminhtml\Form\Field\SortList;
use Thecommerceshop\Predictivesearch\Block\Adminhtml\Form\Field\SortDirection;

class SortAttributes extends AbstractFieldArray
{
    /**
     * @var SortList
     */
    private $attributesRenderer;

    /**
     * @var SortDirection
     */
    private $sortDirection;

    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    protected function _prepareToRender()
    {
        $this->addColumn('sortAttribute', [
            'label' => __('Attributes'),
            'renderer' => $this->getAttributesRenderer()
        ]);

        $this->addColumn(
            'sortDirection',
            [
                'label' => __('Order'),
                'renderer' => $this->getDropdownRenderer(),
            ]
        );
        $this->addColumn(
            'fieldName',
            [
                'label' => __('Label'),
                'class' => 'required-entry',
            ]
        );

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

        $sortAttribute = $row->getSortAttribute();
        if ($sortAttribute !== null) {
            $options[
                'option_' . $this->getAttributesRenderer()->calcOptionHash($sortAttribute)
            ] = 'selected="selected"';
        }

        $filterDirection = $row->getSortDirection();
        if ($filterDirection !== null) {
            $options[
                'option_' . $this->getDropdownRenderer()->calcOptionHash($filterDirection)
            ] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }

    /**
     * Attributes Render
     *
     * @return SortList
     * @throws LocalizedException
     */
    private function getAttributesRenderer()
    {
        if (!$this->attributesRenderer) {
            $this->attributesRenderer = $this->getLayout()->createBlock(
                SortList::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->attributesRenderer;
    }

    /**
     * DropDown Render
     *
     * @return SortDirection
     * @throws LocalizedException
     */
    private function getDropdownRenderer()
    {
        if (!$this->sortDirection) {
            $this->sortDirection = $this->getLayout()->createBlock(
                SortDirection::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->sortDirection;
    }
}
