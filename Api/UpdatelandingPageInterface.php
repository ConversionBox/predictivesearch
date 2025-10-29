<?php
namespace Conversionbox\Predictivesearch\Api;

interface UpdatelandingPageInterface
{
    /**
     * Edit a landing page by ID.
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
     * @throws \Magento\Framework\Exception\LocalizedException
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
    );
}
