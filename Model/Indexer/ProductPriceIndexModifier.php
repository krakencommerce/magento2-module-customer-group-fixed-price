<?php
/**
 * ProductPriceIndexModifier
 *
 * @copyright Copyright © 2022 Maison du Net. All rights reserved.
 * @author    vincent@maisondunet.com
 */

namespace Kraken\CustomerGroupFixedPrice\Model\Indexer;

use Kraken\CustomerGroupFixedPrice\Helper\Config;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Tierprice;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\IndexTableStructure;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\PriceModifierInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Zend_Db_Expr;

class ProductPriceIndexModifier implements PriceModifierInterface
{
    private ResourceConnection $resourceConnection;
    private string $connectionName;
    private AdapterInterface $connection;
    private Tierprice $tierPriceResource;
    private Config $config;

    public function __construct(
        ResourceConnection $resourceConnection,
        Tierprice $tierPriceResource,
        Config $config,
        string $connectionName = 'indexer'
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->connectionName = $connectionName;
        $this->tierPriceResource = $tierPriceResource;
        $this->config = $config;
    }

    /**
     * Apply group price to temporary index price table
     *
     * @param IndexTableStructure $priceTable
     * @param array               $entityIds
     *
     * @return void
     */
    public function modifyPrice(IndexTableStructure $priceTable, array $entityIds = []): void
    {
        $connection = $this->getConnection();

        $where = [
            $priceTable->getEntityField() . ' IN (?)' => $entityIds,
            $priceTable->getCustomerGroupField() . ' IN (?)' => $this->config->getCustomerGroups(),
            $priceTable->getTierPriceField() . ' > ' . $priceTable->getOriginalPriceField(),
            $priceTable->getTierPriceField() . ' IS NOT NULL '
        ];

        $connection->update(
            $priceTable->getTableName(),
            [
                $priceTable->getOriginalPriceField() => new Zend_Db_Expr($priceTable->getTierPriceField()),
                $priceTable->getFinalPriceField() => new Zend_Db_Expr($priceTable->getTierPriceField())
            ],
            $where
        );
    }

    /**
     * Get connection.
     *
     * @return AdapterInterface
     */
    private function getConnection(): AdapterInterface
    {
        if (!isset($this->connection)) {
            $this->connection = $this->resourceConnection->getConnection($this->connectionName);
        }

        return $this->connection;
    }
}
