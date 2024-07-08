<?php

declare(strict_types=1);

namespace Thecommerceshop\Predictivesearch\Controller\Add;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Action\Context;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Framework\Message\ManagerInterface;

class AddToWishlist extends Action
{
    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var WishlistProviderInterface
     */
    private $wishlistProvider;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * Constructor
     *
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param ProductRepositoryInterface $productRepository
     * @param WishlistProviderInterface $wishlistProvider
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        ProductRepositoryInterface $productRepository,
        WishlistProviderInterface $wishlistProvider,
        ManagerInterface $messageManager
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->productRepository = $productRepository;
        $this->wishlistProvider = $wishlistProvider;
        $this->messageManager = $messageManager;
    }

    /**
     * Controller for adding product to wishlist
     */
    public function execute()
    {
        $productId = $this->getRequest()->getParam('id');
        $isCustomerLoggedIn = $this->getRequest()->getParam('isCustomerLoggedIn');
        $result = $this->jsonFactory->create();
        $response = ['success' => false];
        if ($isCustomerLoggedIn && $productId) {
            try {
                $product = $this->productRepository->getById($productId);
                if ($product) {
                    $wishlist = $this->wishlistProvider->getWishlist();
                    $wishlist->addNewItem($product);
                    $response['success'] = true;
                    $this->messageManager->addSuccessMessage(__("Product added to wishlist successfully."));
                }
            } catch (\Exception $e) {
                $response['error'] = $e->getMessage();
            }
        } else {
            $this->messageManager->addErrorMessage(__("You must login or register to add items to your wishlist."));
        }
        return $result->setData($response);
    }
}
