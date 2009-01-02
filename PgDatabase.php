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
 * Class <code>PgDatabase</code> provides an implementation of the
 * <code>Database</code> interface for <b>PostgreSQL</b> databases.
 * <p>
 *   To use sockets (often the preferred method), leave the hostname blank.
 *   Using sockets leads to much better performance than making a connection
 *   to <code>localhost</code>.
 * </p>
 ***/
class PgDatabase extends Database 
{
    // CREATORS

    /***
     * Prepare a new database connection on the specified host. If the
     * host is left empty, sockets are used
     * @param $name the name of the database
     * @param $host the host the database is running on
     ***/
    function PgDatabase($name, $host = '') 
    {
        $this->Database($name, $host);
    }

        // MANIPULATORS

    /***
     * @returns bool
     ***/
    function connect($username, $password, $type = ECLIPSE_DB_NON_PERSISTENT)
    {
        $host = $this->getHost();
        $name = $this->getName();
        $conn = ($host == '' ? '' : "host=$host ") .
                "dbname=$name user=$username" .
                ($password == '' ? '' : " password=$password");
        $this->setLink(
            ($type == ECLIPSE_DB_PERSISTENT)
                ? pg_pconnect($conn)
                : pg_connect($conn)
        );
        return $this->isConnected();
    }

    /***
     * @returns void
     ***/
    function disconnect() 
    {
        pg_close($this->getLink());
        $this->setLink(0);
    }

    /***
     * @returns PgQueryResult
     ***/
    function &query($sql) 
    {
        return new PgQueryResult($this, pg_query($this->getLink(), $sql));
    }

    /***
     * @returns PgTransaction
     ***/
    function &createTransaction()
    {
        include_once(ECLIPSE_ROOT . 'PgTransaction.php');
        return new PgTransaction($this);
    }

    // ACCESSORS

    /***
     * @returns string
     ***/
    function getErrorMessage() 
    {
        return pg_last_error($this->getLink());
    }
}

/***
 * Class <code>PgQueryResult</code> provides an implementation of the
 * <code>QueryResult</code> interface for use with the <code>PgDatabase</code>
 * class.
 * @see PgDatabase
 ***/
class PgQueryResult extends QueryResult 
{
    // DATA MEMBERS

    /***
     * The optional error message from running the query
     * @type string
     ***/
    var $errorMessage;

    // CREATORS

    function PgQueryResult(&$database, $resultId) 
    {
        $this->QueryResult($database, $resultId);
        $this->errorMessage = ($resultId !== false)
            ? ''
            : pg_last_error($database->getLink());
    }

    // MANIPULATORS

    /***
     * @returns string
     ***/
    function getErrorMessage() 
    {
        return $this->errorMessage;
    }

    /***
     * @returns void
     ***/
    function clear() 
    {
        pg_free_result($this->getResultId());
    }

    // ACCESSORS

    /***
     * @returns int
     ***/
    function getRowCount() 
    {
        return pg_num_rows($this->getResultId());
    }

    /***
     * @returns array
     ***/
    function getRow($index, $type = ECLIPSE_DB_BOTH) 
    {
        switch ($type)
        {
            case ECLIPSE_DB_ASSOC:
                return pg_fetch_array(
                    $this->getResultId(), 
                    $index, 
                    PGSQL_ASSOC
                );
            case ECLIPSE_DB_NUM:
                return pg_fetch_row($this->getResultId(), $index);
            case ECLIPSE_DB_BOTH:
            default:
                return pg_fetch_array(
                    $this->getResultId(), 
                    $index, 
                    PGSQL_BOTH
                );
        }
    }
}
?>
