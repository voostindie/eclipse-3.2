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
 * Class <code>MSDatabase</code> provides an implementation of the
 * <code>Database</code> interface for <b>Microsoft SQL Server</b> databases.
 * <p>
 *   Note that it's probably not a good idea to connect to <b>Microsoft SQL
 *   Server</b> from a <b>Unix</b> machine. Although this is possible with 
 *   <b>FreeTDS</b> (<code>www.freetds.org</code>), this software package is
 *   still in beta stage. Another method is by using the <b>Sybase</b> classes
 *   in this library in cooperation with the free <b>Sybase</b> drivers for 
 *   <b>Unix</b>.
 * </p>
 ***/
class MSDatabase extends Database 
{
    // CREATORS

    function MSDatabase($name, $host) 
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
                ? mssql_pconnect($this->getHost(), $username, $password)
                : mssql_connect($this->getHost(), $username, $password)
        );
        mssql_select_db($this->getName(), $this->getLink());
        return $this->isConnected();
    }

    /***
     * @returns void
     ***/
    function disconnect() 
    {
        mssql_close($this->getLink());
        $this->setLink(0);
    }

    /***
     * @returns MSQueryResult
     ***/
    function &query($sql)
    {
        return new MSQueryResult($this, mssql_query($sql, $this->getLink()));
    }

    /***
     * @returns MSTransaction
     ***/
    function &createTransaction()
    {
        include_once(ECLIPSE_ROOT . 'MSTransaction.php');
        return new MSTransaction($this);
    }

    // ACCESSORS

    /***
     * @returns string
     ***/
    function getErrorMessage() 
    {
        return mssql_get_last_message($this->getLink());
    }
}

/***
 * Class <code>MSQueryResult</code> provides an implementation of the
 * <code>QueryResult</code> interface for use with the <code>MSDatabase</code>
 * class.
 * @see MSDatabase
 ***/
class MSQueryResult extends QueryResult 
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

    function MSQueryResult(&$database, $resultId) 
    {
        $this->QueryResult($database, $resultId);
        $this->currentRow   = 0;
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
        mssql_free_result($this->getResultId());
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
        return mssql_num_rows($this->getResultId());
    }

    /***
     * @returns array
     ***/
    function getRow($index, $type = ECLIPSE_DB_BOTH)
    {
        // Jump to the correct row if necessary
        if ($index != $this->currentRow) 
        {
            mssql_data_seek($this->getResultId(), $index);
        }
        $this->currentRow = $index + 1;
        switch ($type)
        {
            case ECLIPSE_DB_ASSOC:
                return mssql_fetch_assoc($this->getResultId());
            case ECLIPSE_DB_NUM:
                return mssql_fetch_row($this->getResultId());
            case ECLIPSE_DB_BOTH:
            default:
                return mssql_fetch_array($this->getResultId());
        }
    }
}
?>
