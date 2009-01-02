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
 * Database connection methods; either persistent or non-persistent. The latter
 * (ECLIPSE_DB_NON_PERSISTENT) is the default.
 ***/
define('ECLIPSE_DB_PERSISTENT'    , true);
define('ECLIPSE_DB_NON_PERSISTENT', false);

/***
 * Class <code>Database</code> provides a simple, efficient abstract base class
 * for setting up up a database connection and executing SQL-queries.
 * <p>
 *   Setting up a connection can be as simple as:
 * </p>
 * <pre>
 *   $db =& new Database('dbname' ,'host');
 *   if (!$db->connect('user', 'pass'))
 *   {
 *       header('Location: dberror.php');
 *   }
 *   $result =& $db->query('SELECT title FROM books');
 * </pre>
 * <p>
 *   To check if a connection is valid, the method <code>isConnected</code> is
 *   available, which returns either <code>true</code> or <code>false</code> and
 *   can be called at any time.
 * </p>
 * <p>
 *   Once a connection has been established, SQL queries can be executed by
 *   calling the <code>query</code>-method with the appropriate SQL query.
 *   This method returns an object of class <code>QueryResult</code>.
 * </p>
 * <p>
 *  If a transaction must be performed, call the method
 *  <code>createTransaction</code> and run all queries through the returned
 *  <code>Transaction</code> object.
 * </p>
 * <p>
 *   Although not strictly necessary, closing the connection at the end of the
 *   PHP-script is a proper thing to do, and for this the method
 *   <code>disconnect</code> is provided, but note that this method is of no use
 *   (i.e.: does nothing) if persistent connections are used.
 * </p>
 * <p>
 *   A note about persistent connections: although they are by far the best way
 *   to connect to a database if the database server is on the same machine as
 *   the web server, but:
 * </p>
 * <ol>
 *   <li>
 *     PHP must be run as a module (non-CGI) for persistent connections to work.
 *   </li>
 *   <li>
 *     Persistent connections are still buggy, as of PHP 4.2.2. I personally
 *     found it impossible to keep the web server running for more than a few
 *     weeks with persistent connections enabled. Your mileage may vary.
 *   </li>
 * </ol>
 * @see QueryResult
 * @see Transaction
 ***/
class Database
{
    // DATA MEMBERS

     /***
     * The name of the database
     * @type string
     ***/
    var $name;

    /***
     * The host the database is on; if left empty, sockets are used
     * @type string
     ***/
    var $host;

    /***
     * The internal link to the database
     * @type int
     ***/
    var $link;

    // CREATORS

    /***
     * Prepare a new database connection on the specified host
     * @param $name the name of the database
     * @param $host the host the database is running on
     ***/
    function Database($name, $host)
    {
        $this->name = $name;
        $this->host = $host;
        $this->link = 0;
    }

    // MANIPULATORS

    /***
     * Set the internal link identifier; this is a protected method.
     * @param $link the resource index
     * @returns void
     * @protected
     ***/
    function setLink($link)
    {
        $this->link = $link;
    }

    /***
     * Make a connection with a database; return <code>true</code> on success,
     * false otherwise
     * @param $username the name of the database user
     * @param $password the password of the database user
     * @param $type the connection type; either
     * <code>ECLIPSE_DB_NON_PERSISTENT</code> or
     * <code>ECLIPSE_DB_PERSISTENT</code>
     * @returns bool
     ***/
    function connect($username, $password, $type = ECLIPSE_DB_NON_PERSISTENT)
    {
        die('Method <b>connect</b> of class <b>Database</b> is not ' .
            'implemented.');
    }

    /***
     * Close the connection with the database; this method needn't be called
     * when using a persistent connection
     * @returns void
     ***/
    function disconnect()
    {
        die('Method <b>disconnect</b> of class <b>Database</b> is not ' .
            'implemented.');
    }

    /***
     * Execute a query and return the result
     * @param $sql the query to perform
     * @returns QueryResult
     ***/
    function &query($sql)
    {
        die('Method <b>query</b> of class <b>Database</b> is not implemented.');
    }

    /***
     * Create a transaction and return it. Subclasses can override this method
     * to return an instance of a database-specific transaction class.
     * @returns Transaction
     ***/
    function &createTransaction()
    {
        include_once(ECLIPSE_ROOT . 'Transaction.php');
        return new Transaction($this);
    }

    // ACCESSORS

    /***
     * Get the internal link identifier; this is a protected method.
     * @returns int
     * @protected
     ***/
    function getLink()
    {
        return $this->link;
    }

    /***
     * Get the host the database is running on
     * @returns string
     ***/
    function getHost()
    {
        return $this->host;
    }

    /***
     * Get the name of the database
     * @returns string
     ***/
    function getName()
    {
        return $this->name;
    }

    /***
     * Check if a connection with the database has been established
     * @returns bool
     ***/
    function isConnected()
    {
        return ($this->link != 0);
    }

    /***
     * Get the last error message produced by the database
     * @returns string
     ***/
    function getErrorMessage()
    {
        die('Method <b>getErrorMessage</b> of class <b>Database</b> is not ' .
            'implemented.');
    }

}

/***
 * Database result types; results can be returned as an indexed array, an
 * associative array, or both. The latter (ECLIPSE_DB_BOTH) is the default.
 ***/
define('ECLIPSE_DB_NUM'  , 1);
define('ECLIPSE_DB_ASSOC', 2);
define('ECLIPSE_DB_BOTH' , 3);

/***
 * Class <code>QueryResult</code> is an abstract base class for query results;
 * it holds all information on the result of a query processed after calling
 * <code>query</code> on a <code>Database</code>-object.
 * <p>
 *   <code>isSuccess</code> can be called to find out if the query actually
 *   worked or not. If not, the accompanying error message can be requested with
 *   <code>getErrorMessage()</code>. Note that the returned error message
 *   needn't necessarily be the same as the one returned after calling
 *   <code>getErrorMessage()</code> on the database connection object.
 * </p>
 * <p>
 *   If the query performed was a <code>SELECT</code>-query, the following
 *   methods are useful:
 * <ul>
 *   <li>
 *     <code>getRowCount()</code>: returns the number of rows in the result.
 *   </li>
 *   <li>
 *     <code>getRow($index, $type)</code>: returns the row at the specified
 *     index. <code>$type</code> is one of <code>ECLIPSE_DB_NUM</code> for an
 *     indexed result array, <code>ECLIPSE_DB_ASSOC</code> for an associative
 *     result array, or <code>ECLIPSE_DB_BOTH</code> for a combination of the
 *     first two. The default is <code>ECLIPSE_DB_BOTH</code>.
 *   </li>
 * </ul>
 * <p>
 *   Alternatively, a <code>QueryIterator</code> can be used to process the rows
 *   in a selection query in a more generic way.
 * </p>
 * @see Database
 * @see QueryIterator
 ***/
class QueryResult {

    // DATA MEMBERS

    /***
     * The database object
     * @type Database
     ***/
    var $database;

    /***
     * The internal result identifier
     * @type int
     ***/
    var $resultId;

    // CREATORS

    /***
     * Construct a new <code>QueryResult</code>-object
     * @private
     * @param $database the database the query was executed on
     * @param $resultId an internal result identifier
     ***/
    function QueryResult(&$database, $resultId)
    {
        $this->database =& $database;
        $this->resultId =  $resultId;
    }

    // MANIPULATORS

    /***
     * Clear the result data from memory; this method need only be called if
     * memory usage is high in a single script. After calling this method, the
     * result can no longer be accessed.
     * @returns void
     ***/
    function clear()
    {
        die('Method <b>clear</b> of class <b>QueryResult</b> is not ' .
            'implemented.');
    }

    // ACCESSORS

    /***
     * Get the internal result identifier; this is a protected method.
     * @returns int
     * @protected
     ***/
    function getResultId() 
    {
        return $this->resultId;
    }

    /***
     * Get a reference to the database
     * @returns Database
     ***/
    function &getDatabase()
    {
        return $this->database;
    }

    /***
     * Check if the query was executed successfully
     * @returns bool
     ***/
    function isSuccess()
    {
        return ($this->resultId != 0);
    }

    /***
     * Get the error message; useful in case <code>isSuccess()</code> returns
     * <code>false</code>. Returns the empty string if there was no error.
     * @returns string
     ***/
    function getErrorMessage() 
    {
        die('Method <b>getErrorMessage</b> of class <b>QueryResult</b> is ' .
            'not implemented');
    }

    /***
     * Get the number of rows in the result for a <code>SELECT</code>-query
     * @returns int
     ***/
    function getRowCount() 
    {
        die('Method <b>getRowCount</b> of class <b>QueryResult</b> is not ' .
            'implemented.');
    }

    /***
     * Get the row at the specified index for a <code>SELECT</code>-query. The
     * optional argument <code>$type</code> defines how the row should be 
     * retrieved: as an indexed array (<code>ECLIPSE_DB_NUM</code>), an 
     * associative array (<code>ECLIPSE_DB_ASSOC</code>) or as both an indexed
     * and an associative array (<code>ECLIPSE_DB_BOTH</code>). The latter is
     * the default.
     * @param $index the index of the row to retrieve
     * @param $type how the row should be retrieved
     * @returns array
     ***/
    function getRow($index, $type = ECLIPSE_DB_BOTH) 
    {
        die('Method <b>getRow</b> of class <b>QueryResult</b> is not ' .
            'implemented.');
    }
}
?>
