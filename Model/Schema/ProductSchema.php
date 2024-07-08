<?php
declare(strict_types=1);

namespace Thecommerceshop\Predictivesearch\Model\Schema;

use Thecommerceshop\Predictivesearch\Model\Types\TypesenseTypes;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Thecommerceshop\Predictivesearch\Model\ConfigData;

class ProductSchema
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ConfigData
     */
    private $configData;

    /**
     * Constructor
     *
     * @param CollectionFactory $collectionFactory
     * @param ConfigData $configData
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        ConfigData $configData
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->configData = $configData;
    }

    /**
     * Return product shcema structure
     *
     * @param string $indexName
     * @return array
     */
    public function getProductSchema($indexName)
    {
        $attrubuteArr = $this->getFilterableAttributes();
        $fieldArray = [
            ['name' => 'id', 'type' => TypesenseTypes::STRING],
            ['name' => 'product_id', 'type' => TypesenseTypes::STRING],
            ['name' => 'product_name', 'type' => TypesenseTypes::STRING],
            ['name' => 'name', 'type' => TypesenseTypes::STRING, 'sort'  => true],
            ['name' => 'sku', 'type' => TypesenseTypes::STRING],
            ['name' => 'url', 'type' => TypesenseTypes::STRING],
            ['name' => 'image_url', 'type' => TypesenseTypes::AUTO],
            ['name' => 'thumbnail', 'type' => TypesenseTypes::AUTO],
            ['name' => 'type_id', 'type' => TypesenseTypes::STRING],
            ['name' => 'visibility', 'type' => TypesenseTypes::STRING],
            ['name' => 'category', 'type' => TypesenseTypes::ARRAY_STRTING, 'facet' => true],
            ['name' => 'storeCode', 'type' => TypesenseTypes::STRING, 'facet' => true],
            ['name' => 'url_path', 'type' => TypesenseTypes::ARRAY_STRTING, 'facet' => true],
            ['name' => 'stock_status', 'type' => TypesenseTypes::BOOL],
            ['name' => 'product_status', 'type' => TypesenseTypes::STRING],
            ['name' => 'created_at', 'type' => TypesenseTypes::STRING, 'sort'  => true],
            ['name' => 'stock_qty', 'type' => TypesenseTypes::STRING, 'sort'  => true],
            ['name' => 'special_price', 'type' => TypesenseTypes::STRING, 'sort'  => true],
            ['name' => 'rating_summary', 'type' => TypesenseTypes::STRING, 'sort'  => true],
            ['name' => 'special_from_date', 'type' => TypesenseTypes::STRING, 'sort'  => true],
            ['name' => 'special_to_date', 'type' => TypesenseTypes::STRING, 'sort'  => true],
            ['name' => 'price', 'type' => TypesenseTypes::FLOAT, 'facet' => true,'sort'  => true],
            ['name' => 'bestseller', 'type' => TypesenseTypes::STRING, 'sort'  => true],
            ['name' => 'small_image', 'type' => TypesenseTypes::AUTO],
            ['name' => 'category_ids', 'type' => TypesenseTypes::ARRAY_STRTING, 'facet' => true],
            ['name' => 'description', 'type' => TypesenseTypes::STRING],
            ['name' => 'short_description', 'type' => TypesenseTypes::STRING],
        ];
        $productFields = array_merge($attrubuteArr, $fieldArray);
        return [
            'name'   => $indexName,
            'fields' => $productFields,
        ];
    }

    /**
     * Get Product Filterable Attributes
     *
     * @param void
     */
    public function getFilterableAttributes()
    {
        $filterArray = [];
        $productAttributes = $this->collectionFactory->create();
        $productAttributes->addFieldToFilter(
            ['is_filterable', 'is_filterable_in_search'],
            [[1, 2], 1]
        );

        foreach ($productAttributes as $attributes) {
            $multiListArr = ['multiselect', 'dropdown', 'select'];
            if (in_array($attributes->getFrontendInput(), $multiListArr)) {
                $filterArray[] = [
                    'name'  => $attributes->getAttributeCode(),
                    'type'  => TypesenseTypes::ARRAY_STRTING,
                    'facet' => true,
                ];
            } else {
                if ($attributes->getAttributeCode() != 'price') {
                    $filterArray[] = [
                        'name'  => $attributes->getAttributeCode(),
                        'type'  => TypesenseTypes::STRING,
                        'facet' => true,
                        'sort'  => true
                    ];
                }
            }
        }

        return $filterArray;
    }
}
