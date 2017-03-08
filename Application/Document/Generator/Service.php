<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 08.03.2017
 * Time: 13:48
 */

namespace SPHERE\Application\Document\Generator;


use SPHERE\Application\Document\Generator\Service\Data;
use SPHERE\Application\Document\Generator\Service\Entity\TblDocument;
use SPHERE\Application\Document\Generator\Service\Entity\TblDocumentSubject;
use SPHERE\Application\Document\Generator\Service\Setup;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Document\Standard
 */
class Service extends AbstractService
{

    /**
     * @param bool $doSimulation
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($doSimulation, $withData)
    {

        $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation);
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param $id
     *
     * @return false|TblDocument
     */
    public function getDocumentById($id)
    {

        return (new Data($this->getBinding()))->getDocumentById($id);
    }

    /**
     * @param $name
     *
     * @return false|TblDocument
     */
    public function getDocumentByName($name)
    {

        return (new Data($this->getBinding()))->getDocumentByName($name);
    }

    /**
     * @param $documentClass
     *
     * @return false|TblDocument
     */
    public function getDocumentByClass($documentClass)
    {

        return (new Data($this->getBinding()))->getDocumentByClass($documentClass);
    }

    /**
     * @param TblDocument $tblDocument
     * @param integer $ranking
     *
     * @return false|TblDocumentSubject
     */
    public function getDocumentSubjectByDocumentAndRanking(TblDocument $tblDocument, $ranking)
    {

        return (new Data($this->getBinding()))->getDocumentSubjectByDocumentAndRanking($tblDocument, $ranking);
    }
}