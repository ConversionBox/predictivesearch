<?php
namespace Conversionbox\Predictivesearch\Ui\Component;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Framework\Option\ArrayInterface;

class FacetOptions implements ArrayInterface
{

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * Attribute Constructor
     *
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    public function toOptionArray()
    {
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
