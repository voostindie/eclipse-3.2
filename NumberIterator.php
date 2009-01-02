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
 * Class <code>NumberIterator</code> is an iterator for generating consecutive
 * numbers.
 * <p>
 *   On creation the total number of numbers to generate, the starting number
 *   and the stepping number must be set. The latter two are optional.
 * </p>
 * <p>
 *   Consider a query that returns many columns, and there's a need to divide
 *   the result over multiple pages (with a <code>PagedQuery</code>). A link to
 *   each page in the result is useful, and some simple navigation to advance to
 *   the next page or jump back to the previous one might also come in handy.
 *   The nice way to do this is with the <code>Loop</code>, this
 *   <code>NumberIterator</code>, and a convenient <code>LoopManipulator</code>
 *   object. For example:
 * </p>
 * <pre>
 *   Loop::run(new NumberIterator($pageCount), new PageNavigator);
 *</pre>
 * <p>
 *   Of course, the <code>LoopManipulator</code> (<code>PageNavigator</code> in
 *   the example) that writes the appropriate HTML still has to be defined, but
 *   that's a very simple task and if done properly it can be used over and over
 *   again.
 * </p>
 * <p>
 *   Because it is possible to set the number to start with, as well as the
 *   stepping, this class can do more than just the above, although this won't
 *   be necessary very often. A simple application is the generation of
 *   the multiplication table of some number <code>$n</code>:
 * </p>
 * <pre>    $it =& new NumberIterator(10, $n, $n);</pre>
 * @see Loop
 * @see LoopManipulator
 ***/
class NumberIterator extends Iterator
{
    // DATA MEMBERS

    /***
     * The total number of numbers to generate
     * @type int
     ***/
    var $size;

    /***
     * The first number that should be generated
     * @type int
     ***/
    var $base;

    /***
     * The stepping size
     * @type int
     ***/
    var $step;

    /***
     * The index of the current number
     * @type int
     ***/
    var $index;

    /***
     * The current number
     * @type int
     ***/
    var $current;

    // CREATORS

    /***
     * Construct a new <code>NumberIterator</code>
     * @param $size the total number of numbers to generate
     * @param $base the first number
     * @param $step the number to add with each consecutive step
     ***/
    function NumberIterator($size, $base = 1, $step = 1) 
    {
        $this->size = $size;
        $this->base = $base;
        $this->step = $step;
        $this->reset();
    }

    // MANIPULATORS

    /***
     * @returns void
     ***/
    function reset() 
    {
        $this->index = 0;
        $this->current = $this->base;
    }

    /***
     * @returns void
     ***/
    function next() 
    {
        $this->index++;
        $this->current += $this->step;
    }

    // ACCESSORS

    /***
     * @returns bool
     ***/
    function isValid() 
    {
        return $this->index < $this->size;
    }

    /***
     * @returns int
     ***/
    function &getCurrent() 
    {
        return $this->current;
    }
}
?>
