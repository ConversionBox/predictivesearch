<?php
namespace Conversionbox\Predictivesearch\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\App\RequestInterface;

class SaveCategoryAttribute implements ObserverInterface
{
    protected $logger;
    protected $categoryRepository;
    protected $request;

    public function __construct(
        LoggerInterface $logger, 
        CategoryRepositoryInterface $categoryRepository,
        RequestInterface $request
    ) {
        $this->logger = $logger;
        $this->categoryRepository = $categoryRepository;
        $this->request = $request;
    }

    public function execute(Observer $observer)
    {
        $category = $observer->getEvent()->getCategory();
        $facetData = $this->request->getParam('conversion_categories_facet');
        // $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/catcustom.log');
        // $logger = new \Zend_Log();
        // $logger->addWriter($writer);
        // $logger->info(print_r($facetData['facet_settings'],true));
        // $logger->info(json_encode(($facetData['facet_settings'])));
        $value = json_encode($facetData);
        $category->setConversionCategoriesFacet($value);
    //     if (!empty($facetData) && isset($facetData['facet_settings'])) {
    //         $formattedFacets = array_values($facetData['facet_settings']);
    //         $category->setData('conversion_categories_facet', json_encode($formattedFacets));
    // }
}
}
?>