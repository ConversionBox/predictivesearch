<?php

declare(strict_types=1);

namespace Thecommerceshop\Predictivesearch\Controller\Add;

use Exception;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey;
use Magento\Checkout\Model\Cart;
use Magento\Catalog\Model\Product;
use Magento\Framework\Message\ManagerInterface;

class AddToCart implements HttpGetActionInterface
{
    /**
     * @var Http
     */
    private $request;

    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var FormKey
     */
    private $formKey;

    /**
     * @var Cart
     */
    private $cart;

    /**
     * @var Product
     */
    private $product;

    /**
     * @var ManagerInterface
     */
    private $managerInterface;

    /**
     * Constructor
     *
     * @param Http $request
     * @param JsonFactory $jsonFactory
     * @param FormKey $formKey
     * @param Cart $cart
     * @param Product $product
     * @param ManagerInterface $managerInterface
     */
    public function __construct(
        Http $request,
        JsonFactory $jsonFactory,
        FormKey $formKey,
        Cart $cart,
        Product $product,
        ManagerInterface $managerInterface
    ) {
        $this->request = $request;
        $this->jsonFactory = $jsonFactory;
        $this->formKey = $formKey;
        $this->cart = $cart;
        $this->product = $product;
        $this->managerInterface = $managerInterface;
    }

    /**
     * Controller for adding product to cart
     */
    public function execute()
    {
        $success = false;
        $message = '';
        $url = null;
        $resultJson = $this->jsonFactory->create();
        try {
            $productId = $this->request->getParam('id');
            if ($productId) {
                $params = [
                    'form_key' => $this->formKey->getFormKey(),
                    'product' => $productId,
                    'qty'   => 1
                ];
                $product = $this->product->load($productId);
                if (!$product->getId()) {
                    $success = false;
                    $this->managerInterface->addWarning(__("The product you requested is not exist."));
                }
    
                if ($product->getTypeId() == 'simple') {
                    $this->cart->addProduct($product, $params);
                    $quote = $this->cart->save();
                    $success = true;
                    $message = __('You added %1 to your shopping cart.', $product->getName());
                } else {
                    $success = false;
                    if ($product->getTypeId()) {
                        $url = $product->getProductUrl();
                        $this->managerInterface->addWarning(__("Need to choose product options"));
                    }
                }
            }
        } catch (Exception $e) {
            $success = false;
            $message = $e->getMessage();
        }
       
        return $resultJson->setData([
            'message' => $message,
            'success' => $success,
            'url' => $url
        ]);
    }
}
