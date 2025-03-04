<?php
namespace Conversionbox\Predictivesearch\Api;

interface LandingPageInterface
{
    /**
     * Get paginated data
     *
     * @param int $page
     * @param int $pageSize
     * @return array
     */
    public function getData($page = 1, $pageSize = 10);
}
