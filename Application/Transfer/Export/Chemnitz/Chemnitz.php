<?php
namespace SPHERE\Application\Transfer\Export\Chemnitz;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Search\Group\Group;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Stage;

/**
 * Class Chemnitz
 *
 * @package SPHERE\Application\Transfer\Export\Chemnitz
 */
class Chemnitz implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Class', __CLASS__.'::frontendClassExport'
        ));
    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {
        // TODO: Implement useService() method.
    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {

    }

    /**
     * @return Stage
     */
    public static function frontendClassExport()
    {

        $View = new Stage();
        $View->setTitle('ESZC Export');
        $View->setDescription('Klassenliste');

        /** @var PhpExcel $export */
        $export = Document::getDocument("Chemnitz Klassenliste.xls");
        $export->setValue($export->getCell("0", "0"), "Anrede");
        $export->setValue($export->getCell("1", "0"), "Vorname V.");
        $export->setValue($export->getCell("2", "0"), "Vorname M.");
        $export->setValue($export->getCell("3", "0"), "Name");
        $export->setValue($export->getCell("4", "0"), "Konfession");
        $export->setValue($export->getCell("5", "0"), "Straße");
        $export->setValue($export->getCell("6", "0"), "Hausnr.");
        $export->setValue($export->getCell("7", "0"), "PLZ Ort");
        $export->setValue($export->getCell("8", "0"), "Schüler");
        $export->setValue($export->getCell("9", "0"), "Geburtsdatum");
        $export->setValue($export->getCell("10", "0"), "Geburtsort");

        $studentList = Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByName('Schüler'));

        // Todo JohK Klassen einbauen

        $Row = 1;
        if (!empty( $studentList )) {
            foreach ($studentList as $tblPerson) {
                $father = new TblPerson();
                $mother = new TblPerson();
                $guardianList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                if ($guardianList) {
                    foreach ($guardianList as $guardian) {
                        if (( $guardian->getTblType()->getId() == 1 )
                            && ( $guardian->getServiceTblPersonFrom()->getTblSalutation()->getId() == 1 )
                        ) {
                            $father = $guardian->getServiceTblPersonFrom();
                        }
                        if (( $guardian->getTblType()->getId() == 1 )
                            && ( $guardian->getServiceTblPersonFrom()->getTblSalutation()->getId() == 2 )
                        ) {
                            $mother = $guardian->getServiceTblPersonFrom();
                        }
                    }
                }
                $common = Common::useService()->getCommonByPerson($tblPerson);

                if ($addressList = Address::useService()->getAddressAllByPerson($tblPerson)) {
                    $address = $addressList[0];
                } else {
                    $address = null;
                }

                $tblPerson->Salutation = $tblPerson->getSalutation();
                $export->setValue($export->getCell("0", $Row), $tblPerson->Salutation);
                $tblPerson->Father = $fatherFirstName = $father !== null ? $father->getFirstName() : '';
                $export->setValue($export->getCell("1", $Row), $tblPerson->Father);
                $tblPerson->Mother = $mother !== null ? $mother->getFirstName() : '';
                $export->setValue($export->getCell("2", $Row), $tblPerson->Mother);
                $export->setValue($export->getCell("3", $Row), $tblPerson->getLastName());
                $tblPerson->Denomination = $common->getTblCommonInformation()->getDenomination();
                $export->setValue($export->getCell("4", $Row), $tblPerson->Denomination);
                if ($address !== null) {
                    $tblPerson->StreetName = $address->getTblAddress()->getStreetName();
                    $export->setValue($export->getCell("5", $Row), $tblPerson->StreetName);
                    $tblPerson->StreetNumber = $address->getTblAddress()->getStreetNumber();
                    $export->setValue($export->getCell("6", $Row), $tblPerson->StreetNumber);
                    $tblPerson->City = $address->getTblAddress()->getTblCity()->getCode()
                        .' '.$address->getTblAddress()->getTblCity()->getName();
                    $export->setValue($export->getCell("7", $Row), $tblPerson->City);
                }
                $export->setValue($export->getCell("8", $Row), $tblPerson->getFirstName());
                $tblPerson->Birthday = $common->getTblCommonBirthDates()->getBirthday();
                $export->setValue($export->getCell("9", $Row), $tblPerson->Birthday);
                $tblPerson->Birthplace = $common->getTblCommonBirthDates()->getBirthplace();
                $export->setValue($export->getCell("10", $Row), $tblPerson->Birthplace);

                $Row++;
            }
        }

        $View->setContent(
            new TableData($studentList, null,
                array(
                    'Salutation'   => 'Anrede',
                    'Father'       => 'Vorname V.',
                    'Mother'       => 'Vorname M.',
                    'LastName'     => 'Name',
                    'Denomination' => 'Konfession',
                    'StreetName'   => 'Straße',
                    'StreetNumber' => 'Hausnr.',
                    'City'         => 'PLZ Ort',
                    'FirstName'    => 'Schüler',
                    'Birthday'     => 'Geburtsdatum',
                    'Birthplace'   => 'Geburtsort',
                )
            )
            .
            $Form = new Form(
                new FormGroup(
                    new FormRow(
                        new FormColumn(
                            new Warning('Test')
                        )
                    )
                )
                , new Primary('Herunterladen'))
        );

        return $View;

//        for($i = 0;$i < count($studentList); $i++)
//        {
//            $Row = $i+1;
//
//            $father = new TblPerson();
//            $mother = new TblPerson();
//            $guardianList = Relationship::useService()->getPersonRelationshipAllByPerson($studentList[$i]);
//            //Debugger::screenDump($guardianList);
//            foreach ($guardianList as $guardian)
//            {
//                if (($guardian->getTblType()->getId() == 1)
//                    && ($guardian->getServiceTblPersonFrom()->getTblSalutation()->getId() == 1 ))
//                {
//                    $father = $guardian->getServiceTblPersonFrom();
//                }
//                if (($guardian->getTblType()->getId() == 1)
//                    && ($guardian->getServiceTblPersonFrom()->getTblSalutation()->getId() == 2 ))
//                {
//                    $mother = $guardian->getServiceTblPersonFrom();
//                }
//            }
//
//            $common = Common::useService()->getCommonByPerson($studentList[$i]);
//
//            if ($addressList = Address::useService()->getAddressAllByPerson($studentList[$i]))
//            {
//                $address = $addressList[0];
//            }
//            else
//            {
//                $address = null;
//            }
//
//            $export->setValue($export->getCell("0",$Row), $studentList[$i]->getSalutation());
//            $export->setValue($export->getCell("1",$Row), $father !== null ? $father->getFirstName():'');
//            $export->setValue($export->getCell("2",$Row), $mother !== null ? $mother->getFirstName():'');
//            $export->setValue($export->getCell("3",$Row), $studentList[$i]->getLastName());
//            $export->setValue($export->getCell("4",$Row), $common->getTblCommonInformation()->getDenomination());
//            if ($address !== null) {
//                $export->setValue($export->getCell("5", $Row), $address->getTblAddress()->getStreetName());
//                $export->setValue($export->getCell("6", $Row), $address->getTblAddress()->getStreetNumber());
//                $export->setValue($export->getCell("7", $Row),
//                    $address->getTblAddress()->getTblCity()->getCode() . ' ' . $address->getTblAddress()->getTblCity()->getName());
//
//            }
//            $export->setValue($export->getCell("8",$Row), $studentList[$i]->getFirstName());
//            $export->setValue($export->getCell("9",$Row), $common->getTblCommonBirthDates()->getBirthday());
//            $export->setValue($export->getCell("10",$Row), $common->getTblCommonBirthDates()->getBirthplace());
//        }
//        $export->saveFile();

    }
}
