<?
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

require_once(ECLIPSE_ROOT . 'Transaction.php');

/***
 * Class <code>PgTransaction</code> implements the <code>Transaction</code>
 * interface for <b>PostgreSQL</b> databases.
 * <p>
 *   To create concurrent transactions on a <b>PostgreSQL</b> database, it is
 *   not enough to set up multiple connections: care must be taken that the
 *   various connection parameters are different for each connection, or else
 *   an already existent connection will be used. (This is an implementation
 *   decision of the PHP PostgreSQL module writers, not mine!)
 * </p>
 ***/
class PgTransaction extends Transaction
{
    // CREATORS

    function PgTransaction(&$database)
    {
        $this->Transaction($database);
    }

    // ACCESSORS

    /***
     * @returns string
     * @protected
     ***/
    function getBeginSql()
    {
        return 'BEGIN WORK';
    }
}
?>
