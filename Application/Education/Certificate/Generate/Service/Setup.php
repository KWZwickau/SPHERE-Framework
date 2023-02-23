<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 25.11.2016
 * Time: 11:28
 */

namespace SPHERE\Application\Education\Certificate\Generate\Service;

use SPHERE\System\Database\Binding\AbstractSetup;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;

/**
 * Class Setup
 * @package SPHERE\Application\Education\Certificate\Generate\Service
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
        $tblGenerateCertificate = $this->setTableGenerateCertificate($Schema);
        $this->setTableGenerateCertificateSetting($Schema, $tblGenerateCertificate);

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
    private function setTableGenerateCertificate(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblGenerateCertificate');
        $this->createColumn( $Table, 'Date', self::FIELD_TYPE_DATETIME );
        $this->createColumn( $Table, 'AppointedDateForAbsence', self::FIELD_TYPE_DATETIME, true );
        $this->createColumn( $Table, 'Name', self::FIELD_TYPE_STRING );
        $this->createColumn( $Table, 'serviceTblCertificateType', self::FIELD_TYPE_BIGINT );
        $this->createColumn( $Table, 'serviceTblAppointedDateTask', self::FIELD_TYPE_BIGINT, true );
        $this->createColumn( $Table, 'serviceTblBehaviorTask', self::FIELD_TYPE_BIGINT, true );
        $this->createColumn( $Table, 'HeadmasterName', self::FIELD_TYPE_STRING );
        $this->createColumn( $Table, 'IsDivisionTeacherAvailable', self::FIELD_TYPE_BOOLEAN );
        $this->createColumn( $Table, 'serviceTblYear', self::FIELD_TYPE_BIGINT );
        $this->createColumn( $Table, 'serviceTblCommonGenderHeadmaster', self::FIELD_TYPE_BIGINT, true);

        if ($this->getConnection()->hasColumn('tblGenerateCertificate', 'IsLocked')) {
            $Table->dropColumn('IsLocked');
        }

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblGenerateCertificate
     *
     * @return Table
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    private function setTableGenerateCertificateSetting(Schema &$Schema, Table $tblGenerateCertificate)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblGenerateCertificateSetting');
        $this->createColumn($Table, 'Field', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Value', self::FIELD_TYPE_TEXT);

        $this->getConnection()->addForeignKey($Table, $tblGenerateCertificate);
        $this->createIndex($Table, array('Field' , 'tblGenerateCertificate'));

        return $Table;
    }
}