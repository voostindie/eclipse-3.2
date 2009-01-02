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

require_once(ECLIPSE_ROOT . 'LoopManipulator.php');
require_once(ECLIPSE_ROOT . 'ArrayIterator.php');

/***
 * Class <code>RowLoopManipulator</code> implements a loop manipulator 
 * specialized for iterators where each element of the iteration is represented
 * by an array (<code>QueryIterator</code>, <code>DataFileIterator</code>).
 * <p>
 *   In many cases, each element in an iteration is an array. This is the case,
 *   for example, in query results and data files. Often, some column in that
 *   array is special, in that it stays the same for many items in the loop. As
 *   an example, consider the query <code>SELECT author.name AS author,
 *   book.title AS title FROM author, book WHERE book.author_id = author.id
 *   GROUP BY author</code>. This query selects all books by all authors, and
 *   groups them on author. If all rows are printed in HTML, it is likely that
 *   the name of each author should be shown only once.
 * </p>
 * <p>
 *   This class is a specialized loop manipulator that simplifies the handling
 *   of special columns by using so called <i>watchers</i>. For every column a
 *   watcher can be created, after which any number of methods can be registered
 *   on the watcher. When the watched column changes, all registered methods are
 *   called on the manipulator one by one.
 * </p>
 * <p>
 *   The watchers have a number of nice properties:
 * </p>
 * <ul>
 *   <li>
 *     For any column, any number of methods can be registered in a watcher. The
 *     methods are called in the order they are registered in.
 *   </li>
 *   <li>
 *     Watchers can be registered and/or unregistered at any time during the
 *     loop. It is possible, for example, to let some watcher trigger the
 *     creation of another watcher, which will then be active for the remainder
 *     of the iteration (unless it is unregistered again some time later).
 *   </li>
 *   <li>
 *     The code that is executed for a watcher is just a method of some
 *     manipulator. This makes it possible for new manipulators to be
 *     implemented as a subclass of that manipulator, overriding just the
 *     methods that need to express different behavior.
 *   </li>
 * </ul>
 * <p>
 *   The following code implements a manipulator for the example given earlier:
 * </p>
 * <pre>
 *   class BookAuthorsPrinter extends RowLoopManipulator
 *   {
 *       function BookAuthorsPrinter()
 *       {
 *           $this->RowLoopManipulator();
 *           $watcher =& $this->addWatcher('author');
 *           $watcher->register('author');
 *       }
 *
 *       function author(&$row, $index)
 *       {
 *           echo "&lt;b&gt;${row['author']}&lt;/b&gt;:&lt;br&gt;\n";
 *       }
 *
 *       function current(&$row, $index)
 *       {
 *           parent::current($row, $index);
 *           echo " - ${row['title']}&lt;br&gt;\n";
 *       }
 *   }
 *
 *   $result = $database->query(
 *       'SELECT author.name AS author, book.title AS title
 *        FROM author, book
 *        WHERE book.author_id = author.id
 *        GROUP BY author'
 *   );
 *   Loop::run(new QueryIterator($result), new BookAuthorsPrinter);
 * </pre>
 * <p>
 *   Subclasses that override the method <code>current(&$row, $index)</code>
 *   <i>must</i> call the parent method to make sure the watchers are processed.
 * </p>
 * @see Loop
 * @see LoopManipulator
 * @see RowLoopManipulatorWatcher
 ***/
class RowLoopManipulator extends LoopManipulator
{
    // DATA MEMBERS

    /***
     * The list of watchers; every column has at most one
     * @type array
     ***/
    var $watchers;
    
    // CREATORS
    
    /***
     * Construct a new <code>RowLoopManipulator</code>
     ***/
    function RowLoopManipulator()
    {
        $this->watchers = array();
    }
    
    // MANIPULATORS
    
    /***
     * Add a watcher for some column and return it
     * @param $column the name of the column to watch; either an index or a key
     * @returns RowLoopManipulatorWatcher
     ***/
    function &addWatcher($column)
    {
        if ($this->getWatcher($column) === false)
        {
            $this->watchers[$column] =&
                new RowLoopManipulatorWatcher($this, $column);
        }
        return $this->getWatcher($column);
    }
    
    /***
     * @returns void
     ***/
    function current(&$row, $index)
    {
        $it =& new ArrayIterator($this->watchers);
        for ( ; $it->isValid(); $it->next())
        {
            $watcher =& $it->getCurrent();
            $watcher->check($row, $index);
        }
    }

    // ACCESSORS

    /***
     * Get the watcher for a specific column; if the watcher doesn't exist, this
     * method returns <code>false</code>
     * @param $column the name of the column to get the watcher for
     * @returns RowLoopManipulatorWatcher
     ***/
    function &getWatcher($column)
    {
        if (isset($this->watchers[$column]))
        {
            return $this->watchers[$column];
        }
        return false;
    }
}

/***
 * Class <code>RowLoopManipulatorWatcher</code> implements a watcher to be used
 * with class <code>RowLoopManipulator</code>.
 * <p>
 *   This class is meant to be used only in cooperation with class 
 *   <code>RowLoopManipulator</code>. See that class for more details.
 * </p>
 * @see RowLoopManipulator
 ***/
class RowLoopManipulatorWatcher
{
    // DATA MEMBERS
    
    /***
     * The manipulator this watcher is for
     * @type RowLoopManipulator
     ***/
    var $manipulator;
    
    /***
     * The column this watcher is watching
     * @type string
     ***/
    var $column;
    
    /***
     * The list of methods to call on the manipulator when the watched column
     * changes
     * @type array
     ***/
    var $methods;
    
    /***
     * The last known value of the watched column
     * @type string
     ***/
    var $value;
    
    // CREATORS
    
    /***
     * Construct a new watcher
     * @param $manipulator the loop manipulator this watcher is for
     * @param $column the column this watcher is for
     ***/
    function RowLoopManipulatorWatcher(&$manipulator, $column)
    {
        $this->manipulator =& $manipulator;
        $this->column      =  $column;
        $this->methods     =  array();
        unset($this->value);
    }
    
    // MANIPULATORS
    
    /***
     * Register a method to call on the manipulator when the watched column
     * changes. If the specified method is already registered, nothing happens.
     * @param $method the method to call when the watched column changes
     * @returns void
     ***/
    function register($method)
    {
        if (!in_array($method, $this->methods))
        {
            array_push($this->methods, $method);
        }
    }
    
    /***
     * Unregister a method from this watcher. If the method wasn't registered in
     * the first place, nothing happens.
     * @param $method the method to remove from the list of registered methods
     * @returns void
     ***/
    function unregister($method)
    {
        if (($index = array_search($method, $this->methods)) !== false)
        {
            array_splice($this->methods, $index, 1);
        }
    }
    
    /***
     * Check a row and call all registered methods if the watched column has
     * changed.
     * @param $row the row to check
     * @param $index the index of the current row
     * @returns void
     ***/
    function check(&$row, $index)
    {
        if (!isset($row[$this->column]))
        {
            return;
        }
        if (isset($this->value) && $row[$this->column] == $this->value)
        {
            return;
        }
        $it =& new ArrayIterator($this->methods);
        for ( ; $it->isValid(); $it->next())
        {
            $method =& $it->getCurrent();
            $this->manipulator->$method($row, $index);
        }
        $this->value = $row[$this->column];
    }
}
?>
