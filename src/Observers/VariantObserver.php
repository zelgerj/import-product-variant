<?php

/**
 * TechDivision\Import\Product\Variant\Observers\VariantObserver
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

use TechDivision\Import\Product\Variant\Utils\ColumnKeys;
use TechDivision\Import\Product\Variant\Utils\MemberNames;
use TechDivision\Import\Product\Observers\AbstractProductImportObserver;

/**
 * Oberserver that provides functionality for the product variant replace operation.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/import-product-variant
 * @link      http://www.techdivision.com
 */
class VariantObserver extends AbstractProductImportObserver
{

    /**
     * The product relation's parent ID.
     *
     * @var integer
     */
    protected $parentId;

    /**
     * The product relation's child ID.
     *
     * @var integer
     */
    protected $childId;

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

        // load and map the parent + child ID
        $this->parentId = $this->mapParentSku($this->getValue(ColumnKeys::VARIANT_PARENT_SKU));
        $this->childId = $this->mapChildSku($this->getValue(ColumnKeys::VARIANT_CHILD_SKU));

        // prepare and persist the product relation
        if ($productRelation = $this->initializeProductRelation($this->prepareProductRelationAttributes())) {
            $this->persistProductRelation($productRelation);
        }

        // prepare and persist the product super link
        if ($productSuperLink = $this->initializeProductSuperLink($this->prepareProductSuperLinkAttributes())) {
            $this->persistProductSuperLink($productSuperLink);
        }
    }

    /**
     * Prepare the product relation attributes that has to be persisted.
     *
     * @return array The prepared product relation attributes
     */
    protected function prepareProductRelationAttributes()
    {

        // initialize and return the entity
        return $this->initializeEntity(
            array(
                MemberNames::PARENT_ID => $this->parentId,
                MemberNames::CHILD_ID  => $this->childId
            )
        );
    }

    /**
     * Prepare the product super link attributes that has to be persisted.
     *
     * @return array The prepared product super link attributes
     */
    protected function prepareProductSuperLinkAttributes()
    {

        // initialize and return the entity
        return $this->initializeEntity(
            array(
                MemberNames::PRODUCT_ID => $this->childId,
                MemberNames::PARENT_ID  => $this->parentId
            )
        );
    }

    /**
     * Initialize the product relation with the passed attributes and returns an instance.
     *
     * @param array $attr The product relation attributes
     *
     * @return array|null The initialized product relation, or null if the relation already exsist
     */
    protected function initializeProductRelation(array $attr)
    {
        return $attr;
    }

    /**
     * Initialize the product super link with the passed attributes and returns an instance.
     *
     * @param array $attr The product super link attributes
     *
     * @return array|null The initialized product super link, or null if the super link already exsist
     */
    protected function initializeProductSuperLink(array $attr)
    {
        return $attr;
    }

    /**
     * Map's the passed SKU of the parent product to it's PK.
     *
     * @param string $parentSku The SKU of the parent product
     *
     * @return integer The primary key used to create relations
     */
    protected function mapParentSku($parentSku)
    {
        return $this->mapSkuToEntityId($parentSku);
    }

    /**
     * Map's the passed SKU of the child product to it's PK.
     *
     * @param string $childSku The SKU of the child product
     *
     * @return integer The primary key used to create relations
     */
    protected function mapChildSku($childSku)
    {
        return $this->mapSkuToEntityId($childSku);
    }

    /**
     * Return the entity ID for the passed SKU.
     *
     * @param string $sku The SKU to return the entity ID for
     *
     * @return integer The mapped entity ID
     * @throws \Exception Is thrown if the SKU is not mapped yet
     */
    protected function mapSkuToEntityId($sku)
    {
        return $this->getSubject()->mapSkuToEntityId($sku);
    }

    /**
     * Persist's the passed product relation data and return's the ID.
     *
     * @param array $productRelation The product relation data to persist
     *
     * @return void
     */
    protected function persistProductRelation($productRelation)
    {
        return $this->getSubject()->persistProductRelation($productRelation);
    }

    /**
     * Persist's the passed product super link data and return's the ID.
     *
     * @param array $productSuperLink The product super link data to persist
     *
     * @return void
     */
    protected function persistProductSuperLink($productSuperLink)
    {
        return $this->getSubject()->persistProductSuperLink($productSuperLink);
    }
}
