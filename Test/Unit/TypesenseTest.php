<?php
namespace Conversionbox\Predictivesearch\Test\Unit;

use PHPUnit\Framework\TestCase;
use Conversionbox\Predictivesearch\Helper\Typesense;
use Magento\Framework\App\Helper\Context;
use Typesense\Client;
use Typesense\Collections;
use Typesense\Collection;
use Typesense\Documents;

class TypesenseTest extends TestCase {
    /**
     * typesense Helper
     */
    protected $typesenseHelper;
    /**
     * Client
     */
    protected $client;

    protected function setUp(): void {
        $context = $this->createMock(Context::class);

        $this->typesenseHelper = $this->getMockBuilder(Typesense::class)
            ->setConstructorArgs([$context])
            ->onlyMethods(['getClient'])
            ->getMock();

        $this->client = $this->createMock(Client::class);

        $this->typesenseHelper->method('getClient')->willReturn($this->client);

        // Mock the Collections class
        $collections = $this->createMock(Collections::class);

        // Mock Collection class
        $collection = $this->createMock(Collection::class);

        $documents = $this->createMock(Documents::class);

        // Mock Documents class
        $collections->method('create')
            ->willReturn([
                'name' => 'products',
                'num_documents' => 0,
                'fields' => [
                    ['name' => 'name', 'type' => 'string'],
                    ['name' => 'price', 'type' => 'float'],
                    ['name' => 'category', 'type' => 'string', 'facet' => true]
                ],
                'default_sorting_field' => 'price'
            ]);
           $this->client->collections = $collections;
         $documents->method('import')->willReturn([
            ['id' => '1', 'name' => 'Product 1', 'price' => 100, 'category' => 'Category 1'],
            ['id' => '2', 'name' => 'Product 2', 'price' => 200, 'category' => 'Category 2']
         ]);
            
        $searchResults = [
            'hits' => [
                ['document' => ['name' => 'Product 1']]
            ]
        ];

        $documents->method('search')
            ->willReturn($searchResults);

        // Set up method mocks for the Collections class
        $collections->method('__get')
            ->willReturn($collection);

        // // Set up method mocks for the Collection class
        $collection->method('getDocuments')
            ->willReturn($documents);

        // // Set the mocked Collections class on the Client mock
        $this->client->method('getCollections')
            ->willReturn($collections);
    }
    /**
     * Testcase for typesense search
     */
    public function testTypesenseSearch()
    {
      $schema = [
            'name' => 'products',
            'fields' => [
                ['name' => 'name', 'type' => 'string'],
                ['name' => 'price', 'type' => 'float'],
                ['name' => 'category', 'type' => 'string', 'facet' => true]
            ],
            'default_sorting_field' => 'price'
        ];

        // Create a collection
        $this->client->collections->create($schema);

        // Index documents
        $documentsData = [
            ['id' => '1', 'name' => 'Product 1', 'price' => 100, 'category' => 'Category 1'],
            ['id' => '2', 'name' => 'Product 2', 'price' => 200, 'category' => 'Category 2']
        ];
       $documents = $this->client->collections->__get('products')->getDocuments();
        $documents->import($documentsData);
       
        // Perform a search query
        $searchParameters = [
            'q' => 'Product 1',
            'query_by' => 'name'
        ];

        $actualResults = $documents->search($searchParameters);
        // Validate the search results
        $this->assertCount(1, $actualResults['hits']);
        $this->assertEquals('Product 1', $actualResults['hits'][0]['document']['name']);
    }
}
