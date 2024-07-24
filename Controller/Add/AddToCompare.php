<?php

declare(strict_types=1);

namespace Conversionbox\Predictivesearch\Controller\Add;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Product\Compare;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Compare\Item;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Catalog\Model\Product\Compare\ListCompare;

class AddToCompare extends Action
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Compare
     */
    private $compare;

    /**
     * @var Item
     */
    private $compareItem;

    /**
     * @var JsonFactory
     */
    private $jsonResultFactory;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var ListCompare
     */
    private $listCompare;

    /**
     * Constructor
     *
     * @param Context $context
     * @param ProductRepositoryInterface $productRepository
     * @param Compare $compare
     * @param Item $compareItem
     * @param JsonFactory $jsonResultFactory
     * @param ManagerInterface $messageManager
     * @param ListCompare $listCompare
     */
    public function __construct(
        Context $context,
        ProductRepositoryInterface $productRepository,
        Compare $compare,
        Item $compareItem,
        JsonFactory $jsonResultFactory,
        ManagerInterface $messageManager,
        ListCompare $listCompare
    ) {
        parent::__construct($context);
        $this->productRepository = $productRepository;
        $this->compare = $compare;
        $this->compareItem = $compareItem;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->messageManager = $messageManager;
        $this->listCompare = $listCompare;
    }

    /**
     * Controller for adding product to compare
     */
    public function execute()
    {
        $productId = (int)$this->getRequest()->getParam('id');
        $response = ['success' => false];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $baseUrl = $storeManager->getStore()->getBaseUrl();
        $compareUrl = $baseUrl .'catalog/product_compare/';
        if ($productId) {
            try {
                $product = $this->productRepository->getById($productId);
                if ($product) {
                    $this->listCompare->addProduct($product);
                    $this->compare->calculate();
                    $message = __('You added %1 to your <a href="%2">comparison list</a>.', $product->getName(),$compareUrl);
                    $response = ['success' => true, 'message' => $message];
                }
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__("Product not found."));
                $response = ['success' => false, 'message' => $e->getMessage()];
            }
        }
        $result = $this->jsonResultFactory->create();
        return $result->setData($response);
    }
}
