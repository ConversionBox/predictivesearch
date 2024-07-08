<?php

declare(strict_types=1);

namespace Thecommerceshop\Predictivesearch\Model\Data;

use Thecommerceshop\Predictivesearch\Model\Api\Data\TypesenseSearchInterface;

class TypesenseSearch extends \Magento\Framework\Api\AbstractExtensibleObject implements TypesenseSearchInterface
{
    /**
     * Get Id
     *
     * @return int
     */
    public function getId()
    {
        return $this->_get(self::JOB_ID);
    }

    /**
     * Set ID
     *
     * @param int $job_id
     * @return $this
     */
    public function setId($job_id)
    {
        return $this->setData(self::JOB_ID, $job_id);
    }
    
    /**
     * Get Job Type
     *
     * @return string|null
     */
    public function getJobType()
    {
        return $this->_get(self::JOB_TYPE);
    }
    
    /**
     * Set Job Type
     *
     * @param string $job_id
     * @return $this
     */
    public function setJobType($job_id)
    {
        return $this->setData(self::JOB_TYPE, $job_id);
    }

    /**
     * Get Job Data
     *
     * @return string|null
     */
    public function getJobData()
    {
        return $this->_get(self::JOB_DATA);
    }
    
    /**
     * Set Job Data
     *
     * @param string $job_data
     * @return $this
     */
    public function setJobData($job_data)
    {
        return $this->setData(self::JOB_DATA, $job_data);
    }

    /**
     * Get Job Index
     *
     * @return string|null
     */
    public function getJobIndex()
    {
        return $this->_get(self::JOB_INDEX);
    }
    
    /**
     * Set Job Index
     *
     * @param string $job_index
     * @return $this
     */
    public function setJobIndex($job_index)
    {
        return $this->setData(self::JOB_INDEX, $job_index);
    }

    /**
     * Get Job Data
     *
     * @return string|null
     */
    public function getJobStatus()
    {
        return $this->_get(self::JOB_STATUS);
    }
    
    /**
     * Set Job Data
     *
     * @param string $job_status
     * @return $this
     */
    public function setJobStatus($job_status)
    {
        return $this->setData(self::JOB_STATUS, $job_status);
    }

    /**
     * Get Errors
     *
     * @return string|null
     */
    public function getErrors()
    {
        return $this->_get(self::ERRORS);
    }
    
    /**
     * Set Errors
     *
     * @param string $errors
     * @return $this
     */
    public function setErrors($errors)
    {
        return $this->setData(self::ERRORS, $errors);
    }

    /**
     * Get Created At
     *
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->_get(self::CREATED_AT);
    }
    
    /**
     * Set Created At
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get Updated At
     *
     * @return string|null
     */
    public function getUpdatedAt()
    {
        return $this->_get(self::UPDATED_AT);
    }
    
    /**
     * Set Update At
     *
     * @param string $updated_at
     * @return $this
     */
    public function setUpdatedAt($updated_at)
    {
        return $this->setData(self::UPDATED_AT, $updated_at);
    }
}
