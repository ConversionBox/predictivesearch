<?php

declare(strict_types=1);

namespace Thecommerceshop\Predictivesearch\Model\Resolver\Query\DataProvider;

use Exception;
use Thecommerceshop\Predictivesearch\Model\Api\TypeSenseApi;
use Thecommerceshop\Predictivesearch\Model\ConfigData;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

class SearchResults
{
    /**
     * @var TypeSenseApi
     */
    private $typeSenseApi;

    /**
     * @var ConfigData
     */
    private $configData;

    /**
     * Constructor
     *
     * @param TypeSenseApi $typeSenseApi
     * @param ConfigData $configData
     */
    public function __construct(
        TypeSenseApi $typeSenseApi,
        ConfigData $configData
    ) {
        $this->typeSenseApi = $typeSenseApi;
        $this->configData = $configData;
    }

    /**
     * Perform search
     *
     * @param array $arguments
     * @return array
     */
    public function performSearchAction($arguments)
    {
        $productData = [];
        $searchResult = [];
        $facet = [];
        $filterParam = '';
        $sortOrder = '';
        $productCount = 0;

        $allowedFacet = $this->configData->getSearchFilters();
        $facetData = '';
        foreach ($allowedFacet as $data) {
            $facetData .= $data['filterAttribute'].',';
        }

        if ($facetData) {
            $facetData = substr($facetData, 0, -1);
        }
        
        if (isset($arguments['sort'])) {
            if (!isset($arguments['sort']['attribute'])) {
                throw new GraphQlInputException(__("attribute code is required."));
            }

            if (!isset($arguments['sort']['sort_order'])) {
                throw new GraphQlInputException(__("sort_order code is required."));
            }
            $sortOrder = $arguments['sort']['attribute'].':'.$arguments['sort']['sort_order'];
        }

        if (isset($arguments['filters'])) {
            foreach ($arguments['filters'] as $data) {
                $filterParam .= $data['attribute'].':['.$data['value_string'].'] &&';
            }
            $filterParam = substr($filterParam, 0, -2);
        }

        try {
            //create search parameter based on input
            $searchParameter = [
                'q'         => $arguments['keyword'],
                'query_by'  => 'product_name',
                'per_page'  => $arguments['pageSize'],
                'page'      => $arguments['currentPage'],
                'facet_by'  => $facetData,
                'filter_by' => $filterParam,
                'sort_by'   => $sortOrder
            ];

            $index = $this->configData->getIndexPrefix().'products';
            $response = $this->typeSenseApi->performSearch($searchParameter, $index);
            if ($response) {
                $productCount = $response['found'];
                foreach ($response['hits'] as $data) {
                    $productData[] = $data['document'];
                }
                $facet = $this->getFacetData($response['facet_counts']);
            }

            $searchResult['productItems'] = $productData;
            $searchResult['facetData'] = $facet;
            $searchResult['product_count'] = $productCount;

            return $searchResult;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Get Facet Info
     *
     * @param array $facet
     * @return array
     */
    public function getFacetData($facet)
    {
        $response = [];
        foreach ($facet as $data) {
            $response[] = [
                'attribute' => $data['field_name'],
                'total_values' => $data['stats']['total_values'],
                'values' => $this->getValues($data)
            ];
        }
        return $response;
    }

    /**
     * Get facet values
     *
     * @param array $data
     * @return array
     */
    public function getValues($data)
    {
        $response = [];
        foreach ($data['counts'] as $item) {
            $response[] = [
                'value' => $item['value'],
                'label' => $item['highlighted'],
                'count' => $item['count']
            ];
        }
        return $response;
    }
}
