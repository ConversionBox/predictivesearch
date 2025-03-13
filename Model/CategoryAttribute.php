<?php
namespace Conversionbox\Predictivesearch\Model;

use Conversionbox\Predictivesearch\Api\CategoryAttributeInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;

class CategoryAttribute implements CategoryAttributeInterface
{
    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * Constructor
     *
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Update custom attribute value for a category
     *
     * @param int $categoryId
     * @param mixed $data
     * @return string
     */
     public function updateCategoryAttribute($categoryId, $data)
    {     
            // Load the category
            $category = $this->categoryRepository->get($categoryId);
            $category->setStoreId(0);
            foreach($data as $data){
                $category->setData($data['attributeCode'] , json_encode($data['value']));
                // Save category
                $category->getResource()->saveAttribute($category, $data['attributeCode']);
            }

             return "Category value saved successfully.";

      
    }
    
}
