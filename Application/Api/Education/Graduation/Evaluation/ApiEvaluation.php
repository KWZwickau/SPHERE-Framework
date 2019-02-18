<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 30.11.2018
 * Time: 08:41
 */

namespace SPHERE\Application\Api\Education\Graduation\Evaluation;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTest;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount\SelectBoxItem;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\AbstractReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Sorter\DateTimeSorter;

/**
 * Class ApiEvaluation
 *
 * @package SPHERE\Application\Api\Education\Graduation\Evaluation
 */
class ApiEvaluation extends Extension implements IApiInterface
{

    use ApiTrait;

    /**
     * @param string $Method
     *
     * @return string
     */
    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);
        $Dispatcher->registerMethod('loadTestPlanning');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param string $Content
     *
     * @return BlockReceiver
     */
    public static function receiverContent($Content = '')
    {

        return new BlockReceiver($Content);
    }

    /**
     * @param AbstractReceiver $Receiver
     * @param $Data
     * @param $IsDivisionTeacher
     * @param $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineCreateTestPlanningContent(AbstractReceiver $Receiver, $Data, $IsDivisionTeacher, $PersonId)
    {

        $FieldPipeline = new Pipeline(false);
        $FieldEmitter = new ServerEmitter($Receiver, self::getEndpoint());
        $FieldEmitter->setGetPayload(array(
            self::API_TARGET => 'loadTestPlanning'
        ));
        $FieldEmitter->setPostPayload(array(
            'Data' => $Data,
            'IsDivisionTeacher' => $IsDivisionTeacher,
            'PersonId' => $PersonId
        ));
        $FieldPipeline->appendEmitter($FieldEmitter);
        $FieldPipeline->setLoadingMessage('Leistungsüberprüfungen werden aktualisiert');

        return $FieldPipeline;
    }

    /**
     * @param null $Data
     * @param null $IsDivisionTeacher
     * @param null $PersonId
     *
     * @return Layout|string
     */
    public function loadTestPlanning($Data = null, $IsDivisionTeacher = null, $PersonId = null)
    {
        $IsDivisionTeacher = $IsDivisionTeacher === 'true';
        if ($Data === null) {
            return '';
        }

        if (!($tblYear = Term::useService()->getYearById($Data['Year']))) {
            return new Warning('Bitte wählen Sie ein Schuljahr aus!', new Exclamation());
        }

        $tblGradeType = false;
        $isHighlighted = false;
        if ($Data['GradeType'] < 0) {
            if ($Data['GradeType'] == -SelectBoxItem::HIGHLIGHTED_IS_HIGHLIGHTED) {
                $isHighlighted = true;
            }
        } elseif (($tblGradeType = Gradebook::useService()->getGradeTypeById($Data['GradeType']))) {

        } else {
            return new Warning('Bitte wählen Sie einen Zensuren-Typ aus!', new Exclamation());
        }

        if ($Data['Option']  == 2) {
            $dateTime = new \DateTime('now');
        } elseif ($Data['Option']  == 1) {
            $dateTime = null;
        } else {
            return new Warning('Bitte wählen Sie eine Option aus!', new Exclamation());
        }
        $nowWeek = date('W');
        $nowYear = (new \DateTime('now'))->format('Y');

        $tblType = Type::useService()->getTypeById($Data['Type']);

        if (isset($Data['DivisionName']) && ($divisionName = $Data['DivisionName']) && $divisionName != '') {
            $tblDivisionList = Division::useService()->getDivisionAllByName($divisionName, $tblYear, $tblType ? $tblType : null);
            if (empty($tblDivisionList)) {
                return new Warning('Klasse nicht gefunden', new Exclamation());
            }
        } else {
            if ($tblType) {
                $tblDivisionList = Division::useService()->getDivisionAllByYearAndType($tblYear, $tblType);
            } else {
                $tblDivisionList = Division::useService()->getDivisionAllByYear($tblYear);
            }
        }

        // Klassenlehrer können nur ihre eigenen Klassen sehen
        if ($IsDivisionTeacher && $tblDivisionList) {
            $tempDivisionList = array();
            if (($tblPerson = Person::useService()->getPersonById($PersonId))) {
                foreach ($tblDivisionList as $tblDivision) {
                    if (Division::useService()->getDivisionTeacherByDivisionAndTeacher($tblDivision, $tblPerson)) {
                        $tempDivisionList[] = $tblDivision;
                    }
                }
            }

            $tblDivisionList = empty($tempDivisionList) ? false : $tempDivisionList;
        }

        if ($tblDivisionList
            && ($tblTestList = Evaluation::useService()->getTestListForPlanning($tblDivisionList, $tblGradeType ? $tblGradeType : null, $isHighlighted))
        ) {
            $testArray = array();

            $tblTestList = $this->getSorter($tblTestList)->sortObjectBy('Date', new DateTimeSorter());
            /** @var TblTest $tblTest */
            foreach($tblTestList as $tblTest) {
                if (($date = $tblTest->getDate()) || ($date = $tblTest->getFinishDate())) {
                    // Tests ab der aktuellen Woche
                    if ($dateTime) {
                        $dateWeek = date('W', strtotime($date));
                        $dateYear = (new \DateTime($date))->format('Y');
                        if ($dateWeek !== false && (($dateYear == $nowYear && $dateWeek >= $nowWeek) || $dateYear > $nowYear)) {
                            $testArray[$dateWeek][$tblTest->getId()] = $tblTest;
                        }
                    // alle Tests
                    } else {
                        $dateWeek = date('W', strtotime($date));
                        $testArray[$dateWeek][$tblTest->getId()] = $tblTest;
                    }
                }
            }

            $preview = Evaluation::useService()->getLayoutRowsForTestPlanning($testArray);
            if (!empty($preview)) {
                return new Layout(new LayoutGroup($preview));
            }
        }

        return new Warning('Keine Leistungsüberprüfungen gefunden', new Exclamation());
    }
}