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
 * Class <code>MyDatabase</code> provides an implementation of the
 * <code>Database</code> interface for <b>MySQL</b> databases.
 * <p>
 *   To use sockets (often the preferred method), use the path to the socket
 *   file prepended with a colon as the hostname, e.q. <code>$db =& new
 *   MyDatabase('database', ':/var/mysql/mysql.sock');</code>.
 * </p>
 * <p>
 *   To be able to use transactions in <b>MySQL</b>, transaction-safe table
 *   types must be used (<i>BDB</i> or <i>InnoDB</i>).
 * </p>
 ***/
class MyDatabase extends Database 
{
    // CREATORS

    /***
     * Prepare a new database connection on the specified host. If the
     * host is left empty, the localhost is used. To use sockets, specify
     * the path to the socket as the hostname (prepended with ':')
     * @param $name the name of the database
     * @param $host the host the database is running on
     ***/
    function MyDatabase($name, $host = '') 
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
                ? mysql_pconnect($this->getHost(), $username, $password)
                : mysql_connect($this->getHost(), $username, $password)
        );
        mysql_select_db($this->getName(), $this->getLink());
        return $this->isConnected();
    }

    /***
     * @returns void
     ***/
    function disconnect() 
    {
        mysql_close($this->getLink());
        $this->setLink(0);
    }

    /***
     * @returns MyQueryResult
     ***/
    function &query($sql) 
    {
        return new MyQueryResult($this, mysql_query($sql, $this->getLink()));
    }

    /***
     * @returns MyTransaction
     ***/
    function &createTransaction()
    {
        include_once(ECLIPSE_ROOT . 'MyTransaction.php');
        return new MyTransaction($this);
    }

    // ACCESSORS

    /***
     * @returns string
     ***/
    function getErrorMessage() 
    {
        return mysql_error($this->getLink());
    }
}

/***
 * Class <code>MyQueryResult</code> provides an implementation of the
 * <code>QueryResult</code> interface for use with the <code>MyDatabase</code>
 * class.
 * @see MyDatabase
 ***/
class MyQueryResult extends QueryResult 
{
    // DATA MEMBERS

    /***
     * The optional error message
     * @type string
     ***/
    var $errorMessage;

    /***
     * The current row index
     * @type int
     ***/
    var $currentRow;

    // CREATORS

    function MyQueryResult(&$database, $resultId) 
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
        mysql_free_result($this->getResultId());
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
        return mysql_num_rows($this->getResultId());
    }

    /***
     * @returns array
     ***/
    function getRow($index, $type = ECLIPSE_DB_BOTH) 
    {
        if ($index != $this->currentRow) 
        {
            mysql_data_seek($this->getResultId(), $index);
        }
        $this->currentRow = $index + 1;
        switch ($type)
        {
            case ECLIPSE_DB_ASSOC:
                return mysql_fetch_assoc($this->getResultId());
            case ECLIPSE_DB_NUM:
                return mysql_fetch_row($this->getResultId());
            case ECLIPSE_DB_BOTH:
            default:
                return mysql_fetch_array($this->getResultId());
        }
    }
}
?>
