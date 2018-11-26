<?php
namespace SPHERE\Application\Billing\Accounting\Causer\Service;

//use Doctrine\DBAL\Schema\Schema;
//use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Billing\Accounting\SchoolAccount\Service
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
//        $this->setTableCauser($Schema);

        /**
         * Migration & Protocol
         */
        $this->getConnection()->addProtocol(__CLASS__);
        $this->getConnection()->setMigration($Schema, $Simulate);
        return $this->getConnection()->getProtocol($Simulate);
    }

//    /**
//     * @param Schema $Schema
//     *
//     * @return Table $tblSchoolAccount
//     *
//     * @return Table
//     */
//    private function setTableCauser(Schema &$Schema)
//    {
//
//        $Table = $this->createTable($Schema, 'tblCauser');
//        $this->createColumn($Table, 'Owner', self::FIELD_TYPE_STRING);
//
//        return $Table;
//    }
}
