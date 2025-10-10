<?php
namespace Conversionbox\Predictivesearch\Model;

use Conversionbox\Predictivesearch\Api\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;

class Config implements ConfigInterface
{
    protected $scopeConfig;
    protected $resourceConnection;
    protected $storeManager;
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ResourceConnection $resourceConnection,
        StoreManagerInterface  $storeManager,
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->resourceConnection = $resourceConnection;
       $this->storeManager = $storeManager;
    }

    /**
     * Get configuration by section
     *
     * @param string $section
     * @return array
     */
    public function getBySection($section)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('core_config_data');

        $query = $connection->select()
            ->from($tableName, ['scope', 'scope_id','path', 'value'])
            ->where('path LIKE ?', $section . '/%');

        $configData = $connection->fetchAll($query);
        if (!$configData) {
            return ['message' => 'No configuration found for section: ' . $section];
        }

        $configValues = [];
        foreach ($configData as $config) {
            if (isset($config['path'], $config['value'])) {
                $pathParts = explode('/', $config['path']);
                $field = end($pathParts); // Get the last part (field name)
               // $scopeKey = $config['scope'];
            $scopeKey = '';
            switch ($config['scope']) {
                case 'default':
                    $scopeKey = 'default'; // global config
                    break;
                case 'websites':
                    $website = $this->storeManager->getWebsite($config['scope_id']);
                    $scopeKey =  $website->getCode();
                    break;
              /*  case 'stores':
                    $store = $this->storeManager->getStore($config['scope_id']);
                    $scopeKey = $store->getCode();
                    break; */
            }
                $configValues[$scopeKey][$field] = $config['value']; // Store as key-value
            }
        }

        return ["value" => $configValues];
    }
}
