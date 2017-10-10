<?php

namespace SPHERE\Application\Document\Standard\StudentTransfer;


use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Api\Document\Standard\Repository\StudentTransfer\ApiStudentTransfer;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Contact\Phone\Service\Entity\TblToPerson as TblToPersonPhone;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Thumbnail;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

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
    public function frontendFillStudentTransfer($Id = null)
    {

        $Stage = new Stage('Schülerüberweisung', 'Erstellen');
        $tblPerson = Person::useService()->getPersonById($Id);
        $Global = $this->getGlobal();
        if ($tblPerson) {
            $Global->POST['Data']['LastFirstName'] = $tblPerson->getLastFirstName();
            $Global->POST['Data']['Date'] = (new \DateTime())->format('d.m.Y');
            $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
            if ($tblStudent) {
                // Schuldaten der Schule des Schülers
                $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
                $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                    $tblStudentTransferType);
                if ($tblStudentTransfer) {
                    $tblCompanySchool = $tblStudentTransfer->getServiceTblCompany();
                    if ($tblCompanySchool) {
                        $Global->POST['Data']['LeaveSchool'] = $tblCompanySchool->getDisplayName();
                        $tblAddressSchool = Address::useService()->getAddressByCompany($tblCompanySchool);
                        if ($tblAddressSchool) {
                            $Global->POST['Data']['AddressStreet'] = $tblAddressSchool->getStreetName().', '.$tblAddressSchool->getStreetNumber();
                            $tblCitySchool = $tblAddressSchool->getTblCity();
                            if ($tblCitySchool) {
                                $Global->POST['Data']['AddressCity'] = $tblCitySchool->getCode().', '.$tblCitySchool->getName();
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
                $Global->POST['Data']['MainAddress'] = $tblAddress->getGuiString();
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
                                        new Title('Informationen abgebende Schule')
                                    ),
                                    new LayoutColumn(new Well(
                                        new Layout(
                                            new LayoutGroup(
                                                new LayoutRow(array(
                                                    new LayoutColumn(
                                                        (new TextField('Data[LeaveSchool]', 'Abgebende Schule',
                                                            'Abgebende Schule')
                                                        )->ajaxPipelineOnKeyUp(ApiStudentTransfer::pipelineButtonRefresh($PersonId))
                                                    ),
                                                    new LayoutColumn(
                                                        (new TextField('Data[ContactPerson]', 'Ansprechpartner',
                                                            'Ansprechpartner')
                                                        )->ajaxPipelineOnKeyUp(ApiStudentTransfer::pipelineButtonRefresh($PersonId))
                                                        , 3),
                                                    new LayoutColumn(
                                                        (new TextField('Data[DocumentNumber]', 'Aktenzeichen',
                                                            'Aktenzeichen')
                                                        )->ajaxPipelineOnKeyUp(ApiStudentTransfer::pipelineButtonRefresh($PersonId))
                                                        , 3),
                                                    new LayoutColumn(
                                                        (new TextField('Data[Phone]', 'Telefon', 'Telefon')
                                                        )->ajaxPipelineOnKeyUp(ApiStudentTransfer::pipelineButtonRefresh($PersonId))
                                                        , 3),
                                                    new LayoutColumn(
                                                        (new TextField('Data[Fax]', 'Telefax', 'Telefax')
                                                        )->ajaxPipelineOnKeyUp(ApiStudentTransfer::pipelineButtonRefresh($PersonId))
                                                        , 3),
                                                    new LayoutColumn(
                                                        (new TextField('Data[AddressStreet]', 'Straße, Nr.',
                                                            'Straße, Nr.')
                                                        )->ajaxPipelineOnKeyUp(ApiStudentTransfer::pipelineButtonRefresh($PersonId))
                                                        , 12),
                                                    new LayoutColumn(
                                                        (new TextField('Data[AddressCity]', 'PLZ, Ort', 'PLZ, Ort')
                                                        )->ajaxPipelineOnKeyUp(ApiStudentTransfer::pipelineButtonRefresh($PersonId))
                                                        , 9),
                                                    new LayoutColumn(
                                                        (new TextField('Data[Date]', 'Datum', 'Datum')
                                                        )->ajaxPipelineOnKeyUp(ApiStudentTransfer::pipelineButtonRefresh($PersonId))
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
                                                        (new TextField('Data[NewSchool1]', 'Aufnehmende Schule',
                                                            'Aufnehmende Schule')
                                                        )->ajaxPipelineOnKeyUp(ApiStudentTransfer::pipelineButtonRefresh($PersonId))
                                                        , 6)
                                                ),
                                                new LayoutRow(
                                                    new LayoutColumn(
                                                        (new TextField('Data[NewSchool2]', '', '')
                                                        )->ajaxPipelineOnKeyUp(ApiStudentTransfer::pipelineButtonRefresh($PersonId))
                                                        , 6)
                                                ),
                                                new LayoutRow(
                                                    new LayoutColumn(
                                                        (new TextField('Data[NewSchool3]', '', '')
                                                        )->ajaxPipelineOnKeyUp(ApiStudentTransfer::pipelineButtonRefresh($PersonId))
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
                                                        (new TextField('Data[LastFirstName]', 'Name, Vorname',
                                                            'Name, Vorname des Schülers/der Schülerin')
                                                        )->ajaxPipelineOnKeyUp(ApiStudentTransfer::pipelineButtonRefresh($PersonId))
                                                        , 12),
                                                    new LayoutColumn(
                                                        (new TextField('Data[MainAddress]', 'Bisherige Anschrift',
                                                            'Bisherige Anschrift')
                                                        )->ajaxPipelineOnKeyUp(ApiStudentTransfer::pipelineButtonRefresh($PersonId))
                                                        , 12),
                                                    new LayoutColumn(
                                                        (new TextField('Data[NewAddress]', 'Neue Anschrift',
                                                            'Neue Anschrift')
                                                        )->ajaxPipelineOnKeyUp(ApiStudentTransfer::pipelineButtonRefresh($PersonId))
                                                        , 12),
                                                    new LayoutColumn(
                                                        (new TextArea('Data[Custody]', 'Sorgeberechtigte',
                                                            'Sorgeberechtigte')
                                                        )->ajaxPipelineOnKeyUp(ApiStudentTransfer::pipelineButtonRefresh($PersonId))
                                                        , 12),
                                                    new LayoutColumn(
                                                        (new TextField('Data[Division]', 'Aktuell besuchte Klasse',
                                                            'Aktuell besuchte Klasse')
                                                        )->ajaxPipelineOnKeyUp(ApiStudentTransfer::pipelineButtonRefresh($PersonId))
                                                        , 6),
                                                    new LayoutColumn(
                                                        (new TextField('Data[DateUntil]', 'Besucht die Einrichtung bis',
                                                            'Besucht die Einrichtung bis')
                                                        )->ajaxPipelineOnKeyUp(ApiStudentTransfer::pipelineButtonRefresh($PersonId))
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
                                                        (new TextField('Data[SchoolEntry]', 'Eintritt in unsere Schule',
                                                            'Eintritt in unsere Schule')
                                                        )->ajaxPipelineOnKeyUp(ApiStudentTransfer::pipelineButtonRefresh($PersonId))
                                                        , 4),
                                                    new LayoutColumn(
                                                        (new TextField('Data[SchoolEntryDivision]', 'In Klasse',
                                                            'In Klasse')
                                                        )->ajaxPipelineOnKeyUp(ApiStudentTransfer::pipelineButtonRefresh($PersonId))
                                                        , 4),
                                                    new LayoutColumn(
                                                        (new TextField('Data[DivisionRepeat]', 'Wiederholte Klassen',
                                                            'Wiederholte Klassen')
                                                        )->ajaxPipelineOnKeyUp(ApiStudentTransfer::pipelineButtonRefresh($PersonId))
                                                        , 4)
                                                ))
                                            )
                                        )
                                    ))
                                ))
                            )
                        )
                    )
                ),

                new FormRow(array(
                    new FormColumn(
                        ApiStudentTransfer::receiverService(ApiStudentTransfer::pipelineButtonRefresh($PersonId))
//                        (new Standard('PDF erzeugen', ApiStudentTransfer::getEndpoint()))->ajaxPipelineOnClick(ApiStudentTransfer::pipelineDownload($PersonId))
                    )
                ))
            ))
        );
    }
}