<?php
declare(strict_types=1);

namespace Thecommerceshop\Predictivesearch\Block\Adminhtml\Form\Field\Autocomplete;

use Magento\Framework\View\Element\Html\Select;
use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Option\ArrayInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\View\Element\Context;

class Page extends Select
{
    /**
     * @var PageRepositoryInterface
     */
    private $pageRepositoryInterface;
 
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
 
    /**
     * CmsPages constructor.
     *
     * @param Context $context
     * @param PageRepositoryInterface $pageRepositoryInterface
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param array $data
     */
    public function __construct(
        Context $context,
        PageRepositoryInterface $pageRepositoryInterface,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        array $data = []
    ) {
        $this->pageRepositoryInterface = $pageRepositoryInterface;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
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
     * Get Source Options
     *
     * @return array
     */
    public function getSourceOptions(): array
    {
        $optionArray = [];
        try {
            $pages = $this->getCmsPageCollection();
            if ($pages instanceof LocalizedException) {
                throw $pages;
            }
            $cnt = 0;
            foreach ($pages as $page) {
                $optionArray[$cnt]['value'] = $page->getIdentifier();
                $optionArray[$cnt++]['label'] = $page->getTitle();
            }
        } catch (LocalizedException $e) {
            return $e;
        } catch (\Exception $e) {
            return $e;
        }
        return $optionArray;
    }

    /**
     * Get CMS Page Collection
     *
     * @return \Exception|PageInterface[]|LocalizedException
     */
    public function getCmsPageCollection()
    {
        $searchCriteria = $searchCriteria = $this->searchCriteriaBuilder->create();
        try {
            $collection = $this->pageRepositoryInterface->getList($searchCriteria)->getItems();
        } catch (LocalizedException $e) {
            return $e;
        }
        return $collection;
    }
}
