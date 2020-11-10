<?php

declare(strict_types=1);

namespace Gandung\Tokopedia\Service;

/**
 * @author Paulus Gandung Prakosa <rvn.plvhx@gmail.com>
 */
class Logistic extends Resource
{
    /**
     * Get shipment info.
     *
     * @param int $shopID Shop ID.
     * @return string
     */
    public function getShipmentInfo(int $shopID)
    {
        $endpoint   = sprintf(
            '/v2/logistic/fs/%s/info',
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
     * Update shipment info.
     *
     * @param int $shopID
     * @param array $data
     * @return string
     */
    public function updateShipmentInfo(int $shopID, array $data)
    {
        $endpoint = sprintf(
            '/v2/logistic/fs/%s/update',
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
}
