<?php

namespace Conversionbox\Predictivesearch\Block;

use Conversionbox\Predictivesearch\Model\LandingPage as LandingPageModel;
use Conversionbox\Predictivesearch\Model\LandingPageFactory;
use Magento\Cms\Model\Template\FilterProvider;
use Conversionbox\Predictivesearch\Helper\LandingPageHelper;
use Magento\Framework\View\Element\Template;

/**
 * @method int getPageId()
 */
class LandingPage extends Template
{
    /** @var FilterProvider */
    protected $filterProvider;

    /** @var LandingPageModel */
    protected $landingPage;

    /** @var LandingPageFactory */
    protected $landingPageFactory;
    /** @var LandingPageHelper */
     protected $landingPageHelper;
    /**
     * Construct
     *
     * @param Magento\Framework\View\Element\Template\Context $context
     * @param FilterProvider $filterProvider
     * @param LandingPageModel $landingPage
     * @param LandingPageFactory $landingPageFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        FilterProvider $filterProvider,
        LandingPageModel $landingPage,
        LandingPageFactory $landingPageFactory,
        LandingPageHelper $landingPageHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->filterProvider = $filterProvider;
        $this->landingPage = $landingPage;
        $this->landingPageFactory = $landingPageFactory;
        $this->landingPageHelper = $landingPageHelper;
    }

    /**
     * Retrieve Page instance
     *
     * @return LandingPageModel
     */
    public function getPage()
    {
        if (!$this->hasData('page')) {
            if ($this->getPageId()) {
                /** @var LandingPageModel $page */
                $page = $this->landingPageFactory->create();
                $page->setStoreId($this->_storeManager->getStore()->getId())->load($this->getPageId(), 'url_key');
            } else {
                $page = $this->landingPage;
            }
            $this->setData('page', $page);
        }

        return $this->getData('page');
    }

    /**
     * Prepare global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
         parent::_prepareLayout();
         $page = $this->getPage();
         $this->pageConfig->addBodyClass('conversionbox-landingpage-' . $page->getUrlKey());
         $metaTitle = $page->getMetaTitle();
         $this->pageConfig->getTitle()->set($page->getTitle() ? $page->getTitle() : $metaTitle);
         $this->pageConfig->setKeywords($page->getMetaKeywords());
         $this->pageConfig->setDescription($page->getMetaDescription());
        return $this;
    }

    public function getLandingPageContent()
    {
        return $this->filterProvider->getPageFilter()->filter($this->getPage()->getContent());
    }

    public function getLandingCustomJs()
    {
        $customJs = $this->getPage()->getCustomJs();

        if (!$customJs) {
            return '';
        }

        return '<script type="text/javascript">' . $customJs . '</script>';
    }

    public function getLandingCustomCss()
    {
        $customCss = $this->getPage()->getCustomCss();

        if (!$customCss) {
            return '';
        }

        return '<style type="text/css">' . $customCss . '</style>';
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        return [\Conversionbox\Predictivesearch\Model\LandingPage::CACHE_TAG . '_' . $this->getPage()->getId()];
    }
     protected function isLandingPage()
     {
        $landingPageId = $this->getRequest()->getParam('landing_page_id');
        if (!$landingPageId) {
            return false;
        }
        return true;
     }
    public function getLandingPageId()
    {
        return $this->isLandingPage() ? $this->getCurrentLandingPage()->getId() : '';
    }

    public function getLandingPageConfiguration()
    {
        return $this->isLandingPage() ? $this->getCurrentLandingPage()->getConfiguration() :"";
    }
    public function getCurrentLandingPage(): LandingPageModel|null|false
    {
        $landingPageId = $this->getRequest()->getParam('landing_page_id');
        if (!$landingPageId) {
            return null;
        }
        return $this->landingPageHelper->getLandingPage($landingPageId);
    }
}
