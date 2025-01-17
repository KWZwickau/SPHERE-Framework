<?php
namespace SPHERE\Application\Document\Standard\StudentTransfer;

use DateTime;
use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Api\Document\Standard\ApiStandard;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Contact\Phone\Service\Entity\TblToPerson as TblToPersonPhone;
use SPHERE\Application\Document\Standard\EnrollmentDocument\EnrollmentDocument;
use SPHERE\Application\Document\Standard\EnrollmentDocument\Frontend;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblStudentEducation;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransferType;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\HiddenField;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Person as PersonIcon;
use SPHERE\Common\Frontend\Icon\Repository\Search;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
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
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Sorter;

class StudentTransfer extends Extension
{
    public static function registerModule()
    {
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Schülerüberweisung'))
        );

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendSelectPerson'
        ));

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Fill', __CLASS__.'::frontendFillStudentTransfer'
        ));

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Archive', __CLASS__.'::frontendStudentArchiv'
        ));
    }

    /**
     * @param Stage $Stage
     */
    private static function setButtonList(Stage $Stage): void
    {
        $Stage->addButton(new Standard('Schüler', '/Document/Standard/StudentTransfer', new PersonIcon(), array(), 'Schülerüberweisung eines Schülers'));
        $Url = $_SERVER['REDIRECT_URL'];

        if(strpos($Url, '/StudentTransfer/Archiv')){
            $Stage->addButton(new Standard(new Info(new Bold('Ehemalige (Archiv)')), '/Document/Standard/StudentTransfer/Archive', new PersonIcon(),
                array(), 'Schülerüberweisung eines Schülers'));
        } else {
            $Stage->addButton(new Standard('Ehemalige (Archiv)', '/Document/Standard/StudentTransfer/Archive', new PersonIcon(),
                array(), 'Schülerüberweisung eines ehemaligen Schülers'));
        }
    }

    /**
     * @param null $Search
     *
     * @return Stage
     */
    public function frontendStudentArchiv($Search = null): Stage
    {
        $Route = '/Document/Standard/StudentTransfer/Fill';
        if ($Search) {
            $global = $this->getGlobal();
            $global->POST['Data']['Search'] = $Search;
            $global->savePost();
        }

        $Stage = new Stage('Schülerüberweisung', 'Ehemaligen Schüler auswählen');
        self::setButtonList($Stage);

        $panel = new Panel(
            new Search() . ' Personen-Suche',
            (new Form(new FormGroup(new FormRow(array(
                new FormColumn(
                    (new TextField('Data[Search]', '', ''))
                        ->ajaxPipelineOnKeyUp(ApiStandard::pipelineSearchPerson($Route))
                ),
            )))))->disableSubmitAction(),
            Panel::PANEL_TYPE_INFO
        );

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            $panel,
                            ApiStandard::receiverBlock($Search ? EnrollmentDocument::useFrontend()->loadPersonSearch($Route, $Search) : '', 'SearchContent')
                        )),
                    ))
                )),
            ))
        );

        return $Stage;
    }

    /**
     * @return Stage
     */
    public static function frontendSelectPerson(): Stage
    {
        $Stage = new Stage('Schülerüberweisung', 'Schüler auswählen');
        self::setButtonList($Stage);

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            Frontend::getStudentSelectDataTable('/Document/Standard/StudentTransfer/Fill')
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
    public function frontendFillStudentTransfer($PersonId = null): Stage
    {
        $Stage = new Stage('Schülerüberweisung', 'Erstellen');
        $Stage->addButton(new Standard('Zurück', '/Document/Standard/StudentTransfer', new ChevronLeft()));
        $tblPerson = Person::useService()->getPersonById($PersonId);
        $Global = $this->getGlobal();
        if ($tblPerson) {
            $Global->POST['Data']['PersonId'] = $PersonId;
            $Global->POST['Data']['LastFirstName'] = $tblPerson->getLastFirstName();
            $Global->POST['Data']['Date'] = (new DateTime())->format('d.m.Y');

            $tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndDate($tblPerson);
            // Schüler hat keine aktuelle SchülerBildung mehr
            if (!$tblStudentEducation && ($tblStudentEducationList = DivisionCourse::useService()->getStudentEducationListByPerson($tblPerson))) {
                $tblStudentEducationList = (new Extension())->getSorter($tblStudentEducationList)->sortObjectBy('YearNameForSorter', null, Sorter::ORDER_DESC);

                $tblStudentEducation = reset($tblStudentEducationList);
            }

            if ($tblStudentEducation) {
                if (($tblDivision = $tblStudentEducation->getTblDivision())) {
                    $Global->POST['Data']['Division'] = $tblDivision->getName();
                } elseif (($tblCoreGroup = $tblStudentEducation->getTblCoreGroup())) {
                    $Global->POST['Data']['Division'] = $tblCoreGroup->getName();
                }

                if (($tblCompanySchool = $tblStudentEducation->getServiceTblCompany())) {
                    $Global->POST['Data']['LeaveSchool'] = $tblCompanySchool->getDisplayName();
                    $tblAddressSchool = Address::useService()->getAddressByCompany($tblCompanySchool);
                    if ($tblAddressSchool) {
                        $Global->POST['Data']['AddressStreet'] = $tblAddressSchool->getStreetName() . ' ' . $tblAddressSchool->getStreetNumber();
                        $tblCitySchool = $tblAddressSchool->getTblCity();
                        if ($tblCitySchool) {
                            $Global->POST['Data']['AddressCity'] = $tblCitySchool->getCode() . ' ' . $tblCitySchool->getName();
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

            if (($tblStudent = Student::useService()->getStudentByPerson($tblPerson))) {
                // Datum Aufnahme
                $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier(TblStudentTransferType::ARRIVE);
                $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                    $tblStudentTransferType);
                if ($tblStudentTransfer) {
                    $EntryDate = $tblStudentTransfer->getTransferDate();
                    $Global->POST['Data']['SchoolEntry'] = $EntryDate;
                    if ($EntryDate != '') {
                        if (($tblStudentEducationEntry = DivisionCourse::useService()->getStudentEducationByPersonAndDate($tblPerson, $EntryDate))) {
                            if (($tblDivisionEntry = $tblStudentEducationEntry->getTblDivision())) {
                                $Global->POST['Data']['SchoolEntryDivision'] = $tblDivisionEntry->getName();
                            } elseif (($tblCoreGroupEntry = $tblStudentEducationEntry->getTblCoreGroup())) {
                                $Global->POST['Data']['SchoolEntryDivision'] = $tblCoreGroupEntry->getName();
                            }
                        }
                    }
                }
                // Datum Abgabe
                // Schule Abgabe
                $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier(TblStudentTransferType::LEAVE);
                $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                    $tblStudentTransferType);
                if ($tblStudentTransfer) {
                    $LeaveDate = $tblStudentTransfer->getTransferDate();
                    $Global->POST['Data']['DateUntil'] = $LeaveDate;
                    if(($tblCompanyLeave = $tblStudentTransfer->getServiceTblCompany())){
//                        // Lange Schulnamen (ab 42 Buchstaben) werden auf 2 Zeilen aufgeteilt
//                        if(strlen($tblCompanyLeave->getName()) >= 42){
//                            // Versuch, nach letztem Leerzeichen zu trennen.
//                            $ShortString = substr($tblCompanyLeave->getName(), 0, 41);
//                            $CutPosition = strripos($ShortString, ' ');
//                            if($CutPosition){
//                                $Global->POST['Data']['NewSchool1'] = substr($tblCompanyLeave->getName(), 0, $CutPosition);
//                                $Global->POST['Data']['NewSchool2'] = substr($tblCompanyLeave->getName(), $CutPosition);
//                                $Global->POST['Data']['NewSchool3'] = $tblCompanyLeave->getExtendedName();
//                            } else {
//                                // trennt Notalls fest
//                                $Global->POST['Data']['NewSchool1'] = substr($tblCompanyLeave->getName(), 0, 41);
//                                $Global->POST['Data']['NewSchool2'] = substr($tblCompanyLeave->getName(), 41);
//                                $Global->POST['Data']['NewSchool3'] = $tblCompanyLeave->getExtendedName();
//                            }
//                        } else {

                            $Global->POST['Data']['NewSchool1'] = $tblCompanyLeave->getName();
                            $Global->POST['Data']['NewSchool2'] = $tblCompanyLeave->getExtendedName();
                            if($tblCompanyLeave->getExtendedName()){
                                $LineCount = 3;
                            } else {
                                $LineCount = 2;
                            }
                                if(($tblAddressCompany = Address::useService()->getAddressByCompany($tblCompanyLeave))){
                                    $Global->POST['Data']['NewSchool'.$LineCount++] = $tblAddressCompany->getStreetName().' '.$tblAddressCompany->getStreetNumber();
                                    if(($tblCityCompany = $tblAddressCompany->getTblCity())){
                                        $Global->POST['Data']['NewSchool'.$LineCount] = $tblCityCompany->getCode().' '.$tblCityCompany->getDisplayName();
                                    }
                                }



//                        }
                    }
                }
            }

            // Hauptadresse Schüler
            $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
            if ($tblAddress) {
                $Global->POST['Data']['MainAddress'] = $tblAddress->getGuiString(false);
            }

            // Sorgeberechtigte
            $tblRelationshipType = Relationship::useService()->getTypeByName('Sorgeberechtigt');
            $tblToPersonCustodyList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson,
                $tblRelationshipType);
            $Global->POST['Data']['Custody'] = '';
            if ($tblToPersonCustodyList) {
                foreach ($tblToPersonCustodyList as $tblToPersonCustody) {
                    $tblPersonParent = $tblToPersonCustody->getServiceTblPersonFrom();
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
            if (($tblStudentEducationList = DivisionCourse::useService()->getStudentEducationListByPerson($tblPerson))) {
                // Sortierung absteigend, notwendig wegen Schuljahrwiederholung
                $levelList = array();
                $repeatList = array();
                $tblStudentEducationList = $this->getSorter($tblStudentEducationList)->sortObjectBy('YearNameForSorter');
                /** @var TblStudentEducation $tblStudentEducationTemp */
                foreach ($tblStudentEducationList as $tblStudentEducationTemp) {
                    if (!$tblStudentEducationTemp->isInActive() && $tblStudentEducationTemp->getLevel()) {
                        if (isset($levelList[$tblStudentEducationTemp->getLevel()])) {
                            if (($tblDivisionTemp = $tblStudentEducationTemp->getTblDivision())) {
                                $repeatList[] = $tblDivisionTemp->getName();
                            } elseif (($tblCoreGroupTemp = $tblStudentEducationTemp->getTblCoreGroup())) {
                                $repeatList[] = $tblCoreGroupTemp->getName();
                            }
                        }

                        $levelList[$tblStudentEducationTemp->getLevel()] = 1;
                    }
                }

                if (!empty($repeatList)) {
                    $Global->POST['Data']['DivisionRepeat'] = implode(', ', $repeatList);
                }
            }
        }
        $Global->savePost();

        $form = $this->formStudentTransfer();

        $HeadPanel = new Panel('Schüler', $tblPerson->getLastFirstName());

        $Stage->addButton(new External(
            'Blanko Schülerüberweisung herunterladen',
            'SPHERE\Application\Api\Document\Standard\StudentTransfer\Create',
            new Download(),
            array('Data' => array('empty')),
            'Schülerüberweisung herunterladen'
        ));

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
                                FileSystem::getFileLoader('/Common/Style/Resource/Document/StudentTransfer.png')
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
     * @return Form
     */
    private function formStudentTransfer(): Form
    {
        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new HiddenField('Data[PersonId]')   //ToDO Hidden ersetzen
                    ),
                    new FormColumn(
                        new Layout(
                            new LayoutGroup(
                                new LayoutRow(array(
                                    new LayoutColumn(
                                        new Title('Informationen abgebende Schule')
                                    ),
                                    new LayoutColumn(new Well(
                                        new Layout(
                                            new LayoutGroup(
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[LeaveSchool]', 'Abgebende Schule',
                                                            'Abgebende Schule')
                                                    ),
                                                    new LayoutColumn(
                                                        new TextField('Data[ContactPerson]', 'Ansprechpartner',
                                                            'Ansprechpartner')
                                                        , 3),
                                                    new LayoutColumn(
                                                        new TextField('Data[DocumentNumber]', 'Aktenzeichen',
                                                            'Aktenzeichen')
                                                        , 3),
                                                    new LayoutColumn(
                                                        new TextField('Data[Phone]', 'Telefon', 'Telefon')
                                                        , 3),
                                                    new LayoutColumn(
                                                        new TextField('Data[Fax]', 'Telefax', 'Telefax')
                                                        , 3),
                                                    new LayoutColumn(
                                                        new TextField('Data[AddressStreet]', 'Straße, Nr.',
                                                            'Straße, Nr.')
                                                        , 12),
                                                    new LayoutColumn(
                                                        new TextField('Data[AddressCity]', 'PLZ, Ort', 'PLZ, Ort')
                                                        , 9),
                                                    new LayoutColumn(
                                                        new TextField('Data[Date]', 'Datum', 'Datum')
                                                        , 3)
                                                ))
                                            )
                                        )
                                    )),
                                    new LayoutColumn(
                                        new Title('Informationen aufnehmende Schule')
                                    ),
                                    new LayoutColumn(new Well(
                                        new Layout(
                                            new LayoutGroup(array(
                                                new LayoutRow(
                                                    new LayoutColumn(
                                                        new TextField('Data[NewSchool1]', 'Aufnehmende Schule',
                                                            'Aufnehmende Schule')
                                                        , 6)
                                                ),
                                                new LayoutRow(
                                                    new LayoutColumn(
                                                        new TextField('Data[NewSchool2]', '', '')
                                                        , 6)
                                                ),
                                                new LayoutRow(
                                                    new LayoutColumn(
                                                        new TextField('Data[NewSchool3]', '', '')
                                                        , 6)
                                                ),
                                                new LayoutRow(
                                                    new LayoutColumn(
                                                        new TextField('Data[NewSchool4]', '', '')
                                                        , 6)
                                                ),
                                            ))
                                        )
                                    )),

                                    new LayoutColumn(
                                        new Title('Informationen zum Schüler')
                                    ),
                                    new LayoutColumn(new Well(
                                        new Layout(
                                            new LayoutGroup(
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[LastFirstName]', 'Name, Vorname',
                                                            'Name, Vorname des Schülers/der Schülerin')
                                                        , 12),
                                                    new LayoutColumn(
                                                        new TextField('Data[MainAddress]', 'Bisherige Anschrift',
                                                            'Bisherige Anschrift')
                                                        , 12),
                                                    new LayoutColumn(
                                                        new TextField('Data[NewAddress]', 'Neue Anschrift',
                                                            'Neue Anschrift')
                                                        , 12),
                                                    new LayoutColumn(
                                                        new TextArea('Data[Custody]', 'Sorgeberechtigte',
                                                            'Sorgeberechtigte')
                                                        , 12),
                                                    new LayoutColumn(
                                                        new TextField('Data[Division]', 'Aktuell besuchte Klasse',
                                                            'Aktuell besuchte Klasse')
                                                        , 6),
                                                    new LayoutColumn(
                                                        new TextField('Data[DateUntil]', 'Besucht die Einrichtung bis',
                                                            'Besucht die Einrichtung bis')
                                                        , 6)
                                                ))
                                            )
                                        )
                                    )),
                                    new LayoutColumn(new Well(
                                        new Layout(
                                            new LayoutGroup(
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        new TextField('Data[SchoolEntry]', 'Eintritt in unsere Schule',
                                                            'Eintritt in unsere Schule')
                                                        , 4),
                                                    new LayoutColumn(
                                                        new TextField('Data[SchoolEntryDivision]', 'In Klasse',
                                                            'In Klasse')
                                                        , 4),
                                                    new LayoutColumn(
                                                        new TextField('Data[DivisionRepeat]', 'Wiederholte Klassen',
                                                            'Wiederholte Klassen')
                                                        , 4)
                                                ))
                                            )
                                        )
                                    )),
                                    new LayoutColumn(new Well(
                                        new Layout(
                                            new LayoutGroup(
                                                new LayoutRow(
                                                    new LayoutColumn(
                                                        new TextField('Data[Additional]', 'Anlagen',
                                                            'Anlagen')
                                                    )
                                                )
                                            )
                                        )
                                    ))
                                ))
                            )
                        )
                    )
                )),

//                new FormRow(array(
//                    new FormColumn(
//                        ApiStudentTransfer::receiverService(ApiStudentTransfer::pipelineButtonRefresh($PersonId))
////                        (new Standard('PDF erzeugen', ApiStudentTransfer::getEndpoint()))->ajaxPipelineOnClick(ApiStudentTransfer::pipelineDownload($PersonId))
//                    )
//                ))
            ))
            , new Primary('Download', new Download(), true),
            '\Api\Document\Standard\StudentTransfer\Create' //, array('PersonId' => 15) // ToDo zusätzliche Daten mitgeben
        );
    }
}