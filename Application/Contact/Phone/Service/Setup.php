<?php
namespace SPHERE\Application\Contact\Phone\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\Application\Contact\Phone\Service\Entity\TblPhone;
use SPHERE\Application\Contact\Phone\Service\Entity\TblToCompany;
use SPHERE\Application\Contact\Phone\Service\Entity\TblToPerson;
use SPHERE\Application\Contact\Phone\Service\Entity\TblType;
use SPHERE\System\Database\Binding\AbstractSetup;
use SPHERE\System\Database\Fitting\Element;
use SPHERE\System\Database\Fitting\View;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Contact\Phone\Service
 */
class Setup extends AbstractSetup
{

    /**
     * @param bool $Simulate
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupDatabaseSchema($Simulate = true, $UTF8 = false)
    {

        /**
         * Table
         */
        $Schema = clone $this->getConnection()->getSchema();
        $tblPhone = $this->setTablePhone($Schema);
        $tblType = $this->setTableType($Schema);
        $this->setTableToPerson($Schema, $tblPhone, $tblType);
        $this->setTableToCompany($Schema, $tblPhone, $tblType);
        /**
         * Migration & Protocol
         */
        $this->getConnection()->addProtocol(__CLASS__);
        if(!$UTF8){
            $this->getConnection()->setMigration($Schema, $Simulate);
        } else {
            $this->getConnection()->setUTF8();
        }

        $this->getConnection()->createView(
            ( new View($this->getConnection(), 'viewPhoneToPerson') )
                ->addLink(new TblToPerson(), 'tblType', new TblType())
                ->addLink(new TblToPerson(), 'tblPhone', new TblPhone())
        );

        $this->getConnection()->createView(
            ( new View($this->getConnection(), 'viewPhoneToCompany') )
                ->addLink(new TblToCompany(), 'tblType', new TblType())
                ->addLink(new TblToCompany(), 'tblPhone', new TblPhone())
        );

        return $this->getConnection()->getProtocol($Simulate);
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTablePhone(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblPhone');
        if (!$this->getConnection()->hasColumn('tblPhone', 'Number')) {
            $Table->addColumn('Number', 'string');
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
     * @param Table  $tblPhone
     * @param Table  $tblType
     *
     * @return Table
     */
    private function setTableToPerson(Schema &$Schema, Table $tblPhone, Table $tblType)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblToPerson');
        if (!$this->getConnection()->hasColumn('tblToPerson', 'Remark')) {
            $Table->addColumn('Remark', 'text');
        }
        if (!$this->getConnection()->hasColumn('tblToPerson', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasIndex($Table, array('serviceTblPerson', Element::ENTITY_REMOVE))) {
            $Table->addIndex(array('serviceTblPerson', Element::ENTITY_REMOVE));
        }
        $this->createColumn($Table, 'IsEmergencyContact', self::FIELD_TYPE_BOOLEAN, false, false);

        $this->getConnection()->addForeignKey($Table, $tblPhone, null);
        $this->getConnection()->addForeignKey($Table, $tblType);
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblPhone
     * @param Table  $tblType
     *
     * @return Table
     */
    private function setTableToCompany(Schema &$Schema, Table $tblPhone, Table $tblType)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblToCompany');
        if (!$this->getConnection()->hasColumn('tblToCompany', 'Remark')) {
            $Table->addColumn('Remark', 'text');
        }
        if (!$this->getConnection()->hasColumn('tblToCompany', 'serviceTblCompany')) {
            $Table->addColumn('serviceTblCompany', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasIndex($Table, array('serviceTblCompany', Element::ENTITY_REMOVE))) {
            $Table->addIndex(array('serviceTblCompany', Element::ENTITY_REMOVE));
        }
        $this->createColumn($Table, 'IsEmergencyContact', self::FIELD_TYPE_BOOLEAN, false, false);

        $this->getConnection()->addForeignKey($Table, $tblPhone, null);
        $this->getConnection()->addForeignKey($Table, $tblType);
        return $Table;
    }
}
