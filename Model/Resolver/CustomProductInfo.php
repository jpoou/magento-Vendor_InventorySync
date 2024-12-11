<?php
namespace Vendor\InventorySync\Model\Resolver;

use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Api\SearchCriteriaBuilder;

class CustomProductInfo implements ResolverInterface
{
    private ProductRepository $productRepository;
    private CustomerSession $customerSession;
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    public function __construct(
        ProductRepository $productRepository,
        CustomerSession $customerSession,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->productRepository = $productRepository;
        $this->customerSession = $customerSession;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        /*if (!$this->customerSession->isLoggedIn()) {
            throw new GraphQlAuthorizationException(__("You must be logged in to access this information."));
        }*/

        if (!isset($args['skus']) || !is_array($args['skus'])) {
            throw new LocalizedException(__("Invalid 'skus' argument."));
        }

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('sku', $args['skus'], 'in')
            ->create();

        $products = $this->productRepository->getList($searchCriteria)->getItems();

        if (empty($products)) {
            throw new LocalizedException(__("No products found."));
        }

        $result = [];
        foreach ($products as $product) {
            $result[] = [
                'sku' => $product->getSku(),
                'name' => $product->getName(),
                'price' => $product->getPrice(),
                'qty' => 1
            ];
        }

        return $result;
    }
}
