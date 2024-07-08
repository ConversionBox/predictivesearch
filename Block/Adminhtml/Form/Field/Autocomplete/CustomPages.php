<?php

declare(strict_types=1);

namespace Thecommerceshop\Predictivesearch\Block\Adminhtml\Form\Field\Autocomplete;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Thecommerceshop\Predictivesearch\Block\Adminhtml\Form\Field\Autocomplete\Page;

class CustomPages extends AbstractFieldArray
{
    /**
     * @var Page
     */
    private $pageRender;

    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    protected function _prepareToRender()
    {
        $this->addColumn('page', [
            'label' => __('Page'),
            'renderer' => $this->getPageRenderer()
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

        $page = $row->getPage();
        if ($page !== null) {
            $options['option_' . $this->getPageRenderer()->calcOptionHash($page)] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }

    /**
     * Get Page Render
     *
     * @return Page
     * @throws LocalizedException
     */
    private function getPageRenderer()
    {
        if (!$this->pageRender) {
            $this->pageRender = $this->getLayout()->createBlock(
                Page::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->pageRender;
    }
}
