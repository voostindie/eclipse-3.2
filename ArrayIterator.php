<?php
/*** 
 * Eclipse - Extensible Class Library for PHP Software Engineers
 * Copyright (C) 2001, 2002  Vincent Oostindie
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 ***/

if (!defined('ECLIPSE_ROOT'))
{
    define('ECLIPSE_ROOT', '');
}

require_once(ECLIPSE_ROOT . 'Iterator.php');

/***
 * Class <code>ArrayIterator</code> offers an implementation of the
 * <code>Iterator</code> interface for iterating over the elements in an array.
 * <p>
 *   The advantage of this iterator over the built-in array iteration functions
 *   is two-fold:
 * </p>
 * <ol>
 *   <li>
 *     This is a genuine <code>Iterator</code>, and can therefore be used in
 *     generic algorithms.
 *   </li>
 *   <li>
 *     It's possible to run multiple iterations on the same array concurrently.
 *   </li>
 * </ol>
 * <p>
 *   This class uses the built-in array iteration functions <code>reset</code>,
 *   <code>next</code> and <code>key</code>. On the one hand this means the
 *   overhead in using this iterator is fairly small. On the other hand it also
 *   means that using two or more iterators on the same array at the same time
 *   is incredibly slow: when one iterator moves to another element in the
 *   array, all other iterators will be out of sync and must re-iterate the
 *   entire array (worst case) to find their correct positions again. However,
 *   this almost never happens; in most cases just one iterator is used on some
 *   array.
 * </p>
 ***/
class ArrayIterator extends Iterator
{
    // DATA MEMBERS

    /***
     * The array to iterate over
     * @type array
     ***/
    var $array;

    /***
     * The current key
     * @type string
     ***/
    var $key;

    /***
     * The current value
     * @type mixed
     ***/
    var $value;

    // CREATORS

    /***
     * Construct a new <code>ArrayIterator</code> for an array
     * @param $array the array to create an iterator for
     ***/
    function ArrayIterator(&$array)
    {
        $this->array =& $array;
        $this->reset();
    }

    // MANIPULATORS

    /***
     * @returns void
     ***/
    function reset()
    {
        reset($this->array);
        $this->key = key($this->array);
        if ("$this->key" != '')
        {
            $this->value =& $this->array[$this->key];
        }
    }

    /***
     * @returns void
     ***/
    function next()
    {
        if ($this->key != key($this->array))
        {
            reset($this->array);
            while (key($this->array) != $this->key)
            {
                next($this->array);
            }
        }
        next($this->array);
        $this->key = key($this->array);
        if ("$this->key" != '')
        {
            $this->value =& $this->array[$this->key];
        }
    }

    // ACCESSORS

    /***
     * @returns bool
     ***/
    function isValid()
    {
        return "$this->key" != '';
    }

    /***
     * @returns mixed
     ***/
    function &getCurrent()
    {
        return $this->value;
    }

    /***
     * Return the current key
     * @returns string
     ***/
    function getKey()
    {
        return $this->key;
    }
}
?>
