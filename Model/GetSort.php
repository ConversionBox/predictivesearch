<?php
namespace Conversionbox\Predictivesearch\Model;

use Conversionbox\Predictivesearch\Api\SortInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
class GetSort implements SortInterface
{
      /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        CollectionFactory $collectionFactory,
    ) {
         $this->collectionFactory = $collectionFactory;
    }

    /**
     * Get Filter Attribute Collection
     *
     * @return  array
     * 
     */
    public function getSortby()
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $productAttributes */
      $productAttributes = $this->collectionFactory->create();
        $productAttributes->addFieldToFilter(
            ['is_filterable', 'is_filterable_in_search'],
            [[1, 2], 1]
        );

        $response = [];
        $sortOptions = [];
        foreach ($productAttributes as $item) {
            $multiListArr = ['multiselect', 'dropdown', 'select'];
            if (!in_array($item->getFrontendInput(), $multiListArr)) {
                if ($item->getData('attribute_code') == 'category_gear') {
                    $attibuteCode = 'category';
                    $categoryLabel = 'Category';
                } else {
                    $attibuteCode = $item->getData('attribute_code');
                    $categoryLabel = $item->getData('frontend_label');
                }

                $sortOptions = [
                    [
                        'label' => __('Newest'),
                        'value' => 'created_at'
                    ],
                    [
                        'label' => __('Most Relevent'),
                        'value' => 'bestseller'
                    ]
                ];
                
                $response[] = [
                    'label' => $categoryLabel,
                    'value' => $attibuteCode
                ];
            }
        }
        $response = array_merge($response, $sortOptions);
        return $response;
    }
}
