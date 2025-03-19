<?php
namespace Conversionbox\Predictivesearch\Model;

use Conversionbox\Predictivesearch\Api\UpdatelandingPageInterface;
use Conversionbox\Predictivesearch\Model\LandingPageFactory;
use Magento\Framework\Exception\LocalizedException;

class UpdateLanding  implements  UpdatelandingPageInterface
{
    protected $landingPageFactory;


    public function __construct(
        LandingPageFactory $landingPageFactory
    ) {
        $this->landingPageFactory = $landingPageFactory;

    }
      /**
     * Edit a landing page by ID
     *
     * @param int $landingPageId
     * @param string|null $title
     * @param string|null $urlkey
     * @param string|null $isActive
     * @param string|null $metaTitle
     * @param string|null $metaDescription
     * @param string|null $metaKeywords
     * @param string|null $content
     * @param string|null $configuration
     * @param string|null $customJs
     * @param string|null $customCss
     * @return string
     * @throws LocalizedException
     */
    public function updateLandingPage(
        $landingPageId,
        $title = null,
        $urlkey = null,
        $isActive = null,
        $metaTitle = null,
        $metaDescription = null,
        $metaKeywords = null,
        $content = null,
        $configuration = null,
        $customJs = null,
        $customCss = null
    )
    {
        // Load the landing page by ID
        $landingPage = $this->landingPageFactory->create()->load($landingPageId);

        if (!$landingPage->getId()) {
            throw new LocalizedException(__('Landing Page not found.'));
        }

        // Update fields if provided
        if ($title !== null) {
            $landingPage->setData('title', (string) $title);
        }
        if ($urlkey !== null) {
            $landingPage->setData('url_key', (string) $urlkey);
        }
        if ($isActive !== null) {
            $landingPage->setData('is_active', (bool) $isActive);
        }
        if ($metaTitle !== null) {
            $landingPage->setData('meta_title', (string) $metaTitle);
        }
        if ($metaDescription !== null) {
            $landingPage->setData('meta_description', (string) $metaDescription);
        }
        if ($metaKeywords !== null) {
            $landingPage->setData('meta_keywords', (string) $metaKeywords);
        }
        if ($content !== null) {
            $landingPage->setData('content', (string) $content);
        }
        if ($configuration !== null) {
            $landingPage->setData('configuration', (string) $configuration);
        }
        if ($customJs !== null) {
            $landingPage->setData('custom_js', (string) $customJs);
        }
        if ($customCss !== null) {
            $landingPage->setData('custom_css', (string) $customCss);
        }

        // Save the updated landing page
        try {
            $landingPage->save();
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
$cacheManager = $objectManager->get(\Magento\Framework\App\Cache\Manager::class);
$cacheManager->flush($cacheManager->getAvailableTypes());
            return "Landing Page updated successfully!";
        } catch (\Exception $e) {
            throw new LocalizedException(__('Error saving the landing page: %1', $e->getMessage()));
        }
    }
}
