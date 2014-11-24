<?php

/**
 * AppserverIo\Synchronizable\Object
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
 * This is test implementation for a basic Object class.
 *
 * @category  Library
 * @package   Synchronizable
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://github.com/appserver-io/synchronizable
 * @link      http://www.appserver.io
 */
class Object implements SynchronizableInterface
{

    /**
     * The unique object identifier.
     *
     * @var string
     */
    protected $serial;

    /**
     * Constructor that'll initialize the synchronizable object.
     *
     * @throws \RuntimeException Is thrown if either APCu has not been loaded or the initialization data can't be written to APCu
     */
    public function __construct()
    {

        // actually we're using APCu to store the object properties
        if (extension_loaded('apc') === false) {
            throw new \RuntimeException('PHP Extension APCu has to be loaded');
        }

        // initialize the serial and the reference counter
        $this->serial = uniqid();

        // register the synchronizable
        Registry::attach($this);
    }

    /**
     * Decrements the reference counter and removes the instance
     * data from APCu if no more references will exist.
     *
     * @return void
     */
    public function __destruct()
    {
        Registry::destroy($this);
    }

    /**
     * Returns the objects unique identifier.
     *
     * @return string The unique identifier
     */
    public function __serial()
    {
        return $this->serial;
    }

    /**
     * Returns the reference counter.
     *
     * @return integer The reference counter
     */
    public function __refCount()
    {
        return Registry::refCount($this);
    }

    /**
     * Stores the object propery with the passed data.
     *
     * @param string $name  The property name
     * @param mixed  $value The property value
     *
     * @return void
     * @throws \Exception Is thrown if the property data can't be stored to APCu
     */
    public function __set($name, $value)
    {
        if (apc_store($this->serial . '_' . $name, serialize($value)) === false) { // throw an exception if data can't be stored to APCu
            throw new \Exception(sprintf('Can\'t store data for property: %s::%s', __CLASS__, $name));
        }
    }

    /**
     * Invoked when someone tries to read from an undefined property.
     *
     * @param string $name The name of the property the value has to be returned
     *
     * @return mixed The value of the undefined property
     * @throws \OutOfBoundsException Is thrown if the property has not been initialized before someone tries to access it
     * @throws \RuntimeException     Is thrown if the data is available, be can't be loaded from datasource
     */
    public function __get($name)
    {

        // try to load the data from APCu
        $rawData = apc_fetch($this->serial . '_' . $name);
        if ($rawData === false) { // throw an exception if property is not set or data can't be resolve
            throw new \OutOfBoundsException(sprintf('Undefined property: %s::%s', __CLASS__, $name));
        }

        // unserialize data and check if property is set
        $data = unserialize($rawData);
        if ($data === false) { // throw an exception if data can't be loaded from APCu
            throw new \RuntimeException(sprintf('Can\'t load data for property: %s::%s', __CLASS__, $name));
        }

        // return the data
        return $data;
    }

    /**
     * Raises the reference counter by one, because after waking
     * up, e. g. from a serialization we've a new reference.
     *
     * @return void
     */
    public function __wakeup()
    {
        Registry::attach($this);
    }

    /**
     * Invoked when the object will be serialized for example.
     *
     * @return array Array with variables that has to be serialized
     */
    public function __sleep()
    {
        return array('serial');
    }

    /**
     * Destroys the instance and detaches it from the registry.
     *
     * @return void
     */
    public function __destroy()
    {
        Registry::destroy($this);
    }

    /**
     * Attaches the instance to the registry again and returns it.
     *
     * @return \AppserverIo\Synchronizable\SynchronizableInterface The instance itself
     */
    public function __copy()
    {
        Registry::attach($this);
        return $this;
    }
}
