<?php
namespace SPHERE\Application\Education\Certificate\Setting;

use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Service;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Title;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Setting extends Extension implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Einstellungen'))
        );

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendDashboard'
        ));
    }


    /**
     * @return \SPHERE\Application\Education\Certificate\Generator\Service
     */
    public static function useService()
    {

        return Generator::useService();
    }

    /**
     * @return Frontend
     */
    public static function useFrontend()
    {

        return new Frontend();
    }

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Dashboard', 'Einstellungen');

        $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('BEHAVIOR');
        $tblGradeTypeBehavior = Gradebook::useService()->getGradeTypeAllByTestType($tblTestType);

        $tblSubjectAll = Subject::useService()->getSubjectAll();

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Form(array(
                                new FormGroup(array(
                                    new FormRow(array(
                                        new FormColumn(new Panel('Linke Spalte', array(
                                            new SelectBox('Behavior[1][1]', 'Betragen',
                                                array('{{ Code }} - {{ Name }}' => $tblGradeTypeBehavior)
                                            ),
                                            new SelectBox('Behavior[1][2]', 'Fleiß',
                                                array('{{ Code }} - {{ Name }}' => $tblGradeTypeBehavior)
                                            ),
                                        )), 6),
                                        new FormColumn(new Panel('Rechte Spalte', array(
                                            new SelectBox('Behavior[2][1]', 'Mitarbeit',
                                                array('{{ Code }} - {{ Name }}' => $tblGradeTypeBehavior)
                                            ),
                                            new SelectBox('Behavior[2][2]', 'Ordnung',
                                                array('{{ Code }} - {{ Name }}' => $tblGradeTypeBehavior)
                                            ),
                                        )), 6),
                                    ))
                                ), new Title('Kopfnoten')),
                                new FormGroup(array(
                                    new FormRow(array(
                                        new FormColumn(
                                            $this->getSubjectLane(1, 'Linke Spalte', $tblSubjectAll)
                                            , 6),
                                        new FormColumn(
                                            $this->getSubjectLane(2, 'Rechte Spalte', $tblSubjectAll)
                                            , 6),
                                    )),
                                ), new Title('Fachnoten')),
                                new FormGroup(array(
                                    new FormRow(array(
                                        new FormColumn(array(
                                            new Panel('', array(
                                                new SelectBox('Subject[2][3]', '',
                                                    array('{{ Acronym }} - {{ Name }}' => $tblSubjectAll)
                                                ),
                                                new CheckBox('Subject[2][3][IsEssential]', 'Must have', 1),
                                            ))
                                        )),
                                    )),
                                ), new Title('Speziell'))
                            ))
                        )
                    )
                )
            )
        );

        return $Stage;
    }

    private function getSubjectLane($LaneIndex, $LaneTitle, $tblSubjectAll)
    {

        $LaneLength = floor(( count($tblSubjectAll) + 1 ) / 2);

        $Result = array();

        for ($Run = 1; $Run < $LaneLength; $Run++) {
            array_push($Result,
                new Panel(( $Run == 1 ? $LaneTitle : '' ), array(
                    new SelectBox('Subject['.$LaneIndex.']['.$Run.']', 'Fach',
                        array('{{ Acronym }} - {{ Name }}' => $tblSubjectAll)
                    ),
                    new SelectBox('Release['.$LaneIndex.']['.$Run.']', 'Befreihung',
                        array()
                    ),
                    new CheckBox('Subject['.$LaneIndex.']['.$Run.'][IsEssential]', 'Muss immer ausgewiesen werden', 1),
                ))
            );
        }

        return $Result;
    }
}
