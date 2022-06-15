<?php
namespace SPHERE\Application\People\Meta\Agreement\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 *
 * @package SPHERE\Application\People\Meta\Agreement\Service
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

        $tblPersonAgreementCategory = $this->setTablePersonAgreementCategory($Schema);
        $tblPersonAgreementType = $this->setTablePersonAgreementType($Schema, $tblPersonAgreementCategory);
        $this->setTablePersonAgreement($Schema, $tblPersonAgreementType);

        /**
         * Migration & Protocol
         */
        $this->getConnection()->addProtocol(__CLASS__);
        if(!$UTF8){
            $this->getConnection()->setMigration($Schema, $Simulate);
        } else {
            $this->getConnection()->setUTF8();
        }

        return $this->getConnection()->getProtocol($Simulate);
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTablePersonAgreementCategory(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblPersonAgreementCategory');
        if (!$this->getConnection()->hasColumn('tblPersonAgreementCategory', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblPersonAgreementCategory', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblPersonAgreementCategory
     *
     * @return Table
     */
    private function setTablePersonAgreementType(Schema &$Schema, Table $tblPersonAgreementCategory)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblPersonAgreementType');
        if (!$this->getConnection()->hasColumn('tblPersonAgreementType', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblPersonAgreementType', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        $this->getConnection()->addForeignKey($Table, $tblPersonAgreementCategory);
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblPersonAgreementType
     *
     * @return Table
     */
    private function setTablePersonAgreement(
        Schema &$Schema,
        Table $tblPersonAgreementType
    ) {

        $Table = $this->getConnection()->createTable($Schema, 'tblPersonAgreement');
        $this->createColumn($Table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT);
        $this->getConnection()->addForeignKey($Table, $tblPersonAgreementType);
        return $Table;
    }
}
