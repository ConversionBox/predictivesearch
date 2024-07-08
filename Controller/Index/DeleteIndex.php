<?php

declare(strict_types=1);

namespace Thecommerceshop\Predictivesearch\Controller\Index;

use Exception;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Request\Http;
use Thecommerceshop\Predictivesearch\Model\Api\TypeSenseApi;

class DeleteIndex implements HttpGetActionInterface
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
     * @var TypeSenseApi
     */
    private $typeSenseApi;

    /**
     * Constructor
     *
     * @param JsonFactory $jsonFactory
     * @param Http $http
     * @param TypeSenseApi $typeSenseApi
     */
    public function __construct(
        JsonFactory $jsonFactory,
        Http $http,
        TypeSenseApi $typeSenseApi
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->http = $http;
        $this->typeSenseApi = $typeSenseApi;
    }

    /**
     * Controller for Deleting index
     */
    public function execute()
    {
        $success = true;
        $resultJson = $this->jsonFactory->create();
        $index = $this->http->getParam('index');
        try {
            if ($index) {
                $this->typeSenseApi->deleteCollection($index);
            }
        } catch (\Exception $e) {
            $success = false;
        }

        return $resultJson->setData([
            'success' => $success,
        ]);
    }
}
