<?php
namespace Conversionbox\Predictivesearch\Api;

interface LandingPageInterface
{
    public const TABLE_NAME = 'conversionbox_landing_page';

    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    public const FIELD_LANDING_PAGE_ID = 'landing_page_id';
    public const FIELD_STORE_ID = 'store_id';
    public const FIELD_URL_KEY = 'url_key';
    public const FIELD_IS_ACTIVE = 'is_active';
    public const FIELD_TITLE = 'title';
    public const FIELD_DATE_FROM = 'date_from';
    public const FIELD_DATE_TO = 'date_to';
    public const FIELD_META_TITLE = 'meta_title';
    public const FIELD_META_DESCRIPTION = 'meta_description';
    public const FIELD_META_KEYWORDS = 'meta_keywords';
    public const FIELD_CONTENT = 'content';
    public const FIELD_CUSTOM_JS = 'custom_js';
    public const FIELD_CUSTOM_CSS = 'custom_css';
    public const FIELD_CONFIGURATION ='configuration';
    
    /**
     * Get paginated data
     *
     * @param int $page
     * @param int $pageSize
     * @return array
     */
    public function getData($page = 1, $pageSize = 10);
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
