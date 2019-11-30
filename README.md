# Kraken CustomerGroupFixedPrice Extension

## Description

Allows an admin to specify a list of Customer Groups who will receive specific Tiered Pricing, even if that Tiered Pricing amount is higher than the normal product price.

If a customer in one of the selected Customer Groups is viewing a product:

* The price will be set to the value of the Tiered Pricing that is associated with their Customer Group, even if the Regular Price, Special Price, or other Tiered Prices are lower values
* They will not see any tiered pricing

## Usage Instructions

* Install extension
* Configure Customer Groups in **STORES > Configuration > KRAKEN > General > Customer Group Pricing** and configure extension
* To test:
    * Assign a customer to a Customer Group
    * Edit a product, click on "Advanced Pricing" and then assign a custom price to that Customer Group
    * Login as the customer on the frontend and you should see that custom price on the product detail page

## Installation Instructions

### Option 1 - Install extension using Composer (default approach)

```bash
composer config repositories.kraken/module-customer-group-fixed-price git https://github.com/krakencommerce/magento2-module-customer-group-fixed-price.git
composer require kraken/module-customer-group-fixed-price:dev-master
bin/magento module:enable --clear-static-content Kraken_CustomerGroupFixedPrice
bin/magento setup:upgrade
bin/magento cache:flush
```

### Option 2 - Install extension by copying files into project (only if the project requires it for some reason)

```bash
mkdir -p app/code/Kraken/CustomerGroupFixedPrice
git archive --format=tar --remote=git@github.com:krakencommerce/magento2-module-customer-group-fixed-price.git master | tar xf - -C app/code/Kraken/CustomerGroupFixedPrice/
bin/magento module:enable --clear-static-content Kraken_CustomerGroupFixedPrice
bin/magento setup:upgrade
bin/magento cache:flush
```

## Uninstallation Instructions

These instructions work regardless of how you installed the extension:

```bash
bin/magento module:disable --clear-static-content Kraken_CustomerGroupFixedPrice
rm -rf app/code/Kraken/CustomerGroupFixedPrice
composer remove kraken/module-customer-group-fixed-price
mr2 db:query 'DELETE FROM `setup_module` WHERE `module` = "Kraken_CustomerGroupFixedPrice"'
bin/magento cache:flush
```