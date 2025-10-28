<?php
namespace Conversionbox\Predictivesearch\Model;

use Conversionbox\Predictivesearch\Api\FilterInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;

class GetFilter implements FilterInterface
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
    public function getFilter()
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $productAttributes */
        $productAttributes = $this->collectionFactory->create();
        $productAttributes->addFieldToFilter(
            ['is_filterable', 'is_filterable_in_search'],
            [[1, 2], 1]
        );

        $response = []; 
        // Always add Category as first filter option
$response[] = [
    'label' => 'Category',
    'value' => 'category'
];
        foreach ($productAttributes as $item) {
        
                $attibuteCode = $item->getData('attribute_code');
                $categoryLabel = $item->getData('frontend_label');
            
            $response[] = [
                'label' => $categoryLabel,
                'value' => $attibuteCode
            ];
        }
        return $response;
    }
}
