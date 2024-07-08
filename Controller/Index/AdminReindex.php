<?php


declare(strict_types=1);

namespace Thecommerceshop\Predictivesearch\Controller\Index;

use Exception;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Request\Http;
use Thecommerceshop\Predictivesearch\Model\DataProcessor\ProductDataProcessor;
use Thecommerceshop\Predictivesearch\Model\DataProcessor\CategoryDataProcessor;
use Thecommerceshop\Predictivesearch\Model\DataProcessor\PageDataProcessor;

class AdminReindex implements HttpGetActionInterface
{
    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var Http
     */
    private $http;

    /**
     * @var ProductDataProcessor
     */
    private $productDataProcessor;

    /**
     * @var CategoryDataProcessor
     */
    private $categoryDataProcessor;

    /**
     * @var PageDataProcessor
     */
    private $pageDataProcessor;

    /**
     * Constructor
     *
     * @param JsonFactory $jsonFactory
     * @param Http $http
     * @param ProductDataProcessor $productDataProcessor
     * @param CategoryDataProcessor $categoryDataProcessor
     * @param PageDataProcessor $pageDataProcessor
     */
    public function __construct(
        JsonFactory $jsonFactory,
        Http $http,
        ProductDataProcessor $productDataProcessor,
        CategoryDataProcessor $categoryDataProcessor,
        PageDataProcessor $pageDataProcessor
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->http = $http;
        $this->productDataProcessor = $productDataProcessor;
        $this->categoryDataProcessor = $categoryDataProcessor;
        $this->pageDataProcessor = $pageDataProcessor;
    }

    /**
     * Controller for Admin reindex
     */
    public function execute()
    {
        $success = true;
        $resultJson = $this->jsonFactory->create();

        $mode = $this->http->getParam('mode');
        if ($mode) {
            $this->productDataProcessor->syncAllProducts([], null, $mode);
            $this->categoryDataProcessor->syncCategory([], null, $mode);
            $this->pageDataProcessor->syncPages([]);
        }
        return $resultJson->setData([
            'success' => $success,
        ]);
    }
}
