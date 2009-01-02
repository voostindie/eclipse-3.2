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
 * Class <code>StringIterator</code> provides an iterator for strings.
 * <p>
 *   Class <code>StringIterator</code> offers an implementation of the
 *   <code>Iterator</code> interface for iterating over the characters in a
 *   string. As with any iterator, changing the structure of the object iterated
 *   over (e.g. changing the string to a different string) might result in
 *   unexpected behavior. However it is possible to change the character 
 *   returned by <code>getCurrent</code>. For example:
 * </p>
 * <pre>
 *   $string = 'Encrypt me!';
 *   $key    = array(-1, 3, -2, 0, 0, 1);
 *   $size   = count($key);
 *   $index  = 0;
 *   for ($it =& new StringIterator($string); $it->isValid(); $it->next()) 
 *   {
 *       $char  =& $it->getCurrent();
 *       $char  =  chr(ord($char) + $key[$index]);
 *       $index =  ($index + 1) % $size;
 *   }
 *   print 'Encrypted string: ' . $string;
 * </pre>
 * <p>
 *   The above encryption algorithm is actually unbreakable if the key-array
 *   is at least as long as the encrypted string. (It's a one-time-pad in that
 *   case.) The decryption algorithm is left as an exercise for the reader...
 * </p>
 * @see Iterator
 ***/
class StringIterator extends Iterator 
{
    // DATA MEMBERS

    /***
     * The string to iterate over
     * @type string
     ***/
    var $string;

    /***
     * The current index in the string
     * @type int
     ***/
    var $index;

    /***
     * The current character
     * @type char
     ***/
    var $char;

    /***
     * The total length of the string
     * @type int
     ***/
    var $size;

    // CREATORS

    /***
     * Construct a new <code>StringIterator</code>
     * @param $string the string to iterate over
     ***/
    function StringIterator(&$string) 
    {
        $this->string =& $string;
        $this->size   =  strlen($string);
        $this->reset();
    }

    // MANIPULATORS

    /***
     * @returns void
     ***/
    function reset() 
    {
        $this->index = 0;
        $this->char  = ($this->size) ? $this->string{0} : '';
    }

    /***
     * @returns void
     ***/
    function next() 
    {
        $this->string{$this->index} = $this->char{0};
        $this->index++;
        $this->char = ($this->index < $this->size) 
            ? $this->string{$this->index} : '';
    }

    // ACCESSORS

    /***
     * @returns bool
     ***/
    function isValid() 
    {
        return ($this->index < $this->size);
    }

    /***
     * Return a reference to the current character
     * @returns char
     ***/
    function &getCurrent() 
    {
        return $this->char;
    }
}
?>
