<?php
/**
 * InvalidateIndex
 *
 * @copyright Copyright © 2022 Maison du Net. All rights reserved.
 * @author    vincent@maisondunet.com
 */

namespace Kraken\CustomerGroupFixedPrice\Observer\Config;

use Magento\Catalog\Model\Indexer\Product\Price\Processor;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Indexer\IndexerRegistry;

/**
 * Invalidate product price index
 * Bind on configuration change event for section "krakenink_general"
 */
class InvalidateIndex implements ObserverInterface
{

    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * Constructor
     *
     * @param IndexerRegistry $indexerRegistry
     */
    public function __construct(
        IndexerRegistry $indexerRegistry
    ) {
        $this->indexerRegistry = $indexerRegistry;
    }

    public function execute(Observer $observer)
    {
        // When configuration changes we need to invalidate the price index
        $this->indexerRegistry->get(Processor::INDEXER_ID)->invalidate();
    }
}
