<?php

declare(strict_types=1);

namespace Thecommerceshop\Predictivesearch\Model\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface TypesenseSearchInterface extends ExtensibleDataInterface
{
    public const JOB_ID    = 'job_id';
    public const JOB_TYPE = 'job_type';
    public const JOB_DATA  = 'job_data';
    public const JOB_INDEX  = 'job_index';
    public const JOB_STATUS  = 'job_status';
    public const ERRORS = 'errors';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';

    /**
     * Get Id
     *
     * @return int
     */
    public function getId();

    /**
     * Set Id
     *
     * @param int $job_id
     * @return TypesenseSearchInterface
     */
    public function setId($job_id);
  
    /**
     * Get Job Type
     *
     * @return string|null
     */
    public function getJobType();

    /**
     * Set Job Type
     *
     * @param string $job_type
     * @return TypesenseSearchInterface
     */
    public function setJobType($job_type);

    /**
     * Get Data
     *
     * @return string|null
     */
    public function getJobData();

    /**
     * Set Data
     *
     * @param string $job_data
     * @return TypesenseSearchInterface
     */
    public function setJobData($job_data);

    /**
     * Get Index
     *
     * @return string|null
     */
    public function getJobIndex();

    /**
     * Set Index
     *
     * @param string $job_index
     * @return TypesenseSearchInterface
     */
    public function setJobIndex($job_index);

    /**
     * Get Status
     *
     * @return string|null
     */
    public function getJobStatus();

    /**
     * Set Data
     *
     * @param int $job_status
     * @return TypesenseSearchInterface
     */
    public function setJobStatus($job_status);

    /**
     * Get Status
     *
     * @return string|null
     */
    public function getErrors();

    /**
     * Set Data
     *
     * @param string $errors
     * @return TypesenseSearchInterface
     */
    public function setErrors($errors);

    /**
     * Get CreatedAt
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set Created At
     *
     * @param string $updated_at
     * @return TypesenseSearchInterface
     */
    public function setCreatedAt($updated_at);

     /**
      * Get UpdatedAt
      *
      * @return string|null
      */
    public function getUpdatedAt();

    /**
     * Set Updated At
     *
     * @param string $updated_at
     * @return TypesenseSearchInterface
     */
    public function setUpdatedAt($updated_at);
}
