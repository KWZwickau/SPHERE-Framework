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
        $tblFactor = $this->setTableFactor($schema);
        $this->setTableIdentification($schema, $tblFactor);
        $this->setTableProcess($schema, $tblFactor);
        $this->setTableToken($schema);
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

    private function setTableFactor(Schema $Schema): Table
    {
        $table = $this->createTable($Schema, 'tblFactor');
        $this->createColumn($table, 'factorName');
        $this->createColumn($table, 'factorDescription', self::FIELD_TYPE_TEXT, true);
        return $table;
    }

    /**
     * Which factor is used by which identification?
     * Example: Identification "system" uses factor "credentials" and "yubikey"
     */
    private function setTableIdentification(Schema $Schema, Table $tblFactor): void
    {
        $table = $this->createTable($Schema, 'tblIdentification');
        $this->createServiceKey($table, new TblIdentification(''));
        $this->createForeignKey($table, $tblFactor);
    }

    /**
     * The process of the current sign-in attempt
     */
    private function setTableProcess(Schema $Schema, Table $tblFactor): void
    {
        $table = $this->createTable($Schema, 'tblProcess');
        $this->createServiceKey($table, new TblAccount(''));
        $this->createForeignKey($table, $tblFactor);
        /**
         * null = not attempted
         * true = the factor was successfully resolved
         * false = the attempt failed
         */
        $this->createColumn($table, 'isSolved', self::FIELD_TYPE_BOOLEAN, true);
    }

    private function setTableToken(Schema $Schema): void
    {
        $table = $this->createTable($Schema, 'tblToken');
        $this->createServiceKey($table, new TblAccount(''));
        $this->createColumn($table, 'authenticationToken', self::FIELD_TYPE_STRING, true);
        $this->createColumn($table, 'authenticationTokenTimeout', self::FIELD_TYPE_INTEGER, true);
        $this->createColumn($table, 'accessToken', self::FIELD_TYPE_STRING, true);
        $this->createColumn($table, 'accessTokenTimeout', self::FIELD_TYPE_INTEGER, true);
    }
}
