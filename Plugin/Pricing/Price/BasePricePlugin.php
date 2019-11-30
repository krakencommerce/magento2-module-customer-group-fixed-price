<?php
/**
 * @category    KrakenCommerce
 * @copyright   Copyright (c) 2019 Kraken, LLC
 */
namespace Kraken\CustomerGroupFixedPrice\Plugin\Pricing\Price;

use Kraken\CustomerGroupFixedPrice\Helper\Config;
use Magento\Catalog\Pricing\Price\BasePrice;

/**
 * Class BasePricePlugin
 * @package Kraken\CustomerGroupFixedPrice\Plugin\Pricing\Price
 */
class BasePricePlugin
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
     * @param BasePrice $subject
     * @param callable $proceed
     * @return float|null
     */
    public function aroundGetValue(BasePrice $subject, callable $proceed)
    {
        $fixedPrice = $this->config->getCustomerGroupFixedPrice($subject->getProduct());

        if ($fixedPrice) {
            return $fixedPrice;
        }

        return $proceed();
    }
}