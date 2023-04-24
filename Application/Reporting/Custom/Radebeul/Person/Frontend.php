<?php
namespace SPHERE\Application\Reporting\Custom\Radebeul\Person;

use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\Reporting\Standard\Person\Person as PersonStandard;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info as InfoText;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Reporting\Custom\Radebeul\Person
 */
class Frontend extends Extension implements IFrontendInterface
{
    /**
     * @param int|null $DivisionCourseId
     * @param null     $All
     *
     * @return Stage
     */
    public function frontendParentTeacherConferenceList(?int $DivisionCourseId = null, $All = null)
    {

        $Stage = new Stage('Individuelle Auswertung', 'Anwesenheitsliste Elternabende');
        $Route = '/Reporting/Custom/Radebeul/Person/ParentTeacherConferenceList';
        if($DivisionCourseId === null) {
            if($All) {
                $Stage->addButton(new Standard('aktuelles Schuljahr', $Route));
                $Stage->addButton(new Standard(new InfoText(new Bold('Alle Schuljahre')), $Route, null, array('All' => 1)));
            } else {
                $Stage->addButton(new Standard(new InfoText(new Bold('aktuelles Schuljahr')), $Route));
                $Stage->addButton(new Standard('Alle Schuljahre', $Route, null, array('All' => 1)));
            }
            $Stage->setContent(PersonStandard::useFrontend()->getChooseDivisionCourse($Route, $All));
            return $Stage;
        }
        $Stage->addButton(new Standard('Zurück', $Route, new ChevronLeft()));
        if(!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return $Stage->setContent(new Warning('Klasse nicht verfügbar.'));
        }
        if(!($tblPersonList = $tblDivisionCourse->getStudents())) {
            return $Stage->setContent(new Warning('Keine Schüler hinterlegt.'));
        }
        $TableContent = Person::useService()->createParentTeacherConferenceList($tblDivisionCourse);
        if(!empty($TableContent)) {
            $Stage->addButton(new Primary('Herunterladen', '/Api/Reporting/Custom/Radebeul/Person/ParentTeacherConferenceList/Download', new Download(),
                    array('DivisionCourseId' => $tblDivisionCourse->getId()))
            );
            $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
        }
        $Stage->setContent(
            new Layout(array(
                PersonStandard::useFrontend()->getDivisionHeadOverview($tblDivisionCourse),
                new LayoutGroup(new LayoutRow(new LayoutColumn(
                    new TableData($TableContent, null,
                        array(
                            'Number' => 'lfdNr.',
                            'LastName' => 'Name',
                            'FirstName' => 'Vorname',
                            'Attendance' => 'Unterschrift',
                        ),
                        array(
                            "columnDefs" => array(
                                array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 1),
                                array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 2),
                            ),
                            "pageLength" => -1,
                            "responsive" => false
                        )
                    )
                ))),
                PersonStandard::useFrontend()->getGenderLayoutGroup($tblPersonList)
            ))
        );
        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendDenominationList()
    {

        $Stage = new Stage('Individuelle Auswertung', 'Religionszugehörigkeit');
        $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
        list($TableContent, $countArray) = Person::useService()->createDenominationList();
        if(!empty($TableContent)) {
            $Stage->addButton(new Primary('Herunterladen', '/Api/Reporting/Custom/Radebeul/Person/DenominationList/Download', new Download()));
        }
        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(new LayoutRow(new LayoutColumn(
                    new TableData($TableContent, null,
                        array(
                            'Number' => 'lfdNr.',
                            'LastName' => 'Name',
                            'FirstName' => 'Vorname',
                            'Denomination' => 'Religion',
                        ),
                        array(
                            "columnDefs" => array(
                                array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 1),
                                array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 2),
                            ),
                            "pageLength" => -1,
                            "responsive" => false
                        )
                    )
                ))),
                new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn(new Panel('Schüler', array($countArray['All'],), Panel::PANEL_TYPE_INFO), 3),
                    new LayoutColumn(new Panel('Evangelisch (EV)', array($countArray['EV'],), Panel::PANEL_TYPE_INFO), 3),
                    new LayoutColumn(new Panel('Katholisch (RK)', array($countArray['RK'],), Panel::PANEL_TYPE_INFO), 3),
                    new LayoutColumn(new Panel('Ohne Angabe', array($countArray['KEINE'],), Panel::PANEL_TYPE_INFO), 3)
                )))
            ))
        );
        return $Stage;
    }

    /**
     * @param $GroupId
     *
     * @return Stage
     */
    public function frontendPhoneList($GroupId = null)
    {

        $Stage = new Stage('Individuelle Auswertung', 'Telefonliste');
        $TableContent = array();
        if ($GroupId === null) {
            $tblGroupAll = Group::useService()->getGroupAll();
            if ($tblGroupAll) {
                array_walk($tblGroupAll, function (TblGroup $tblGroup) use (&$TableContent) {
                    $item['Name'] = $tblGroup->getName();
                    $item['Option'] = new Standard('', '/Reporting/Custom/Radebeul/Person/PhoneList',
                        new EyeOpen(), array('GroupId' => $tblGroup->getId()), 'Anzeigen');
                    array_push($TableContent, $item);
                });
            }
            $Stage->setContent(new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                new TableData($TableContent, null,
                    array(
                        'Name' => 'Gruppe',
                        'Option' => '',
                    )
                )
            , 12)), new Title(new Listing() . ' Übersicht'))));
            return $Stage;
        }
        $Stage->addButton(new Standard('Zurück', '/Reporting/Custom/Radebeul/Person/PhoneList', new ChevronLeft()));
        if(($tblGroup = Group::useService()->getGroupById($GroupId))) {
            if(!empty($TableContent = Person::useService()->createPhoneList($tblGroup))){
                $Stage->addButton(new Primary('Herunterladen', '/Api/Reporting/Custom/Radebeul/Person/PhoneList/Download', new Download(),
                        array('GroupId' => $tblGroup->getId())));
                $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
            }

            $Stage->setContent(new Layout(array(
                new LayoutGroup(new LayoutRow(array(new LayoutColumn(
                    new Panel('Gruppe', $tblGroup->getName(), Panel::PANEL_TYPE_SUCCESS)
                )))),
                new LayoutGroup(new LayoutRow(new LayoutColumn(
                    new TableData($TableContent, null,
                        array(
                            'Division'        => 'Klasse',
                            'Title'           => 'Titel',
                            'LastName'        => 'Name',
                            'FirstName'       => 'Vorname',
                            'PhoneHome'       => 'Zuhause',
                            'PhoneMobileS1'   => 'Mutter Handy',
                            'PhoneMobileS2'   => 'Vater Handy',
                            'PhoneBusinessS1' => 'Mutter dienstl.',
                            'PhoneBusinessS2' => 'Vater dienstl.',
                            'PhoneEmergency'  => 'Notfall',
                            'Birthday'        => 'Geb.-Datum',
                        ),
                        array(
                            "pageLength" => -1,
                            "responsive" => false,
                            'order'      => array(
                                array(0, 'asc'),
                                array(1, 'asc'),
                                array(2, 'asc')
                            ),
                            "columnDefs" => array(
                                array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 2),
                                array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 3),
                            ),
                        )
                    )
                )))))
            );
        }
        return $Stage;
    }

    /**
     * @param $GroupId
     *
     * @return Stage
     */
    public function frontendKindergartenList($GroupId = null)
    {

        $Stage = new Stage('Individuelle Auswertung', 'Kinderhausliste');
        $TableContent = array();
        if ($GroupId === null) {
            $tblGroupAll = Group::useService()->getGroupAll();
            if ($tblGroupAll) {
                array_walk($tblGroupAll, function (TblGroup $tblGroup) use (&$TableContent) {
                    $item['Name'] = $tblGroup->getName();
                    $item['Option'] = new Standard('', '/Reporting/Custom/Radebeul/Person/KindergartenList', new EyeOpen(),
                        array('GroupId' => $tblGroup->getId()), 'Anzeigen');
                    array_push($TableContent, $item);
                });
            }
            $Stage->setContent(new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                new TableData($TableContent, null,
                    array(
                        'Name' => 'Gruppe',
                        'Option' => '',
                    )
                )
            , 12)), new Title(new Listing() . ' Übersicht'))));
            return $Stage;
        }
        $Stage->addButton(new Standard('Zurück', '/Reporting/Custom/Radebeul/Person/KindergartenList', new ChevronLeft()));
        if(($tblGroup = Group::useService()->getGroupById($GroupId))) {
            if(!empty($TableContent = Person::useService()->createKindergartenList($tblGroup))) {
                $Stage->addButton(new Primary('Herunterladen', '/Api/Reporting/Custom/Radebeul/Person/KindergartenList/Download', new Download(),
                        array('GroupId' => $tblGroup->getId()))
                );
                $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
            }
            $Stage->setContent(new Layout(array(
                new LayoutGroup(new LayoutRow(array(new LayoutColumn(
                    new Panel('Gruppe', $tblGroup->getName(), Panel::PANEL_TYPE_SUCCESS)
                )))),
                new LayoutGroup(new LayoutRow(new LayoutColumn(
                    new TableData($TableContent, null,
                        array(
                            'Number' => 'lfdNr.',
                            'LastName' => 'Name',
                            'FirstName' => 'Vorname',
                            'Birthday' => 'Geburtstag',
                            'Kindergarten' => 'Kinderhaus',
                        ),
                        array(
                            "columnDefs" => array(
                                array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 1),
                                array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 2),
                            ),
                            "pageLength" => -1,
                            "responsive" => false,
                        )
                    )
                )))
            )));
        }
        return $Stage;
    }

    /**
     * @param $GroupId
     *
     * @return Stage
     */
    public function frontendRegularSchoolList($GroupId = null)
    {

        $Stage = new Stage('Individuelle Auswertung', 'Stammschulenliste');
        $TableContent = array();
        if ($GroupId === null) {
            $tblGroupAll = Group::useService()->getGroupAll();
            if ($tblGroupAll) {
                array_walk($tblGroupAll, function (TblGroup $tblGroup) use (&$TableContent) {
                    $item['Name'] = $tblGroup->getName();
                    $item['Option'] = new Standard('', '/Reporting/Custom/Radebeul/Person/RegularSchoolList', new EyeOpen(),
                        array('GroupId' => $tblGroup->getId()), 'Anzeigen');
                    array_push($TableContent, $item);
                });
            }
            $Stage->setContent(new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                new TableData($TableContent, null,
                    array(
                        'Name' => 'Gruppe',
                        'Option' => '',
                    )
                )
            , 12)), new Title(new Listing() . ' Übersicht'))));
            return $Stage;
        }
        $Stage->addButton(new Standard('Zurück', '/Reporting/Custom/Radebeul/Person/RegularSchoolList', new ChevronLeft()));
        if (($tblGroup = Group::useService()->getGroupById($GroupId))) {
            if (!empty($TableContent = Person::useService()->createRegularSchoolList($tblGroup))) {
                $Stage->addButton(new Primary('Herunterladen', '/Api/Reporting/Custom/Radebeul/Person/RegularSchoolList/Download', new Download(),
                        array('GroupId' => $tblGroup->getId()))
                );
                $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
            }
            $Stage->setContent(new Layout(array(
                new LayoutGroup(new LayoutRow(new LayoutColumn(
                    new Panel('Gruppe', $tblGroup->getName(), Panel::PANEL_TYPE_SUCCESS)
                ))),
                new LayoutGroup(new LayoutRow(new LayoutColumn(
                    new TableData($TableContent, null,
                        array(
                            'Number' => 'lfdNr.',
                            'LastName' => 'Name',
                            'FirstName' => 'Vorname',
                            'RegularSchool' => 'Stammschule',
                        ),
                        array(
                            "columnDefs" => array(
                                array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 1),
                                array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 2),
                            ),
                            "pageLength" => -1,
                            "responsive" => false,
                        )
                    )
                )))
            )));
        }
        return $Stage;
    }

    /**
     * @param $GroupId
     *
     * @return Stage
     */
    public function frontendDiseaseList($GroupId = null)
    {

        $Stage = new Stage('Individuelle Auswertung', 'Allergieliste');
        $TableContent = array();
        if ($GroupId === null) {
            if(($tblGroupAll = Group::useService()->getGroupAll())) {
                array_walk($tblGroupAll, function (TblGroup $tblGroup) use (&$TableContent) {
                    $item['Name'] = $tblGroup->getName();
                    $item['Option'] = new Standard('', '/Reporting/Custom/Radebeul/Person/DiseaseList',
                        new EyeOpen(), array('GroupId' => $tblGroup->getId()), 'Anzeigen');
                    array_push($TableContent, $item);
                });
            }
            $Stage->setContent(new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                new TableData($TableContent, null,
                    array(
                        'Name' => 'Gruppe',
                        'Option' => '',
                    )
                )
            , 12)), new Title(new Listing() . ' Übersicht'))));
            return $Stage;
        }
        $Stage->addButton(new Standard('Zurück', '/Reporting/Custom/Radebeul/Person/DiseaseList', new ChevronLeft()));
        if (($tblGroup = Group::useService()->getGroupById($GroupId))) {
            if(($TableContent = Person::useService()->createDiseaseList($tblGroup))) {
                $Stage->addButton(new Primary('Herunterladen', '/Api/Reporting/Custom/Radebeul/Person/DiseaseList/Download', new Download(),
                        array('GroupId' => $tblGroup->getId()))
                );
                $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
            }
            $Stage->setContent(new Layout(array(
                new LayoutGroup(new LayoutRow(array(new LayoutColumn(
                    new Panel('Gruppe', $tblGroup->getName(), Panel::PANEL_TYPE_SUCCESS)
                )))),
                new LayoutGroup(new LayoutRow(new LayoutColumn(
                    new TableData($TableContent, null,
                        array(
                            'Division' => 'Klasse',
                            'LastName' => 'Name',
                            'FirstName' => 'Vorname',
                            'Disease' => 'Allergie',
                        ),
                        array(
                            'order' => array(
                                array(0, 'asc'),
                                array(1, 'asc'),
                                array(2, 'asc')
                            ),
                            "columnDefs" => array(
                                array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 1),
                                array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 2),
                            ),
                            "pageLength" => -1,
                            "responsive" => false
                        )
                    )
                )))
            )));
        }
        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendNursery()
    {

        $Stage = new Stage('Individuelle Auswertung', 'Stichtagsmeldung Hort');
        if (!($tblGroup = Group::useService()->getGroupByName('Hort'))) {
            $Stage->setContent(new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(new Warning('Keine Gruppe "Hort" vorhanden'))))));
        }
        // mark persons without this city code
        $PLZ = '01445';
        if(!empty($TableContent = Person::useService()->createNursery($tblGroup, $PLZ))) {
            $Stage->addButton(new Primary('Deckblatt Herunterladen', '/Api/Reporting/Custom/Radebeul/Person/Nursery/Download', new Save(),
                array('PLZ' => $PLZ)));
            $Stage->addButton(new Primary('Hortliste Herunterladen', '/Api/Reporting/Custom/Radebeul/Person/NurseryList/Download', new Save(),
                array('PLZ' => $PLZ)));
            $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
        }
        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(new LayoutRow(array(new LayoutColumn(
                    new Panel('Gruppe', $tblGroup->getName(), Panel::PANEL_TYPE_SUCCESS)
                )))),
                new LayoutGroup(new LayoutRow(new LayoutColumn(
                    (!empty($TableContent)
                        ? new TableData($TableContent, null,
                            array(
                                'Count'     => '#',
                                'LastName'  => 'Name',
                                'FirstName' => 'Vorname',
                                'Birthday'  => 'Geb.-datum',
                                'City'      => 'Wohnort',
                                'PLZ'       => 'PLZ',
                                'Street'    => 'Straße',
                            ),
                            array(
                                'order'      => array(
                                    array(0, 'asc')
                                ),
                                "columnDefs" => array(
                                    array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 1),
                                    array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 2),
                                ),
                                "responsive" => false,
                            )
                        )
                        : new Warning('Keine Schüler in der Gruppe "Hort" vorhanden'))
                )))
            ))
        );
        return $Stage;
    }
}