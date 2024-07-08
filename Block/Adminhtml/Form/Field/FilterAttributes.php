<?php
declare(strict_types=1);

namespace Thecommerceshop\Predictivesearch\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Thecommerceshop\Predictivesearch\Block\Adminhtml\Form\Field\AttributeList;
use Thecommerceshop\Predictivesearch\Block\Adminhtml\Form\Field\FacetOptions;
use Thecommerceshop\Predictivesearch\Block\Adminhtml\Form\Field\FacetType;

class FilterAttributes extends AbstractFieldArray
{
    /**
     * @var AttributeList
     */
    private $attributesRenderer;

    /**
     * @var FacetOptions
     */
    private $facetOptionRenderer;
    /**
     * @var FacetType
     */
    private $facetTypeRenderer;

    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    protected function _prepareToRender()
    {
        $this->addColumn('filterAttribute', [
            'label' => __('Attributes'),
            'renderer' => $this->getAttributesRenderer()
        ]);
         $this->addColumn(
             'facet',
             [
                'label' => __('Facet type'),
                'class' => 'required-entry',
                 'renderer' => $this->getFacetTypeRenderer()
             ]
         );
         $this->addColumn(
             'fieldName',
             [
                'label' => __('Label'),
                'class' => 'required-entry',
             ]
         );
        $this->addColumn('filterOption', [
            'label' => __('Options'),
            'renderer' => $this-> getFacetOptionRenderer()
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

        $filterAttribute = $row->getFilterAttribute();
        if ($filterAttribute !== null) {
            $options[
                'option_' . $this->getAttributesRenderer()->calcOptionHash($filterAttribute)
            ] = 'selected="selected"';
        }

        $filterOption = $row->getFilterOption();
        if ($filterOption !== null) {
            $options[
                'option_' . $this->getFacetOptionRenderer()->calcOptionHash($filterOption)
            ] = 'selected="selected"';
        }
      $filterFacet = $row->getFacet();
        if ($filterOption !== null) {
            $options[
                'option_' . $this->getFacetTypeRenderer()->calcOptionHash($filterFacet)
            ] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }

    /**
     * Attribute Render
     *
     * @return AttributeList
     * @throws LocalizedException
     */
    private function getAttributesRenderer()
    {
        if (!$this->attributesRenderer) {
            $this->attributesRenderer = $this->getLayout()->createBlock(
                AttributeList::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->attributesRenderer;
    }

    /**
     * Facet Option Render
     *
     * @return FacetOptions
     * @throws LocalizedException
     */
    private function getFacetOptionRenderer()
    {
        if (!$this->facetOptionRenderer) {
            $this->facetOptionRenderer = $this->getLayout()->createBlock(
                FacetOptions::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->facetOptionRenderer;
    }
      /**
     * Facet Option Render
     *
     * @return FacetOptions
     * @throws LocalizedException
     */
    private function getFacetTypeRenderer()
    {
        if (!$this->facetTypeRenderer) {
            $this->facetTypeRenderer = $this->getLayout()->createBlock(
                FacetType::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->facetTypeRenderer;
    }
}
