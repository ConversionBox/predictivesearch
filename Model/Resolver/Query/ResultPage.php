<?php

declare(strict_types=1);

namespace Thecommerceshop\Predictivesearch\Model\Resolver\Query;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Thecommerceshop\Predictivesearch\Model\Resolver\Query\DataProvider\SearchResults;

class ResultPage implements ResolverInterface
{
    /**
     * @var SearchResults
     */
    private $dataProvider;

    /**
     * Constructor
     *
     * @param SearchResults $dataProvider
     */
    public function __construct(
        SearchResults $dataProvider
    ) {
        $this->dataProvider = $dataProvider;
    }

    /**
     * Resolve function
     *
     * @param Field $field
     * @param array $context
     * @param ResolveInfo $info
     * @param array $value
     * @param array $args
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $response = [];
        if (!isset($args['keyword'])) {
            throw new GraphQlInputException(__("please enter the any keyword and try again."));
        }
        $response = $this->dataProvider->performSearchAction($args);

        return $response;
    }
}
