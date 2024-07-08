<?php

declare(strict_types=1);

namespace Thecommerceshop\Predictivesearch\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Html\Select;

class SortDirection extends Select
{
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
        $response = [
            [
               'value' => 'asc',
               'label' => __('Ascending'),
            ],
            [
                'value' => 'desc',
                'label' => __('Descending'),
             ]
        ];
     
        return $response;
    }
}
