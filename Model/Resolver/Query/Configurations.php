<?php
declare(strict_types=1);

namespace Conversionbox\Predictivesearch\Model\Resolver\Query;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Conversionbox\Predictivesearch\Model\ConfigData;

class Configurations implements ResolverInterface
{
    /**
     * @var ConfigData
     */
    private $configData;

    /**
     * Constructor
     *
     * @param ConfigData $configData
     */
    public function __construct(
        ConfigData $configData
    ) {
        $this->configData = $configData;
    }

    /**
     * Resolve function
     *
     * @param Field $field
     * @param array $context
     * @param ResolveInfo $info
     * @param array $value
     * @param array $args
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $response = [];

        $response['general'] = $this->getGeneralConfig();
        $response['search_result'] = $this->getResultPageConfig();
        return $response;
    }

    /**
     * Get General Config values
     *
     * @param void
     * @return array
     */
    public function getGeneralConfig()
    {
        return [
            'module_status' => $this->configData->getModuleStatus(),
            'cloud_key' => $this->configData->getHost(),
            'search_api_key' => $this->configData->getSearchApiKey(),
            'admin_api_key' => $this->configData->getAdminApiKey(),
            'node' => $this->configData->getNode(),
            'protocol' => $this->configData->getProtocol(),
            'port' => $this->configData->getPort(),
            'index_name' => $this->configData->getIndexPrefix()
        ];
    }

    /**
     * Get result page Config values
     *
     * @param void
     * @return array
     */
    public function getResultPageConfig()
    {
        return [
            'status' => $this->configData->getCustomResultPage(),
            'product_per_page' => $this->configData->getNoProductsPage(),
            'search_filters' => $this->getAllowedFilters(),
            'sort_option' => $this->getAllowedSort()
        ];
    }

    /**
     * Get Allowed Sort
     *
     * @param void
     * @return array
     */
    public function getAllowedSort()
    {
        $response = [];
        $sortData = $this->configData->getSortOptions();
        foreach ($sortData as $item) {
            $response[] = [
                'attribute' => $item['sortAttribute'],
                'order' => $item['sortDirection'],
                'label' => $item['fieldName']
            ];
        }
        return  $response;
    }

    /**
     * Get Allowed Filters
     *
     * @param void
     * @return array
     */
    public function getAllowedFilters()
    {
        $response = [];
        $filterData = $this->configData->getSearchFilters();
        foreach ($filterData as $item) {
            $response[] = [
                'attribute' => $item['filterAttribute'],
                'label' => $item['fieldName']
            ];
        }
        return  $response;
    }
}
