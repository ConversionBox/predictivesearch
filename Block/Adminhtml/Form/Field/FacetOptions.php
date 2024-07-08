<?php
declare(strict_types=1);

namespace Thecommerceshop\Predictivesearch\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Html\Select;

class FacetOptions extends Select
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
        return [
            ['label' => 'Searchable', 'value' => '1'],
            ['label' => 'Not Searchable', 'value' => '2'],
            ['label' => 'Filter Only', 'value' => '3'],
        ];
    }
}
