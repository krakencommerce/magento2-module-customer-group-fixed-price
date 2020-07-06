<?php
/**
 * Copyright (C) 2019 Kraken, LLC
 *
 * This file included in Kraken/CustomerGroupFixedPrice is licensed under OSL 3.0
 *
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license
 */

namespace Kraken\CustomerGroupFixedPrice\Helper;

use Magento\Backend\Model\Session\Quote as BackendQuote;
use Magento\Checkout\Model\Session as FrontendQuote;
use Magento\Customer\Model\Context;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\SaleableInterface;

class Config
{
    /**
     * Customer Groups
     */
    const XML_PATH_CUSTOMER_GROUPS = 'krakenink_general/customer_group_pricing/customer_groups';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var BackendQuote
     */
    protected $backendQuoteSession;

    /**
     * @var FrontendQuote
     */
    protected $frontendQuoteSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var State
     */
    protected $state;

    /**
     * @var array
     */
    protected $productCache = [];

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param BackendQuote $backendQuoteSession
     * @param FrontendQuote $frontendQuoteSession
     * @param State $state
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Http\Context $httpContext,
        BackendQuote $backendQuoteSession,
        FrontendQuote $frontendQuoteSession,
        \Magento\Customer\Model\Session $customerSession,
        State $state
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->httpContext = $httpContext;
        $this->backendQuoteSession = $backendQuoteSession;
        $this->frontendQuoteSession = $frontendQuoteSession;
        $this->customerSession = $customerSession;
        $this->state = $state;
    }

    /**
     * Gets the list of Product Attributes specified in the Admin Configuration as an array of strings
     *
     * @return array
     */
    public function getCustomerGroups()
    {
        return explode(',', $this->scopeConfig->getValue(self::XML_PATH_CUSTOMER_GROUPS));
    }

    /**
     * Get Customer Group ID from session or admin quote
     *
     * @return int|mixed|null
     * @throws LocalizedException
     */
    protected function getCurrentCustomerGroup()
    {
        $customerGroupId = $this->httpContext->getValue(Context::CONTEXT_GROUP);

        if (
            !$customerGroupId
            && $this->state->getAreaCode() != Area::AREA_ADMINHTML
        ) {
            $customerGroupId = $this->customerSession->getCustomerGroupId();
        }

        // Only run this code if there is an active admin quote
        if (
            !$customerGroupId
            && $this->backendQuoteSession->getQuoteId()
            && $this->state->getAreaCode() == Area::AREA_ADMINHTML
        ) {
            $customerGroupId = $this->backendQuoteSession->getQuote()->getCustomerGroupId();
        }

        return $customerGroupId;
    }

    /**
     * @return bool
     * @throws LocalizedException
     */
    public function isCurrentCustomerFixedCustomerGroup()
    {
        $customerGroupId = $this->getCurrentCustomerGroup();
        $isCurrentCustomerFixedCustomerGroup = in_array($customerGroupId, $this->getCustomerGroups());
        return $isCurrentCustomerFixedCustomerGroup;
    }

    /**
     * If it exists, return fixed customer group price
     *
     * @param SaleableInterface $product
     * @param int $qty
     * @return float|null
     * @throws LocalizedException
     */
    public function getCustomerGroupFixedPrice(SaleableInterface $product, $qty = 1)
    {
        $isCurrentCustomerFixedCustomerGroup = $this->isCurrentCustomerFixedCustomerGroup();

        if (!$isCurrentCustomerFixedCustomerGroup) {
            return null;
        }

        $cacheKey = $product->getSku() . '_' . (float)$qty;
        if (isset($this->productCache[$cacheKey])) {
            // Prevent from returning the 'false' value set earlier
            return $this->productCache[$cacheKey] ?? null;
        }

        $this->productCache[$cacheKey] = false;

        // Avoid using the getTierPrices method since it will cause a load of the tiered pricing if it doesn't exist
        $tierPrices = $product->getData('tier_price');

        if (!is_array($tierPrices) || !count($tierPrices)) {
            /**
             * This method loads Tiered Prices, since they are not already loaded. Unfortunately this creates and N+1
             * unoptimized query (loading in a loop). However it's the simplest approach to implementing this. With
             * full_page and block_html disabled, it adds about 2% to the loading of a product listing page with 32
             * products (testing was done with full_page and block_html caches disabeld). Since so few customers will
             * fall within the "Fixed" customer group, this performance implication is acceptable.
             */
            $product->getTierPrices();
            $tierPrices = $product->getData('tier_price');
        }

        $tierPricesForCustomerGroup = [];
        if (is_array($tierPrices) && $isCurrentCustomerFixedCustomerGroup) {
            foreach ($tierPrices as $tierPrice) {
                if (
                    in_array($tierPrice['cust_group'], $this->getCustomerGroups())
                    && $tierPrice['cust_group'] == $this->getCurrentCustomerGroup()
                    && $tierPrice['website_price'] > 0
                    && $qty >= $tierPrice['price_qty']
                ) {
                    $tierPricesForCustomerGroup[] = $tierPrice['website_price'];
                }
            }
            if (count($tierPricesForCustomerGroup)) {
                $this->productCache[$cacheKey] = min($tierPricesForCustomerGroup);
            }
        }

        // Prevent from returning the 'false' value set earlier
        return $this->productCache[$cacheKey] ?? null;
    }
}
