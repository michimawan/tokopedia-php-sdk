<?php

declare(strict_types=1);

namespace Gandung\Tokopedia\Service;

use InvalidArgumentException;

use function http_build_query;

/**
 * @author Paulus Gandung Prakosa <rvn.plvhx@gmail.com>
 */
class Product extends Resource
{
    /**
     * @var int
     */
    const SORT_DEFAULT = 1;

    /**
     * @var int
     */
    const SORT_LAST_UPDATE_PRODUCT = 2;

    /**
     * @var int
     */
    const SORT_HIGHEST_SOLD = 3;

    /**
     * @var int
     */
    const SORT_LOWEST_SOLD = 4;

    /**
     * @var int
     */
    const SORT_HIGHEST_PRICE = 5;

    /**
     * @var int
     */
    const SORT_LOWEST_PRICE = 6;

    /**
     * @var int
     */
    const SORT_PRODUCT_NAME_ASCENDING = 7;

    /**
     * @var int
     */
    const SORT_PRODUCT_NAME_DESCENDING = 8;

    /**
     * @var int
     */
    const SORT_FEWEST_STOCK = 9;

    /**
     * @var int
     */
    const SORT_HIGHEST_STOCK = 10;

    /**
     * Get product info (can filtered by product ID and product URL optionally).
     *
     * @param int $productID Product ID.
     * @param string $productUrl Product URL.
     * @return string
     */
    public function getProductInfo(int $productID = 0, string $productUrl = '')
    {
        $endpoint = sprintf(
            '/inventory/v1/fs/%s/product/info',
            $this->getFulfillmentServiceID()
        );

        $queryParams = [];

        if (!empty($productID)) {
            $queryParams['product_id'] = $productID;
        }

        if (!empty($productUrl)) {
            $queryParams['product_url'] = $productUrl;
        }

        $serialized = http_build_query($queryParams);
        $response   = $this->call(
            'GET',
            sprintf(
                '%s%s',
                $endpoint,
                empty($serialized) ? '' : ('?' . $serialized)
            )
        );

        return $this->getContents($response);
    }

    /**
     * Get product info from related shop ID.
     *
     * @param int $shopID Shop ID.
     * @param int $page Current page.
     * @param int $perPage How much item showed per page.
     * @param int $sort Sort type.
     * @return string
     * @throws InvalidArgumentException When given an invalid sort type.
     */
    public function getProductInfoFromRelatedShopID(int $shopID, int $page, int $perPage, int $sort)
    {
        $this->validateSortOptions($sort);

        $endpoint = sprintf(
            '/inventory/v1/fs/%s/product/info',
            $this->getFulfillmentServiceID()
        );

        $queryParams             = [];
        $queryParams['shop_id']  = $shopID;
        $queryParams['page']     = $page;
        $queryParams['per_page'] = $perPage;
        $queryParams['sort']     = $sort;

        $response = $this->call(
            'GET',
            sprintf('%s?%s', $endpoint, http_build_query($queryParams))
        );

        return $this->getContents($response);
    }

    /**
     * Get all product variants by category ID.
     *
     * @param int $categoryID Category ID.
     * @return string
     */
    public function getAllVariantsByCategoryID(int $categoryID)
    {
        $endpoint = sprintf(
            '/inventory/v1/fs/%s/category/get_variant',
            $this->getFulfillmentServiceID()
        );

        $queryParams           = [];
        $queryParams['cat_id'] = $categoryID;

        $response = $this->call(
            'GET',
            sprintf('%s?%s', $endpoint, http_build_query($queryParams))
        );

        return $this->getContents($response);
    }

    /**
     * Get all product variants by product ID.
     *
     * @param int $productID Product ID.
     * @return string
     */
    public function getAllVariantsByProductID(int $productID)
    {
        $response = $this->call(
            'GET',
            sprintf(
                '/inventory/v1/fs/%s/product/variant/%d',
                $this->getFulfillmentServiceID(),
                $productID
            )
        );

        return $this->getContents($response);
    }

    /**
     * Get all etalase.
     *
     * @param int $shopID Shop ID.
     * @return string
     */
    public function getAllEtalase(int $shopID)
    {
        $endpoint = sprintf(
            '/inventory/v1/fs/%s/product/etalase',
            $this->getFulfillmentServiceID()
        );

        $queryParams            = [];
        $queryParams['shop_id'] = $shopID;

        $response = $this->call(
            'GET',
            sprintf('%s?%s', $endpoint, http_build_query($queryParams))
        );

        return $this->getContents($response);
    }

    /**
     * Create products.
     *
     * @param int $shopID Shop ID.
     * @param array $data
     * @return string
     */
    public function createProducts(int $shopID, array $data)
    {
        $endpoint = sprintf(
            '/v2/products/fs/%s/create',
            $this->getFulfillmentServiceID()
        );

        $queryParams            = [];
        $queryParams['shop_id'] = $shopID;

        $response = $this->call(
            'POST',
            sprintf('%s?%s', $endpoint, http_build_query($queryParams)),
            $data
        );

        return $this->getContents($response);
    }

    /**
     * Edit product.
     *
     * @param int $shopID Shop ID.
     * @param array $data
     * @return string
     */
    public function editProduct(int $shopID, array $data)
    {
        $endpoint = sprintf(
            '/v2/products/fs/%s/edit',
            $this->getFulfillmentServiceID()
        );

        $queryParams            = [];
        $queryParams['shop_id'] = $shopID;

        $response = $this->call(
            'PATCH',
            sprintf('%s?%s', $endpoint, http_build_query($queryParams)),
            $data
        );

        return $this->getContents($response);
    }

    /**
     * Check product upload status.
     *
     * @param int $shopID Shop ID.
     * @param int $uploadID Product upload ID.
     * @return string
     */
    public function checkUploadStatus(int $shopID, int $uploadID)
    {
        $endpoint = sprintf(
            '/v2/products/fs/%s/status/%d',
            $this->getFulfillmentServiceID(),
            $uploadID
        );

        $queryParams            = [];
        $queryParams['shop_id'] = $shopID;

        $response = $this->call(
            'GET',
            sprintf('%s?%s', $endpoint, http_build_query($queryParams))
        );

        return $this->getContents($response);
    }

    /**
     * Set product status to active.
     *
     * @param int $shopID Shop ID.
     * @param array $data
     * @return string
     */
    public function setActiveProduct(int $shopID, array $data)
    {
        $endpoint = sprintf(
            '/v1/products/fs/%s/active',
            $this->getFulfillmentServiceID()
        );

        $queryParams            = [];
        $queryParams['shop_id'] = $shopID;

        $response = $this->call(
            'POST',
            sprintf('%s?%s', $endpoint, http_build_query($queryParams)),
            $data
        );

        return $this->getContents($response);
    }

    /**
     * Set product status to inactive.
     *
     * @param int $shopID Shop ID.
     * @param array $data
     * @return string
     */
    public function setInactiveProduct(int $shopID, array $data)
    {
        $endpoint = sprintf(
            '/v1/products/fs/%s/inactive',
            $this->getFulfillmentServiceID()
        );

        $queryParams            = [];
        $queryParams['shop_id'] = $shopID;

        $response = $this->call(
            'POST',
            sprintf('%s?%s', $endpoint, http_build_query($queryParams)),
            $data
        );

        return $this->getContents($response);
    }

    /**
     * Update price for defined product.
     *
     * @param int $shopID Shop ID.
     * @param array $data
     * @return string
     */
    public function updatePriceOnly(int $shopID, array $data)
    {
        $endpoint = sprintf(
            '/inventory/v1/fs/%s/price/update',
            $this->getFulfillmentServiceID()
        );

        $queryParams            = [];
        $queryParams['shop_id'] = $shopID;

        $response = $this->call(
            'POST',
            sprintf('%s?%s', $endpoint, http_build_query($queryParams)),
            $data
        );

        return $this->getContents($response);
    }

    /**
     * Update stock for defined product.
     *
     * @param int $shopID Shop ID.
     * @param array $data
     * @return string
     */
    public function updateStockOnly(int $shopID, array $data)
    {
        $endpoint = sprintf(
            '/inventory/v1/fs/%s/stock/update',
            $this->getFulfillmentServiceID()
        );

        $queryParams            = [];
        $queryParams['shop_id'] = $shopID;

        $response = $this->call(
            'POST',
            sprintf('%s?%s', $endpoint, http_build_query($queryParams)),
            $data
        );

        return $this->getContents($response);
    }

    /**
     * Delete a product or list of products.
     *
     * @param int $shopID Shop ID.
     * @param array $data
     * @return string
     */
    public function deleteProduct(int $shopID, array $data)
    {
        $endpoint = sprintf(
            '/v3/products/fs/%s/delete',
            $this->getFulfillmentServiceID()
        );

        $queryParams            = [];
        $queryParams['shop_id'] = $shopID;

        $response = $this->call(
            'POST',
            sprintf('%s?%s', $endpoint, http_build_query($queryParams)),
            $data
        );

        return $this->getContents($response);
    }

    /**
     * @return void
     * @throws InvalidArgumentException When sort options is invalid.
     */
    private function validateSortOptions(int $sort)
    {
        switch ($sort) {
            case SORT_DEFAULT:
            case SORT_LAST_UPDATE_PRODUCT:
            case SORT_HIGHEST_SOLD:
            case SORT_LOWEST_SOLD:
            case SORT_HIGHEST_PRICE:
            case SORT_LOWEST_PRICE:
            case SORT_PRODUCT_NAME_ASCENDING:
            case SORT_PRODUCT_NAME_DESCENDING:
            case SORT_FEWEST_STOCK:
            case SORT_HIGHEST_STOCK:
                break;
            default:
                throw new InvalidArgumentException("Invalid sort options.");
        }

        return;
    }
}
