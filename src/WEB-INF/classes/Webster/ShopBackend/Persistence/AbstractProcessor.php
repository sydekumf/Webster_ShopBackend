<?php

namespace Webster\ShopBackend\Persistence;

use Doctrine\ODM\MongoDB\DocumentManager;

/**
 * <REPLACE WITH FILE DESCRIPTION>
 *
 * PHP version 5
 *
 * @category   AppServer
 * @package    $package
 * @subpackage $subPackage
 * @author     Florian Sydekum <f.sydekum@techdivision.com>
 * @copyright  2014 TechDivision GmbH - <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 *             Open Software License (OSL 3.0)
 * @link       http://www.techdivision.com/
 */
abstract class AbstractProcessor
{
    /**
     * @var DocumentManager $_dm Holds the document manager.
     */
    protected $_dm;

    public function __construct(DocumentManager $dm)
    {
        $this->_dm = $dm;
    }

    /**
     * Returns the document manager.
     *
     * @return DocumentManager
     */
    public function getDocumentManager()
    {
        return $this->_dm;
    }
}