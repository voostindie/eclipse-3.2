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

/***
 * Class <code>PagedQuery</code> makes it easy to work with queries that return
 * several pages of results.
 * <p>
 *   Given a selection query, this class can be used to select only a specific
 *   number of rows (<code>$pageSize</code>) on a specific page. This is
 *   achieved by wrapping the full query result inside an object of class
 *   <code>PagedQueryResult</code>. Without going into the details, the result
 *   is that this class works directly for <i>any</i> database supported by
 *   <b>Eclipse</b> - no database-specific SQL commands are used to limit the
 *   resultset (e.g. <code>LIMIT</code> or <code>SELECT TOP</code>).
 * </p>
 * <p>
 *   This class might seem inefficient, exactly because it doesn't use mentioned
 *   database-specific SQL commands. However, for one very good reason, that
 *   isn't really true: it is almost always necessary to know the total number
 *   of pages in a query result - for example to be able to show a page
 *   navigator - and as this value is computed from the total number of rows in
 *   the query, the only way to get this number is by executing the full query.
 *   This completely eliminates the possible gain in efficiency when using
 *   <code>LIMIT</code>- or <code>SELECT TOP</code>-clauses, because in that
 *   case an additional (counting) query must be executed. Also, consider that
 *   most queries specify an ORDER BY clause, and remember that a DBMS can only
 *   compute this ordering by examining all rows in the result, even when just
 *   the first 10 are selected.
 * </p>
 * <p>
 *   When using this class, <i>always</i> use an <code>ORDER BY</code>-clause in
 *   the SQL query; if such a clause is omitted, the order the rows are returned
 *   in is undefined. (This is actually an SQL standard.)
 * </p>
 * <p>
 *   The following example select all book titles from some database and prints
 *   them, 20 at a time:
 * </p>
 * <pre>
 *   $sql   =  'SELECT title FROM book ORDER BY title';
 *   $query =& new PagedQuery($database->query($sql), 20);
 *   $page  =  $query->getPage(isset($_GET['page']) ? (int)$_GET['page'] : 0);
 *   for ($it =& new QueryIterator($page); $it->isValid(); $it->next())
 *   {
 *       $row =& $it->getCurrent();
 *       print "${row['title']}, ${row['year']}&lt;br&gt;\n";
 *   }
 * </pre>
 * @see Database
 * @see QueryResult
 * @see QueryIterator
 ***/
class PagedQuery
{
    // DATA MEMBERS

    /***
     * The number of rows on each page
     * @type int
     ***/
    var $pageSize;

    /***
     * The total number of pages
     * @type int
     ***/
    var $pageCount;

    /***
     * The result of the full query
     * @type QueryResult
     ***/
    var $queryResult;

    // CREATORS

    /***
     * Construct a new <code>PagedQuery</code>
     * @param $queryResult the <code>QueryResult</code> to create a pager for
     * @param $pageSize the number of rows on a single page
     ***/
    function PagedQuery(&$queryResult, $pageSize = 30)
    {
        $this->queryResult =& $queryResult;
        $this->pageSize    =  $pageSize;
        $this->pageCount   =  ceil($this->getRowCount() / $this->pageSize);
    }

    // MANIPULATORS

    /***
     * Get the rows on the specified page. This method returns an instance of
     * class <code>PagedQueryResult</code>, which has the exact same interface
     * as class <code>QueryResult</code>. If <code>$index</code> is invalid, the
     * first is are returned.
     * @returns PagedQueryResult
     ***/
    function &getPage($index = 0)
    {
        if ($index < 0 || $index > $this->pageCount)
        {
            $index = 0;
        }
        return new PagedQueryResult(
            $this->queryResult,
            $this->pageSize * $index,
            $this->pageSize
        );
    }

    // ACCESSORS

    /***
     * Return the size of a single page; note that this isn't necessary the
     * total number of rows on the current page: the last page can have less.
     * @returns int
     ***/
    function getPageSize()
    {
        return $this->pageSize;
    }
    
    /***
     * Get the total number of rows in the query.
     * @returns int
     ***/
    function getRowCount() 
    {
        return $this->queryResult->getRowCount();
    }

    /***
     * Return the total number of pages in the query result.
     * @returns int
     ***/
    function getPageCount() 
    {
        return $this->pageCount;
    }
}

/***
 * Class <code>PagedQueryResult</code> wraps around a <code>QueryResult</code>
 * to allow access to only part of a query result.
  * <p>
 *   This class is an implementation detail of class <code>PagedQuery</code>,
 *   and is not meant to be used on its own.
 * </p>
 * <p>
 *   This class behaves just like class <code>QueryResult</code>. For more
 *   information on processing rows in query results, please examine the
 *   documentation for that class.
 * </p>
 * @see PagedQuery
 * @see QueryResult
 ***/
class PagedQueryResult
{
    // DATA MEMBERS

    /***
     * The wrapped QueryResult
     * @type QueryResult
     ***/
    var $queryResult;

    /***
     * The first row that can be accessed
     * @type int
     ***/
    var $offset;
    
    /***
     * The maximum number of rows that may be accessed
     * @type int
     ***/
    var $total;

    // CREATORS
    
    /***
     * Construct a new PagedQueryResult
     * @param $queryResult the <code>QueryResult</code> wrapped by this object
     * @param $offset the index of the first row
     * @param $total the number of rows on a single page
     ***/
    function PagedQueryResult(&$queryResult, $offset, $total)
    {
        $this->queryResult =& $queryResult;
        $this->total       =  $total;
        $this->offset      =  $offset;
    }
    
    // MANIPULATORS
    
    /***
     * Clear the result data from memory
     * @returns void
     ***/
    function clear()
    {
        $this->queryResult->clear();
    }
    
    // ACCESSORS
    
    /***
     * Get the internal result identifier; this is a protected method
     * @returns int
     * @protected
     ***/
    function getResultId()
    {
        return $this->queryResult->getResultId();
    }
    
    /***
     * Return a reference to the database
     * @returns Database
     ***/
    function &getDatabase()
    {
        return $this->queryResult->getDatabase();
    }
    
    /***
     * Check if the query was executed successfully
     * @returns bool
     ***/
    function isSuccess()
    {
        return $this->queryResult->isSuccess();
    }
    
    /***
     * Get the error message
     * @returns string
     ***/
    function getErrorMessage()
    {
        return $this->queryResult->getErrorMessage();
    }

    /***
     * Return the number of rows on the active page
     * @returns int
     ***/
    function getRowCount()
    {
        return min(
            $this->queryResult->getRowCount() - $this->offset,
            $this->total
        );
    }

    /***
     * Return the row at the specified index of the active page
     * @returns array
     ***/
    function getRow($index, $type = ECLIPSE_DB_BOTH)
    {
        return $this->queryResult->getRow($index + $this->offset, $type);
    }
}
?>
