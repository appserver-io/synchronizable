<?php

/**
 * AppserverIo\Synchronizable\ArrayObjectTest
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @category  Library
 * @package   Synchronizable
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://github.com/appserver-io/synchronizable
 * @link      http://www.appserver.io
 */

namespace AppserverIo\Synchronizable;

/**
 * This is test implementation for ArrayObject class.
 *
 * @category  Library
 * @package   Synchronizable
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://github.com/appserver-io/synchronizable
 * @link      http://www.appserver.io
 */
class ArrayObjectTest extends \PHPUnit_Framework_TestCase
{

    /**
     * The synchronizable array object implementation.
     *
     * @var \AppserverIo\Synchronizable\ArrayObject
     */
    protected $arrayObject;

    /**
     * Initializes the object we want to test.
     *
     * @return void
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    public function setUp()
    {
        apc_clear_cache(); // clear the APCu cache
        $this->arrayObject = new ArrayObject();
    }

    /**
     * Cleans up before the the next test case will be invoked.
     *
     * @return void
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    protected function tearDown()
    {

        // load the serial
        $serial = $this->arrayObject->__serial();

        // destroy the object
        Registry::destroy($this->arrayObject);

        // load the iterator for the object properties
        $iterator = new \ApcIterator('user', '/^' . $serial . '\./');

        // make sure that memory has been cleaned up
        $this->assertSame(0, $iterator->getTotalCount());
    }

    /**
     * Assigning a value to the array object.
     *
     * @return void
     */
    public function testAssignValue()
    {
        $this->arrayObject['test'] = 'testValue';
        $this->assertSame('testValue', $this->arrayObject['test']);
    }
}
