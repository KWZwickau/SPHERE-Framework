<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 27.04.2017
 * Time: 09:38
 */

namespace SPHERE\Application\Document\Standard\StudentCard;

use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Well;
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

class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public static function frontendSelectPerson()
    {

        $Stage = new Stage('Schülerkartei', 'Schüler auswählen');
        $Stage->addButton(new Standard('Einstellungen', '/Document/Standard/StudentCard/Setting', new CogWheels(),
            array(),
            'Fächer-Einstellungen für die Schülerkarteien'));

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
                                    "columnDefs" => array(
                                        array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 0),
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
     * @return Stage|string
     */
    public function frontendSelectStudentCard()
    {

        $Stage = new Stage('Schülerkartei Einstellungen', 'Schülerkartei auswählen');
        $Stage->addButton(new Standard(
            'Zurück', '/Document/Standard/StudentCard', new ChevronLeft()
        ));


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
            }
            if (strpos($tblDocument->getName(), 'Gymnasium') !== false){
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