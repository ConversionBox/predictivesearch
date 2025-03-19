<?php


declare(strict_types=1);

namespace Conversionbox\Predictivesearch\Controller\Index;

use Exception;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Request\Http;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;



class Connection implements HttpGetActionInterface
{

    private const UNIQUEID ='typesense_general/credentials/unique_id';
    /**
     * Index Prefix
     */
    private const INDEX_PERFIX = 'typesense_general/credentials/index_prefix';
    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var Http
     */
    private $http;
    protected $curl;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfigInterface;

    /**
     * Constructor
     *
     * @param JsonFactory $jsonFactory
     * @param Http $http
     */
    public function __construct(
        JsonFactory $jsonFactory,
        Http $http,
        Curl $curl,
        ScopeConfigInterface $scopeConfigInterface

    ) {
        $this->jsonFactory = $jsonFactory;
        $this->http = $http;
        $this->curl = $curl;
        $this->scopeConfigInterface = $scopeConfigInterface;
    }

    /**
     * Controller for Admin reindex
     */
    public function execute()
    {
        $response = ['success' => false, 'message' => 'Failed to connect'];

        try {
            $url = 'https://devmagebe.conversionbox.io/api/v1/magento/connectMagentoStore';
            $uniqueId = $this->getUniqueId();
            $indexPrefix = $this->getIndexPrefix();
            // Data to send in the POST request
            $data = [
                'unique_id' => $this->getUniqueId(),  // Replace with actual unique ID
                'index_name' => $this->getIndexPrefix()// Replace with actual index name
            ];
            // Set the cURL options for POST request
            $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
            $this->curl->setOption(CURLOPT_TIMEOUT, 30);
            $this->curl->setOption(CURLOPT_SSL_VERIFYPEER, false);  // Disable SSL verification (use carefully)
            $this->curl->post($url, $data);

            // Get the response
            $responseBody = $this->curl->getBody();

            // Decode JSON response if necessary
            $decodedResponse = json_decode($responseBody, true);

            if ($decodedResponse && isset($decodedResponse['success']) && $decodedResponse['success']) {
                $response = [
                    'success' => true,
                    'message' => 'Successfully connected to Conversionbox Merchant account'
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => isset($decodedResponse['message']) ? $decodedResponse['message'] : 'No success message returned.'
                ];
            }
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }

        // Return JSON response
        $resultJson =  $this->jsonFactory->create();
        return $resultJson->setData($response);
        
    }
    /**
     * Get System config values
     *
     * @param string $configPath
     */
    public function getSystemConfigValues($configPath)
    {
        return $this->scopeConfigInterface->getValue($configPath, ScopeInterface::SCOPE_STORE);
    }
    /**
     * Get Index Prefix
     *
     * @param void
     * @return string
     */
    public function getIndexPrefix()
    {
        return $this->getSystemConfigValues(self::INDEX_PERFIX);
    }
    public function getUniqueId(){
        
        return $this->getSystemConfigValues(self::UNIQUEID);

    }

}
