<?php
namespace Conversionbox\Predictivesearch\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\App\RequestInterface;

class SaveCategoryAttribute implements ObserverInterface
{
    /**
     * @var PsrLoggerInterface
     */
    protected $logger;
    /**
     * @var CategoryRepositoryInterface;
     */
    protected $categoryRepository;
    /**
     * @var RequestInterface ;
     */
    protected $request;

    /**
     * Save Category Attribute constructor.
     *
     * @param PsrLoggerInterface $logger
     * @param CategoryRepositoryInterface $categoryRepository
     * @param RequestInterface $request
     */
    public function __construct(
        LoggerInterface $logger, 
        CategoryRepositoryInterface $categoryRepository,
        RequestInterface $request
    ) {
        $this->logger = $logger;
        $this->categoryRepository = $categoryRepository;
        $this->request = $request;
    }
    /**
     * Custom Attribute save Observer
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $category = $observer->getEvent()->getCategory();
        $request = $this->request->getPostValue();
        if (isset($request['conversion_categories_facet']) && is_array($request['conversion_categories_facet'])) {
            $logger->info(print_r($request['conversion_categories_facet'],true));
            $jsonValue = json_encode($request['conversion_categories_facet']);
            $category->setData('conversion_categories_facet', $jsonValue);
            $category->getResource()->saveAttribute($category, 'conversion_categories_facet');

        }else{
            $category->setData('conversion_categories_facet', null);
            $category->getResource()->saveAttribute($category, 'conversion_categories_facet');
        }
        if (isset($request['conversion_categories_sortorder']) && is_array($request['conversion_categories_sortorder'])) {
            $logger->info(print_r($request['conversion_categories_sortorder'],true));
            $jsonValue = json_encode($request['conversion_categories_sortorder']);
            $category->setData('conversion_categories_sortorder', $jsonValue);
            $category->getResource()->saveAttribute($category, 'conversion_categories_sortorder');

        }else{
            $category->setData('conversion_categories_sortorder', null);
            $category->getResource()->saveAttribute($category, 'conversion_categories_sortorder');
        }
  } 
}
?>