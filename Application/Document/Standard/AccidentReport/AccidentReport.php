<?php
namespace SPHERE\Application\Document\Standard\AccidentReport;

use DateTime;
use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Contact\Phone\Service\Entity\TblToPerson as TblToPersonPhone;
use SPHERE\Application\Document\Standard\EnrollmentDocument\Frontend;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommon;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer as GatekeeperConsumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Setting\Consumer\Responsibility\Responsibility;
use SPHERE\Application\Setting\Consumer\Responsibility\Service\Entity\TblResponsibility;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Header;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullLeft;
use SPHERE\Common\Frontend\Layout\Repository\Thumbnail;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Sup;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;


/**
 * Class AccidentReport
 *
 * @package SPHERE\Application\Document\Standard\AccidentReport
 */

class AccidentReport extends Extension
{
    public static function registerModule()
    {

        Main::getDisplay()->addModuleNavigation(new Link(new Link\Route(__NAMESPACE__), new Link\Name('Unfallanzeige')));

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(__NAMESPACE__, __CLASS__.'::frontendSelectPerson'));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(__NAMESPACE__.'/Fill', __CLASS__.'::frontendFillAccidentReport'));
    }

    /**
     * @return Stage
     */
    public static function frontendSelectPerson(): Stage
    {
        $Stage = new Stage('Unfallanzeige', 'Schüler auswählen');

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            Frontend::getStudentSelectDataTable('/Document/Standard/AccidentReport/Fill')
                        )),
                    ))
                )),
            ))
        );

        return $Stage;
    }



    /**
     * @param null $PersonId
     *
     * @return Stage
     */
    public function frontendFillAccidentReport($PersonId = null): Stage
    {
        $Stage = new Stage('Unfallanzeige', 'Erstellen');
        $Stage->addButton(new Standard('Zurück', '/Document/Standard/AccidentReport', new ChevronLeft()));
        $tblPerson = Person::useService()->getPersonById($PersonId);
        $Global = $this->getGlobal();

        // Sachsen Standard
        $Global->POST['Data']['AddressTarget'] = 'Unfallkasse Sachsen';
        $Global->POST['Data']['TargetAddressStreet'] = 'Postfach 42';
        $Global->POST['Data']['TargetAddressCity'] = '01651 Meißen';
        if (GatekeeperConsumer::useService()->getConsumerBySessionIsConsumerType(TblConsumer::TYPE_BERLIN)) {
            $Global->POST['Data']['AddressTarget'] = 'Unfallkasse Berlin';
            $Global->POST['Data']['TargetAddressStreet'] = 'Culemeyerstraße 2';
            $Global->POST['Data']['TargetAddressCity'] = '12277 Berlin';
        }
        if (GatekeeperConsumer::useService()->getConsumerBySessionIsConsumerType(TblConsumer::TYPE_THUERINGEN)) {
            $Global->POST['Data']['AddressTarget'] = 'Unfallkasse Thüringen';
            $Global->POST['Data']['TargetAddressStreet'] = 'Humboldtstrasse 111';
            $Global->POST['Data']['TargetAddressCity'] = '99867 Gotha ';
        }
        if ($tblPerson) {
            $Global->POST['Data']['LastFirstName'] = $tblPerson->getLastFirstName();
            $Global->POST['Data']['Date'] = (new DateTime())->format('d.m.Y');

            if (($tblCommon = Common::useService()->getCommonByPerson($tblPerson))) {
                if (($tblCommonInformation = $tblCommon->getTblCommonInformation())) {
                    $Global->POST['Data']['Nationality'] = $tblCommonInformation->getNationality();
                }
                if (($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())) {
                    $Global->POST['Data']['Birthday'] = $tblCommonBirthDates->getBirthday();
                    if (($tblCommonGender = $tblCommonBirthDates->getTblCommonGender())) {
                        $Global->POST['Data']['Gender'] = $tblCommonGender->getName();
                    }
                }
                if(($tblStudent = $tblPerson->getStudent())){
                    if(($tblStudentMedicalRecord = $tblStudent->getTblStudentMedicalRecord())){
                        $Global->POST['Data']['Insurance'] = $tblStudentMedicalRecord->getInsurance();
                    }
                }
            }

            $tblResponsibilityList = Responsibility::useService()->getResponsibilityAll();
            if ($tblResponsibilityList) {
                /** @var TblResponsibility $tblResponsibility */
                $tblResponsibility = current($tblResponsibilityList);
                $Global->POST['Data']['CompanyNumber'] = $tblResponsibility->getCompanyNumber();
                $tblResponsibilityCompany = $tblResponsibility->getServiceTblCompany();
                if ($tblResponsibilityCompany) {
                    $Global->POST['Data']['SchoolResponsibility'] = $tblResponsibilityCompany->getDisplayName();
                }
            }

            if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndDate($tblPerson))) {
                $tblCompanySchool = $tblStudentEducation->getServiceTblCompany();
                $tblType = $tblStudentEducation->getServiceTblSchoolType();

                // Unternehmensnummer wird sofern möglich und vorhanden aus den Mandantenschulen gezogen
                // und überschreibt damit die Unternehmensnummer der Schulträger
                if($tblType){
                    // Schule aus Mandanteneinstellung mit Schulart
                    if(($tblSchoolList = School::useService()->getSchoolByType($tblType))){
                        // bei einer Schule kann diese genommen werden. (Normalfall)
                        if(count($tblSchoolList) == 1){
                            $tblSchool = current($tblSchoolList);
                            // Übernahme nur, wenn eine Unternehmensnummer hinterlegt ist
                            if($tblSchool->getCompanyNumber() != ''){
                                $Global->POST['Data']['CompanyNumber'] = $tblSchool->getCompanyNumber();
                            }
                        } else {
                            // mehr als eine Schule mit gleicher Schulart
                            if(($tblSchool = School::useService()->getSchoolByCompanyAndType($tblCompanySchool, $tblType))){
                                // Übernahme nur, wenn eine Unternehmensnummer hinterlegt ist
                                if($tblSchool->getCompanyNumber() != '') {
                                    $Global->POST['Data']['CompanyNumber'] = $tblSchool->getCompanyNumber();
                                }
                            }
                        }
                    }
                }

                if ($tblCompanySchool) {
                    $Global->POST['Data']['School'] = $tblCompanySchool->getName();
                    $Global->POST['Data']['SchoolExtended'] = $tblCompanySchool->getExtendedName();
                    $tblAddressSchool = Address::useService()->getAddressByCompany($tblCompanySchool);
                    if ($tblAddressSchool) {
                        $Global->POST['Data']['SchoolAddressStreet'] = $tblAddressSchool->getStreetName().', '.$tblAddressSchool->getStreetNumber();
                        $tblCitySchool = $tblAddressSchool->getTblCity();
                        if ($tblCitySchool) {
                            $Global->POST['Data']['SchoolAddressCity'] = $tblCitySchool->getCode().' '.$tblCitySchool->getName();
                        }
                    }

                    $tblToPersonList = Phone::useService()->getPhoneAllByCompany($tblCompanySchool);
                    $tblToPersonPhoneList = array();
                    $tblToPersonFaxList = array();
                    if ($tblToPersonList) {
                        foreach ($tblToPersonList as $tblToPerson) {
                            if ($tblType = $tblToPerson->getTblType()) {
                                $TypeName = $tblType->getName();
                                $TypeDescription = $tblType->getDescription();
                                if (($TypeName == 'Privat' || $TypeName == 'Geschäftlich') && $TypeDescription == 'Festnetz') {
                                    $tblToPersonPhoneList[] = $tblToPerson;
                                }
                                if ($TypeName == 'Fax') {
                                    $tblToPersonFaxList[] = $tblToPerson;
                                }
                            }
                        }
                        if (!empty($tblToPersonPhoneList)) {
                            /** @var TblToPersonPhone $tblPersonToPhone */
                            $tblPersonToPhone = current($tblToPersonPhoneList);
                            $tblPhone = $tblPersonToPhone->getTblPhone();
                            if ($tblPhone) {
                                $Global->POST['Data']['Phone'] = $tblPhone->getNumber();
                            }
                        }
                        if (!empty($tblToPersonFaxList)) {
                            /** @var TblToPersonPhone $tblPersonToFax */
                            $tblPersonToFax = current($tblToPersonFaxList);
                            $tblPhoneFax = $tblPersonToFax->getTblPhone();
                            if ($tblPhoneFax) {
                                $Global->POST['Data']['Fax'] = $tblPhoneFax->getNumber();
                            }
                        }
                    }
                }
            }

            // Hauptadresse Schüler
            $ChildAddressId = false;
            $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
            if ($tblAddress) {
                $Global->POST['Data']['AddressStreet'] = $tblAddress->getStreetName().', '.$tblAddress->getStreetNumber();
                $tblCity = $tblAddress->getTblCity();
                if ($tblCity) {
                    $Global->POST['Data']['AddressPLZ'] = $tblCity->getCode();
                    $Global->POST['Data']['AddressCity'] = $tblCity->getDisplayName();
                }
                $ChildAddressId = $tblAddress->getId();
            }

            // Sorgeberechtigte
            $tblRelationshipType = Relationship::useService()->getTypeByName('Sorgeberechtigt');
            $tblToPersonCustodyList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, $tblRelationshipType);
            if ($tblToPersonCustodyList) {
                // female preferred (first run)
                $PersonCustodySameAddress = array();
                $PersonCustodyDifferentAddress = array();
                $AddressString = '';
                foreach ($tblToPersonCustodyList as $tblToPersonCustody) {
                    $Ranking = $tblToPersonCustody->getRanking();
                    $tblPersonCustody = $tblToPersonCustody->getServiceTblPersonFrom();

                    $tblAddressCustody = Address::useService()->getAddressByPerson($tblPersonCustody);
                    if ($ChildAddressId
                        && $tblAddressCustody
                        && $ChildAddressId == $tblAddressCustody->getId()
                    ) {
                        $AddressString = $tblAddressCustody->getGuiString(false);
                        $PersonCustodySameAddress[$Ranking] = $tblPersonCustody;
                        continue;
                    } else {
                        $PersonCustodyDifferentAddress[$Ranking] = $tblPersonCustody;
                    }
                }
                if (!empty($PersonCustodySameAddress)) {
                    // Adresse mit allen Sorgeberechtigten mit der gleichen Adresse
                    /** @var TblPerson $PersonCustody */
                    $ParentList = array();
                    foreach ($PersonCustodySameAddress as $PersonCustody) {
                        $ParentList[] = $PersonCustody->getFullName();
                    }
                    if (!empty($ParentList)) {
                        if (!isset($Global->POST['Data']['CustodyAddress'])) {
                            $Global->POST['Data']['CustodyAddress'] = implode(', ', $ParentList).', '.$AddressString;
                        }
                    }
                } elseif (!empty($PersonCustodyDifferentAddress)) {
                    // Adresse des ersten Sorgeberechtigten, wenn der Schüler woanders wohnt
                    // lowest ranking
                    /** @var TblPerson $PersonCustody */
                    $PersonCustody = array_shift($PersonCustodyDifferentAddress);
                    /** @var TblCommon $tblCommonCustody */
                    if (!isset($Global->POST['Data']['CustodyAddress'])) {
                        $tblAddressCustody = Address::useService()->getAddressByPerson($PersonCustody);
                        if ($tblAddressCustody) {
                            $Global->POST['Data']['CustodyAddress'] = $PersonCustody->getFullName().', '.$tblAddressCustody->getGuiString();
                        }
                    }
                }
            }
        }
        $Global->savePost();


        $form = null;
        if (GatekeeperConsumer::useService()->getConsumerBySessionIsConsumerType(TblConsumer::TYPE_BERLIN)) {
            $form = $this->formAccidentReportBE();
        }
        if (GatekeeperConsumer::useService()->getConsumerBySessionIsConsumerType(TblConsumer::TYPE_THUERINGEN)) {
            $form = $this->formAccidentReportTH();
        }
        if (GatekeeperConsumer::useService()->getConsumerBySessionIsConsumerType(TblConsumer::TYPE_SACHSEN)) {
            $form = $this->formAccidentReportSN();
        }


        $HeadPanel = new Panel('Schüler', $tblPerson->getLastFirstName());

        $Stage->addButton(new External('Blanko Unfallanzeige herunterladen',
            'SPHERE\Application\Api\Document\Standard\AccidentReport\Create',
            new Download(), array('Data' => array('empty')),
            'Unfallanzeige herunterladen'));

        $Stage->setContent(
            new Layout(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            $HeadPanel
                            , 7)
                    ),
                    new LayoutRow(array(
                        new LayoutColumn(
                            $form
                            , 7),
                        new LayoutColumn(
                            new Title('Vorlage des Standard-Dokuments "Unfallanzeige"')
                            .new Thumbnail(FileSystem::getFileLoader('/Common/Style/Resource/Document/AccidentReportStandard.png'), '')
                            , 5),
                    ))
                ))
            )
        );

        return $Stage;
    }

    /**
     * @return LayoutColumn
     */
    private function getFormCompany(): LayoutColumn
    {
        return new LayoutColumn(new Well(
            new Layout(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new TextField('Data[School]', 'Schule',
                                new Sup(1).' Schule')
                            , 6),
                        new LayoutColumn(
                            new TextField('Data[SchoolExtended]', 'Zusatz',
                                new Sup(1).' Zusatz')
                            , 6)
                    )),
                    new LayoutRow(array(
                        new LayoutColumn(
                            new TextField('Data[SchoolAddressStreet]', 'Straße Nr.',
                                new Sup(1).' Straße Hausnummer')
                            , 6),
                        new LayoutColumn(
                            new TextField('Data[SchoolAddressCity]', 'PLZ Ort',
                                new Sup(1).' PLZ Ort')
                            , 6)
                    ))
                ))
            )
        ));
    }

    /**
     * @return LayoutColumn
     */
    private function getFormResponibility(): LayoutColumn
    {
        return new LayoutColumn(new Well(new Layout(new LayoutGroup(
            new LayoutRow(array(
                new LayoutColumn(
                    new TextField('Data[SchoolResponsibility]',
                        'Träger der Einrichtung',
                        new Sup(2).' Träger der Einrichtung')
                    , 6),
                new LayoutColumn(
                    new TextField('Data[CompanyNumber]', 'Unternehmensnr.',
                        new Sup(3).' Unternehmensnr.')
                    , 6)
            ))
        ))));
    }

    /**
     * @return LayoutColumn
     */
    private function getFormReceiver(): LayoutColumn
    {
        return new LayoutColumn(new Well(new Layout(new LayoutGroup(
            new LayoutRow(array(
                new LayoutColumn(
                    new TextField('Data[AddressTarget]', 'Empfänger',
                        new Sup(4).' Empfänger')
                    , 4),
                new LayoutColumn(
                    new TextField('Data[TargetAddressStreet]', 'Straße Hausnummer',
                        new Sup(4).' Straße Hausnummer')
                    , 4),
                new LayoutColumn(
                    new TextField('Data[TargetAddressCity]', 'PLZ Ort',
                        new Sup(4).' PLZ Ort')
                    , 4)
            ))
        ))));
    }

    /**
     * @return LayoutColumn
     */
    private function getFormStudent(): LayoutColumn
    {
        return new LayoutColumn(new Well(
            new Layout(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new TextField('Data[LastFirstName]', 'Name, Vorname',
                                new Sup(5).' Name, Vorname des Schülers/der Schülerin')
                            , 8),
                        new LayoutColumn(
                            new TextField('Data[Birthday]', 'Geburtstag',
                                new Sup(6).' Geburtstag')
                            , 4),
                    )),
                    new LayoutRow(array(
                        new LayoutColumn(
                            new TextField('Data[AddressStreet]', 'Straße, Hausnummer',
                                new Sup(7).' Straße, Hausnummer')
                            , 6),
                        new LayoutColumn(
                            new TextField('Data[AddressPLZ]', 'Postleitzahl',
                                'Postleitzahl')
                            , 3),
                        new LayoutColumn(
                            new TextField('Data[AddressCity]', 'Ort',
                                'Ort')
                            , 3),
                    )),
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Bold(new Sup(7).' Geschlecht')
                            .(new Listing(array(
                            new Layout(new LayoutGroup(
                                new LayoutRow(array(
                                    new LayoutColumn(
                                        new RadioBox('Data[Gender]', 'Männlich', 'Männlich')
                                        , 3),
                                    new LayoutColumn(
                                        new RadioBox('Data[Gender]', 'Weiblich', 'Weiblich')
                                        , 3),
                                    new LayoutColumn(
                                        new RadioBox('Data[Gender]', 'Divers', 'Divers')
                                        , 2),
                                    new LayoutColumn(
                                        new RadioBox('Data[Gender]', 'Keine&nbsp;Angabe', 'Without')
                                        , 4),
                                ))
                            ))
                        ))), 8),
                        new LayoutColumn(
                            new TextField('Data[Nationality]', 'Staatsangehörigkeit',
                                new Sup(9).' Staatsangehörigkeit')
                            , 4)
                    )),
                    new LayoutRow(
                        new LayoutColumn(
                            new TextField('Data[CustodyAddress]', 'Vertreter',
                                new Sup(10).' Name, Anschrift und Telefonnummer der gesetzlich Vertretungsberechtigten')
                        ),
                    ),
                    new LayoutRow(array(
                        new LayoutColumn(
                            new TextField('Data[Insurance]', 'Krankenkasse',
                                new Sup(11).' Krankenkasse (Name, PLZ, Ort, bei Familienversicherung Name des Mitglieds)')
                        ),
                    )),
                ))
            )
        ));
    }

    private function getFormAccident(): LayoutColumn
    {
        $now = new DateTime();
        return new LayoutColumn(new Well(
            new Layout(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new PullClear(new Bold(new Sup(12).' Tödlicher Unfall?')).
                            new PullLeft(new CheckBox('Data[DeathAccidentNo]',
                                'Nein &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', true, array('Data[DeathAccidentYes]'))).
                            new PullLeft(new CheckBox('Data[DeathAccidentYes]',
                                'Ja', true, array('Data[DeathAccidentNo]')))
                            , 3),
                        new LayoutColumn(
                            new TextField('Data[AccidentDate]',
                                (new DateTime())->format('d.m.Y'),
                                new Sup(13).' Datum des Unfalls (TT.MM.JJJJ)')
                            , 4),
                        new LayoutColumn(
                            new TextField('Data[AccidentTime]', $now->format('H:i'),
                                new Sup(13).'Uhrzeit (hh:mm)')
                            , 3),
                    )),
                    new LayoutRow(array(
                        new LayoutColumn(
                            new TextField('Data[AccidentPlace]', 'Ort',
                                new Sup(14).' Unfallort')
                            , 6),
                        new LayoutColumn(
                            new PullClear(new Header(new Bold(new Sup(15).' Unfall beim Distanzunterricht')))
                            .new Container(
                                new PullLeft(new CheckBox('Data[DistanceNo]',
                                    'Nein &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', true, array('Data[DistanceYes]')))
                                .new PullLeft(new CheckBox('Data[DistanceYes]', 'Ja', true, array('Data[DistanceNo]')))
                            )
                            , 6)
                    )),
                    new LayoutRow(
                        new LayoutColumn(
                            new TextArea('Data[AccidentDescription]', 'Beschreibung',
                                new Sup(16).' Ausführliche Beschreibung des Unfallhergangs')
                        )
                    ),
                    new LayoutRow(
                        new LayoutColumn(
                            new PullLeft(new Header(new Bold('Die Angaben beruhen auf der Schilderung &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;')))
                            .new Container(
                                new PullLeft(new CheckBox('Data[DescriptionActive]',
                                    'des Versicherten &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', true, array('Data[DescriptionPassive]')))
                                .new PullLeft(new CheckBox('Data[DescriptionPassive]', 'andere Personen', true, array('Data[DescriptionActive]')))
                            )
                        )
                    ),
                    new LayoutRow(
                        new LayoutColumn(
                            new Title('')
                        )
                    ),
                    new LayoutRow(array(
                        new LayoutColumn(
                            new TextField('Data[AccidentBodyParts]',
                                'Kopf, Bein, Arm, etc.',
                                new Sup(17).' Verletzte Körperteile')
                            , 6),
                        new LayoutColumn(
                            new TextField('Data[AccidentType]', 'Art',
                                new Sup(18).' Art der Verletzung')
                            , 6),
                    )),
                    new LayoutRow(
                        new LayoutColumn(
                            new Title('')
                        )
                    ),
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Bold(new Sup(19).' Hat der Versicherte den Besuch der Einrichtung unterbrochen?')
                            .new PullClear(
                                new PullLeft(new CheckBox('Data[BreakNo]',
                                    'nein &nbsp;&nbsp;&nbsp;&nbsp;', true, array('Data[BreakYes]', 'Data[BreakAt]', 'Data[BreakDate]', 'Data[BreakTime]')))
                                .new PullLeft(new CheckBox('Data[BreakYes]',
                                    'sofort &nbsp;&nbsp;&nbsp;&nbsp;', true, array('Data[BreakNo]', 'Data[BreakAt]', 'Data[BreakDate]', 'Data[BreakTime]')))
                                .new PullLeft(new CheckBox('Data[BreakAt]',
                                    'später, am &nbsp;&nbsp;&nbsp;&nbsp;', true, array('Data[BreakNo]', 'Data[BreakYes]')))
                            )
                            , 6),
                        new LayoutColumn(
                            new TextField('Data[BreakDate]',
                                $now->format('d.m'),
                                'Datum der Unterbrechung (TT.MM)')
                            , 3),
                        new LayoutColumn(
                            new TextField('Data[BreakTime]', $now->format('H:i'),
                                'Zeitpunkt der Unterbrechung')
                            , 3)
                    )),
                    new LayoutRow(
                        new LayoutColumn(
                            new Title('')
                        )
                    ),
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Bold(new Sup(20).' Hat der Versicherte den Besuch der Einrichtung wieder aufgenommen?')
                            .new PullClear(
                                new PullLeft(new CheckBox('Data[ReturnNo]',
                                    'nein &nbsp;&nbsp;&nbsp;&nbsp;', true, array('Data[ReturnYes]', 'Data[ReturnDate]')))
                                .new PullLeft(new CheckBox('Data[ReturnYes]',
                                    'ja, am &nbsp;&nbsp;&nbsp;&nbsp;', true, array('Data[ReturnNo]')))
                            )
                            , 9),
                        new LayoutColumn(
                            new TextField('Data[ReturnDate]',
                                (new DateTime())->format('d.m.Y'),
                                'Datum der Wiederaufnahme')
                            , 3),
                    )),
                    new LayoutRow(
                        new LayoutColumn(
                            new Title('')
                        )
                    ),
                    new LayoutRow(array(
                        new LayoutColumn(
                            new TextField('Data[WitnessInfo]', 'Name, Vorname Adresse',
                                new Sup(21).' Wer hat von dem Unfall zuerst Kenntnis genommen? (Name, Anschrift von Zeugen)')
                            , 9),
                        new LayoutColumn(
                            new Bold('War diese Person Augenzeuge?')
                            .new PullClear(
                                new PullLeft(new CheckBox('Data[EyeWitnessNo]',
                                    'nein &nbsp;&nbsp;&nbsp;&nbsp;', true, array('Data[EyeWitnessYes]')))
                                .new PullLeft(new CheckBox('Data[EyeWitnessYes]',
                                    'ja &nbsp;&nbsp;&nbsp;&nbsp;', true, array('Data[EyeWitnessNo]')))
                            )
                            , 3),
                    )),
                    new LayoutRow(
                        new LayoutColumn(
                            new Title('')
                        )
                    ),
                    new LayoutRow(array(
                        new LayoutColumn(
                            new TextField('Data[DoctorAddress]', 'Adresse',
                                new Sup(22).' Adresse des erstbehandelnden Arztes / Krankenhaus')
                            , 6),
                        new LayoutColumn(
                            new Header(new Bold(new Sup(23).' Beginn des Besuchs der Einrichtung'))
                            .new TextField('Data[LocalStartTime]', $now->format('H:i'), 'Uhrzeit (hh:mm)')
                            , 3),
                        new LayoutColumn(
                            new Header(new Bold(new Sup(23).' Ende des Besuchs der Einrichtung'))
                            .new TextField('Data[LocalEndTime]', $now->format('H:i'), 'Uhrzeit (hh:mm)')
                            , 3),
                    )),
                    new LayoutRow(
                        new LayoutColumn(
                            new Title('')
                        )
                    ),
                    new LayoutRow(array(
                        new LayoutColumn(
                            new TextField('Data[Date]', (new DateTime())->format('d.m.Y'),
                                new Sup(23).' Datum')
                            , 3),
                        new LayoutColumn(
                            new TextField('Data[LocalLeader]', 'Leiter',
                                'Leiter (Beauftragter) der Einrichtung')
                            , 4),
                        new LayoutColumn(
                            new TextField('Data[Recall]', 'Telefonnummer',
                                'Telefon-Nr. für Rückfragen (Ansprechpartner)')
                            , 5),
                    )),
                ))
            )
        ));
    }

    private function getFormAccidentTH(): LayoutColumn
    {
        $now = new DateTime();
        return new LayoutColumn(new Well(
            new Layout(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new PullClear(new Bold(new Sup(12).' Tödlicher Unfall?')).
                            new PullLeft(new CheckBox('Data[DeathAccidentNo]',
                                'Nein &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', true, array('Data[DeathAccidentYes]'))).
                            new PullLeft(new CheckBox('Data[DeathAccidentYes]',
                                'Ja', true, array('Data[DeathAccidentNo]')))
                            , 2),
                        new LayoutColumn(
                            new TextField('Data[AccidentDate]',
                                (new DateTime())->format('d.m.Y'),
                                new Sup(13).' Datum des Unfalls (TT.MM.JJJJ)')
                            , 4),
                        new LayoutColumn(
                            new TextField('Data[AccidentTime]', $now->format('H:i'),
                                new Sup(13).'Uhrzeit (hh:mm)')
                            , 2),
                        new LayoutColumn(
                            new TextField('Data[PhoneNumber]', '', 'Telefonnummer der vers. Person / gesetzlichen Vertreters')
                            , 4),
                    )),
                    new LayoutRow(array(
                        new LayoutColumn(
                            new TextField('Data[AccidentPlace]', 'Ort',
                                new Sup(14).' Unfallort')
                            , 6),
                        new LayoutColumn(
                            new PullClear(new Header(new Bold(new Sup(15).' Unfall beim Distanzunterricht')))
                            .new Container(
                                new PullLeft(new CheckBox('Data[DistanceNo]',
                                    'Nein &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', true, array('Data[DistanceYes]')))
                                .new PullLeft(new CheckBox('Data[DistanceYes]', 'Ja', true, array('Data[DistanceNo]')))
                            )
                            , 6)
                    )),
                    new LayoutRow(
                        new LayoutColumn(
                            new TextArea('Data[AccidentDescription]', 'Beschreibung',
                                new Sup(16).' Ausführliche Beschreibung des Unfallhergangs')
                        )
                    ),
                    new LayoutRow(
                        new LayoutColumn(
                            new PullLeft(new Header(new Bold('Die Angaben beruhen auf der Schilderung &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;')))
                            .new Container(
                                new PullLeft(new CheckBox('Data[DescriptionActive]',
                                    'des Versicherten &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', true, array('Data[DescriptionPassive]')))
                                .new PullLeft(new CheckBox('Data[DescriptionPassive]', 'andere Personen', true, array('Data[DescriptionActive]')))
                            )
                        )
                    ),
                    new LayoutRow(
                        new LayoutColumn(
                            new PullLeft(new Header(new Bold('Hat ein Gewaltereignis vorgelegen (körperlicher Übergriff, sexueller Übergriff)?
                             &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;')))
                            .new Container(
                                new PullLeft(new CheckBox('Data[DescriptionViolenceNo]',
                                    'Nein &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', true, array('Data[DescriptionViolenceYes]')))
                                .new PullLeft(new CheckBox('Data[DescriptionViolenceYes]', 'Ja', true, array('Data[DescriptionViolenceNo]')))
                            )
                        )
                    ),
                    new LayoutRow(
                        new LayoutColumn(
                            new Title('')
                        )
                    ),
                    new LayoutRow(array(
                        new LayoutColumn(
                            new TextField('Data[AccidentBodyParts]',
                                'Kopf, Bein, Arm, etc.',
                                new Sup(17).' Verletzte Körperteile')
                            , 6),
                        new LayoutColumn(
                            new TextField('Data[AccidentType]', 'Art',
                                new Sup(18).' Art der Verletzung')
                            , 6),
                    )),
                    new LayoutRow(
                        new LayoutColumn(
                            new Title('')
                        )
                    ),
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Bold(new Sup(19).' Hat der Versicherte den Besuch der Einrichtung unterbrochen?')
                            .new PullClear(
                                new PullLeft(new CheckBox('Data[BreakNo]',
                                    'nein &nbsp;&nbsp;&nbsp;&nbsp;', true, array('Data[BreakYes]', 'Data[BreakAt]', 'Data[BreakDate]', 'Data[BreakTime]')))
                                .new PullLeft(new CheckBox('Data[BreakYes]',
                                    'sofort &nbsp;&nbsp;&nbsp;&nbsp;', true, array('Data[BreakNo]', 'Data[BreakAt]', 'Data[BreakDate]', 'Data[BreakTime]')))
                                .new PullLeft(new CheckBox('Data[BreakAt]',
                                    'später, am &nbsp;&nbsp;&nbsp;&nbsp;', true, array('Data[BreakNo]', 'Data[BreakYes]')))
                            )
                            , 6),
                        new LayoutColumn(
                            new TextField('Data[BreakDate]',
                                $now->format('d.m'),
                                'Datum der Unterbrechung')
                            , 3),
                        new LayoutColumn(
                            new TextField('Data[BreakTime]', $now->format('H:i'),
                                'Zeitpunkt der Unterbrechung')
                            , 3)
                    )),
                    new LayoutRow(
                        new LayoutColumn(
                            new Title('')
                        )
                    ),
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Bold(new Sup(20).' Hat der Versicherte den Besuch der Einrichtung wieder aufgenommen?')
                            .new PullClear(
                                new PullLeft(new CheckBox('Data[ReturnNo]',
                                    'nein &nbsp;&nbsp;&nbsp;&nbsp;', true, array('Data[ReturnYes]', 'Data[ReturnDate]')))
                                .new PullLeft(new CheckBox('Data[ReturnYes]',
                                    'ja, am &nbsp;&nbsp;&nbsp;&nbsp;', true, array('Data[ReturnNo]')))
                            )
                            , 9),
                        new LayoutColumn(
                            new TextField('Data[ReturnDate]',
                                (new DateTime())->format('d.m.Y'),
                                'Datum der Wiederaufnahme')
                            , 3),
                    )),
                    new LayoutRow(
                        new LayoutColumn(
                            new Title('')
                        )
                    ),
                    new LayoutRow(array(
                        new LayoutColumn(
                            new TextField('Data[WitnessInfo]', 'Name, Vorname Adresse',
                                new Sup(21).' Wer hat von dem Unfall zuerst Kenntnis genommen? (Name, Anschrift von Zeugen)')
                            , 9),
                        new LayoutColumn(
                            new Bold('War diese Person Augenzeuge?')
                            .new PullClear(
                                new PullLeft(new CheckBox('Data[EyeWitnessNo]',
                                    'nein &nbsp;&nbsp;&nbsp;&nbsp;', true, array('Data[EyeWitnessYes]')))
                                .new PullLeft(new CheckBox('Data[EyeWitnessYes]',
                                'ja &nbsp;&nbsp;&nbsp;&nbsp;', true, array('Data[EyeWitnessNo]')))
                            )
                            , 3),
                    )),
                    new LayoutRow(
                        new LayoutColumn(
                            new Title('')
                        )
                    ),
                    new LayoutRow(array(
                        new LayoutColumn(
                            new TextField('Data[DoctorAddress]', 'Adresse',
                                new Sup(22).' Adresse des erstbehandelnden Arztes / Krankenhaus')
                            , 6),
                        new LayoutColumn(
                            new Header(new Bold(new Sup(23).' Beginn des Besuchs der Einrichtung'))
                            .new TextField('Data[LocalStartTime]', $now->format('H:i'), 'Uhrzeit (hh:mm)')
                            , 3),
                        new LayoutColumn(
                            new Header(new Bold(new Sup(23).' Ende des Besuchs der Einrichtung'))
                            .new TextField('Data[LocalEndTime]', $now->format('H:i'), 'Uhrzeit (hh:mm)')
                            , 3),
                    )),
                    new LayoutRow(
                        new LayoutColumn(
                            new Title('')
                        )
                    ),
                    new LayoutRow(array(
                        new LayoutColumn(
                            new TextField('Data[Date]', (new DateTime())->format('d.m.Y'),
                                new Sup(23).' Datum')
                            , 3),
                        new LayoutColumn(
                            new TextField('Data[LocalLeader]', 'Leiter',
                                'Leiter (Beauftragter) der Einrichtung')
                            , 4),
                        new LayoutColumn(
                            new TextField('Data[Recall]', 'Telefonnummer',
                                'Telefon-Nr. für Rückfragen (Ansprechpartner)')
                            , 5),
                    )),
                ))
            )
        ));
    }

    /**
     * @return Form
     */
    private function formAccidentReportSN(): Form
    {
        return new Form(
            new FormGroup(array(
                new FormRow(
                    new FormColumn(
                        new Layout(
                            new LayoutGroup(
                                new LayoutRow(array(
                                    new LayoutColumn(
                                        new Title('Einrichtung')
                                    ),
                                    $this->getFormCompany(),
                                    new LayoutColumn(
                                        new Title('Informationen Träger')
                                    ),
                                    $this->getFormResponibility(),
                                    new LayoutColumn(
                                        new Title('Empfänger')
                                    ),
                                    $this->getFormReceiver(),
                                    new LayoutColumn(
                                        new Title('Informationen Versicherter')
                                    ),
                                    $this->getFormStudent(),
                                    new LayoutColumn(
                                        new Title('Informationen Unfall')
                                    ),
                                    $this->getFormAccident()
                                ))
                            )
                        )
                    )
                ),
            ))
            , new Primary('Download', new Download(), true), '\Api\Document\Standard\AccidentReport\Create'
        );
    }

    /**
     * @return Form
     */
    private function formAccidentReportBE(): Form
    {
        return new Form(
            new FormGroup(array(
                new FormRow(
                    new FormColumn(
                        new Layout(
                            new LayoutGroup(
                                new LayoutRow(array(
                                    new LayoutColumn(
                                        new Title('Einrichtung')
                                    ),
                                    $this->getFormCompany(),
                                    new LayoutColumn(
                                        new Title('Informationen Träger')
                                    ),
                                    $this->getFormResponibility(),
                                    new LayoutColumn(
                                        new Title('Empfänger')
                                    ),
                                    $this->getFormReceiver(),
                                    new LayoutColumn(
                                        new Title('Informationen Versicherter')
                                    ),
                                    $this->getFormStudent(),
                                    new LayoutColumn(
                                        new Title('Informationen Unfall')
                                    ),
                                    $this->getFormAccident()
                                ))
                            )
                        )
                    )
                ),
            ))
            , new Primary('Download', new Download(), true), '\Api\Document\Standard\AccidentReport\Create'
        );
    }

    /**
     * @return Form
     */
    private function formAccidentReportTH(): Form
    {
        return new Form(
            new FormGroup(array(
                new FormRow(
                    new FormColumn(
                        new Layout(
                            new LayoutGroup(
                                new LayoutRow(array(
                                    new LayoutColumn(
                                        new Title('Einrichtung')
                                    ),
                                    $this->getFormCompany(),
                                    new LayoutColumn(
                                        new Title('Informationen Träger')
                                    ),
                                    $this->getFormResponibility(),
                                    new LayoutColumn(
                                        new Title('Empfänger')
                                    ),
                                    $this->getFormReceiver(),
                                    new LayoutColumn(
                                        new Title('Informationen Versicherter')
                                    ),
                                    $this->getFormStudent(),
                                    new LayoutColumn(
                                        new Title('Informationen Unfall')
                                    ),
                                    $this->getFormAccidentTH()
                                ))
                            )
                        )
                    )
                ),
            ))
            , new Primary('Download', new Download(), true), '\Api\Document\Standard\AccidentReport\Create'
        );
    }
}