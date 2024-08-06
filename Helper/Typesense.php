<?php
namespace Conversionbox\Predictivesearch\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Typesense\Client;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Conversionbox\Predictivesearch\Model\ConfigData;

class Typesense extends AbstractHelper
{
    /**
     * @var ConfigData
     */
    protected $client;

    /**
     * @var ConfigData
     */
    protected $configData;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
        $apikey ='tpCrvD1CBJcdCrB7uzyMOnkj7dla8mJDW';
        $host = 'zi126lo3enjyr7qbp-1.a1.typesense.net';
         $port = '443';
         $protocal = 'https';

        $this->client =  new Client([
            'nodes' => [
                [
                    'host' => $host,
                    'port' => $port,
                    'protocol' => $protocal
                ]
            ],
            'api_key' =>  $apikey,
            'connection_timeout_seconds' => 2
        ]);
    }

    public function getClient()
    {
        return $this->client;
    }
  
}
