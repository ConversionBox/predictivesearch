<?php

namespace Conversionbox\Predictivesearch\Controller\Adminhtml\Landingpage;

use Conversionbox\Predictivesearch\Model\LandingPageFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Store\Model\StoreManagerInterface;

abstract class AbstractAction extends \Magento\Backend\App\Action
{

    /** @var SessionManagerInterface */
    protected $backendSession;

    /** @var LandingPageFactory */
    protected $landingPageFactory;


    /** @var StoreManagerInterface */
    protected $storeManager;

    /**
     * @param Context $context
     * @param SessionManagerInterface $backendSession
     * @param LandingPageFactory $landingPageFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        SessionManagerInterface $backendSession,
        LandingPageFactory $landingPageFactory,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);

        $this->backendSession = $backendSession;
        $this->landingPageFactory = $landingPageFactory;
        $this->storeManager = $storeManager;
    }

    /** @return bool */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Conversionbox_Predictivesearch::manage');
    }

    /** @return \Conversionbox\Predictivesearch\Model\LandingPage */
    protected function initLandingPage()
    {
        $landingPageId = (int) $this->getRequest()->getParam('id');

        /** @var \Conversionbox\Predictivesearch\Model\LandingPage $landingPage */
        $landingPage = $this->landingPageFactory->create();

        if ($landingPageId) {
            $landingPage->getResource()->load($landingPage, $landingPageId);
            if (!$landingPage->getId()) {
                return null;
            }
        }

        $this->backendSession->setData('conversionbox_landing_page', $landingPage);

        return $landingPage;
    }
}
