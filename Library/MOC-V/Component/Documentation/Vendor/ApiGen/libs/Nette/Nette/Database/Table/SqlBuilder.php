<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Database\Table;

use Nette;
use PDO;

/**
 * Builds SQL query.
 * SqlBuilder is based on great library NotORM http://www.notorm.com written by Jakub Vrana.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 */
class SqlBuilder extends Nette\Object
{

    /** @var Selection */
    protected $selection;

    /** @var Nette\Database\Connection */
    protected $connection;

    /** @var string delimited table name */
    protected $delimitedTable;

    /** @var array of column to select */
    protected $select = array();

    /** @var array of where conditions */
    protected $where = array();

    /** @var array of where conditions for caching */
    protected $conditions = array();

    /** @var array of parameters passed to where conditions */
    protected $parameters = array();

    /** @var array or columns to order by */
    protected $order = array();

    /** @var int number of rows to fetch */
    protected $limit = null;

    /** @var int first row to fetch */
    protected $offset = null;

    /** @var string columns to grouping */
    protected $group = '';

    /** @var string grouping condition */
    protected $having = '';


    public function __construct( Selection $selection )
    {

        $this->selection = $selection;
        $this->connection = $selection->getConnection();
        $this->delimitedTable = $this->connection->getSupplementalDriver()->delimite( $selection->getName() );
    }


    public function setSelection( Selection $selection )
    {

        $this->selection = $selection;
    }


    public function buildInsertQuery()
    {

        return "INSERT INTO {$this->delimitedTable}";
    }


    public function buildUpdateQuery()
    {

        return "UPDATE{$this->buildTopClause()} {$this->delimitedTable} SET ?".$this->buildConditions();
    }

    protected function buildTopClause()
    {

        if ($this->limit !== null && $this->connection->getAttribute( PDO::ATTR_DRIVER_NAME ) === 'dblib') {
            return " TOP ($this->limit)"; //! offset is not supported
        }
        return '';
    }

    protected function buildConditions()
    {

        $return = '';
        $driver = $this->connection->getAttribute( PDO::ATTR_DRIVER_NAME );
        $where = $this->where;
        if ($this->limit !== null && $driver === 'oci') {
            $where[] = ( $this->offset ? "rownum > $this->offset AND " : '' ).'rownum <= '.( $this->limit + $this->offset );
        }
        if ($where) {
            $return .= ' WHERE ('.implode( ') AND (', $where ).')';
        }
        if ($this->group) {
            $return .= ' GROUP BY '.$this->tryDelimite( $this->removeExtraTables( $this->group ) );
        }
        if ($this->having) {
            $return .= ' HAVING '.$this->tryDelimite( $this->removeExtraTables( $this->having ) );
        }
        if ($this->order) {
            $return .= ' ORDER BY '.$this->tryDelimite( $this->removeExtraTables( implode( ', ', $this->order ) ) );
        }
        if ($this->limit !== null && $driver !== 'oci' && $driver !== 'dblib') {
            $return .= " LIMIT $this->limit";
            if ($this->offset !== null) {
                $return .= " OFFSET $this->offset";
            }
        }
        return $return;
    }

    protected function tryDelimite( $s )
    {

        $driver = $this->connection->getSupplementalDriver();
        return preg_replace_callback( '#(?<=[^\w`"\[]|^)[a-z_][a-z0-9_]*(?=[^\w`"(\]]|$)#i',
        function ( $m ) use ( $driver ) {

            return strtoupper( $m[0] ) === $m[0] ? $m[0] : $driver->delimite( $m[0] );
        }, $s );
    }

    protected function removeExtraTables( $expression )
    {

        return preg_replace( '~(?:\\b[a-z_][a-z0-9_.:]*[.:])?([a-z_][a-z0-9_]*)[.:]([a-z_*])~i', '\\1.\\2',
            $expression ); // rewrite tab1.tab2.col
    }

    public function buildDeleteQuery()
    {

        return "DELETE{$this->buildTopClause()} FROM {$this->delimitedTable}".$this->buildConditions();
    }

    public function importConditions( SqlBuilder $builder )
    {

        $this->where = $builder->where;
        $this->parameters = $builder->parameters;
        $this->conditions = $builder->conditions;
    }

    /********************* SQL selectors ****************d*g**/

    public function addSelect( $columns )
    {

        $this->select[] = $columns;
    }

    public function getSelect()
    {

        return $this->select;
    }

    public function addWhere( $condition, $parameters = array() )
    {

        $args = func_get_args();
        $hash = md5( json_encode( $args ) );
        if (isset( $this->conditions[$hash] )) {
            return false;
        }

        $this->conditions[$hash] = $condition;
        $condition = $this->removeExtraTables( $condition );
        $condition = $this->tryDelimite( $condition );

        if (count( $args ) !== 2 || strpbrk( $condition, '?:' )) { // where('column < ? OR column > ?', array(1, 2))
            if (count( $args ) !== 2 || !is_array( $parameters )) { // where('column < ? OR column > ?', 1, 2)
                $parameters = $args;
                array_shift( $parameters );
            }

            $this->parameters = array_merge( $this->parameters, $parameters );

        } elseif ($parameters === null) { // where('column', NULL)
            $condition .= ' IS NULL';

        } elseif ($parameters instanceof Selection) { // where('column', $db->$table())
            $clone = clone $parameters;
            if (!$clone->getSqlBuilder()->select) {
                $clone->select( $clone->primary );
            }

            if ($this->connection->getAttribute( PDO::ATTR_DRIVER_NAME ) !== 'mysql') {
                $condition .= ' IN ('.$clone->getSql().')';
            } else {
                $in = array();
                foreach ($clone as $row) {
                    $this->parameters[] = array_values( iterator_to_array( $row ) );
                    $in[] = ( count( $row ) === 1 ? '?' : '(?)' );
                }
                $condition .= ' IN ('.( $in ? implode( ', ', $in ) : 'NULL' ).')';
            }

        } elseif (!is_array( $parameters )) { // where('column', 'x')
            $condition .= ' = ?';
            $this->parameters[] = $parameters;

        } else { // where('column', array(1, 2))
            if ($parameters) {
                $condition .= " IN (?)";
                $this->parameters[] = $parameters;
            } else {
                $condition .= " IN (NULL)";
            }
        }

        $this->where[] = $condition;
        return true;
    }

    public function getConditions()
    {

        return array_values( $this->conditions );
    }

    public function addOrder( $columns )
    {

        $this->order[] = $columns;
    }

    public function getOrder()
    {

        return $this->order;
    }

    public function getLimit()
    {

        return $this->limit;
    }

    public function setLimit( $limit, $offset )
    {

        $this->limit = $limit;
        $this->offset = $offset;
    }


    /********************* SQL building ****************d*g**/

    public function getOffset()
    {

        return $this->offset;
    }

    public function getGroup()
    {

        return $this->group;
    }

    public function setGroup( $columns, $having )
    {

        $this->group = $columns;
        $this->having = $having;
    }

    public function getHaving()
    {

        return $this->having;
    }

    /**
     * Returns SQL query.
     *
     * @return string
     */
    public function buildSelectQuery()
    {

        $join = $this->buildJoins( implode( ',', $this->conditions ), true );
        $join += $this->buildJoins( implode( ',', $this->select ).",{$this->group},{$this->having},".implode( ',',
                $this->order ) );

        $prefix = $join ? "{$this->delimitedTable}." : '';
        if ($this->select) {
            $cols = $this->tryDelimite( $this->removeExtraTables( implode( ', ', $this->select ) ) );

        } elseif ($prevAccessed = $this->selection->getPreviousAccessed()) {
            $cols = array_map( array( $this->connection->getSupplementalDriver(), 'delimite' ),
                array_keys( array_filter( $prevAccessed ) ) );
            $cols = $prefix.implode( ', '.$prefix, $cols );

        } else {
            $cols = $prefix.'*';
        }

        return "SELECT{$this->buildTopClause()} {$cols} FROM {$this->delimitedTable}".implode( $join ).$this->buildConditions();
    }

    protected function buildJoins( $val, $inner = false )
    {

        $driver = $this->selection->getConnection()->getSupplementalDriver();
        $reflection = $this->selection->getConnection()->getDatabaseReflection();
        $joins = array();
        preg_match_all( '~\\b([a-z][\\w.:]*[.:])([a-z]\\w*|\*)(\\s+IS\\b|\\s*<=>)?~i', $val, $matches );
        foreach ($matches[1] as $names) {
            $parent = $this->selection->getName();
            if ($names !== "$parent.") { // case-sensitive
                preg_match_all( '~\\b([a-z][\\w]*|\*)([.:])~i', $names, $matches, PREG_SET_ORDER );
                foreach ($matches as $match) {
                    list( , $name, $delimiter ) = $match;

                    if ($delimiter === ':') {
                        list( $table, $primary ) = $reflection->getHasManyReference( $parent, $name );
                        $column = $reflection->getPrimary( $parent );
                    } else {
                        list( $table, $column ) = $reflection->getBelongsToReference( $parent, $name );
                        $primary = $reflection->getPrimary( $table );
                    }

                    $joins[$name] = ' '
                        .( !isset( $joins[$name] ) && $inner && !isset( $match[3] ) ? 'INNER' : 'LEFT' )
                        .' JOIN '.$driver->delimite( $table ).( $table !== $name ? ' AS '.$driver->delimite( $name ) : '' )
                        .' ON '.$driver->delimite( $parent ).'.'.$driver->delimite( $column )
                        .' = '.$driver->delimite( $name ).'.'.$driver->delimite( $primary );

                    $parent = $name;
                }
            }
        }
        return $joins;
    }

    public function getParameters()
    {

        return $this->parameters;
    }

}
