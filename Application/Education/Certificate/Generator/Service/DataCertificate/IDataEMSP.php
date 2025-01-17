<?php
namespace SPHERE\Application\Education\Certificate\Generator\Service\DataCertificate;

use SPHERE\Application\Education\Certificate\Generator\Service\Data;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;

class IDataEMSP
{

    public static function setCertificateIndividually(Data $Data)
    {

        $tblConsumerCertificate = Consumer::useService()->getConsumerByAcronym('EMSP');

        if ($tblConsumerCertificate){
            self::setEmspGsHj($Data, $tblConsumerCertificate);
            self::setEmspGsJ($Data, $tblConsumerCertificate);
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setEmspGsHj(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Grundschule Halbjahresinformation', 'Klasse 1-3',
            'EMSP\EmspGsHj', $tblConsumerCertificate);
        if ($tblCertificate) {

            // Begrenzung des Bemerkungsfeld
            $FieldName = 'RemarkWithoutTeam';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 2000);
            }

            if ($Data->getTblSchoolTypePrimary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeHalfYear(), $Data->getTblSchoolTypePrimary(), null, true);
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    $Data->createCertificateLevel($tblCertificate, 1);
                    $Data->createCertificateLevel($tblCertificate, 2);
                    $Data->createCertificateLevel($tblCertificate, 3);
                }
            }
        }
    }

    /**
     * @param Data        $Data
     * @param TblConsumer $tblConsumerCertificate
     */
    private static function setEmspGsJ(Data $Data, TblConsumer $tblConsumerCertificate)
    {

        $tblCertificate = $Data->createCertificate('Grundschule Jahreszeugnis', 'Klasse 1-3', 'EMSP\EmspGsJ',
            $tblConsumerCertificate);
        if ($tblCertificate) {

            // Begrenzung des Bemerkungsfeld
            $FieldName = 'RemarkWithoutTeam';
            if (!$Data->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $Data->createCertificateField($tblCertificate, $FieldName, 2000);
            }

            if ($Data->getTblSchoolTypePrimary()) {
                $Data->updateCertificate($tblCertificate, $Data->getTblCertificateTypeYear(), $Data->getTblSchoolTypePrimary());
                if (!$Data->getCertificateLevelAllByCertificate($tblCertificate)) {
                    $Data->createCertificateLevel($tblCertificate, 1);
                    $Data->createCertificateLevel($tblCertificate, 2);
                    $Data->createCertificateLevel($tblCertificate, 3);
                }
            }
        }
    }
}