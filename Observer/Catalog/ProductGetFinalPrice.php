<?php
/**
 * Allows an admin to specify a list of Customer Groups who will receive specific Tiered Pricing, even if that Tiered Pricing amount is higher than the normal product price
 * Copyright (C) 2019 Kraken, LLC
 *
 * This file included in Kraken/CustomerGroupFixedPrice is licensed under OSL 3.0
 *
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license
 */

namespace Kraken\CustomerGroupFixedPrice\Observer\Catalog;

use Kraken\CustomerGroupFixedPrice\Helper\Config;

class ProductGetFinalPrice implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * ProductGetFinalPrice constructor.
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $observer->getEvent()->getProduct();
        $qty = $observer->getEvent()->getQty();

        $fixedPrice = $this->config->getCustomerGroupFixedPrice($product, $qty);

        if ($fixedPrice) {
            $product->setFinalPrice($fixedPrice);
            $product->setData('final_price_fixed_customer_group_price', $fixedPrice);
        }

        return;
    }
}
