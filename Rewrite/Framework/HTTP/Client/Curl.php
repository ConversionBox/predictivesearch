<?php

declare(strict_types=1);

namespace Thecommerceshop\Predictivesearch\Rewrite\Framework\HTTP\Client;

/**
 * Class to work with HTTP protocol using curl library
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @api
 */
class Curl extends \Magento\Framework\HTTP\Client\Curl
{
    /**
     * Put operation
     *
     * @param string $uri
     * @param string $params
     */
    public function put($uri, $apiKey, $params)
    {
        $this->makePutCurlCall($uri, $apiKey, $params);
    }

    /**
     * Put Api call
     *
     * @param string $url
     * @param string $apiKey
     * @param string $params
     * @return string
     */
    private function makePutCurlCall($url, $apiKey, $params)
    {
        // @codingStandardsIgnoreFile
        // @codingStandardsIgnoreStart
        // @codingStandardsIgnoreLine
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["x-typesense-api-key:".$apiKey]);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        $response = curl_exec($ch);
        return $response;
    }
}
