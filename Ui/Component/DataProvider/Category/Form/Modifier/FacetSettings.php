<?php
namespace Conversionbox\Predictivesearch\Ui\DataProvider\Category\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class FacetSettings implements ModifierInterface
{
    private $locator;

    public function __construct(LocatorInterface $locator)
    {
        $this->locator = $locator;
    }

    public function modifyData(array $data)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/category.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info("category++modifier");
        $category = $this->locator->getCategory();
        $categoryId = $category->getId();
        
        if ($categoryId) {
            $data = $category->getData('conversion_categories_facet');
            if ($data && is_string($data)) {
             $data = json_decode($data, true);
      }
        $result['conversion_categories_facet']['facet_settings'] = $data ?? [];
        }
        
        return $result;
    }

    public function modifyMeta(array $meta)
    {
        return $meta;
    }
}
?>