<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\ComponentModel;

use Nette;

/**
 * Component is the base class for all components.
 *
 * Components are objects implementing IComponent. They has parent component and own name.
 *
 * @author     David Grudl
 *
 * @property-read string          $name
 * @property-read IContainer|NULL $parent
 */
abstract class Component extends Nette\Object implements IComponent
{

    /** @var IContainer */
    private $parent;

    /** @var string */
    private $name;

    /** @var array of [type => [obj, depth, path, is_monitored?]] */
    private $monitors = array();


    /**
     */
    public function __construct( IContainer $parent = null, $name = null )
    {

        if ($parent !== null) {
            $parent->addComponent( $this, $name );

        } elseif (is_string( $name )) {
            $this->name = $name;
        }
    }

    /**
     * Lookup for component specified by class or interface name. Returns backtrace path.
     * A path is the concatenation of component names separated by self::NAME_SEPARATOR.
     *
     * @param  string class/interface type
     * @param  bool   throw exception if component doesn't exist?
     *
     * @return string
     */
    public function lookupPath( $type, $need = true )
    {

        $this->lookup( $type, $need );
        return $this->monitors[$type][2];
    }

    /**
     * Lookup hierarchy for component specified by class or interface name.
     *
     * @param  string class/interface type
     * @param  bool   throw exception if component doesn't exist?
     *
     * @return IComponent
     */
    public function lookup( $type, $need = true )
    {

        if (!isset( $this->monitors[$type] )) { // not monitored or not processed yet
            $obj = $this->parent;
            $path = self::NAME_SEPARATOR.$this->name;
            $depth = 1;
            while ($obj !== null) {
                if ($obj instanceof $type) {
                    break;
                }
                $path = self::NAME_SEPARATOR.$obj->getName().$path;
                $depth++;
                $obj = $obj->getParent(); // IComponent::getParent()
                if ($obj === $this) {
                    $obj = null; // prevent cycling
                }
            }

            if ($obj) {
                $this->monitors[$type] = array( $obj, $depth, substr( $path, 1 ), false );

            } else {
                $this->monitors[$type] = array( null, null, null, false ); // not found
            }
        }

        if ($need && $this->monitors[$type][0] === null) {
            throw new Nette\InvalidStateException( "Component '$this->name' is not attached to '$type'." );
        }

        return $this->monitors[$type][0];
    }

    /**
     * Starts monitoring.
     *
     * @param  string class/interface type
     *
     * @return void
     */
    public function monitor( $type )
    {

        if (empty( $this->monitors[$type][3] )) {
            if ($obj = $this->lookup( $type, false )) {
                $this->attached( $obj );
            }
            $this->monitors[$type][3] = true; // mark as monitored
        }
    }

    /**
     * This method will be called when the component (or component's parent)
     * becomes attached to a monitored object. Do not call this method yourself.
     *
     * @param  IComponent
     *
     * @return void
     */
    protected function attached( $obj )
    {
    }

    /**
     * Stops monitoring.
     *
     * @param  string class/interface type
     *
     * @return void
     */
    public function unmonitor( $type )
    {

        unset( $this->monitors[$type] );
    }

    /**
     * @return string
     */
    final public function getName()
    {

        return $this->name;
    }



    /********************* interface IComponent ****************d*g**/

    /**
     * Returns the container if any.
     *
     * @return IContainer|NULL
     */
    final public function getParent()
    {

        return $this->parent;
    }

    /**
     * Sets the parent of this component. This method is managed by containers and should
     * not be called by applications
     *
     * @param  IContainer  New parent or null if this component is being removed from a parent
     * @param  string
     *
     * @return Component  provides a fluent interface
     * @throws Nette\InvalidStateException
     * @internal
     */
    public function setParent( IContainer $parent = null, $name = null )
    {

        if ($parent === null && $this->parent === null && $name !== null) {
            $this->name = $name; // just rename
            return $this;

        } elseif ($parent === $this->parent && $name === null) {
            return $this; // nothing to do
        }

        // A component cannot be given a parent if it already has a parent.
        if ($this->parent !== null && $parent !== null) {
            throw new Nette\InvalidStateException( "Component '$this->name' already has a parent." );
        }

        // remove from parent?
        if ($parent === null) {
            $this->refreshMonitors( 0 );
            $this->parent = null;

        } else { // add to parent
            $this->validateParent( $parent );
            $this->parent = $parent;
            if ($name !== null) {
                $this->name = $name;
            }

            $tmp = array();
            $this->refreshMonitors( 0, $tmp );
        }
        return $this;
    }

    /**
     * Refreshes monitors.
     *
     * @param  int
     * @param  array|NULL (array = attaching, NULL = detaching)
     * @param  array
     *
     * @return void
     */
    private function refreshMonitors( $depth, & $missing = null, & $listeners = array() )
    {

        if ($this instanceof IContainer) {
            foreach ($this->getComponents() as $component) {
                if ($component instanceof Component) {
                    $component->refreshMonitors( $depth + 1, $missing, $listeners );
                }
            }
        }

        if ($missing === null) { // detaching
            foreach ($this->monitors as $type => $rec) {
                if (isset( $rec[1] ) && $rec[1] > $depth) {
                    if ($rec[3]) { // monitored
                        $this->monitors[$type] = array( null, null, null, true );
                        $listeners[] = array( $this, $rec[0] );
                    } else { // not monitored, just randomly cached
                        unset( $this->monitors[$type] );
                    }
                }
            }

        } else { // attaching
            foreach ($this->monitors as $type => $rec) {
                if (isset( $rec[0] )) { // is in cache yet
                    continue;

                } elseif (!$rec[3]) { // not monitored, just randomly cached
                    unset( $this->monitors[$type] );

                } elseif (isset( $missing[$type] )) { // known from previous lookup
                    $this->monitors[$type] = array( null, null, null, true );

                } else {
                    $this->monitors[$type] = null; // forces re-lookup
                    if ($obj = $this->lookup( $type, false )) {
                        $listeners[] = array( $this, $obj );
                    } else {
                        $missing[$type] = true;
                    }
                    $this->monitors[$type][3] = true; // mark as monitored
                }
            }
        }

        if ($depth === 0) { // call listeners
            $method = $missing === null ? 'detached' : 'attached';
            foreach ($listeners as $item) {
                $item[0]->$method( $item[1] );
            }
        }
    }

    /**
     * Is called by a component when it is about to be set new parent. Descendant can
     * override this method to disallow a parent change by throwing an Nette\InvalidStateException
     *
     * @param  IContainer
     *
     * @return void
     * @throws Nette\InvalidStateException
     */
    protected function validateParent( IContainer $parent )
    {
    }

    /**
     * Object cloning.
     */
    public function __clone()
    {

        if ($this->parent === null) {
            return;

        } elseif ($this->parent instanceof Container) {
            $this->parent = $this->parent->_isCloning();
            if ($this->parent === null) { // not cloning
                $this->refreshMonitors( 0 );
            }

        } else {
            $this->parent = null;
            $this->refreshMonitors( 0 );
        }
    }



    /********************* cloneable, serializable ****************d*g**/

    /**
     * Prevents serialization.
     */
    final public function __sleep()
    {

        throw new Nette\NotImplementedException( 'Object serialization is not supported by class '.get_class( $this ) );
    }

    /**
     * Prevents unserialization.
     */
    final public function __wakeup()
    {

        throw new Nette\NotImplementedException( 'Object unserialization is not supported by class '.get_class( $this ) );
    }

    /**
     * This method will be called before the component (or component's parent)
     * becomes detached from a monitored object. Do not call this method yourself.
     *
     * @param  IComponent
     *
     * @return void
     */
    protected function detached( $obj )
    {
    }

}
