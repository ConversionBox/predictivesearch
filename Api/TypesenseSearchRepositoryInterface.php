<?php

declare(strict_types=1);

namespace Conversionbox\Predictivesearch\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Conversionbox\Predictivesearch\Api\Data\TypesenseSearchInterface;
use Conversionbox\Predictivesearch\Api\Data\TypesenseSearchSearchResultsInterface;
use Magento\Framework\Exception\LocalizedException;

interface TypesenseSearchRepositoryInterface
{
    /**
     * Get Data by Id
     *
     * @param int $job_id
     * @return TypesenseSearchInterface
     * @throws LocalizedException
     */
    public function getById($job_id);

    /**
     * Save Data
     *
     * @param TypesenseSearchInterface $jobData
     * @return TypesenseSearchInterface
     * @throws LocalizedException
     */
    public function save(TypesenseSearchInterface $jobData);

    /**
     * Delete Data with  job_id
     *
     * @param int $job_id
     * @return bool
     * @throws LocalizedException
     */
    public function deleteById($job_id);

    /**
     * Load data collection by given search criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return TypesenseSearchSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);
}
