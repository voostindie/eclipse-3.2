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
 * Class <code>QueryIterator</code> implements an iterator for query results.
 * <p>
 *   In <b>Eclipse</b>, this class can be used on an object of class
 *   <code>QueryResult</code> for queries that return rows (a 
 *   <code>SELECT</code>-query). An object is this class is returned after
 *   running a query on a <code>Database</code> or a <code>Query</code>.
 * </p>
 * <p>
 *   Pass an object of class <code>QueryResult</code> to the constructor to
 *   enable iteration of the records in the result, together with the type
 *   each row in the result should have. This is <code>ECLIPSE_DB_NUM</code>,
 *   <code>ECLIPSE_DB_ASSOC</code> or <code>ECLIPSE_DB_BOTH</code>, with the
 *   latter the default. See class <code>QueryResult</code> for additional
 *   information.
 * </p>
 * @see Database
 * @see QueryResult
 ***/
class QueryIterator extends Iterator 
{
    // DATA MEMBERS

    /***
     * The query to iterator over (a <code>QueryResult</code> or a
     * <code>Query</code>)
     * @type QueryResult
     ***/
    var $queryResult;

    /***
     * How each row should be retrieved. Should be <code>ECLIPSE_DB_BOTH</code>,
     * <code>ECLIPSE_DB_ASSOC</code> or <code>ECLIPSE_DB_NUM</code>
     * @type int
     ***/
    var $rowType;
    
    /***
     * The index of the current row
     * @type int
     ***/
    var $index;

    /***
     * The total number of rows in the query result
     * @type int
     ***/
    var $size;

    // CREATORS

    /***
     * Construct a new <code>QueryIterator</code>-object
     * @param $queryResult the <code>QueryResult</code> to iterate over
     ***/
    function QueryIterator(&$queryResult, $rowType = ECLIPSE_DB_BOTH) 
    {
        $this->queryResult =& $queryResult;
        $this->size        =  $queryResult->getRowCount();
        $this->rowType     =  $rowType;
        $this->reset();
    }

    // MANIPULATORS

    /***
     * @returns void
     ***/
    function reset() 
    {
        $this->index = 0;
    }

    /***
     * @returns void
     ***/
    function next() 
    {
        $this->index++;
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
     * Return a reference to the current row of the <code>QueryResult</code>
     * @returns array
     ***/
    function &getCurrent() 
    {
        return $this->queryResult->getRow($this->index, $this->rowType);
    }
}
?>
