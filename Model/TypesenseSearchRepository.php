<?php

declare(strict_types=1);

namespace Thecommerceshop\Predictivesearch\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Thecommerceshop\Predictivesearch\Model\TypesenseSearchFactory;
use Thecommerceshop\Predictivesearch\Model\Api\Data\TypesenseSearchInterface;
use Thecommerceshop\Predictivesearch\Model\ResourceModel\TypesenseSearch;
use Thecommerceshop\Predictivesearch\Model\Api\Data\TypesenseSearchInterfaceFactory;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Thecommerceshop\Predictivesearch\Model\Api\Data\TypesenseSearchResultsInterfaceFactory;
use Thecommerceshop\Predictivesearch\Model\ResourceModel\TypesenseSearch\CollectionFactory;
use Thecommerceshop\Predictivesearch\Model\Api\TypesenseSearchRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Thecommerceshop\Predictivesearch\Model\Api\Data\TypesenseSearchSearchResultsInterface;

class TypesenseSearchRepository implements TypesenseSearchRepositoryInterface
{
    /**
     * @var TypesenseSearchFactory
     */
    private $typesenseSearchFactory;

    /**
     * @var ResourceModel\TypesenseSearch
     */
    private $typesenseSearchResource;

    /**
     * @var TypesenseSearchInterfaceFactory
     */
    private $typesenseSearchDataFactory;

    /**
     * @var ExtensibleDataObjectConverter
     */
    private $dataObjectConverter;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var TypesenseSearchResultsInterfaceFactory
     */
    private $searchResultFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;
    
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * TypesenseSearchRepository constructor.
     *
     * @param TypesenseSearchFactory $typesenseSearchFactory
     * @param TypesenseSearch $typesenseSearchResource
     * @param TypesenseSearchInterfaceFactory $typesenseSearchDataFactory
     * @param ExtensibleDataObjectConverter $dataObjectConverter
     * @param DataObjectHelper $dataObjectHelper
     * @param TypesenseSearchResultsInterfaceFactory $searchResultFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        TypesenseSearchFactory $typesenseSearchFactory,
        TypesenseSearch $typesenseSearchResource,
        TypesenseSearchInterfaceFactory $typesenseSearchDataFactory,
        ExtensibleDataObjectConverter $dataObjectConverter,
        DataObjectHelper $dataObjectHelper,
        TypesenseSearchResultsInterfaceFactory $searchResultFactory,
        CollectionProcessorInterface $collectionProcessor,
        CollectionFactory $collectionFactory
    ) {
        $this->typesenseSearchFactory = $typesenseSearchFactory;
        $this->typesenseSearchResource = $typesenseSearchResource;
        $this->typesenseSearchDataFactory = $typesenseSearchDataFactory;
        $this->dataObjectConverter = $dataObjectConverter;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->searchResultFactory = $searchResultFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Get value by Id
     *
     * @param int $typesenseSearch_id
     * @return TypesenseSearchInterface
     * @throws LocalizedException
     */
    public function getById($typesenseSearch_id)
    {
        $typesenseSearchObj = $this->typesenseSearchFactory->create();
        $this->typesenseSearchResource->load($typesenseSearchObj, $typesenseSearch_id);
        if (!$typesenseSearchObj->getId()) {
            throw new NoSuchEntityException(__('The form id does not exist.'));
        }
        $data = $this->typesenseSearchDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $data,
            $typesenseSearchObj->getData(),
            TypesenseSearchInterface::class
        );
        $data->setId($typesenseSearchObj->getId());
        return $data;
    }

    /**
     * Save typesenseSearch Data
     *
     * @param TypesenseSearchInterface $typesenseSearch
     * @return TypesenseSearchInterface
     * @throws LocalizedException
     */
    public function save(TypesenseSearchInterface $typesenseSearch)
    {
        $typesenseSearchData = $this->dataObjectConverter->toNestedArray(
            $typesenseSearch,
            [],
            TypesenseSearchInterface::class
        );
        $typesenseSearchModel = $this->typesenseSearchFactory->create();
        $typesenseSearchModel->setData($typesenseSearchData);
        try {
            $typesenseSearchModel->setId($typesenseSearch->getId());
            $this->typesenseSearchResource->save($typesenseSearchModel);
            if ($typesenseSearch->getId()) {
                $typesenseSearch = $this->getById($typesenseSearch->getId());
            } else {
                $typesenseSearch->setId($typesenseSearchModel->getId());
            }
        } catch (CouldNotSaveException $e) {
            throw new CouldNotSaveException(__('Could not save the data'));
        }
        return $typesenseSearch;
    }

    /**
     * Delete the data by job id
     *
     * @param int $jobId
     * @return bool
     * @throws LocalizedException
     */
    public function deleteById($jobId)
    {
        $typesenseSearchObj = $this->typesenseSearchFactory->create();
        $this->typesenseSearchResource->load($typesenseSearchObj, $jobId);
        $this->typesenseSearchResource->delete($typesenseSearchObj);
        if ($typesenseSearchObj->isDeleted()) {
            return true;
        }
        return false;
    }

    /**
     * Get list of Data
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return TypesenseSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->collectionFactory->create();

        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResults = $this->searchResultFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }
}
