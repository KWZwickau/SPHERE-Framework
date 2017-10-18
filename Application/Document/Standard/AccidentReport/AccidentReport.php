<?php

namespace SPHERE\Application\Document\Standard\AccidentReport;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Api\Document\Standard\Repository\AccidentReport\ApiAccidentReport;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Contact\Phone\Service\Entity\TblToPerson as TblToPersonPhone;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Setting\Consumer\Responsibility\Responsibility;
use SPHERE\Application\Setting\Consumer\Responsibility\Service\Entity\TblResponsibility;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Header;
use SPHERE\Common\Frontend\Layout\Repository\Label;
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
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
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
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Unfallbericht'))
        );

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendSelectPerson'
        ));

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Fill', __CLASS__.'::frontendFillAccidentReport'
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
        // TODO: Implement useFrontend() method.
    }

    /**
     * @return Stage
     */
    public static function frontendSelectPerson()
    {

        $Stage = new Stage('Schulbescheinigung', 'Schüler auswählen');

        $dataList = array();
        if (($tblGroup = Group::useService()->getGroupByMetaTable('STUDENT'))) {
            if (($tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup))) {
                array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$dataList) {
                    $Data['PersonId'] = $tblPerson->getId();

                    $tblAddress = $tblPerson->fetchMainAddress();
                    $dataList[] = array(
                        'Name'     => $tblPerson->getLastFirstName(),
                        'Address'  => $tblAddress ? $tblAddress->getGuiString() : '',
                        'Division' => Student::useService()->getDisplayCurrentDivisionListByPerson($tblPerson),
                        'Option'   => new Standard('Erstellen', __NAMESPACE__.'/Fill', null,
                            array('Id' => $tblPerson->getId()))
//                            .new External('Herunterladen',
//                                'SPHERE\Application\Api\Document\Standard\StudentTransfer\Create',
//                                new Download(), array('Data' => $Data),
//                                'Schulbescheinigung herunterladen')
                    );
                });
            }
        }

        $YearString = '(SJ ';
        $tblYearList = Term::useService()->getYearByNow();
        if ($tblYearList) {
            $YearString .= current($tblYearList)->getYear();
        } else {
            $YearString .= new ToolTip(new Danger((new \DateTime())->format('Y')),
                'Kein Schuljahr mit aktuellem Zeitraum');
        }
        $YearString .= ')';

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData(
                                $dataList,
                                null,
                                array(
                                    'Name'     => 'Name',
                                    'Address'  => 'Adresse',
                                    'Division' => 'Klasse '.$YearString,
                                    'Option'   => ''
                                ),
                                array(
                                    'columnDefs' => array(
                                        array('type' => 'german-string', 'targets' => 0),
                                        array('width' => '1%', 'targets' => -1),
                                    ),
                                )
                            )
                        )),
                    ))
                )),
            ))
        );

        return $Stage;
    }

    /**
     * @param null $Id
     *
     * @return Stage
     */
    public function frontendFillAccidentReport($Id = null)
    {

        $Stage = new Stage('Schülerüberweisung', 'Erstellen');
        $tblPerson = Person::useService()->getPersonById($Id);
        $Global = $this->getGlobal();
        $Global->POST['Data']['AddressTarget'] = 'Unfallkasse Sachsen';
        $Global->POST['Data']['TargetAddressStreet'] = 'Postfach 42';
        $Global->POST['Data']['TargetAddressCity'] = '01651 Meißen';
        if ($tblPerson) {
            $Global->POST['Data']['LastFirstName'] = $tblPerson->getLastFirstName();
            $Global->POST['Data']['Date'] = (new \DateTime())->format('d.m.Y');

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

            $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
            if ($tblStudent) {
                // Schuldaten der Schule des Schülers
                $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
                $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                    $tblStudentTransferType);
                if ($tblStudentTransfer) {
                    $tblCompanySchool = $tblStudentTransfer->getServiceTblCompany();
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
                // Datum Aufnahme
                $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('ARRIVE');
                $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                    $tblStudentTransferType);
                if ($tblStudentTransfer) {
                    $EntryDate = $tblStudentTransfer->getTransferDate();
                    $Global->POST['Data']['SchoolEntry'] = $EntryDate;
                    if ($EntryDate != '') {
                        $tblYearList = Term::useService()->getYearAllByDate(new \DateTime($EntryDate));
                        if ($tblYearList) {
                            foreach ($tblYearList as $tblYear) {
                                $tblDivision = Division::useService()->getDivisionByPersonAndYear($tblPerson, $tblYear);
                                if ($tblDivision && $tblDivision->getTblLevel()) {
                                    $Global->POST['Data']['SchoolEntryDivision'] = $tblDivision->getTblLevel()->getName();
                                }
                            }
                        }
                    }
                }
            }

            // Hauptadresse Schüler
            $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
            if ($tblAddress) {
                $Global->POST['Data']['AddressStreet'] = $tblAddress->getStreetName().', '.$tblAddress->getStreetNumber();
                $tblCity = $tblAddress->getTblCity();
                if ($tblCity) {
                    $Global->POST['Data']['AddressPLZ'] = $tblCity->getCode();
                    $Global->POST['Data']['AddressCity'] = $tblCity->getDisplayName();
                }
            }

            // Sorgeberechtigte
            $tblRelationshipType = Relationship::useService()->getTypeByName('Sorgeberechtigt');
            $tblToPersonCustodyList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson,
                $tblRelationshipType);
            $Global->POST['Data']['Custody'] = '';
            if ($tblToPersonCustodyList) {
                foreach ($tblToPersonCustodyList as $tblToPersonCustody) {
                    $tblPersonParent = $tblToPersonCustody->getServiceTblPersonFrom();
                    if (!isset($Global->POST['Data']['Custody']))
                        if ($Global->POST['Data']['Custody'] == '') {
                            $Global->POST['Data']['Custody'] .= $tblPersonParent->getSalutation().' '.
                                ($tblPersonParent->getTitle()
                                    ? $tblPersonParent->getTitle().' '
                                    : '')
                                .$tblPersonParent->getLastFirstName();
                        } else {
                            // Linebrake without tabs is important! don't remove
                            $Global->POST['Data']['Custody'] .= '
'.$tblPersonParent->getSalutation().' '.
                                ($tblPersonParent->getTitle()
                                    ? $tblPersonParent->getTitle().' '
                                    : '')
                                .$tblPersonParent->getLastFirstName();
                        }
                }
            }

            // Klassen Wiederholungen
            $tblDivisionStudentList = Division::useService()->getDivisionStudentAllByPerson($tblPerson);
            $DivisionArray = array();
            $DivisionRepeatArray = array();
            if ($tblDivisionStudentList) {
                foreach ($tblDivisionStudentList as $tblDivisionStudent) {
                    $tblDivision = $tblDivisionStudent->getTblDivision();
                    if ($tblDivision) {
                        $tblLevel = $tblDivision->getTblLevel();
                        if (!array_key_exists($tblLevel->getName(), $DivisionArray)) {
                            $DivisionArray[$tblLevel->getName()] = $tblDivision->getDisplayName();
                        } elseif ($tblLevel->getName() != '') {
                            $DivisionRepeatArray[] = $tblDivision->getTblLevel()->getName();
                        }
                    }
                }
            }
            if (!empty($DivisionRepeatArray)) {
                $Global->POST['Data']['DivisionRepeat'] = implode(', ', $DivisionRepeatArray);
            }

            // Aktuelle Klasse
            $tblYearList = Term::useService()->getYearByNow();
            if ($tblYearList) {
                foreach ($tblYearList as $tblYear) {
                    $tblDivision = Division::useService()->getDivisionByPersonAndYear($tblPerson, $tblYear);
                    if ($tblDivision && $tblDivision->getTblLevel() && $tblDivision->getTblLevel()->getName() != '') {
                        $Global->POST['Data']['Division'] = $tblDivision->getTblLevel()->getName();
                    }
                }
            }
        }
        $Global->savePost();

        $form = $this->formStudentTransfer($Id);

        $HeadPanel = new Panel('Schüler', $tblPerson->getLastFirstName());

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
                            new Title('Vorlage des Standard-Dokuments "Schülerüberweisung"')
                            .new Thumbnail(
                                FileSystem::getFileLoader('/Common/Style/Resource/Document/AccidentReport.PNG')
                                , ''
                            )
                            , 5),
                    ))
                ))
            )
        );

        return $Stage;
    }

    /**
     * @param int $PersonId
     *
     * @return Form
     */
    private function formStudentTransfer($PersonId)
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
                                    new LayoutColumn(new Well(
                                        new Layout(
                                            new LayoutGroup(array(
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        (new TextField('Data[School]', 'Schule',
                                                            'Schule'))
                                                            ->ajaxPipelineOnKeyUp(ApiAccidentReport::pipelineButtonRefresh($PersonId))
                                                        , 6),
                                                    new LayoutColumn(
                                                        (new TextField('Data[SchoolExtended]', 'Zusatz',
                                                            'Zusatz'))
                                                            ->ajaxPipelineOnKeyUp(ApiAccidentReport::pipelineButtonRefresh($PersonId))
                                                        , 6)
                                                )),
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        (new TextField('Data[SchoolAddressStreet]', 'Straße Nr.',
                                                            'Straße Hausnummer'))
                                                            ->ajaxPipelineOnKeyUp(ApiAccidentReport::pipelineButtonRefresh($PersonId))
                                                        , 6),
                                                    new LayoutColumn(
                                                        (new TextField('Data[SchoolAddressCity]', 'PLZ Ort', 'PLZ Ort'))
                                                            ->ajaxPipelineOnKeyUp(ApiAccidentReport::pipelineButtonRefresh($PersonId))
                                                        , 6)
                                                ))
                                            ))
                                        )
                                    )),
                                    new LayoutColumn(
                                        new Title('Informationen Träger')
                                    ),
                                    new LayoutColumn(new Well(
                                        new Layout(
                                            new LayoutGroup(
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        (new TextField('Data[SchoolResponsibility]',
                                                            'Träger der Einrichtung',
                                                            'Träger der Einrichtung')
                                                        )->ajaxPipelineOnKeyUp(ApiAccidentReport::pipelineButtonRefresh($PersonId))
                                                        , 6),
                                                    new LayoutColumn(
                                                        (new TextField('Data[CompanyNumber]', 'Unternehmensnr.',
                                                            'Unternehmensnr.')
                                                        )->ajaxPipelineOnKeyUp(ApiAccidentReport::pipelineButtonRefresh($PersonId))
                                                        , 6)
                                                ))
                                            )
                                        )
                                    )),
                                    new LayoutColumn(
                                        new Title('Empfänger')
                                    ),
                                    new LayoutColumn(new Well(
                                        new Layout(
                                            new LayoutGroup(
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        (new TextField('Data[AddressTarget]', 'Empfänger',
                                                            'Empfänger')
                                                        )->ajaxPipelineOnKeyUp(ApiAccidentReport::pipelineButtonRefresh($PersonId))
                                                        , 4),
                                                    new LayoutColumn(
                                                        (new TextField('Data[TargetAddressStreet]', 'Straße Hausnummer',
                                                            'Straße Hausnummer')
                                                        )->ajaxPipelineOnKeyUp(ApiAccidentReport::pipelineButtonRefresh($PersonId))
                                                        , 4),
                                                    new LayoutColumn(
                                                        (new TextField('Data[TargetAddressCity]', 'PLZ Ort', 'PLZ Ort')
                                                        )->ajaxPipelineOnKeyUp(ApiAccidentReport::pipelineButtonRefresh($PersonId))
                                                        , 4)
                                                ))
                                            )
                                        )
                                    )),
                                    new LayoutColumn(
                                        new Title('Informationen Versicherter')
                                    ),
                                    new LayoutColumn(new Well(
                                        new Layout(
                                            new LayoutGroup(array(
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        (new TextField('Data[LastFirstName]', 'Name, Vorname',
                                                            'Name, Vorname des Schülers/der Schülerin')
                                                        )->ajaxPipelineOnKeyUp(ApiAccidentReport::pipelineButtonRefresh($PersonId))
                                                        , 8),
                                                    new LayoutColumn(
                                                        (new TextField('Data[Birthday]', 'Geburtstag',
                                                            'Geburtstag')
                                                        )->ajaxPipelineOnKeyUp(ApiAccidentReport::pipelineButtonRefresh($PersonId))
                                                        , 4),
                                                )),
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        (new TextField('Data[AddressStreet]', 'Straße, Hausnummer',
                                                            'Straße, Hausnummer')
                                                        )->ajaxPipelineOnKeyUp(ApiAccidentReport::pipelineButtonRefresh($PersonId))
                                                        , 6),
                                                    new LayoutColumn(
                                                        (new TextField('Data[AddressPLZ]', 'Postleitzahl',
                                                            'Postleitzahl')
                                                        )->ajaxPipelineOnKeyUp(ApiAccidentReport::pipelineButtonRefresh($PersonId))
                                                        , 3),
                                                    new LayoutColumn(
                                                        (new TextField('Data[AddressCity]', 'Ort',
                                                            'Ort')
                                                        )->ajaxPipelineOnKeyUp(ApiAccidentReport::pipelineButtonRefresh($PersonId))
                                                        , 3),
                                                )),
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new Label('Geschlecht').
                                                        new Listing(array(
                                                            (new RadioBox('Data[Gender]', 'Männlich',
                                                                'Männlich')
                                                            )->ajaxPipelineOnKeyUp(ApiAccidentReport::pipelineButtonRefresh($PersonId)),
                                                            (new RadioBox('Data[Gender]', 'Weiblich',
                                                                'Weiblich')
                                                            )->ajaxPipelineOnKeyUp(ApiAccidentReport::pipelineButtonRefresh($PersonId))
                                                        ))
                                                        , 3),
                                                    new LayoutColumn(
                                                        (new TextField('Data[Nationality]', 'Staatsangehörigkeit',
                                                            'Staatsangehörigkeit')
                                                        )->ajaxPipelineOnKeyUp(ApiAccidentReport::pipelineButtonRefresh($PersonId))
                                                        , 3),
                                                    new LayoutColumn(
                                                        (new TextField('Data[Custody]', 'Vertreter',
                                                            'Vertreter')
                                                        )->ajaxPipelineOnKeyUp(ApiAccidentReport::pipelineButtonRefresh($PersonId))
                                                        , 6),
                                                )),
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        '&nbsp;'
                                                        , 6),
                                                    new LayoutColumn(
                                                        (new TextField('Data[CustodyAddress]', 'Str Nr. PLZ Ort',
                                                            'Anschrift Vertreter')
                                                        )->ajaxPipelineOnKeyUp(ApiAccidentReport::pipelineButtonRefresh($PersonId))
                                                        , 6),
                                                )),
                                            ))
                                        )
                                    )),
                                    new LayoutColumn(
                                        new Title('Informationen Unfall')
                                    ),
                                    new LayoutColumn(new Well(
                                        new Layout(
                                            new LayoutGroup(array(
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new Label('Tödlicher Unfall?').
                                                        new Listing(array(
                                                            (new CheckBox('Data[DeathAccident]', 'Tödlich', '1')
                                                            )->ajaxPipelineOnKeyUp(ApiAccidentReport::pipelineButtonRefresh($PersonId)),
                                                        ))
                                                        , 3),
                                                    new LayoutColumn(
                                                        (new TextField('Data[AccidentDate]',
                                                            (new \DateTime())->format('d.m.Y'),
                                                            'Datum des Unfalls')
                                                        )->ajaxPipelineOnKeyUp(ApiAccidentReport::pipelineButtonRefresh($PersonId))
                                                        , 3),
                                                    new LayoutColumn(
                                                        (new TextField('Data[AccidentHour]', 'Stunde',
                                                            'Stunde Unfallzeitpunkt')
                                                        )->ajaxPipelineOnKeyUp(ApiAccidentReport::pipelineButtonRefresh($PersonId))
                                                        , 3),
                                                    new LayoutColumn(
                                                        (new TextField('Data[AccidentMinute]', 'Minute',
                                                            'Minute Unfallzeitpunkt')
                                                        )->ajaxPipelineOnKeyUp(ApiAccidentReport::pipelineButtonRefresh($PersonId))
                                                        , 3),
                                                )),
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        (new TextField('Data[AccidentPlace]', 'Ort', 'Unfallort')
                                                        )->ajaxPipelineOnKeyUp(ApiAccidentReport::pipelineButtonRefresh($PersonId))
                                                        , 6),
                                                )),
                                                new LayoutRow(
                                                    new LayoutColumn(
                                                        (new TextArea('Data[AccidentDescription]', 'Beschreibung'
                                                            , 'Ausführliche Beschreibung des Unfallhergangs')
                                                        )->ajaxPipelineOnKeyUp(ApiAccidentReport::pipelineButtonRefresh($PersonId))
                                                    )
                                                ),
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        (new TextField('Data[AccidentBodyParts]',
                                                            'Kopf, Bein, Arm, etc.', 'Verletzte Körperteile')
                                                        )->ajaxPipelineOnKeyUp(ApiAccidentReport::pipelineButtonRefresh($PersonId))
                                                        , 6),
                                                    new LayoutColumn(
                                                        (new TextField('Data[AccidentType]', 'Art',
                                                            'Art der Verletzung')
                                                        )->ajaxPipelineOnKeyUp(ApiAccidentReport::pipelineButtonRefresh($PersonId))
                                                        , 6),
                                                )),
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new Header('Hat der Versicherte den Besuch der Einrichtung unterbrochen?')
                                                        .new PullClear(
                                                            new PullLeft((new RadioBox('Data[Brake]',
                                                                'nein &nbsp;&nbsp;&nbsp;&nbsp;', 'No')
                                                            )->ajaxPipelineOnKeyUp(ApiAccidentReport::pipelineButtonRefresh($PersonId)))
                                                            .new PullLeft((new RadioBox('Data[Brake]',
                                                                'sofort &nbsp;&nbsp;&nbsp;&nbsp;', 'Yes')
                                                            )->ajaxPipelineOnKeyUp(ApiAccidentReport::pipelineButtonRefresh($PersonId)))
                                                            .new PullLeft((new RadioBox('Data[Brake]',
                                                                'später am &nbsp;&nbsp;&nbsp;&nbsp;', 'At')
                                                            )->ajaxPipelineOnKeyUp(ApiAccidentReport::pipelineButtonRefresh($PersonId)))
                                                        )
                                                        , 6),
                                                    new LayoutColumn(
                                                        (new TextField('Data[BreakDate]',
                                                            (new \DateTime())->format('d.m.Y'),
                                                            'Datum der Unterbrechung')
                                                        )->ajaxPipelineOnKeyUp(ApiAccidentReport::pipelineButtonRefresh($PersonId))
                                                        , 3),
                                                    new LayoutColumn(
                                                        (new TextField('Data[BreakHour]', 'Stunde',
                                                            'Zeitpunkt der Unterbrechung')
                                                        )->ajaxPipelineOnKeyUp(ApiAccidentReport::pipelineButtonRefresh($PersonId))
                                                        , 3)
                                                )),
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new Header('hat der Versicherte den Besuch der Einrichtung wieder aufgenommen?')
                                                        .new PullClear(
                                                            new PullLeft((new RadioBox('Data[Return]',
                                                                'nein &nbsp;&nbsp;&nbsp;&nbsp;', 'No')
                                                            )->ajaxPipelineOnKeyUp(ApiAccidentReport::pipelineButtonRefresh($PersonId)))
                                                            .new PullLeft((new RadioBox('Data[Return]',
                                                                'ja, am &nbsp;&nbsp;&nbsp;&nbsp;', 'At')
                                                            )->ajaxPipelineOnKeyUp(ApiAccidentReport::pipelineButtonRefresh($PersonId)))
                                                        )
                                                        , 6),
                                                    new LayoutColumn(
                                                        (new TextField('Data[ReturnDate]',
                                                            (new \DateTime())->format('d.m.Y'),
                                                            'Datum der Wiederaufnahme')
                                                        )->ajaxPipelineOnKeyUp(ApiAccidentReport::pipelineButtonRefresh($PersonId))
                                                        , 3),
                                                )),
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        (new TextField('Data[Doctor]', 'Name',
                                                            'Name des erstbehandelnden Arztes / Krankenhaus')
                                                        )->ajaxPipelineOnKeyUp(ApiAccidentReport::pipelineButtonRefresh($PersonId))
                                                        .(new TextField('Data[DoctorAddress]', 'Adresse',
                                                            'Adresse des erstbehandelnden Arztes / Krankenhaus')
                                                        )->ajaxPipelineOnKeyUp(ApiAccidentReport::pipelineButtonRefresh($PersonId))
                                                        , 6),
                                                    new LayoutColumn(
                                                        new Header(new Bold('Beginn des Besuchs der Einrichtung'))
                                                        .(new TextField('Data[LocalStartHour]', 'Stunde', 'Stunde')
                                                        )->ajaxPipelineOnKeyUp(ApiAccidentReport::pipelineButtonRefresh($PersonId))
                                                        .(new TextField('Data[LocalStartMinute]', 'Minute', 'Minute')
                                                        )->ajaxPipelineOnKeyUp(ApiAccidentReport::pipelineButtonRefresh($PersonId))
                                                        , 3),
                                                    new LayoutColumn(
                                                        new Header(new Bold('Ende des Besuchs der Einrichtung'))
                                                        .(new TextField('Data[LocalEndHour]', 'Stunde', 'Stunde')
                                                        )->ajaxPipelineOnKeyUp(ApiAccidentReport::pipelineButtonRefresh($PersonId))
                                                        .(new TextField('Data[LocalEndMinute]', 'Minute', 'Minute')
                                                        )->ajaxPipelineOnKeyUp(ApiAccidentReport::pipelineButtonRefresh($PersonId))
                                                        , 3),
                                                )),
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        (new TextField('Data[Date]', (new \DateTime())->format('d.m.Y'),
                                                            'Datum')
                                                        )->ajaxPipelineOnKeyUp(ApiAccidentReport::pipelineButtonRefresh($PersonId))
                                                        , 3),
                                                    new LayoutColumn(
                                                        (new TextField('Data[LocalLeader]', 'Leiter',
                                                            'Leiter (Beauftragter) der Einrichtung')
                                                        )->ajaxPipelineOnKeyUp(ApiAccidentReport::pipelineButtonRefresh($PersonId))
                                                        , 4),
                                                    new LayoutColumn(
                                                        (new TextField('Data[Recall]', 'Telefonnummer',
                                                            'Telefon-Nr. für Rückfragen (Ansprechpartner)')
                                                        )->ajaxPipelineOnKeyUp(ApiAccidentReport::pipelineButtonRefresh($PersonId))
                                                        , 5),
                                                )),
                                            ))
                                        )
                                    ))
                                ))
                            )
                        )
                    )
                ),

                new FormRow(array(
                    new FormColumn(
                        ApiAccidentReport::receiverService(ApiAccidentReport::pipelineButtonRefresh($PersonId))
//                        (new Standard('PDF erzeugen', ApiAccidentReport::getEndpoint()))->ajaxPipelineOnClick(ApiAccidentReport::pipelineDownload($PersonId))
                    )
                ))
            ))
        );
    }
}