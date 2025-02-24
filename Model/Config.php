<?php
namespace Conversionbox\Predictivesearch\Model;

use Conversionbox\Predictivesearch\Api\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;

class Config implements ConfigInterface
{
    protected $scopeConfig;
    protected $resourceConnection;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ResourceConnection $resourceConnection
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->resourceConnection = $resourceConnection;
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
            ->from($tableName, ['path', 'value'])
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
                $configValues[$field] = $config['value']; // Store as key-value
            }
        }

        return ["value" => $configValues];
    }
}
