<?php

declare(strict_types=1);

namespace Thecommerceshop\Predictivesearch\Model\Schema;

use Thecommerceshop\Predictivesearch\Model\Types\TypesenseTypes;
use Magento\Catalog\Model\ResourceModel\Category\Attribute\CollectionFactory;
use Thecommerceshop\Predictivesearch\Model\ConfigData;

class CategorySchema
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
    public function getCategorySchema($indexName)
    {
        $attributeArr = $this->getFilterableAttributes();
        $fieldArray =  [
            ['name' => 'id', 'type' => TypesenseTypes::STRING],
            ['name' => 'category_id', 'type' => TypesenseTypes::STRING],
            ['name' => 'category_name', 'type' => TypesenseTypes::STRING],
            ['name' => 'path', 'type' => TypesenseTypes::AUTO],
            ['name' => 'include_in_menu', 'type' => TypesenseTypes::INTEGER],
            ['name' => 'level', 'type' => TypesenseTypes::INTEGER],
            ['name' => 'product_count', 'type' => TypesenseTypes::INTEGER],
            ['name' => 'url', 'type' => TypesenseTypes::STRING],
            ['name' => 'status', 'type' => TypesenseTypes::INTEGER],
            ['name' => 'created_at', 'type' => TypesenseTypes::STRING],
            ['name' => 'store', 'type' => TypesenseTypes::STRING, 'facet' => true]
        ];
        $categoryFields = array_merge($attributeArr, $fieldArray);
        return [
            'name'   => $indexName,
            'fields' => $categoryFields,
        ];
    }

    /**
     * Get Category Filterable Attributes
     *
     * @param void
     */
    public function getFilterableAttributes()
    {
        $filterArray = [];
        $categoryAttributes = $this->collectionFactory->create();
        foreach ($categoryAttributes as $attributes) {
            $multiListArr = ['multiselect', 'dropdown', 'select'];
            if (!in_array($attributes->getAttributeCode(), $this->exculdedAttributes())) {
                if (in_array($attributes->getFrontendInput(), $multiListArr)) {
                    $filterArray[] = [
                        'name'  => $attributes->getAttributeCode(),
                        'type'  => TypesenseTypes::ARRAY_STRTING,
                        'facet' => true,
                    ];
                } else {
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

    /**
     * Excluded Categories Attributes
     *
     * @return object
     */
    public function exculdedAttributes()
    {
        try {
            $excludedAttributes = [
                'all_children', 'available_sort_by', 'children', 'children_count', 'custom_apply_to_products',
                'custom_design', 'custom_design_from', 'custom_design_to', 'custom_layout_update',
                'custom_use_parent_settings', 'default_sort_by', 'display_mode', 'filter_price_range',
                'global_position', 'image', 'include_in_menu', 'is_active', 'is_always_include_in_menu', 'is_anchor',
                'landing_page', 'level', 'lower_cms_block', 'page_layout', 'path_in_store', 'position', 'small_image',
                'thumbnail', 'url_key', 'url_path','visible_in_menu','quantity_and_stock_status',
            ];
            return $excludedAttributes;

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
