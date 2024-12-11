<?php
namespace Vendor\InventorySync\Cron;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\HTTP\Client\Curl;
use Psr\Log\LoggerInterface;

class SyncInventory
{
    private ProductRepositoryInterface $productRepository;
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    private Curl $curl;
    private LoggerInterface $logger;
    private const string API_ENDPOINT = 'https://dummyjson.com/products';

    public function __construct(
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Curl $curl,
        LoggerInterface $logger
    ) {
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->curl = $curl;
        $this->logger = $logger;
    }

    public function execute(): void
    {
        try {
            $data = $this->fetchData();

            if (empty($data) || !isset($data['products'])) {
                $this->logger->info('No product data retrieved from API.');
                return;
            }

            foreach ($data['products'] as $item) {
                $this->updateProductData($item);
            }

            $this->logger->info('Product synchronization completed successfully.');
        } catch (\Exception $e) {
            $this->logger->error('Error during product synchronization: ' . $e->getMessage());
        }
    }

    private function fetchData(): array
    {
        try {
            $this->curl->get(self::API_ENDPOINT);
            $response = $this->curl->getBody();
            return json_decode($response, true) ?? [];
        } catch (\Exception $e) {
            $this->logger->error('Failed to fetch data from API: ' . $e->getMessage());
            return [];
        }
    }

    private function updateProductData(array $item): void
    {
        try {
            if (empty($item['sku']) || empty($item['stock']) || empty($item['price'])) {
                $this->logger->warning('Invalid item format: ' . json_encode($item));
                return;
            }

            $sku = (string) $item['sku'];
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('sku', $sku)
                ->create();

            $products = $this->productRepository->getList($searchCriteria)->getItems();

            foreach ($products as $product) {
                $product->setCustomAttribute('quantity_and_stock_status', [
                    'qty' => $item['stock'],
                    'is_in_stock' => $item['stock'] > 0
                ]);
                $product->setPrice($item['price']);

                $this->productRepository->save($product);
                $this->logger->info('Updated data for SKU: ' . $sku);
            }
        } catch (\Exception $e) {
            $this->logger->error('Error updating data for SKU ' . $item['sku'] . ': ' . $e->getMessage());
        }
    }
}
