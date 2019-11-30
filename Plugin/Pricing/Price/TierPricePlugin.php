<?php
/**
 * @category    KrakenCommerce
 * @copyright   Copyright (c) 2019 Kraken, LLC
 */
namespace Kraken\CustomerGroupFixedPrice\Plugin\Pricing\Price;

use Kraken\CustomerGroupFixedPrice\Helper\Config;

class TierPricePlugin
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
     * Fixed Price customer groups should not see tiered pricing
     *
     * @param \Magento\Catalog\Pricing\Price\TierPrice $subject
     * @param callable $proceed
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundGetTierPriceList(\Magento\Catalog\Pricing\Price\TierPrice $subject, callable $proceed)
    {
        if ($this->config->isCurrentCustomerFixedCustomerGroup()) {
            return [];
        }
        return $proceed();
    }
}