<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use SPHERE\Application\Api\Education\Graduation\Grade\ApiScoreType;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreType;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Equalizer;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Group;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\MinusSign;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Link\Repository\ToggleSelective;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Repository\Sorter\StringNaturalOrderSorter;

abstract class FrontendScoreRule extends FrontendScoreGroup
{
    /**
     * @param null $ScoreRule
     *
     * @return Stage
     */
    public function frontendScoreRule($ScoreRule = null): Stage
    {
        $Stage = new Stage('Berechnungsvorschrift', 'Übersicht');
        $Stage->setMessage(
            'Hier werden die Berechnungsvorschriften, für die automatische Durchschnittsberechnung der Zensuren, verwaltet.' . '<br>' .
            'Die Berechnungsvorschrift bildet die 1. Ebene und setzt sich aus einer oder mehrerer Berechnungsvarianten
            zusammen.'
        );

        $this->setScoreStageMenuButtons($Stage, self::SCORE_RULE);

        $contentTable = array();
        $tblScoreRuleAll = Grade::useService()->getScoreRuleAll(true);
        if ($tblScoreRuleAll) {
            foreach ($tblScoreRuleAll as $tblScoreRule) {

                $structure = array();
                if ($tblScoreRule->getDescription() != '') {
                    $structure[] = 'Beschreibung: ' . $tblScoreRule->getDescription() . '<br/>';
                }
                if ($tblScoreRule->getDescriptionForExtern() != '') {
                    $structure[] = new Bold('Beschreibung für Eltern/Schülerzugänge: ') . '<br/>'
                        . str_replace("\n", '<br/>', $tblScoreRule->getDescriptionForExtern()) . '<br/>';
                }

                // todo
//                $structure = Grade::useService()->getScoreRuleStructure($tblScoreRule, $structure);

                $contentTable[] = array(
                    'Status' => $tblScoreRule->getIsActive()
                        ? new \SPHERE\Common\Frontend\Text\Repository\Success(new PlusSign() . ' aktiv')
                        : new \SPHERE\Common\Frontend\Text\Repository\Warning(new MinusSign() . ' inaktiv'),
                    'Name' => $tblScoreRule->getName(),
                    'Structure' => empty($structure) ? '' : implode('<br/>', $structure),
                    'Option' =>
                        (new Standard('', '/Education/Graduation/Grade/ScoreRule/Edit', new Edit(),
                            array('Id' => $tblScoreRule->getId()), 'Bearbeiten')) .
                        ($tblScoreRule->getIsActive()
                            ? (new Standard('', '/Education/Graduation/Grade/ScoreRule/Activate', new MinusSign(),
                                array('Id' => $tblScoreRule->getId()), 'Deaktivieren'))
                            : (new Standard('', '/Education/Graduation/Grade/ScoreRule/Activate', new PlusSign(),
                                array('Id' => $tblScoreRule->getId()), 'Aktivieren'))) .
                        ($tblScoreRule->getIsUsed()
                            ? ''
                            : (new Standard('', '/Education/Graduation/Grade/ScoreRule/Destroy', new Remove(),
                                array('Id' => $tblScoreRule->getId()), 'Löschen'))) .
                        ($tblScoreRule->getIsActive() ?
                            (new Standard('', '/Education/Graduation/Grade/ScoreRule/Condition/Select', new Listing(),
                                array('Id' => $tblScoreRule->getId()), 'Berechnungsvarianten auswählen')) .
                            (new Standard('', '/Education/Graduation/Grade/ScoreRule/Division', new Equalizer(),
                                array('Id' => $tblScoreRule->getId()), 'Fach-Klassen zuordnen')) .
                            (new Standard('', '/Education/Graduation/Grade/ScoreRule/SubjectGroup', new Group(),
                                array('Id' => $tblScoreRule->getId()), 'Fachgruppen zuordnen'))
                            : '')
                );
            }
        }

        $Form = $this->formScoreRule()
            ->appendFormButton(new \SPHERE\Common\Frontend\Form\Repository\Button\Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData($contentTable, null, array(
                                'Status' => 'Status',
                                'Name' => 'Name',
                                'Structure' => '',
                                'Option' => '',
                            ), array(
                                'order' => array(
                                    array('0', 'asc'),
                                    array('1', 'asc'),
                                ),
                                'columnDefs' => array(
                                    array('orderable' => false, 'width' => '215px', 'targets' => -1),
                                ),
                            ))
                        ))
                    ))
                ), new Title(new ListingTable() . ' Übersicht')),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            // todo
//                            new Well(Grade::useService()->createScoreRule($Form, $ScoreRule))
                        ))
                    ))
                ), new Title(new PlusSign() . ' Hinzufügen'))
            ))
        );

        return $Stage;
    }

    /**
     * @return Form
     */
    private function formScoreRule(): Form
    {
        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new TextField('ScoreRule[Name]', '', 'Name'), 4
                ),
                new FormColumn(
                    new TextField('ScoreRule[Description]', '', 'Beschreibung'), 8
                ),
            )),
            new FormRow(array(
                new FormColumn(
                    new TextArea('ScoreRule[DescriptionForExtern]', '', 'Beschreibung für Eltern/Schüler-Zugänge')
                )
            ))
        )));
    }
}