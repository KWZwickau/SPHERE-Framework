<?php
namespace SPHERE\Application\Document\Standard\StudentCard;

use SPHERE\Application\Api\Document\Standard\Repository\StudentCard\ApiDownload;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\Person;
use SPHERE\Common\Frontend\Icon\Repository\PersonGroup;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\System\Extension\Extension;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Group\Group;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\CogWheels;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Document\Standard\StudentCard
 */
class Frontend extends Extension implements IFrontendInterface
{
    /**
     * @param Stage $Stage
     */
    private static function setButtonList(Stage $Stage)
    {
        $Stage->addButton(new Standard('Einstellungen', '/Document/Standard/StudentCard/Setting', new CogWheels(),
            array(), 'Fächer-Einstellungen für die Schülerkarteien'));
        $Stage->addButton(new Standard('Schüler', '/Document/Standard/StudentCard', new Person(),
            array(), 'Schülerkartei eines Schülers'));
        $Url = $_SERVER['REDIRECT_URL'];
        if(strpos($Url, '/StudentCard/Division')){
            $Stage->addButton(new Standard(new Info(new Bold('Kurs')), '/Document/Standard/StudentCard/Division',
                new PersonGroup(), array(), 'Schülerkarteien eines Kurses'));
        } else {
            $Stage->addButton(new Standard('Kurs', '/Document/Standard/StudentCard/Division', new PersonGroup(),
                array(), 'Schülerkarteien eines Kurses'));
        }
    }

    /**
     * @return Stage
     */
    public static function frontendSelectPerson() : Stage
    {
        $Stage = new Stage('Schülerkartei', 'Schüler auswählen');
        self::setButtonList($Stage);

        $dataList = array();
        $showDivision = false;
        $showCoreGroup = false;
        if (($tblGroup = Group::useService()->getGroupByMetaTable('STUDENT'))
            && ($tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup))
        ) {
            foreach ($tblPersonList as $tblPerson) {
                $displayDivision = '';
                $displayCoreGroup = '';
                if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndDate($tblPerson))){
                    if (($tblDivision = $tblStudentEducation->getTblDivision())
                        && ($displayDivision = $tblDivision->getName())
                    ) {
                        $showDivision = true;
                    }
                    if (($tblCoreGroup = $tblStudentEducation->getTblCoreGroup())
                        && ($displayCoreGroup = $tblCoreGroup->getName())
                    ) {
                        $showCoreGroup = true;
                    }
                }
                $tblAddress = $tblPerson->fetchMainAddress();
                $dataList[] = array(
                    'Name'     => $tblPerson->getLastFirstName(),
                    'Address'  => $tblAddress ? $tblAddress->getGuiString() : '',
                    'Division' => $displayDivision,
                    'CoreGroup' => $displayCoreGroup,
                    'Option' =>
                        new External('Herunterladen', '/Api/Document/Standard/StudentCardNew/Create',
                            new Download(), array('PersonId' => $tblPerson->getId()), 'Schülerkartei herunterladen')
                        . new External('(Alt)', '/Api/Document/Standard/StudentCard/Create',
                            new Download(), array('PersonId' => $tblPerson->getId()), 'Schülerkartei (Alt))')
                );
            }
        }

        $columnList['Name'] = 'Name';
        $columnList['Address'] = 'Adresse';
        if ($showDivision) {
            $columnList['Division'] = 'Klasse';
        }
        if ($showCoreGroup) {
            $columnList['CoreGroup'] = 'Stammgruppe';
        }
        $columnList['Option'] = '';

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData(
                                $dataList,
                                null,
                                $columnList,
                                array(
                                    "columnDefs" => array(
                                        array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 0),
                                        array('orderable' => false, 'width' => '200px', 'targets' => -1),
                                    ),
                                    'responsive' => false
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
     * @param bool $IsAllYears
     * @param string|null $YearId
     *
     * @return Stage
     */
    public static function frontendSelectDivision(bool $IsAllYears = false, ?string $YearId = null) : Stage
    {
        $Stage = new Stage('Schülerkartei', 'Kurs auswählen');
        self::setButtonList($Stage);

        list($yearButtonList, $filterYearList)
            = Term::useFrontend()->getYearButtonsAndYearFilters('/Document/Standard/StudentCard/Division', $IsAllYears, $YearId);

        $maxPersonCount = 15;

        if (($tblAccount = Account::useService()->getAccountBySession())
            && ($tblAccountDownloadLock = Consumer::useService()->getAccountDownloadLock($tblAccount, 'StudentCard'))
        ) {
            $isLocked = $tblAccountDownloadLock->getIsFrontendLocked();
        } else {
            $isLocked = false;
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            new Warning(
                                'Die Erstellung der Schülerkarteien über eine Klasse kann sehr lange dauern, deswegen
                                 erfolgt eine Aufteilung ab ' . $maxPersonCount . ' Schülern über mehrere Downloads.
                                 Jeder Nutzer kann immer nur einen Download starten.'
                            )
                        )
                    ),
                    new LayoutRow(new LayoutColumn(
                        empty($yearButtonList) ? '' : $yearButtonList
                    )),
                    new LayoutRow(array(
                        new LayoutColumn(ApiDownload::receiverBlock(ApiDownload::pipelineLoadTable(
                            $isLocked, $filterYearList), 'Table')),
                    )), new Title(new Listing() . ' Übersicht')
                ))
            ));

        return $Stage;
    }

    /**
     * @param bool $isLocked
     * @param array|null $filterYearList
     *
     * @return TableData
     */
    public function loadTable(bool $isLocked, ?array $filterYearList): TableData
    {
        $maxPersonCount = 15;
        $dataList = array();
        $tblDivisionCourseList = array();

        if ($filterYearList) {
            foreach ($filterYearList as $yearId => $value) {
                if (($tblYear = Term::useService()->getYearById($yearId))) {
                    if (($tblDivisionCourseListDivision = DivisionCourse::useService()->getDivisionCourseListBy($tblYear,
                        TblDivisionCourseType::TYPE_DIVISION))) {
                        $tblDivisionCourseList = $tblDivisionCourseListDivision;
                    }
                    if (($tblDivisionCourseListCoreGroup = DivisionCourse::useService()->getDivisionCourseListBy($tblYear,
                        TblDivisionCourseType::TYPE_CORE_GROUP))) {
                        $tblDivisionCourseList = array_merge($tblDivisionCourseList, $tblDivisionCourseListCoreGroup);
                    }
                }
            }
        } else {
            if (($tblDivisionCourseListDivision = DivisionCourse::useService()->getDivisionCourseListBy(null, TblDivisionCourseType::TYPE_DIVISION))) {
                $tblDivisionCourseList = $tblDivisionCourseListDivision;
            }
            if (($tblDivisionCourseListCoreGroup = DivisionCourse::useService()->getDivisionCourseListBy(null, TblDivisionCourseType::TYPE_CORE_GROUP))) {
                $tblDivisionCourseList = array_merge($tblDivisionCourseList, $tblDivisionCourseListCoreGroup);
            }
        }

        /** @var TblDivisionCourse $tblDivisionCourse */
        foreach ($tblDivisionCourseList as $tblDivisionCourse) {
            $count = $tblDivisionCourse->getCountStudents();
            $option = '';
            if ($count > 0) {
                if ($count <= $maxPersonCount) {
                    $external =
                        new External('', '/Api/Document/Standard/StudentCardNew/CreateMulti', new Download(),
                            array('DivisionCourseId' => $tblDivisionCourse->getId()), 'Schülerkarteien herunterladen')
                        . new External('(Alt)', '/Api/Document/Standard/StudentCard/CreateMulti', new Download(),
                            array('DivisionCourseId' => $tblDivisionCourse->getId()), 'Schülerkarteien (Alt)');
                    $option = $isLocked
                        ? new \SPHERE\Common\Frontend\Text\Repository\Warning('Bitte warten ...')
                        : $external;
                } else {
                    $countList = 1;
                    if ($isLocked) {
                        $option = new \SPHERE\Common\Frontend\Text\Repository\Warning('Bitte warten ...');
                    } else {
                        for ($i = 0; $i < $count; $i++) {
                            if ($i % $maxPersonCount == 0) {
                                $name = $countList . '. Teil';
                                $option .= (new External(
                                    $name, '/Api/Document/Standard/StudentCardNew/CreateMulti', new Download(),
                                    array(
                                        'DivisionCourseId' => $tblDivisionCourse->getId(),
                                        'List' => $countList++
                                    ), $name . ' Schülerkarteien herunterladen'
                                ))->__toString();
                            }
                        }
                        $countList = 1;
                        for ($j = 0; $j < $count; $j++) {
                            if ($j % $maxPersonCount == 0) {
                                $option .= (new External($countList.
                                    '. Teil (Alt)', '/Api/Document/Standard/StudentCard/CreateMulti', new Download(),
                                    array(
                                        'DivisionCourseId' => $tblDivisionCourse->getId(),
                                        'List' => $countList++
                                    ), $countList.' Teil Schülerkarteien (Alt)'
                                ))->__toString();
                            }
                        }
                    }
                }
            }

            $dataList[] = array(
                'Year' => $tblDivisionCourse->getYearName(),
                'DivisionCourse' => $tblDivisionCourse->getDisplayName(),
                'DivisionCourseType' => $tblDivisionCourse->getTypeName(),
                'SchoolTypes' => $tblDivisionCourse->getSchoolTypeListFromStudents(true),
                'Count' => $count,
                'Option' => $option
            );
        }

        return new TableData($dataList, null,
            array(
                'Year' => 'Schuljahr',
                'DivisionCourse' => 'Kurs',
                'DivisionCourseType' => 'Kurs-Typ',
                'SchoolTypes' => 'Schularten',
                'Count' => 'Schüler',
                'Option' => '',
            ), array(
                'order' => array(
                    array('0', 'desc'),
                    array('1', 'asc'),
                ),
                'columnDefs' => array(
                    array('type' => 'natural', 'targets' => 1),
                    array('orderable' => false, 'targets' => -1)
                ),
                'responsive' => false
            ));
    }

    /**
     * @return Stage|string
     */
    public function frontendSelectStudentCard()
    {
        $Stage = new Stage('Schülerkartei Einstellungen', 'Schülerkartei auswählen');
        self::setButtonList($Stage);

        if (($tblDocumentAll = StudentCard::useService()->getDocumentAll())) {
            $contentList = array();
            foreach ($tblDocumentAll as $tblDocument) {
                $contentList[] = array(
                    'Name' => $tblDocument->getName(),
                    'Option' => new Standard('', '/Document/Standard/StudentCard/Setting/Subjects', new Select(), array(
                        'Id' => $tblDocument->getId()
                    ), 'Schülerkartei auswählen')
                );
            }

            $content = new TableData($contentList, null, array(
                'Name' => 'Name',
                'Option' => ''
            ), array(
                'columnDefs' => array(
                    array('width' => '1%', 'targets' => 1),
                )
            ));

            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn($content)
                        ), new Title('Verfügbare Schülerkarteien')
                    )
                )
            );
        } else {
            return $Stage . new Warning('Keine Schülerkarteien vorhanden', new Ban());
        }

        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendStudentCardSubjects($Id = null, $Data = null)
    {
        $Stage = new Stage('Schülerkartei Einstellungen', 'Fächer zuweisen');
        $Stage->addButton(new Standard(
            'Zurück', '/Document/Standard/StudentCard/Setting', new ChevronLeft()
        ));

        if (($tblDocument = StudentCard::useService()->getDocumentById($Id))) {
            if ($Data == null) {
                if (($tblDocumentSubjects = StudentCard::useService()->getDocumentSubjectListByDocument($tblDocument))) {
                    $Global = $this->getGlobal();
                    foreach ($tblDocumentSubjects as $tblDocumentSubject) {
                        if (($tblSubject = $tblDocumentSubject->getServiceTblSubject())) {
                            $Global->POST['Data'][$tblDocumentSubject->getRanking()]['Subject'] = $tblSubject->getId();
                            $Global->POST['Data'][$tblDocumentSubject->getRanking()]['IsEssential'] = $tblDocumentSubject->isEssential();
                        }
                    }

                    $Global->savePost();
                }
            }

            $subjectList = array();
            $tblSubjectAll = Subject::useService()->getSubjectAll();
            if (strpos($tblDocument->getName(), TblType::IDENT_OBER_SCHULE) !== false){
                if (($tblSetting = Consumer::useService()->getSetting(
                        'Api',
                        'Education',
                        'Certificate',
                        'OrientationAcronym'
                    ))
                    && $tblSetting->getValue()
                ) {
                    $subjectList = $tblSubjectAll;
                } else {
                    $orientationSubject = Subject::useService()->getPseudoOrientationSubject();

                    if ($tblSubjectAll) {
                        foreach ($tblSubjectAll as $tblSubject) {
                            // eigentliche NKs und Profile ausblenden
                            if ((!Subject::useService()->isOrientation($tblSubject))
                                && (!Subject::useService()->isProfile($tblSubject))
                            ) {
                                $subjectList[] = $tblSubject;
                            }
                        }
                    }

                    $subjectList[] = $orientationSubject;
                }
            } elseif (strpos($tblDocument->getName(), 'Gymnasium') !== false){
                if (($tblSetting = Consumer::useService()->getSetting(
                        'Api',
                        'Education',
                        'Certificate',
                        'ProfileAcronym'
                    ))
                    && $tblSetting->getValue()
                ) {
                    $subjectList = $tblSubjectAll;
                } else {
                    $profileSubject = Subject::useService()->getPseudoProfileSubject();

                    if ($tblSubjectAll) {
                        foreach ($tblSubjectAll as $tblSubject) {
                            // eigentliche NKs und Profile ausblenden
                            if ((!Subject::useService()->isOrientation($tblSubject))
                                && (!Subject::useService()->isProfile($tblSubject))
                            ) {
                                $subjectList[] = $tblSubject;
                            }
                        }
                    }

                    $subjectList[] = $profileSubject;
                }
            } elseif (strpos($tblDocument->getName(), 'Grundschule') !== false){
                if ($tblSubjectAll) {
                    foreach ($tblSubjectAll as $tblSubject) {
                        // NKs und Profile ausblenden
                        if ((!Subject::useService()->isOrientation($tblSubject))
                            && (!Subject::useService()->isProfile($tblSubject))
                        ) {
                            $subjectList[] = $tblSubject;
                        }
                    }
                }
            } else {
                $subjectList = $tblSubjectAll;
            }

            $contentList = array();

            for ($i = 1; $i <= 19; $i++) {
                $contentList[] = new Panel(
                    $i . '. Fach',
                    array(
                        new SelectBox('Data[' . $i . '][Subject]', 'Fach',
                            array('{{ Acronym }} - {{ Name }}' => $subjectList)
                        ),
                        new CheckBox('Data[' . $i . '][IsEssential]',
                            'Muss immer ausgewiesen werden', 1),
                    )
                );
            }

            $form = new Form(array(
                new FormGroup(array(
                    new FormRow(array(
                        new FormColumn(
                            $contentList
                        )
                    ))
                ))
            ));

            $form->appendFormButton(
                new Primary('Speichern', new Save())
            );

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel(
                                    'Schülerkartei',
                                    $tblDocument->getName(),
                                    Panel::PANEL_TYPE_INFO
                                ),
                                new Well(
                                    StudentCard::useService()->createDocumentSubjects($form, $tblDocument, $Data)
                                )
                            ))
                        ))
                    ))
                ))
            );

            return $Stage;
        } else {

            return $Stage . new Danger('Schülerkartei nicht gefunden', new Ban());
        }
    }
}