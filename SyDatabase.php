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

require_once(ECLIPSE_ROOT . 'Database.php');

/***
 * Class <code>SyDatabase</code> provides an implementation of the
 * <code>Database</code> interface for <b>Sybase</b> databases.
 * <p>
 *   This class is almost identical to class <code>MSDatabase</code>, with
 *   all occurrences of <code>mssql</code> replaced with <code>sybase</code>.
 * </p>
 * <p>
 *   This code is in beta stage, because it hasn't been tested; I do not have
 *   access to a <b>Sybase</b> database server. Because of its simplicity and
 *   the similarities with class <code>MSDatabase</code>, I have included this
 *   class in <b>Eclipse</b> anyway.
 * </p>
 ***/
class SyDatabase extends Database 
{
    // CREATORS

    function SyDatabase($name, $host) 
    {
        $this->Database($name, $host);
    }

    // MANIPULATORS

    /***
     * @returns bool
     ***/
    function connect($username, $password, $type = ECLIPSE_DB_NON_PERSISTENT) 
    {
        $this->setLink(
            ($type == ECLIPSE_DB_PERSISTENT)
                ? sybase_pconnect($this->getHost(), $username, $password)
                : sybase_connect($this->getHost(), $username, $password)
        );
        sybase_select_db($this->getName(), $this->getLink());
        return $this->isConnected();
    }

    /***
     * @returns void
     ***/
    function disconnect() 
    {
        sybase_close($this->getLink());
        $this->setLink(0);
    }

    /***
     * @returns SyQueryResult
     ***/
    function &query($sql) 
    {
        return new SyQueryResult($this, sybase_query($sql, $this->getLink()));
    }

    /***
     * @returns SyTransaction
     ***/
    function &createTransaction()
    {
        include_once(ECLIPSE_ROOT . 'SyTransaction.php');
        return new SyTransaction($this);
    }

    // ACCESSORS

    /***
     * @returns string
     ***/
    function getErrorMessage() 
    {
        return sybase_get_last_message();
    }
}

/***
 * Class <code>SyQueryResult</code> provides an implementation of the
 * <code>QueryResult</code> interface for use with the <code>SyDatabase</code>
 * class.
 * <p>
 *   As class <code>SyDatabase</code>, this class isn't officially supported.
 * </p>
 * <p>
 *   According to the PHP manual (version 4.2.0), there is no function to get an
 *   associative array as a row-result, but it is possible to get a row as both
 *   an indexed and an associative array. When an associative array is 
 *   requested from this class, it requests the full array from the database, and
 *   then filters out the indexed elements.
 * </p>
 * @see SyDatabase
 ***/
class SyQueryResult extends QueryResult 
{
    // DATA MEMBERS

    /***
     * The optional error message
     * @type string
     ***/
    var $errorMessage;

    /***
     * The previous row index
     * @type int
     ***/
    var $currentRow;

    // CREATORS

    function SyQueryResult(&$database, $resultId) 
    {
        $this->QueryResult($database, $resultId);
        $this->currentRow = 0;
        $this->errorMessage = ($resultId !== false)
                            ? '' 
                            : $database->getErrorMessage();
    }

    // MANIPULATORS

    /***
     * @returns void
     ***/
    function clear() 
    {
        sybase_free_result($this->getResultId());
    }

    // ACCESSORS

    /***
     * @returns string
     ***/
    function getErrorMessage() 
    {
        return $this->errorMessage;
    }

    /***
     * @returns int
     ***/
    function getRowCount() 
    {
        return sybase_num_rows($this->getResultId());
    }

    /***
     * @returns array
     ***/
    function getRow($index, $type = ECLIPSE_DB_BOTH)
    {
        // Jump to the correct row if necessary
        if ($index != $this->currentRow) 
        {
            sybase_data_seek($this->getResultId(), $index);
        }
        $this->currentRow = $index + 1;
        switch ($type)
        {
            case ECLIPSE_DB_ASSOC:
                include_once(ECLIPSE_ROOT . 'ArrayIterator.php');
                $result =  array();
                $row    =  sybase_fetch_array($this->getResultId());
                $it     =& new ArrayIterator($row);
                for ( ; $it->isValid(); $it->next())
                {
                    if (!is_int($it->getKey()))
                    {
                        $result[$it->getKey()] = $it->getCurrent();
                    }
                }
                return $result;
            case ECLIPSE_DB_NUM:
                return sybase_fetch_row($this->getResultId());
            case ECLIPSE_DB_BOTH:
            default:
                return sybase_fetch_array($this->getResultId());
        }
    }
}
?>
