<?php

/**
 * TechDivision\Import\Product\Variant\Observers\ProductVariantObserver
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/import-product-variant
 * @link      http://www.techdivision.com
 */

namespace TechDivision\Import\Product\Variant\Observers;

use TechDivision\Import\Utils\ProductTypes;
use TechDivision\Import\Product\Variant\Utils\ColumnKeys;
use TechDivision\Import\Product\Observers\AbstractProductImportObserver;

/**
 * A SLSB that handles the process to import product bunches.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/import-product-variant
 * @link      http://www.techdivision.com
 */
class ProductVariantObserver extends AbstractProductImportObserver
{

    /**
     * The artefact type.
     *
     * @var string
     */
    const ARTEFACT_TYPE = 'variants';

    /**
     * Will be invoked by the action on the events the listener has been registered for.
     *
     * @param array $row The row to handle
     *
     * @return array The modified row
     * @see \TechDivision\Import\Product\Observers\ImportObserverInterface::handle()
     */
    public function handle(array $row)
    {

        // initialize the row
        $this->setRow($row);

        // process the functionality and return the row
        $this->process();

        // return the processed row
        return $this->getRow();
    }

    /**
     * Process the observer's business logic.
     *
     * @return array The processed row
     */
    protected function process()
    {

        // query whether or not we've found a configurable product
        if ($this->getValue(ColumnKeys::PRODUCT_TYPE) !== ProductTypes::CONFIGURABLE) {
            return;
        }

        // query whether or not, we've configurables
        if ($configurableVariations = $this->getValue(ColumnKeys::CONFIGURABLE_VARIATIONS)) {
            // load the variation labels, if available
            $configurableVariationLabels = $this->getValue(ColumnKeys::CONFIGURABLE_VARIATION_LABELS);

            // create an array with the variation labels (attribute code as key)
            $varLabels = array();
            foreach ($this->explode($configurableVariationLabels, '|') as $variationLabel) {
                if (strstr($variationLabel, '=')) {
                    list ($key, $value) = $this->explode($variationLabel, '=');
                    $varLabels[$key] = $value;
                }
            }

            // intialize the array for the variations
            $artefacts = array();

            // load the parent SKU from the row
            $parentSku = $this->getValue(ColumnKeys::SKU);

            // load the store view code
            $storeViewCode = $this->getValue(ColumnKeys::STORE_VIEW_CODE);

            // iterate over all variations and import them
            foreach ($this->explode($configurableVariations, '|') as $variation) {
                // sku=Configurable Product 48-option 2,configurable_variation=option 2
                list ($sku, $option) = $this->explode($variation);

                // explode the variations child ID as well as option code and value
                list (, $childSku) = $this->explode($sku, '=');
                list ($optionCode, $optionValue) = $this->explode($option, '=');

                // load the apropriate variation label
                $varLabel = '';
                if (isset($varLabels[$optionCode])) {
                    $varLabel = $varLabels[$optionCode];
                }

                // append the product variation
                $artefacts[] = array(
                    ColumnKeys::STORE_VIEW_CODE         => $storeViewCode,
                    ColumnKeys::VARIANT_PARENT_SKU      => $parentSku,
                    ColumnKeys::VARIANT_CHILD_SKU       => $childSku,
                    ColumnKeys::VARIANT_OPTION_VALUE    => $optionValue,
                    ColumnKeys::VARIANT_VARIATION_LABEL => $varLabel
                );
            }

            // append the variations to the subject
            $this->addArtefacts($artefacts);
        }
    }

    /**
     * Add the passed product type artefacts to the product with the
     * last entity ID.
     *
     * @param array $artefacts The product type artefacts
     *
     * @return void
     * @uses \TechDivision\Import\Product\Variant\Subjects\BunchSubject::getLastEntityId()
     */
    protected function addArtefacts(array $artefacts)
    {
        $this->getSubject()->addArtefacts(ProductVariantObserver::ARTEFACT_TYPE, $artefacts);
    }
}
