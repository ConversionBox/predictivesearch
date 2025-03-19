<?php

namespace Conversionbox\Predictivesearch\Controller\Adminhtml\Landingpage;

use Conversionbox\Predictivesearch\Model\LandingPageFactory;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;

class Save extends AbstractAction
{
    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var SessionManagerInterface
     */
    protected $backendSession;

    /**
     * PHP Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param SessionManagerInterface $backendSession
     * @param LandingPageFactory $landingPageFactory
     * @param MerchandisingHelper $merchandisingHelper
     * @param StoreManagerInterface $storeManager
     * @param DataPersistorInterface $dataPersistor
     * @param CollectionFactory $customerGroupCollectionFactory
     * @return Save
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        SessionManagerInterface $backendSession,
        LandingPageFactory $landingPageFactory,
        StoreManagerInterface $storeManager,
        DataPersistorInterface $dataPersistor,
    ) {
        $this->dataPersistor = $dataPersistor;
        parent::__construct(
            $context,
            $backendSession,
            $landingPageFactory,
            $storeManager
        );
    }

    /**
     * Execute the action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $data = $this->getRequest()->getPostValue();

        if (!empty($data)) {
            if (empty($data['landing_page_id'])) {
                $data['landing_page_id'] = null;
            }
            $landingPageId = $data['landing_page_id'];

            /** @var \Conversionbox\Predictivesearch\Model\LandingPage $landingPage */
            $landingPage = $this->landingPageFactory->create();

            if ($landingPageId) {
                $landingPage->getResource()->load($landingPage, $landingPageId);

                if (!$landingPage->getId()) {
                    $this->messageManager->addErrorMessage(__('This landing page does not exist.'));

                    return $resultRedirect->setPath('*/*/');
                }
            }

            $landingPage->setData($data);

            try {
                $landingPage->getResource()->save($landingPage);

               

                $this->messageManager->addSuccessMessage(__('The landing page has been saved.'));
                $this->dataPersistor->clear('conversionbox_predictivesearch_landing_page');

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $landingPage->getId()]);
                }

                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while saving the landing page. %1', $e->getMessage())
                );
            }

            $this->dataPersistor->set('landing_page', $data);

            return $resultRedirect->setPath('*/*/edit', ['id' => $landingPageId]);
        }

        return $resultRedirect->setPath('*/*/');
    }
}
