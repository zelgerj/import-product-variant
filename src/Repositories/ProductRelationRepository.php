<?php

/**
 * TechDivision\Import\Product\Variant\Repositories\ProductRelationRepository
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

namespace TechDivision\Import\Product\Variant\Repositories;

use TechDivision\Import\Repositories\AbstractRepository;
use TechDivision\Import\Product\Variant\Utils\MemberNames;

/**
 * Repository implementation to load product relation data.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/import-product-variant
 * @link      http://www.techdivision.com
 */
class ProductRelationRepository extends AbstractRepository
{

    /**
     * The prepared statement to load an existing product relation.
     *
     * @var \PDOStatement
     */
    protected $productRelationStmt;

    /**
     * Initializes the repository's prepared statements.
     *
     * @return void
     */
    public function init()
    {

        // load the utility class name
        $utilityClassName = $this->getUtilityClassName();

        // initialize the prepared statements
        $this->productRelationStmt = $this->getConnection()->prepare($utilityClassName::PRODUCT_RELATION);
    }

    /**
     * Load's the product relation with the passed parent/child ID.
     *
     * @param integer $parentId The entity ID of the product relation's parent product
     * @param integer $childId  The entity ID of the product relation's child product
     *
     * @return array The product relation
     */
    public function findOneByParentIdAndChildId($parentId, $childId)
    {

        // initialize the params
        $params = array(
            MemberNames::PARENT_ID => $parentId,
            MemberNames::CHILD_ID  => $childId
        );

        // load and return the product relation with the passed parent/child ID
        $this->productRelationStmt->execute($params);
        return $this->productRelationStmt->fetch(\PDO::FETCH_ASSOC);
    }
}
