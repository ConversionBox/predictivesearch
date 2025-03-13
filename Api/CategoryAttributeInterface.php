<?php
namespace   Conversionbox\Predictivesearch\Api;

interface CategoryAttributeInterface
{
    /**
     * Update custom attribute value for a category
     *
     * @param int $categoryId
     * @param mixed $data
     * @return string
     */

   public function updateCategoryAttribute($categoryId, $data);
}
