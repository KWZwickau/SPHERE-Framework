<?php

namespace SPHERE\Application\App\Authentication\Process\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\Application\App\AppException;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblIdentification;
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

        $tblDevice = $this->setTableDevice($schema);
        $this->setTableToken($schema, $tblDevice);

        $tblFactor = $this->setTableFactor($schema);
        $tblProcess = $this->setTableProcess($schema, $tblFactor);

        $this->setTableStep($schema, $tblDevice, $tblProcess);
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

    private function setTableDevice(Schema $Schema): Table
    {
        $table = $this->createTable($Schema, 'tblDevice');
        $this->createColumn($table, 'deviceIdentifier');
        $this->createColumn($table, 'processToken', self::FIELD_TYPE_STRING, true);
        $this->createColumn($table, 'processTimeout', self::FIELD_TYPE_INTEGER, true);
        return $table;
    }

    private function setTableFactor(Schema $schema): Table
    {
        $table = $this->createTable($schema, 'tblFactor');
        $this->createColumn($table, 'name');
        $this->createColumn($table, 'description', self::FIELD_TYPE_TEXT, true);
        return $table;
    }

    /**
     * Which factor is used by which identification?
     * Example: The identification "system" uses factor "credentials" and "yubikey"
     */
    private function setTableProcess(Schema $schema, Table $tblFactor): Table
    {
        $table = $this->createTable($schema, 'tblProcess');
        $this->createServiceKey($table, new TblIdentification(''));
        $this->createForeignKey($table, $tblFactor);
        $this->createColumn($table, 'sortOrder', self::FIELD_TYPE_INTEGER, false, 0);
        return $table;
    }

    private function setTableToken(Schema $Schema, Table $tblDevice): void
    {
        $table = $this->createTable($Schema, 'tblToken');
        $this->createServiceKey($table, new TblAccount(''));
        $this->createForeignKey($table, $tblDevice);
        $this->createColumn($table, 'authenticationToken', self::FIELD_TYPE_STRING, true);
        $this->createColumn($table, 'authenticationTimeout', self::FIELD_TYPE_INTEGER, true);
        $this->createColumn($table, 'accessToken', self::FIELD_TYPE_STRING, true);
        $this->createColumn($table, 'accessTimeout', self::FIELD_TYPE_INTEGER, true);
    }

    /**
     * The process of the current sign-in attempt
     */
    private function setTableStep(Schema $schema, Table $tblDevice, Table $tblProcess): void
    {
        $table = $this->createTable($schema, 'tblStep');
        $this->createServiceKey($table, new TblAccount(''));
        $this->createForeignKey($table, $tblDevice);
        $this->createForeignKey($table, $tblProcess);
        /**
         * true = the factor was successfully resolved
         * false = the attempt failed or has no answer yet
         */
        $this->createColumn($table, 'isSolved', self::FIELD_TYPE_BOOLEAN, false, false);
    }
}
