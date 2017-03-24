<?php

namespace SPHERE\Application\Document\Generator\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 * @package SPHERE\Application\Document\Standard\Service
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
        $tblDocument = $this->setTableDocument($Schema);
        $this->setTableDocumentSubject($Schema, $tblDocument);

        /**
         * Migration & Protocol
         */
        $this->getConnection()->addProtocol(__CLASS__);
        $this->getConnection()->setMigration($Schema, $Simulate);
        return $this->getConnection()->getProtocol($Simulate);
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableDocument(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblDocument');
        $this->createColumn($Table, 'Name', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'DocumentClass', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'serviceTblSchoolType', self::FIELD_TYPE_BIGINT, true);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblDocument
     *
     * @return Table
     */
    private function setTableDocumentSubject(Schema &$Schema, Table $tblDocument)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblDocumentSubject');
        $this->createColumn($Table, 'IsEssential', self::FIELD_TYPE_BOOLEAN);
        $this->createColumn($Table, 'Ranking', self::FIELD_TYPE_INTEGER);
        $this->createColumn($Table, 'serviceTblSubject', self::FIELD_TYPE_BIGINT, true);
        $this->createForeignKey($Table, $tblDocument);
        $this->createIndex($Table, array('Ranking', 'tblDocument'));

        return $Table;
    }
}
