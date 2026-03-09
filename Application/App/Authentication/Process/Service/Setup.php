<?php

namespace SPHERE\Application\App\Authentication\Process\Service;

use Doctrine\DBAL\Schema\Schema;
use SPHERE\Application\App\AppException;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 *
 */
class Setup extends AbstractSetup
{

    /**
     * @param bool $Simulate
     * @param bool $IsCollation
     *
     * @return string
     * @throws AppException
     */
    public function setupDatabaseSchema($Simulate = true, $IsCollation = false): string
    {
        /**
         * Connection
         */
        $connection = $this->getConnection();
        if (null === $connection) {
            throw new AppException('Connection not set');
        }
        /**
         * Table
         */
        $schema = clone $connection->getSchema();

        $this->setTableDevice($schema);
        /**
         * Migration & Protocol
         */
        $connection->addProtocol(__CLASS__);
        if (!$IsCollation) {
            $connection->setMigration($schema, $Simulate);
        } else {
            $connection->setUTF8();
        }
        return $connection->getProtocol($Simulate);
    }

    private function setTableDevice(Schema $Schema): void
    {
        $table = $this->createTable($Schema, 'tblDevice');
        $this->createColumn($table, 'serviceTblAccount', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($table, 'deviceIdentifier', self::FIELD_TYPE_STRING, false);
        $this->createColumn($table, 'deviceName', self::FIELD_TYPE_STRING, true);
        $this->createColumn($table, 'authenticationToken', self::FIELD_TYPE_STRING, true);
        $this->createColumn($table, 'authenticationTimeout', self::FIELD_TYPE_INTEGER, true);
        $this->createColumn($table, 'accessToken', self::FIELD_TYPE_STRING, true);
        $this->createColumn($table, 'accessTimeout', self::FIELD_TYPE_INTEGER, true);
        $this->createColumn($table, 'otpToken', self::FIELD_TYPE_STRING, true);
        $this->createColumn($table, 'otpTimeout', self::FIELD_TYPE_INTEGER, true);
    }
}
