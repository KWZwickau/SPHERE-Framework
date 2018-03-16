<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 15.03.2018
 * Time: 09:25
 */

namespace SPHERE\Application\Education\Certificate\Prepare\Abitur;

use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Cog;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Education\Certificate\Prepare\Abitur
 */
class Frontend
{
    /**
     * @param null $PrepareId
     * @param null $GroupId
     *
     * @return Stage|string
     */
    public function frontendPrepareDiplomaAbiturPreview($PrepareId = null, $GroupId = null)
    {

        $stage = new Stage('Zeugnisvorbereitungen', 'Übersicht');

        $isGroup = false;
        $tblGroup = false;
        $tblPrepare = Prepare::useService()->getPrepareById($PrepareId);
        $tblPrepareList = false;

        if ($tblPrepare && ($tblGroup = Group::useService()->getGroupById($GroupId))) {
            $isGroup = true;
            $stage->addButton(new Standard(
                'Zurück', '/Education/Certificate/Prepare/Prepare', new ChevronLeft(), array(
                    'GroupId' => $tblGroup->getId(),
                    'Route' => 'Diploma'
                )
            ));
            $description = $tblGroup->getName();
            if (($tblGenerateCertificate = $tblPrepare->getServiceTblGenerateCertificate())) {
                $tblPrepareList = Prepare::useService()->getPrepareAllByGenerateCertificate($tblGenerateCertificate);
            }
        } elseif ($tblPrepare && ($tblDivision = $tblPrepare->getServiceTblDivision())) {
            $stage->addButton(new Standard(
                'Zurück', '/Education/Certificate/Prepare/Prepare', new ChevronLeft(), array(
                'DivisionId' => $tblDivision->getId(),
                'Route' => 'Diploma'
            )));
            $description = $tblDivision->getDisplayName();
            $tblPrepareList = array(0 => $tblPrepare);
        } else {
            return $stage . new Danger('Zeugnisvorbereitung nicht gefunden.', new Ban())
                . new Redirect('/Education/Certificate/Prepare', Redirect::TIMEOUT_ERROR);
        }

        $headerColumns = array();
        if ($tblPrepare) {
            $headerColumns[] = new LayoutColumn(
                new Panel(
                    $isGroup ? 'Gruppe' : 'Klasse',
                    $description,
                    Panel::PANEL_TYPE_INFO
                )
                , 3);

            if (($tblGenerateCertificate = $tblPrepare->getServiceTblGenerateCertificate())) {
                $tblCertificateType = $tblGenerateCertificate->getServiceTblCertificateType();
                $headerColumns[] = new LayoutColumn(
                    new Panel(
                        'Zeugnisdatum',
                        $tblGenerateCertificate->getDate(),
                        Panel::PANEL_TYPE_INFO
                    )
                    , 3);
                $headerColumns[] = new LayoutColumn(
                    new Panel(
                        'Typ',
                        $tblCertificateType ? $tblCertificateType->getName() : '',
                        Panel::PANEL_TYPE_INFO
                    )
                    , 3);
                $headerColumns[] = new LayoutColumn(
                    new Panel(
                        'Name',
                        $tblGenerateCertificate->getName(),
                        Panel::PANEL_TYPE_INFO
                    )
                    , 3);
            }
        }

        if ($tblPrepareList) {
            $studentTable = array();
            $count = 1;
            foreach ($tblPrepareList as $tblPrepareItem) {
                if (($tblDivisionItem = $tblPrepareItem->getServiceTblDivision())
                    && ($tblStudentList = Division::useService()->getStudentAllByDivision($tblDivisionItem))
                ) {
                    foreach ($tblStudentList as $tblPerson) {
                        if (!$isGroup || ($tblGroup && Group::useService()->existsGroupPerson($tblGroup, $tblPerson))) {
                            $tblCertificate = false;
                            if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepareItem,
                                $tblPerson))
                            ) {
                                $tblCertificate = $tblPrepareStudent->getServiceTblCertificate();
                            }

                            $studentTable[] = array(
                                'Number' => $count++,
                                'Name' => $tblPerson->getLastFirstName(),
                                'Division' => $tblDivisionItem->getDisplayName(),
                                'Option' => ($tblCertificate
                                    ?
                                    (new Standard(
                                        'Block I', '/Education/Certificate/Prepare/Prepare/Diploma/Abitur/BlockI',
                                        new Cog(),
                                        array(
                                            'PrepareId' => $tblPrepare->getId(),
                                            'GroupId' => $tblGroup ? $tblGroup->getId() : null,
                                            'PersonId' => $tblPerson->getId(),
                                        ),
                                        'Block I bearbeiten und anzeigen'))
                                    . (new Standard(
                                        'Block II', '/Education/Certificate/Prepare/Prepare/Diploma/Abitur/BlockII',
                                        new Cog(),
                                        array(
                                            'PrepareId' => $tblPrepare->getId(),
                                            'GroupId' => $tblGroup ? $tblGroup->getId() : null,
                                            'PersonId' => $tblPerson->getId(),
                                        ),
                                        'Block II bearbeiten und anzeigen'))
                                    // todo remove
                                    . (new Standard(
                                        '', '/Education/Certificate/Prepare/Certificate/Show', new EyeOpen(),
                                        array(
                                            'PrepareId' => $tblPrepare->getId(),
                                            'GroupId' => $tblGroup ? $tblGroup->getId() : null,
                                            'PersonId' => $tblPerson->getId(),
                                            'Route' => 'Diploma'
                                        ),
                                        'Zeugnisvorschau anzeigen'))
                                    . (new External(
                                        '',
                                        '/Api/Education/Certificate/Generator/Preview',
                                        new Download(),
                                        array(
                                            'PrepareId' => $tblPrepare->getId(),
                                            'PersonId' => $tblPerson->getId(),
                                            'Name' => 'Zeugnismuster'
                                        ),
                                        'Zeugnis als Muster herunterladen'))
                                    : '')
                            );
                        }
                    }
                }
            }

            $table = new TableData(
                $studentTable,
                null,
                array(
                    'Number' => '#',
                    'Name' => 'Name',
                    'Division' => 'Klasse',
                    'Option' => ' '
                ),
                array(
                    'columnDefs' => array(
                        array(
                            "width" => "7px",
                            "targets" => 0
                        ),
                        array(
                            "width" => "200px",
                            "targets" => 1
                        ),
                        array(
                            "width" => "80px",
                            "targets" => 2
                        ),
                    ),
                    'order' => array(
                        array('0', 'asc'),
                    ),
                    "paging" => false, // Deaktivieren Blättern
                    "iDisplayLength" => -1,    // Alle Einträge zeigen
//                    "searching" => false, // Deaktivieren Suchen
                    "info" => false,  // Deaktivieren Such-Info
                    "sort" => false,
                    "responsive" => false
                )
            );

            $stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(
                            $headerColumns
                        )
                    )),
                    new LayoutGroup(array(
                        new LayoutRow(
                            new LayoutColumn($table)
                        )
                    ), new Title('Übersicht')),
                ))
            );
        }

        return $stage;
    }


    /**
     * @param null $PrepareId
     * @param null $GroupId
     * @param null $PersonId
     * @param int $View
     *
     * @return Stage
     */
    public function frontendPrepareDiplomaAbiturBlockI(
        $PrepareId = null,
        $GroupId = null,
        $PersonId = null,
        $View = BlockIView::PREVIEW
    ) {

        $stage = new Stage('Abiturzeugnis', 'Block I: Ergebnisse in der Qualifikationsphase');
        $stage->addButton(
            new Standard('Zurück', '/Education/Certificate/Prepare/Prepare/Diploma/Abitur/Preview', new ChevronLeft(),
                array(
                    'PrepareId' => $PrepareId,
                    'GroupId' => $GroupId,
                    'Route' => 'Diploma'
                ))
        );

        if (($tblPerson = Person::useService()->getPersonById($PersonId))
            && ($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
            && ($tblDivision = $tblPrepare->getServiceTblDivision())
        ) {
            $blockI = new BlockI($tblDivision, $tblPerson, $tblPrepare, $View);

            $form = $blockI->getForm();

            $stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel(
                                    'Schüler',
                                    $tblPerson ? $tblPerson->getLastFirstName() : '',
                                    Panel::PANEL_TYPE_INFO
                                ),
                                new Standard('Punkte bearbeiten',
                                    '/Education/Certificate/Prepare/Prepare/Diploma/Abitur/BlockI',
                                    new Edit(), array(
                                        'PrepareId' => $PrepareId,
                                        'GroupId' => $GroupId,
                                        'PersonId' => $PersonId,
                                        'View' => BlockIView::EDIT_GRADES
                                    )
                                ),
                                new Standard('Kurse einbringen',
                                    '/Education/Certificate/Prepare/Prepare/Diploma/Abitur/BlockI',
                                    new Check(), array(
                                        'PrepareId' => $PrepareId,
                                        'GroupId' => $GroupId,
                                        'PersonId' => $PersonId,
                                        'View' => BlockIView::CHOOSE_COURSES
                                    )
                                ),
                            ))
                        )),
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                '<br>',
                                $form
                            ))
                        ))
                    ))
                ))
            );
        }

        return $stage;
    }

    /**
     * @param null $PrepareId
     * @param null $GroupId
     * @param null $PersonId
     *
     * @return Stage
     */
    public function frontendPrepareDiplomaAbiturBlockII(
        $PrepareId = null,
        $GroupId = null,
        $PersonId = null
    ) {

        $stage = new Stage('Abiturzeugnis', 'Block II: Ergebnisse in der Abiturprüfung');
        $stage->addButton(
            new Standard('Zurück', '/Education/Certificate/Prepare/Prepare/Diploma/Abitur/Preview', new ChevronLeft(),
                array(
                    'PrepareId' => $PrepareId,
                    'GroupId' => $GroupId,
                    'Route' => 'Diploma'
                ))
        );

        if (($tblPerson = Person::useService()->getPersonById($PersonId))
            && ($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
            && ($tblDivision = $tblPrepare->getServiceTblDivision())
        ) {
            $blockII = new BlockII($tblDivision, $tblPerson, $tblPrepare);

            $stage->setContent(
                $blockII->getContent()
            );
        }

        return $stage;
    }
}