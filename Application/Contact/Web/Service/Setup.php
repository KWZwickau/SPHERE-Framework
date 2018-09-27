<?php
namespace SPHERE\Application\Contact\Web\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\Application\Contact\Web\Service\Entity\TblWeb;
use SPHERE\Application\Contact\Web\Service\Entity\TblToCompany;
use SPHERE\Application\Contact\Web\Service\Entity\TblToPerson;
use SPHERE\Application\Contact\Web\Service\Entity\TblType;
use SPHERE\System\Database\Binding\AbstractSetup;
use SPHERE\System\Database\Fitting\Element;
use SPHERE\System\Database\Fitting\View;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Contact\Web\Service
 */
class Setup extends AbstractSetup
{

    /**
     * @param bool $Simulate
     *
     * @return string
     */
    public function setupDatabaseSchema($Simulate = true)
    {

        /**
         * Table
         */
        $Schema = clone $this->getConnection()->getSchema();
        $tblWeb = $this->setTableWeb($Schema);
        $tblType = $this->setTableType($Schema);
        $this->setTableToPerson($Schema, $tblWeb, $tblType);
        $this->setTableToCompany($Schema, $tblWeb, $tblType);
        /**
         * Migration & Protocol
         */
        $this->getConnection()->addProtocol(__CLASS__);
        $this->getConnection()->setMigration($Schema, $Simulate);

        $this->getConnection()->createView(
            ( new View($this->getConnection(), 'viewWebToPerson') )
                ->addLink(new TblToPerson(), 'tblType', new TblType())
                ->addLink(new TblToPerson(), 'tblWeb', new TblWeb())
        );

        $this->getConnection()->createView(
            ( new View($this->getConnection(), 'viewWebToCompany') )
                ->addLink(new TblToCompany(), 'tblType', new TblType())
                ->addLink(new TblToCompany(), 'tblWeb', new TblWeb())
        );

        return $this->getConnection()->getProtocol($Simulate);
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableWeb(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblWeb');
        if (!$this->getConnection()->hasColumn('tblWeb', 'Address')) {
            $Table->addColumn('Address', 'string');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableType(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblType');
        if (!$this->getConnection()->hasColumn('tblType', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblType', 'Description')) {
            $Table->addColumn('Description', 'string');
        }

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblWeb
     * @param Table  $tblType
     *
     * @return Table
     */
    private function setTableToPerson(Schema &$Schema, Table $tblWeb, Table $tblType)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblToPerson');
        if (!$this->getConnection()->hasColumn('tblToPerson', 'Remark')) {
            $Table->addColumn('Remark', 'text');
        }
        if (!$this->getConnection()->hasColumn('tblToPerson', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint', array('notnull' => false));
        }
        $this->getConnection()->removeIndex($Table, array('serviceTblPerson'));
        if (!$this->getConnection()->hasIndex($Table, array('serviceTblPerson', Element::ENTITY_REMOVE))) {
            $Table->addIndex(array('serviceTblPerson', Element::ENTITY_REMOVE));
        }
        $this->getConnection()->addForeignKey($Table, $tblWeb, null);
        $this->getConnection()->addForeignKey($Table, $tblType);
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblWeb
     * @param Table  $tblType
     *
     * @return Table
     */
    private function setTableToCompany(Schema &$Schema, Table $tblWeb, Table $tblType)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblToCompany');
        if (!$this->getConnection()->hasColumn('tblToCompany', 'Remark')) {
            $Table->addColumn('Remark', 'text');
        }
        if (!$this->getConnection()->hasColumn('tblToCompany', 'serviceTblCompany')) {
            $Table->addColumn('serviceTblCompany', 'bigint', array('notnull' => false));
        }
        $this->getConnection()->removeIndex($Table, array('serviceTblCompany'));
        if (!$this->getConnection()->hasIndex($Table, array('serviceTblCompany', Element::ENTITY_REMOVE))) {
            $Table->addIndex(array('serviceTblCompany', Element::ENTITY_REMOVE));
        }
        $this->getConnection()->addForeignKey($Table, $tblWeb, null);
        $this->getConnection()->addForeignKey($Table, $tblType);
        return $Table;
    }
}
