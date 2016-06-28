<?php
namespace SPHERE\Application\Education\Certificate\Generator\Service;

use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateGrade;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateSubject;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 *
 * @package SPHERE\Application\Education\Certificate\Generator\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

        $this->createCertificate('Grundschule Halbjahresinformation', '', 'GsHjInfo');
        $this->createCertificate('Grundschule Halbjahresinformation', 'der ersten Klasse', 'GsHjOneInfo');
        $this->createCertificate('Grundschule Jahreszeugnis', '', 'GsJ');
        $this->createCertificate('Grundschule Jahreszeugnis', 'der ersten Klasse', 'GsJOne');
        $this->createCertificate('Gymnasium Abgangszeugnis', 'Hauptschulabschluss Klasse 9', 'GymAbgHs');
        $this->createCertificate('Gymnasium Abgangszeugnis', 'Realschulabschluss Klasse 10', 'GymAbgRs');
        $this->createCertificate('Gymnasium Halbjahresinformation', '', 'GymHjInfo');
        $this->createCertificate('Gymnasium Halbjahreszeugnis', '', 'GymHj');
        $this->createCertificate('Gymnasium Jahreszeugnis', '', 'GymJ');
        $this->createCertificate('Mittelschule Abgangszeugnis', 'Hauptschule', 'MsAbgHs');
        $this->createCertificate('Mittelschule Abgangszeugnis', 'Realschule', 'MsAbgRs');
        $this->createCertificate('Mittelschule Abschlusszeugnis', 'Hauptschule', 'MsAbsHs');
        $this->createCertificate('Mittelschule Abschlusszeugnis', 'Hauptschule qualifiziert', 'MsAbsHsQ');
        $this->createCertificate('Mittelschule Abschlusszeugnis', 'Realschule', 'MsAbsRs');
        $this->createCertificate('Mittelschule Halbjahresinformation', 'Hauptschule', 'MsHjInfoHs');
        $this->createCertificate('Mittelschule Halbjahresinformation', 'Klasse 5-6', 'MsHjInfo');
        $this->createCertificate('Mittelschule Halbjahresinformation', 'Realschule', 'MsHjInfoRs');
        $this->createCertificate('Mittelschule Halbjahreszeugnis', 'Hauptschule', 'MsHjHs');
        $this->createCertificate('Mittelschule Halbjahreszeugnis', 'Klasse 5-6', 'MsHj');
        $this->createCertificate('Mittelschule Halbjahreszeugnis', 'Realschule', 'MsHjRs');
        $this->createCertificate('Mittelschule Jahreszeugnis', 'Hauptschule', 'MsJHs');
        $this->createCertificate('Mittelschule Jahreszeugnis', 'Klasse 5-6', 'MsJ');
        $this->createCertificate('Mittelschule Jahreszeugnis', 'Realschule', 'MsJRs');

        $tblConsumer = Consumer::useService()->getConsumerByAcronym('ESZC');
        if ($tblConsumer) {
            $this->createCertificate(
                'Bildungsempfehlung', 'Gymnasium', 'ESZC\CheBeGym', $tblConsumer
            );
            $this->createCertificate(
                'Bildungsempfehlung', 'Mittelschule', 'ESZC\CheBeMi', $tblConsumer
            );
            $this->createCertificate(
                'Halbjahresinformation', 'Hauptschule', 'ESZC\CheHjInfoHs', $tblConsumer
            );
            $this->createCertificate(
                'Halbjahresinformation', 'Klasse 5-6', 'ESZC\CheHjInfo', $tblConsumer
            );
            $this->createCertificate(
                'Halbjahresinformation', 'Realschule', 'ESZC\CheHjInfoRs', $tblConsumer
            );
            $this->createCertificate(
                'Halbjahresinformation', 'Gymnasium', 'ESZC\CheHjGymInfo', $tblConsumer
            );
            $this->createCertificate(
                'Halbjahreszeugnis', 'Gymnasium', 'ESZC\CheHjGym', $tblConsumer
            );
            $this->createCertificate(
                'Halbjahreszeugnis', 'Hauptschule', 'ESZC\CheHjHs', $tblConsumer
            );
            $this->createCertificate(
                'Halbjahreszeugnis', 'Klasse 5-6', 'ESZC\CheHj', $tblConsumer
            );
            $this->createCertificate(
                'Halbjahreszeugnis', 'Realschule', 'ESZC\CheHjRs', $tblConsumer
            );
            $this->createCertificate(
                'Jahreszeugnis', 'Mittelschule', 'ESZC\CheJ', $tblConsumer
            );
            $this->createCertificate(
                'Jahreszeugnis', 'Gymnasium', 'ESZC\CheJGym', $tblConsumer
            );
            $this->createCertificate(
                'Bildungsempfehlung', 'Klassenstufe 4', 'ESZC\CheBeGs', $tblConsumer
            );
            $this->createCertificate(
                'Jahreszeugnis', 'Grundschule Klasse 2-4', 'ESZC\CheJGs', $tblConsumer
            );
            $this->createCertificate(
                'Jahreszeugnis', 'Grundschule Klasse 1', 'ESZC\CheJGsOne', $tblConsumer
            );
            $this->createCertificate(
                'Habljahresinformation', 'Grundschule Klasse 2-4', 'ESZC\CheHjInfoGs', $tblConsumer
            );
            $this->createCertificate(
                'Habljahresinformation', 'Grundschule Klasse 1', 'ESZC\CheHjInfoGsOne', $tblConsumer
            );
        }

        $tblConsumer = Consumer::useService()->getConsumerByAcronym('EVSC');
        if ($tblConsumer) {
            $this->createCertificate(
                'Halbjahresinformation', 'Primarstufe', 'EVSC\CosHjPri', $tblConsumer
            );
            $this->createCertificate(
                'Halbjahresinformation', 'Sekundarstufe', 'EVSC\CosHjSek', $tblConsumer
            );
            $this->createCertificate(
                'Jahreszeugnis', 'Primarstufe', 'EVSC\CosJPri', $tblConsumer
            );
            $this->createCertificate(
                'Jahreszeugnis', 'Sekundarstufe', 'EVSC\CosJSek', $tblConsumer
            );
        }

        $tblConsumer = Consumer::useService()->getConsumerByAcronym('FESH');
        if ($tblConsumer) {
            $this->createCertificate(
                'Halbjahresinformation', '', 'FESH\HorHj', $tblConsumer
            );
            $this->createCertificate(
                'Halbjahresinformation', '1. Klasse', 'FESH\HorHjOne', $tblConsumer
            );
            $this->createCertificate(
                'Jahreszeugnis', '', 'FESH\HorJ', $tblConsumer
            );
            $this->createCertificate(
                'Jahreszeugnis', '1. Klasse', 'FESH\HorJOne', $tblConsumer
            );
        }
    }

    /**
     * @param string           $Name
     * @param string           $Description
     * @param string           $Certificate
     * @param TblConsumer|null $tblConsumer
     *
     * @return null|object|TblCertificate
     */
    public function createCertificate($Name, $Description, $Certificate, TblConsumer $tblConsumer = null)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblCertificate')->findOneBy(array(
            TblCertificate::ATTR_CERTIFICATE => $Certificate
        ));

        if (null === $Entity) {
            $Entity = new TblCertificate();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setCertificate($Certificate);
            $Entity->setServiceTblConsumer($tblConsumer);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param null|TblConsumer $tblConsumer
     *
     * @return bool|TblCertificate[]
     */
    public function getCertificateAllByConsumer(TblConsumer $tblConsumer = null)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblCertificate', array(
                TblCertificate::SERVICE_TBL_CONSUMER => ( $tblConsumer ? $tblConsumer->getId() : null )
            )
        );
    }

    /**
     * @return bool|TblCertificate[]
     */
    public function getCertificateAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCertificate');
    }

    /**
     * @param $Id
     *
     * @return bool|TblCertificate
     */
    public function getCertificateById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblCertificate', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param string $Class
     *
     * @return bool|TblCertificate
     */
    public function getCertificateByCertificateClassName($Class)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblCertificate', array(
                TblCertificate::ATTR_CERTIFICATE => $Class
            )
        );
    }

    /**
     * @param $Id
     *
     * @return bool|TblCertificateSubject
     */
    public function getCertificateSubjectById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblCertificateSubject', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param $Id
     *
     * @return bool|TblCertificateGrade
     */
    public function getCertificateGradeById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblCertificateGrade', $Id);
        return ( null === $Entity ? false : $Entity );
    }
}
