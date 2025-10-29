<?php

namespace Conversionbox\Predictivesearch\ViewModel\Adminhtml\Landingpage;

use Conversionbox\Predictivesearch\Model\ResourceModel\LandingPage\CollectionFactory as LandingPageCollectionFactory;

class Suggestions implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /** @var LandingPageCollectionFactory */
    private $landingPageCollectionFactory;

    /**
     * @param LandingPageCollectionFactory $landingPageCollectionFactory
     */
    public function __construct(LandingPageCollectionFactory $landingPageCollectionFactory)
    {
        $this->landingPageCollectionFactory = $landingPageCollectionFactory;
    }

    /**
     * @return int
     */
    public function getNbOfLandingPages()
    {
        $landingPageCollection = $this->landingPageCollectionFactory->create();

        return $landingPageCollection->getSize();
    }
}
