<?php
namespace Conversionbox\Predictivesearch\Model;

use Conversionbox\Predictivesearch\Api\LandingPageInterface;
use Magento\Framework\App\ResourceConnection;
use Conversionbox\Predictivesearch\Model\LandingPageFactory;
use Magento\Framework\Exception\LocalizedException;

class Landing  implements  LandingPageInterface
{
    protected $scopeConfig;
    protected $resource;
    protected $landingPageFactory;
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

    public function __construct(
        ResourceConnection $resource,
        LandingPageFactory $landingPageFactory
    ) {
        $this->resource = $resource;
        $this->landingPageFactory = $landingPageFactory;

    }
 /**
     * Get paginated data from conversion landing page
     *
     * @param int $page
     * @param int $pageSize
     * @return array
     */
    public function getData($page=1, $pageSize=10)
  {
    $connection = $this->resource->getConnection();
    // Replace 'custom_table' with your custom table name
    $tableName = $this->resource->getTableName('conversionbox_landing_page');
    
    // Calculate offset
    $offset = ($page - 1) * $pageSize;

    // Get total count
    $selectCount =  $connection->select()
        ->from($tableName, 'COUNT(*)');
    $totalCount = $connection->fetchOne($selectCount);

    // Define the query with LIMIT and OFFSET for pagination
    $select = $connection->select()
        ->from($tableName)
        ->limit($pageSize, $offset);

    // Execute the query and fetch the data
    $data = $connection->fetchAll($select);
     $value = ['total_count' => $totalCount,
        'total_pages' => ceil($totalCount / $pageSize),
        'current_page' => $page];
    // Return data with metadata
    return [
        'data' => $data,
        $value 
    ];
  }
    /**
     * Edit a landing page by ID
     *
     * @param int $id
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
    public function updateLandingPage($landingPageId, $title = null, $urlkey = null, $isActive = null,$metaTitle=null,$metaDescription = null,$metaKeywords=null,$content=null,$configuration=null,$customJs=null,$customCss=null)
    {
        echo "hi"; exit;
        // Load the landing page by ID
        $landingPage = $this->landingPageFactory->create()->load($landingPageId);

        if (!$landingPage->getId()) {
            throw new LocalizedException(__('Landing Page not found.'));
        }

        if ($title !== null) {
            $landingPage->setData(self::FIELD_TITLE, (string) $title);
        }
        if ($urlkey !== null) {
            $landingPage->setData(self::FIELD_URL_KEY, (string) $urlkey);
        }
        if ($isActive !== null) {
            $landingPage->setData(self::FIELD_IS_ACTIVE, (bool) $isActive);
        }
        if ($metaTitle !== null) {
            $landingPage->setData(self::FIELD_META_TITLE, (string) $metaTitle);
        }
        if ($metaDescription !== null) {
            $landingPage->setData(self::FIELD_META_DESCRIPTION,(string) $metaDescription);
        }
        if ($metaKeywords !== null) {
            $landingPage->setData(self::FIELD_META_KEYWORDS,(string) $metaKeywords);
        }
        if ($content !== null) {
            $landingPage->setData(self::FIELD_CONTENT,(string) $content);
        }
        if ($configuration !== null) {
            $landingPage->setData(self::FIELD_CONFIGURATION,(string) $configuration);
        }
        if ($customJs !== null) {
            $landingPage->setData(self::FIELD_CUSTOM_JS,(string)$customJs);
        }
        if ($customCss !== null) {
            $landingPage->setData(self::FIELD_CUSTOM_CSS, (string) $customCss);
        }
        // Save the updated landing page
        try {
            $landingPage->save();
            return "Landing Page updated successfully!";
        } catch (\Exception $e) {
            throw new LocalizedException(__('Error saving the landing page: %1', $e->getMessage()));
        }
    }
}
