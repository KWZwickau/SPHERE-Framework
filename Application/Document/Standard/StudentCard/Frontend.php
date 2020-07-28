<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 27.04.2017
 * Time: 09:38
 */

namespace SPHERE\Application\Document\Standard\StudentCard;

use SPHERE\Application\Api\Document\Standard\Repository\StudentCard\ApiDownload;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
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
use SPHERE\Application\People\Meta\Student\Student;
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
     *
     * @return Stage
     */
    private static function setButtonList(Stage $Stage)
    {
        $Stage->addButton(new Standard('Einstellungen', '/Document/Standard/StudentCard/Setting', new CogWheels(),
            array(),
            'Fächer-Einstellungen für die Schülerkarteien'));
        $Stage->addButton(new Standard('Schüler', '/Document/Standard/StudentCard', new Person(),
            array(),
            'Schülerkartei eines Schülers'));
        $Stage->addButton(new Standard('Klasse', '/Document/Standard/StudentCard/Division', new PersonGroup(),
            array(),
            'Schülerkarteien einer Klasse'));

        return $Stage;
    }

    /**
     * @return Stage
     */
    public static function frontendSelectPerson()
    {
        $Stage = new Stage('Schülerkartei', 'Schüler auswählen');
        $Stage = self::setButtonList($Stage);

        $dataList = array();
        if (($tblGroup = Group::useService()->getGroupByMetaTable('STUDENT'))) {
            if (($tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup))) {
                foreach ($tblPersonList as $tblPerson) {
                    $tblAddress = $tblPerson->fetchMainAddress();
                    $dataList[] = array(
                        'Name' => $tblPerson->getLastFirstName(),
                        'Address' => $tblAddress ? $tblAddress->getGuiString() : '',
                        'Division' => Student::useService()->getDisplayCurrentDivisionListByPerson($tblPerson),
                        'Option' =>
                            new External(
                                'Herunterladen',
                                'SPHERE\Application\Api\Document\Standard\StudentCard\Create',
                                new Download(),
                                array(
                                    'PersonId' => $tblPerson->getId()
                                ),
                                'Schülerkartei herunterladen'
                            )
                    );
                }
            }
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData(
                                $dataList,
                                null,
                                array(
                                    'Name' => 'Name',
                                    'Address' => 'Adresse',
                                    'Division' => 'Klasse',
                                    'Option' => ''
                                ),
                                array(
                                    'columnDefs' => array(
                                        array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 0),
                                        array('type' => 'natural', 'targets' => array(2)),
                                        array('width' => '5%', 'orderable' => false, 'targets'   => -1),
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
     * @param null $YearId
     *
     * @return Stage
     */
    public static function frontendSelectDivision($IsAllYears = false, $YearId = null)
    {
        $Stage = new Stage('Schülerkartei', 'Klasse auswählen');
        $Stage = self::setButtonList($Stage);

        $tblYear = false;
        $tblYearList = Term::useService()->getYearByNow();
        if ($YearId) {
            $tblYear = Term::useService()->getYearById($YearId);
        } elseif (!$IsAllYears && $tblYearList) {
            $tblYear = end($tblYearList);
        }

        $Route = '/Document/Standard/StudentCard/Division';
        $buttonList = array();
        if ($tblYearList) {
            /** @var TblYear $tblYearItem */
            foreach ($tblYearList as $tblYearItem) {
                if ($tblYear && $tblYear->getId() == $tblYearItem->getId()) {
                    $buttonList[] = new Standard(new Info(new Bold($tblYearItem->getDisplayName())),
                        $Route, new Edit(), array('YearId' => $tblYearItem->getId()));
                } else {
                    $buttonList[] = new Standard($tblYearItem->getDisplayName(), $Route,
                        null, array('YearId' => $tblYearItem->getId()));
                }
            }

            if ($IsAllYears) {
                $buttonList[] = new Standard(new Info(new Bold('Alle Schuljahre')),
                    $Route, new Edit(), array('IsAllYears' => true));
            } else {
                $buttonList[] = new Standard('Alle Schuljahre', $Route, null,
                    array('IsAllYears' => true));
            }
        }

        $maxPersonCount = 15;

        if (($tblAccount = Account::useService()->getAccountBySession())
            && ($tblAccountDownloadLock = Consumer::useService()->getAccountDownloadLock($tblAccount, 'StudentCard'))
        ) {
            $isLocked = $tblAccountDownloadLock->getIsFrontendLocked();
            $isFirstRun = false;
        } else {
            $isLocked = false;
            $isFirstRun = true;
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
                        empty($buttonList) ? '' : $buttonList
                    )),
                    new LayoutRow(array(
                        new LayoutColumn(ApiDownload::receiverBlock($isFirstRun ? '' : ApiDownload::pipelineLoadTable(
                            $isLocked, $tblYear ? $tblYear->getId() : null
                        ), 'Table')),
                        new LayoutColumn(ApiDownload::receiverBlock(ApiDownload::pipelineCheckLock(
                            $tblYear ? $tblYear->getId() : null
                        ), 'CheckLock'))
                    )), new Title(new Listing() . ' Übersicht')
                ))
            ));

        return $Stage;
    }

    /**
     * @param $isLocked
     * @param $YearId
     *
     * @return TableData
     */
    public function loadTable($isLocked, $YearId)
    {
        $maxPersonCount = 15;

        $tblYear = Term::useService()->getYearById($YearId);
        $TableContent = array();
        if (($tblDivisionAll = Division::useService()->getDivisionAll())) {
            foreach ($tblDivisionAll as $tblDivision) {
                if ($tblYear && ($tblYearDivision = $tblDivision->getServiceTblYear()) && $tblYear->getId() != $tblYearDivision->getId()) {
                    continue;
                }

                $count = Division::useService()->countDivisionStudentAllByDivision($tblDivision);
                $Item['Year'] = '';
                $Item['Division'] = $tblDivision->getDisplayName();
                $Item['Type'] = $tblDivision->getTypeName();
                if ($tblDivision->getServiceTblYear()) {
                    $Item['Year'] = $tblDivision->getServiceTblYear()->getDisplayName();
                }
                $Item['Count'] = $count;

                if ($count > 0) {
                    if ($count <= $maxPersonCount) {
                        $external = (new External(
                            '',
                            '/Api/Document/Standard/StudentCard/CreateMulti',
                            new Download(),
                            array(
                                'DivisionId' => $tblDivision->getId()
                            ),
                            'Schülerkarteien herunterladen'
                        ));
                        $Item['Option'] = $isLocked
                            ? new \SPHERE\Common\Frontend\Text\Repository\Warning('Bitte warten ...')
                            : $external;
                    } else {
                        $countList = 1;
                        $Item['Option'] = '';
                        if ($isLocked) {
                            $Item['Option'] = new \SPHERE\Common\Frontend\Text\Repository\Warning('Bitte warten ...');
                        } else {
                            for ($i = 0; $i < $count; $i++) {
                                if ($i % $maxPersonCount == 0) {
                                    $name = $countList . '. Teil';
                                    $Item['Option'] .= (new External(
                                        $name,
                                        '/Api/Document/Standard/StudentCard/CreateMulti',
                                        new Download(),
                                        array(
                                            'DivisionId' => $tblDivision->getId(),
                                            'List' => $countList++
                                        ),
                                        $name . ' Schülerkarteien herunterladen'
                                    ))->__toString();
                                }
                            }
                        }
                    }
                } else {
                    $Item['Option'] = '';
                }

                array_push($TableContent, $Item);
            }
        }

        return new TableData($TableContent, null,
            array(
                'Year' => 'Jahr',
                'Division' => 'Klasse',
                'Type' => 'Schulart',
                'Count' => 'Schüler',
                'Option' => '',
            ), array(
                'columnDefs' => array(
                    array('type' => 'natural', 'targets' => array(1,3)),
                    array('orderable' => false, 'targets'   => -1),
                ),
                'order' => array(
                    array(0, 'desc'),
                    array(2, 'asc'),
                    array(1, 'asc')
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
        $Stage = self::setButtonList($Stage);

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
            if (strpos($tblDocument->getName(), 'Mittelschule') !== false){
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