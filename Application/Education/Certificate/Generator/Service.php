<?php
namespace SPHERE\Application\Education\Certificate\Generator;

use SPHERE\Application\Education\Certificate\Generator\Service\Data;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateGrade;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateInformation;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateLevel;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateReferenceForLanguages;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateSubject;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateType;
use SPHERE\Application\Education\Certificate\Generator\Service\Setup;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareCertificate;
use SPHERE\Application\Education\Certificate\Setting\Setting;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\School\Course\Service\Entity\TblTechnicalCourse;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubject;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Setting\Consumer\Consumer as ConsumerSetting;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Enable;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Education\Certificate\Generator
 */
class Service extends AbstractService
{
    /**
     * @param bool $doSimulation
     * @param bool $withData
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupService($doSimulation, $withData, $UTF8): string
    {
        $Protocol= '';
        if(!$withData){
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
        }
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param null|TblConsumer $tblConsumer
     *
     * @return bool|TblCertificate[]
     */
    public function getCertificateAllByConsumer(TblConsumer $tblConsumer = null)
    {
        return (new Data($this->getBinding()))->getCertificateAllByConsumer($tblConsumer);
    }

    /**
     * @param $Id
     *
     * @return bool|TblCertificate
     */
    public function getCertificateById($Id)
    {
        return (new Data($this->getBinding()))->getCertificateById($Id);
    }

    /**
     * @param string $Class
     *
     * @return bool|TblCertificate
     */
    public function getCertificateByCertificateClassName(string $Class)
    {
        return (new Data($this->getBinding()))->getCertificateByCertificateClassName($Class);
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param TblTechnicalCourse|null $TechnicalCourse
     *
     * @return bool|TblCertificateSubject[]
     */
    public function getCertificateSubjectAll(TblCertificate $tblCertificate, TblTechnicalCourse $TechnicalCourse = null)
    {
        return (new Data($this->getBinding()))->getCertificateSubjectAll($tblCertificate, $TechnicalCourse);
    }

    /**
     * @param TblCertificate $tblCertificate
     *
     * @return bool|TblCertificateGrade[]
     */
    public function getCertificateGradeAll(TblCertificate $tblCertificate)
    {
        return (new Data($this->getBinding()))->getCertificateGradeAll($tblCertificate);
    }

    /**
     * @param null|TblConsumer $tblConsumer
     *
     * @return bool|TblCertificate[]
     */
    public function getTemplateAllByConsumer(TblConsumer $tblConsumer = null)
    {
        return (new Data($this->getBinding()))->getTemplateAllByConsumer($tblConsumer);
    }

    /**
     * @param IFormInterface|null     $Form
     * @param TblCertificate          $tblCertificate
     * @param array $GradeList
     * @param array $SubjectList
     * @param TblTechnicalCourse|null $tblTechnicalCourse
     *
     * @return IFormInterface|string
     */
    public function createCertificateSetting(
        IFormInterface $Form,
        TblCertificate $tblCertificate,
        array $GradeList,
        array $SubjectList,
        TblTechnicalCourse $tblTechnicalCourse = null
    ) {
        /**
         * Skip to Frontend
         */
        if (empty($GradeList) && empty($SubjectList)) {
            return $Form;
        }

        $Error = array();

        // Kopf-Noten
        foreach ($GradeList as $LaneIndex => $FieldList) {
            foreach ($FieldList as $LaneRanking => $Field) {
                if (($tblGradeType = Grade::useService()->getGradeTypeById($Field['GradeType']))) {
                    $tblCertificateGrade = Generator::useService()->getCertificateGradeByIndex(
                        $tblCertificate, $LaneIndex, $LaneRanking
                    );
                    if ($tblCertificateGrade) {
                        // Update
                        (new Data($this->getBinding()))->updateCertificateGrade($tblCertificateGrade, $tblGradeType);
                    } else {
                        // Create
                        (new Data($this->getBinding()))->createCertificateGrade($tblCertificate, $LaneIndex, $LaneRanking, $tblGradeType);
                    }
                } else {
                    if ($Field['GradeType'] > 0) {
                        array_push($Error,
                            'Eine Notenangabe an der Position ' . $LaneIndex . ':' . $LaneRanking . ' konnte nicht gespeichert werden'
                        );
                    }
                }
            }
        }

        // Fach-Noten
        foreach ($SubjectList as $LaneIndex => $FieldList) {
            foreach ($FieldList as $LaneRanking => $Field) {
                if (($tblSubject = Subject::useService()->getSubjectById($Field['Subject']))) {
                    $tblCertificateSubject = Generator::useService()->getCertificateSubjectByIndex(
                        $tblCertificate, $LaneIndex, $LaneRanking, $tblTechnicalCourse
                    );
                    if ($tblCertificateSubject) {
                        // Update
                        (new Data($this->getBinding()))->updateCertificateSubject($tblCertificateSubject,
                            $tblSubject,
                            isset($Field['IsEssential']), $tblTechnicalCourse
//                            , ((isset($Field['Liberation']) && $Field['Liberation'])
//                                ? (Student::useService()->getStudentLiberationCategoryById($Field['Liberation'])
//                                    ? Student::useService()->getStudentLiberationCategoryById($Field['Liberation'])
//                                    : null
//                                )
//                                : null
//                            )
                        );
                    } else {
                        // Create
                        (new Data($this->getBinding()))->createCertificateSubject($tblCertificate,
                            $LaneIndex, $LaneRanking, $tblSubject,
                            isset($Field['IsEssential']), $tblTechnicalCourse
//                            , ((isset($Field['Liberation']) && $Field['Liberation'])
//                                ? (Student::useService()->getStudentLiberationCategoryById($Field['Liberation'])
//                                    ? Student::useService()->getStudentLiberationCategoryById($Field['Liberation'])
//                                    : null
//                                )
//                                : null
//                            )
                        );
                    }
                } else {
                    if ($Field['Subject'] > 0) {
                        array_push($Error,
                            'Eine Fachangabe an der Position ' . $LaneIndex . ':' . $LaneRanking . ' konnte nicht gespeichert werden'
                        );
                    } else {
                        if (($tblCertificateSubject = Generator::useService()->getCertificateSubjectByIndex(
                            $tblCertificate, $LaneIndex, $LaneRanking, $tblTechnicalCourse
                        ))
                        ) {
                            (new Data($this->getBinding()))->removeCertificateSubject($tblCertificateSubject);
                        }
                    }
                }
            }
        }

        if (empty($Error)) {
            return new Success(new Enable() . ' Die Einstellungen wurden gespeichert')
            . new Redirect('/Education/Certificate/Setting/Template', Redirect::TIMEOUT_SUCCESS);
        } else {
            return new Danger(new Disable() . ' Eine oder mehrere Einstellungen wurden nicht gespeichert!')
            . new Redirect('/Education/Certificate/Setting/Configuration', Redirect::TIMEOUT_ERROR,
                array('Certificate' => $tblCertificate->getId()));
        }
    }

    /**
     * @param string $Type
     *
     * @return string
     */
    public function insertCertificate(string $Type = ''): string
    {
        if((new Data($this->getBinding()))->insertCertificate($Type)){
            return new Success('Installation der Zeugnisvorlagen erfolgreich!').
                new Redirect('/Education/Certificate/Setting/Implement', Redirect::TIMEOUT_SUCCESS);
        }

        return new Danger('Installation der Zeugnisvorlagen ist fehlgeschlagen').
            new Redirect('/Education/Certificate/Setting/Implement', Redirect::TIMEOUT_ERROR);
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param $LaneIndex
     * @param $LaneRanking
     *
     * @return bool|TblCertificateGrade
     */
    public function getCertificateGradeByIndex(TblCertificate $tblCertificate, $LaneIndex, $LaneRanking)
    {
        return (new Data($this->getBinding()))->getCertificateGradeByIndex($tblCertificate, $LaneIndex, $LaneRanking);
    }

    /**
     * @param TblCertificate          $tblCertificate
     * @param                         $LaneIndex
     * @param                         $LaneRanking
     * @param TblTechnicalCourse|null $tblTechnicalCourse
     *
     * @return bool|TblCertificateSubject
     */
    public function getCertificateSubjectByIndex(TblCertificate $tblCertificate, $LaneIndex, $LaneRanking, TblTechnicalCourse $tblTechnicalCourse = null)
    {
        return (new Data($this->getBinding()))->getCertificateSubjectByIndex($tblCertificate, $LaneIndex, $LaneRanking, $tblTechnicalCourse);
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param TblSubject $tblSubject
     * @param TblTechnicalCourse|null $tblTechnicalCourse
     *
     * @return false|TblCertificateSubject
     */
    public function getCertificateSubjectBySubject(
        TblCertificate $tblCertificate,
        TblSubject $tblSubject,
        TblTechnicalCourse $tblTechnicalCourse = null
    ) {
        return (new Data($this->getBinding()))->getCertificateSubjectBySubject($tblCertificate, $tblSubject, $tblTechnicalCourse);
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param TblSubject $tblSubject
     *
     * @return false|TblCertificateSubject
     */
    public function getCertificateSubjectIgnoreTechnicalCourseBySubject(
        TblCertificate $tblCertificate,
        TblSubject $tblSubject
    ) {
        return (new Data($this->getBinding()))->getCertificateSubjectIgnoreTechnicalCourseBySubject($tblCertificate, $tblSubject);
    }

    /**
     * @param $Identifier
     *
     * @return bool|TblCertificateType
     */
    public function getCertificateTypeByIdentifier($Identifier)
    {
        return (new Data($this->getBinding()))->getCertificateTypeByIdentifier($Identifier);
    }

    /**
     * @param $Id
     *
     * @return bool|TblCertificateType
     */
    public function getCertificateTypeById($Id)
    {
        return (new Data($this->getBinding()))->getCertificateTypeById($Id);
    }

    /**
     * @return false|TblCertificateType[]
     */
    public function getCertificateTypeAll()
    {
        return (new Data($this->getBinding()))->getCertificateTypeAll();
    }

    /**
     * @param null|TblConsumer $tblConsumer
     * @param ?TblCertificateType $tblCertificateType
     *
     * @return bool|Service\Entity\TblCertificate[]
     */
    public function getCertificateAllByConsumerAndCertificateType(
        TblConsumer $tblConsumer = null,
        TblCertificateType $tblCertificateType = null
    ) {
        return (new Data($this->getBinding()))->getCertificateAllByConsumerAndCertificateType($tblConsumer, $tblCertificateType);
    }

    /**
     * @param null|TblConsumer $tblConsumer
     * @param null|TblCertificateType $tblCertificateType
     * @param null|TblType $tblSchoolType
     *
     * @return bool|Service\Entity\TblCertificate[]
     */
    public function getCertificateAllBy(
        TblConsumer $tblConsumer = null,
        TblCertificateType $tblCertificateType = null,
        TblType $tblSchoolType = null
    ) {
        return (new Data($this->getBinding()))->getCertificateAllBy($tblConsumer, $tblCertificateType, $tblSchoolType);
    }

    /**
     * @param null|TblConsumer $tblConsumer
     * @param null|TblCertificateType $tblCertificateType
     * @param null|TblType $tblSchoolType
     *
     * @return bool|TblCertificate[]
     */
    public function getCertificateAllForAutoSelect(
        TblConsumer $tblConsumer = null,
        TblCertificateType $tblCertificateType = null,
        TblType $tblSchoolType = null
    ) {
        // SSW-939 - Noteninformation Zuweisung Vorlage
        // für die Noteninformation ist keine Schulart angegeben, deswegen wird keine Vorlage gefunden
        if ($tblCertificateType && $tblCertificateType->getIdentifier() == 'GRADE_INFORMATION'
            && ($tblCertificate = Setting::useService()->getCertificateByCertificateClassName('GradeInformation'))
        ) {
            return array(0 => $tblCertificate);
        } else {
            return (new Data($this->getBinding()))->getCertificateAllForAutoSelect($tblConsumer, $tblCertificateType, $tblSchoolType);
        }
    }

    /**
     * @param TblCertificate $tblCertificate
     *
     * @return false|TblCertificateLevel[]
     */
    public function getCertificateLevelAllByCertificate(TblCertificate $tblCertificate)
    {
        return (new Data($this->getBinding()))->getCertificateLevelAllByCertificate($tblCertificate);
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param string $FieldName
     * @param bool $HasTeamInRemark
     *
     * @return false|int
     */
    public function getCharCountByCertificateAndField(TblCertificate $tblCertificate, string $FieldName, bool $HasTeamInRemark)
    {
        return (new Data($this->getBinding()))->getCharCountByCertificateAndField($tblCertificate, $FieldName, $HasTeamInRemark);
    }

    /**
     * @param TblCertificateType $tblCertificateType
     *
     * @return false|TblCertificate[]
     */
    public function getCertificateAllByType(TblCertificateType $tblCertificateType)
    {
        return (new Data($this->getBinding()))->getCertificateAllByType($tblCertificateType);
    }

    /**
     * @return array
     */
    public function getFormField(): array
    {
        $FieldConfiguration = array(
            'Content.Input.Remark'              => 'TextArea',
            'Content.Input.SecondRemark'        => 'TextArea',
            'Content.Input.RemarkWithoutTeam'   => 'TextArea',
            'Content.Input.Rating'              => 'TextArea',
            'Content.Input.TechnicalRating'     => 'TextArea',
            'Content.Input.Survey'              => 'TextArea',
            'Content.Input.Deepening'           => 'TextField',
            'Content.Input.SchoolType'          => 'SelectBox',
            'Content.Input.Type'                => 'SelectBox',
            'Content.Input.DateCertifcate'      => 'DatePicker',
            'Content.Input.DateConference'      => 'DatePicker',
            'Content.Input.DateConsulting'      => 'DatePicker',
            'Content.Input.Transfer'            => 'SelectBox',
            'Content.Input.IndividualTransfer'  => 'TextField',
            'Content.Input.TeamExtra'           => 'TextField',
            'Content.Input.GTA'                 => 'TextArea',
            'Content.Input.BellSubject'         => 'TextField',
            'Content.Input.PerformanceGroup'    => 'TextField',
            'Content.Input.Arrangement'         => 'TextArea',
            'Content.Input.Support'             => 'TextArea',
            'Content.Input.SupportSubject'      => 'TextArea',
            'Content.Input.DivisionName'        => 'TextField',
            'Content.Input.SchoolVisitYear'     => 'TextField',
            'Content.Input.StudentLetter'       => 'TextArea',
            'Content.Input.DialoguesWithYou'    => 'TextArea',
            'Content.Input.DialoguesWithParent' => 'TextArea',
            'Content.Input.DialoguesWithUs'     => 'TextArea',
            // Berufsfachschule
            'Content.Input.CertificateName'     => 'TextField',
            'Content.Input.BfsDestination'      => 'TextField',
            'Content.Input.OperationTimeTotal'  => 'TextField',
            'Content.Input.Operation1'          => 'TextField',
            'Content.Input.OperationTime1'      => 'TextField',
            'Content.Input.Operation2'          => 'TextField',
            'Content.Input.OperationTime2'      => 'TextField',
            'Content.Input.Operation3'          => 'TextField',
            'Content.Input.OperationTime3'      => 'TextField',
            'Content.Input.Operation4'          => 'TextField',
            'Content.Input.OperationTime4'      => 'TextField',
            'Content.Input.DateFrom'            => 'DatePicker',
            'Content.Input.DateTo'              => 'DatePicker',
            'Content.Input.AbsYear'             => 'TextField',
            'Content.Input.YearGradeAverageLesson_Average' => 'TextField',
            'Content.Input.YearGradeAveragePractical_Average' => 'TextField',
            'Content.Input.WrittenExam_Grade'   => 'SelectCompleter',
            'Content.Input.PracticalExam_Grade' => 'SelectCompleter',
            'Content.Input.Subarea1'            => 'TextField',
            'Content.Input.SubareaTimeH1'       => 'TextField',
            'Content.Input.SubareaTimeHDone1'   => 'TextField',
            'Content.Input.Subarea2'            => 'TextField',
            'Content.Input.SubareaTimeH2'       => 'TextField',
            'Content.Input.SubareaTimeHDone2'   => 'TextField',
            'Content.Input.Subarea3'            => 'TextField',
            'Content.Input.SubareaTimeH3'       => 'TextField',
            'Content.Input.SubareaTimeHDone3'   => 'TextField',
            'Content.Input.Subarea4'            => 'TextField',
            'Content.Input.SubareaTimeH4'       => 'TextField',
            'Content.Input.SubareaTimeHDone4'   => 'TextField',
            'Content.Input.AddEducation_Average_BFS' => 'SelectCompleter',
            'Content.Input.AddEducation_Average_EXAM' => 'SelectCompleter',
            'Content.Input.DateExam'            => 'DatePicker',
            'Content.Input.ExamCenter'          => 'TextField',
            // Fachschule
            'Content.Input.FsDestination'       => 'TextField',
            'Content.Input.SubjectArea'         => 'TextField',
            'Content.Input.Focus'               => 'TextField',
            'Content.Input.ChosenArea'          => 'TextField',
            'Content.Input.JobEducation'        => 'TextField',
            'Content.Input.JobEducationDuration'=> 'TextField',
            'Content.Input.AddEducation'        => 'TextField',
            'Content.Input.AddEducation_Grade'   => 'SelectCompleter',
            'Content.Input.AddEducation_GradeText' => 'SelectBox',
            'Content.Input.AddEducation_Average' => 'SelectCompleter',
            'Content.Input.ChosenArea1'         => 'TextField',
            'Content.Input.ChosenArea2'         => 'TextField',
            'Content.Input.SkilledWork'         => 'TextField',
            'Content.Input.SkilledWork_Grade'   => 'SelectCompleter',
            'Content.Input.SkilledWork_GradeText' => 'SelectBox',
            'Content.Input.AdditionalRemarkFhr' => 'CheckBox',
            // Fachoberschule HOGA
            'Content.Input.Job_Grade'           => 'SelectCompleter',
            'Content.Input.Job_Grade_Text'      => 'SelectBox',
            'Content.Input.Success'             => 'SelectBox',
            'Content.Input.IndustrialPlacement' => 'TextField',
            'Content.Input.IndustrialPlacementDuration' => 'TextField',
            'Content.Input.EducationDateFrom' => 'DatePicker',
            // Vorbereitungsklasse HOGA
            'Content.Input.PartialCourse' => 'TextField',
            'Content.Input.PartialIntegration'  => 'TextField',
            // Förderschule
//            'Content.Input.FoesAbsText'         => 'SelectBox', // SSW-1685 Auswahl soll aktuell nicht verfügbar sein, bis aufweiteres aufheben
        );

        if (Consumer::useService()->getConsumerBySessionIsConsumer(TblConsumer::TYPE_SACHSEN, 'EVAB')) {
            $FieldConfiguration['Content.Input.Remark'] = 'Editor';
        }

        return $FieldConfiguration;
    }

    /**
     * @return array
     */
    public function getFormFieldKeyList(): array
    {
        $formFieldList = $this->getFormField();
        $list = array();
        foreach ($formFieldList as $key => $formField) {
            $tempArray = explode('.', $key);
            $tempValue = end($tempArray);
            $list[$tempValue] = $tempValue;
        }

        return $list;
    }

    /**
     * @param TblType|null $tblType
     *
     * @return array
     */
    public function getFormLabel(TblType $tblType = null): array
    {
        $typeName = $tblType ? $tblType->getName() : '...';

        return array(
            'Content.Input.Remark'              => 'Bemerkungen',
            'Content.Input.SecondRemark'        => 'Bemerkung Seite 2',
            'Content.Input.RemarkWithoutTeam'   => 'Bemerkungen',
            'Content.Input.Rating'              => 'Einschätzung',
            'Content.Input.TechnicalRating'     => 'Fachliche Einschätzung',
            'Content.Input.Survey'              => 'Gutachten',
            'Content.Input.Deepening'           => 'Vertiefungsrichtung',
            'Content.Input.SchoolType'          => 'Ausbildung fortsetzen',
            'Content.Input.Type'                => 'Bezieht sich auf',
            'Content.Input.DateCertifcate'      => 'Datum des Zeugnisses',
            'Content.Input.DateConference'      => 'Datum der Klassenkonferenz',
            'Content.Input.DateConsulting'      => 'Datum der Bildungsberatung',
            'Content.Input.Transfer'            => 'Versetzungsvermerk',
            'Content.Input.IndividualTransfer'  => 'Versetzungsvermerk',
            'Content.Input.TeamExtra'           => 'Teilnahme an zusätzlichen schulischen Veranstaltungen',
            'Content.Input.GTA'                 => 'GTA',
            'Content.Input.BellSubject'         => 'Thema BELL',
            'Content.Input.PerformanceGroup'    => 'Leistungsgruppe',
            'Content.Input.Arrangement'         => 'Besonderes Engagement',
            'Content.Input.Support'             => 'Inklusive Unterrichtung',
            'Content.Input.SupportSubject'      => 'Thema der lebenspraktisch orientierten Komplexen Leistung',
            'Content.Input.DivisionName'        => 'Klasse',
            'Content.Input.SchoolVisitYear'     => 'Schulbesuchsjahr',
            'Content.Input.StudentLetter'       => 'Schülerbrief',
            'Content.Input.DialoguesWithYou'    => 'Im Dialog mit dir',
            'Content.Input.DialoguesWithParent' => 'Im Dialog mit deinen Eltern',
            'Content.Input.DialoguesWithUs'     => 'Im Dialog mit uns',
            // Berufsfachschule
            'Content.Input.CertificateName'     => 'Abweichender Zeugnisname (Endjahresinformation)',
            'Content.Input.BfsDestination'      => 'Berufsfachschule für ...',
            'Content.Input.OperationTimeTotal'  => 'Praktische Ausbildung Dauer in Wochen',
            'Content.Input.Operation1'          => 'Einsatzgebiet 1',
            'Content.Input.OperationTime1'      => 'Einsatzgebiet Dauer in Wochen 1',
            'Content.Input.Operation2'          => 'Einsatzgebiet 2',
            'Content.Input.OperationTime2'      => 'Einsatzgebiet Dauer in Wochen 2',
            'Content.Input.Operation3'          => 'Einsatzgebiet 3',
            'Content.Input.OperationTime3'      => 'Einsatzgebiet Dauer in Wochen 3',
            'Content.Input.Operation4'          => 'Einsatzgebiet 4',
            'Content.Input.OperationTime4'      => 'Einsatzgebiet Dauer in Wochen 4',
            'Content.Input.DateFrom'            => 'Besucht "seit" die ' . $typeName,
            'Content.Input.DateTo'              => 'Besuchte "bis" die ' . $typeName,
            'Content.Input.AbsYear'             => 'Abschluss im Schuljahr',
            'Content.Input.YearGradeAverageLesson_Average' => 'Jahresnote über die im Unterricht erbrachten Leistungen',
            'Content.Input.YearGradeAveragePractical_Average' => 'Jahresnote über die in der praktischen Ausbildung erbrachten Leistungen',
            'Content.Input.WrittenExam_Grade'   => 'Schriftlicher Prüfungsteil',
            'Content.Input.PracticalExam_Grade' => 'Praktischer Prüfungsteil',
            'Content.Input.Subarea1'            => 'Teilbereich 1',
            'Content.Input.SubareaTimeH1'        => 'Teilbereich 1 Dauer der Ausbildung (h)',
            'Content.Input.SubareaTimeHDone1'   => 'Teilbereich 1 Davon anwesend (h)',
            'Content.Input.Subarea2'            => 'Teilbereich 2',
            'Content.Input.SubareaTimeH2'        => 'Teilbereich 2 Dauer der Ausbildung (h)',
            'Content.Input.SubareaTimeHDone2'   => 'Teilbereich 2 Davon anwesend (h)',
            'Content.Input.Subarea3'            => 'Teilbereich 3',
            'Content.Input.SubareaTimeH3'        => 'Teilbereich 3 Dauer der Ausbildung (h)',
            'Content.Input.SubareaTimeHDone3'   => 'Teilbereich 3 Davon anwesend (h)',
            'Content.Input.Subarea4'            => 'Teilbereich 4',
            'Content.Input.SubareaTimeH4'        => 'Teilbereich 4 Dauer der Ausbildung (h)',
            'Content.Input.SubareaTimeHDone4'   => 'Teilbereich 4 Davon anwesend (h)',
            'Content.Input.AddEducation_Average_BFS' => 'BFS - Durchschnittsnote',
            'Content.Input.AddEducation_Average_EXAM' => 'Prüfungszeugnis - Durchschnittsnote',
            'Content.Input.DateExam'            => 'Datum des Prüfungszeugnisses',
            'Content.Input.ExamCenter'          => 'Prüfungsstelle',
            // Fachschule
            'Content.Input.FsDestination'       => 'Fachbereich',
            'Content.Input.SubjectArea'         => 'Fachrichtung',
            'Content.Input.Focus'               => 'Schwerpunkt',
            'Content.Input.ChosenArea'          => 'Wahlplfichtbereich (Überschrift)',
            'Content.Input.JobEducation'        => 'Berufspraktische Ausbildung (Überschrift)',
            'Content.Input.JobEducationDuration'=> 'Berufspraktische Ausbildung (Dauer in Wochen)',
            'Content.Input.AddEducation'        => 'Zusatzausbildung zum Erwerb der Fachhochschulreife',
            'Content.Input.AddEducation_Grade'   => 'FHR - oder Zensur',
            'Content.Input.AddEducation_GradeText' => 'FHR - Zeugnistext',
            'Content.Input.AddEducation_Average' => 'FHR - Durchschnittsnote',
            'Content.Input.ChosenArea1'         => 'Wahlbereich 1',
            'Content.Input.ChosenArea2'         => 'Wahlbereich 2',
            'Content.Input.SkilledWork'         => 'Facharbeit - Thema',
            'Content.Input.SkilledWork_Grade'   => 'Facharbeit - oder Zensur',
            'Content.Input.SkilledWork_GradeText' => 'Facharbeit - Zeugnistext',
            'Content.Input.AdditionalRemarkFhr' => 'Teilnahme an FHR-Prüfung',
            // Fachoberschule HOGA
            'Content.Input.Job_Grade'           => 'Fachpraktischer Teil der Ausbildung Zensur',
            'Content.Input.Job_Grade_Text'      => 'Fachpraktischer Teil der Ausbildung',
            'Content.Input.Success'             => 'Abschluss erfolgreich',
            'Content.Input.IndustrialPlacement' => 'Betriebspraktikum',
            'Content.Input.IndustrialPlacementDuration' => 'Betriebspraktikum (Dauer in Wochen)',
            'Content.Input.EducationDateFrom'   => 'Ausbildung Datum vom',
            // Vorbereitungsklasse HOGA
            'Content.Input.PartialCourse' => 'Teilintegration Bildungsgang',
            'Content.Input.PartialIntegration'  => 'Teilintegration in die Berufsschule',
            // Förderschule
//            'Content.Input.FoesAbsText' => 'Auswahltext für das Zeugnis', // SSW-1685 Auswahl soll aktuell nicht verfügbar sein, bis aufweiteres aufheben
        );
    }

    /**
     * @param TblGradeType $tblGradeType
     *
     * @return bool
     */
    public function isGradeTypeUsed(TblGradeType $tblGradeType): bool
    {
        return (new Data($this->getBinding()))->isGradeTypeUsed($tblGradeType);
    }

    /**
     * @param IFormInterface $Form
     * @param $Data
     *
     * @return IFormInterface|string
     */
    public function updateCertificateType(
        IFormInterface $Form,
        $Data
    ) {
        /**
         * Skip to Frontend
         */
        if ($Data === null) {
            return $Form;
        }

        if (($tblCertificateTypeAll = $this->getCertificateTypeAll())) {
            foreach ($tblCertificateTypeAll as $tblCertificateType) {
                if (isset($Data[$tblCertificateType->getId()]) && !$tblCertificateType->isAutomaticallyApproved()) {
                    (new Data($this->getBinding()))->updateCertificateType($tblCertificateType,
                        $tblCertificateType->getIdentifier(), $tblCertificateType->getName(), true);
                } elseif (!isset($Data[$tblCertificateType->getId()]) && $tblCertificateType->isAutomaticallyApproved()) {
                    (new Data($this->getBinding()))->updateCertificateType($tblCertificateType,
                        $tblCertificateType->getIdentifier(), $tblCertificateType->getName(), false);
                }
            }
        }

        return new Success('Die Daten wurden erfolgreich gespeichert', new \SPHERE\Common\Frontend\Icon\Repository\Success())
            . new Redirect('/Education/Certificate/Setting/Approval', Redirect::TIMEOUT_SUCCESS);
    }

    /**
     * @param TblCertificate $tblCertificate
     *
     * @return false|TblCertificateReferenceForLanguages[]
     */
    public function getCertificateReferenceForLanguagesAllByCertificate(TblCertificate $tblCertificate)
    {
        return (new Data($this->getBinding()))->getCertificateReferenceForLanguagesAllByCertificate($tblCertificate);
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param $languageRanking
     *
     * @return false|TblCertificateReferenceForLanguages
     */
    public function getCertificateReferenceForLanguagesByCertificateAndRanking(TblCertificate $tblCertificate, $languageRanking)
    {
        return (new Data($this->getBinding()))->getCertificateReferenceForLanguagesByCertificateAndRanking($tblCertificate, $languageRanking);
    }

    /**
     * @param IFormInterface $Form
     * @param TblCertificate $tblCertificate
     * @param $Data
     * @param $Subject
     *
     * @return IFormInterface|string
     */
    public function updateCertificateReferenceForLanguages(
        IFormInterface $Form,
        TblCertificate $tblCertificate,
        $Data,
        $Subject
    ) {
        /**
         * Skip to Frontend
         */
        if ($Data === null) {
            return $Form;
        }

        // Gemeinsamer Europäischer Referenzrahmen für Sprachen
        if ($Data) {
            foreach ($Data as $ranking => $array) {
                if (($tblCertificateReferenceForLanguages = $this->getCertificateReferenceForLanguagesByCertificateAndRanking($tblCertificate,
                    $ranking))) {
                    (new Data($this->getBinding()))->updateCertificateReferenceForLanguages(
                        $tblCertificateReferenceForLanguages,
                        $array['ToLevel10'],
                        $array['AfterBasicCourse'],
                        $array['AfterAdvancedCourse']
                    );
                } else {
                    (new Data($this->getBinding()))->createCertificateReferenceForLanguages(
                        $tblCertificate,
                        $ranking,
                        $array['ToLevel10'],
                        $array['AfterBasicCourse'],
                        $array['AfterAdvancedCourse']
                    );
                }
            }
        }

        // Zusätzliche Fächer
        if ($Subject) {
            // Fach-Noten
            foreach ($Subject as $LaneIndex => $FieldList) {
                foreach ($FieldList as $LaneRanking => $Field) {
                    if (($tblSubject = Subject::useService()->getSubjectById($Field['Subject']))) {
                        $tblCertificateSubject = Generator::useService()->getCertificateSubjectByIndex(
                            $tblCertificate, $LaneIndex, $LaneRanking
                        );
                        if ($tblCertificateSubject) {
                            // Update
                            (new Data($this->getBinding()))->updateCertificateSubject(
                                $tblCertificateSubject,
                                $tblSubject,
                                isset($Field['IsEssential'])
                            );
                        } else {
                            // Create
                            (new Data($this->getBinding()))->createCertificateSubject(
                                $tblCertificate,
                                $LaneIndex,
                                $LaneRanking,
                                $tblSubject,
                                isset($Field['IsEssential'])
                            );
                        }
                    }
                }
            }
        }

        return new Success('Die Daten wurden erfolgreich gespeichert', new \SPHERE\Common\Frontend\Icon\Repository\Success())
            . new Redirect('/Education/Certificate/Setting/Configuration', Redirect::TIMEOUT_SUCCESS, array('Certificate' => $tblCertificate->getId()));
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param TblStudentSubject $tblStudentSubject
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     *
     * @return string
     */
    public function getReferenceForLanguageByStudent(
        TblCertificate $tblCertificate,
        TblStudentSubject $tblStudentSubject,
        TblPerson $tblPerson,
        TblYear $tblYear
    ): string {
        $reference = '';
        if (($tblSubject = $tblStudentSubject->getServiceTblSubject())) {
            $identifier = 'ToLevel10';
            // bei z.B: EN2 kann in der Schülerakte trotzdem das normale EN eingestellt sein
            if (($tblSubjectList = Subject::useService()->getSubjectAllByName($tblSubject->getName()))) {
                foreach ($tblSubjectList as $tblSubject) {
                    if (($temp = DivisionCourse::useService()->getStudentSubjectByPersonAndYearAndSubjectForCourseSystem($tblPerson, $tblYear, $tblSubject))) {
                        $identifier = $temp->getIsAdvancedCourse() ? 'AfterAdvancedCourse' : 'AfterBasicCourse';
                        break;
                    }
                }
            }

            if (($tblCertificateReferenceForLanguages = $this->getCertificateReferenceForLanguagesByCertificateAndRanking(
                $tblCertificate,
                $tblStudentSubject->getTblStudentSubjectRanking()->getId()
            ))) {
                switch ($identifier) {
                    case 'ToLevel10': $reference = $tblCertificateReferenceForLanguages->getToLevel10(); break;
                    case 'AfterBasicCourse': $reference = $tblCertificateReferenceForLanguages->getAfterBasicCourse(); break;
                    case 'AfterAdvancedCourse': $reference = $tblCertificateReferenceForLanguages->getAfterAdvancedCourse(); break;
                }
            }
        }

        return $reference;
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param $fieldName
     *
     * @return false|TblCertificateInformation
     */
    public function getCertificateInformationByField(
        TblCertificate $tblCertificate,
        $fieldName
    ) {
        return (new Data($this->getBinding()))->getCertificateInformationByField($tblCertificate, $fieldName);
    }

    /**
     * @param TblCertificate $tblCertificate
     *
     * @return false|TblCertificateInformation[]
     */
    public function getCertificateInformationListByCertificate(
        TblCertificate $tblCertificate
    ) {
        return (new Data($this->getBinding()))->getCertificateInformationListByCertificate($tblCertificate);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblCertificate $tblCertificate
     *
     * @return array
     */
    public function getCheckCertificateMissingSubjectsForPerson(TblPerson $tblPerson, TblYear $tblYear, TblCertificate $tblCertificate): array
    {
        $resultList = array();

        $tblTechnicalCourse = Student::useService()->getTechnicalCourseByPerson($tblPerson);

        if (($tblSetting = ConsumerSetting::useService()->getSetting('Api', 'Education', 'Certificate', 'ProfileAcronym'))
            && ($value = $tblSetting->getValue())
        ) {
            $tblProfileSubject = Subject::useService()->getSubjectByAcronym($value);
        } else {
            $tblProfileSubject  = false;
        }
        if (($tblSetting = ConsumerSetting::useService()->getSetting('Api', 'Education', 'Certificate', 'OrientationAcronym'))
            && ($value = $tblSetting->getValue())
        ) {
            $tblOrientationSubject = Subject::useService()->getSubjectByAcronym($value);
        } else {
            $tblOrientationSubject  = false;
        }

        if (($tblSubjectList = DivisionCourse::useService()->getSubjectListByPersonListAndYear(array($tblPerson), $tblYear))) {
            foreach ($tblSubjectList as $tblSubject) {
                // Profile überspringen --> stehen extra im Wahlpflichtbereich
                if ($tblProfileSubject) {
                    if ($tblProfileSubject->getId() == $tblSubject->getId()) {
                        continue;
                    }
                } elseif (($tblStudent = $tblPerson->getStudent())
                    && ($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('PROFILE'))
                    && ($tblProfileList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
                        $tblStudentSubjectType))
                ) {
                    $isIgnore = false;
                    foreach ($tblProfileList as $tblProfile) {
                        if ($tblProfile->getServiceTblSubject() && $tblProfile->getServiceTblSubject()->getId() == $tblSubject->getId()) {
                            $isIgnore = true;
                        }
                    }
                    if ($isIgnore) {
                        continue;
                    }
                }

                // Neigungskurs überspringen --> stehen extra im Wahlpflichtbereich
                if ($tblOrientationSubject) {
                    if ($tblOrientationSubject->getId() == $tblSubject->getId()) {
                        continue;
                    }
                } elseif (($tblStudent = $tblPerson->getStudent())
                    && ($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('ORIENTATION'))
                    && ($tblOrientationList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
                        $tblStudentSubjectType))
                ) {
                    $isIgnore = false;
                    foreach ($tblOrientationList as $tblOrientation) {
                        if ($tblOrientation->getServiceTblSubject() && $tblOrientation->getServiceTblSubject()->getId() == $tblSubject->getId()) {
                            $isIgnore = true;
                        }
                    }
                    if ($isIgnore) {
                        continue;
                    }
                }

                // ab 2. Fremdsprache ignorieren
                if (($tblStudent = $tblPerson->getStudent())
                    // nicht für alle Zeugnisse sinnvoll, z.B. Kurshalbjahreszeugnis
                    && $tblCertificate->getName() !== 'Gymnasium Kurshalbjahreszeugnis'
                    && ($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE'))
                    && ($tblForeignLanguageList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
                        $tblStudentSubjectType))
                ) {
                    $isIgnore = false;
                    foreach ($tblForeignLanguageList as $tblForeignLanguage) {
                        if ($tblForeignLanguage->getServiceTblSubject()
                            && $tblForeignLanguage->getTblStudentSubjectRanking()
                            && $tblForeignLanguage->getTblStudentSubjectRanking()->getName() != '1'
                            && $tblForeignLanguage->getServiceTblSubject()->getId() == $tblSubject->getId()
                        ) {
                            $isIgnore = true;
                        }
                    }
                    if ($isIgnore) {
                        continue;
                    }
                }


                if (!$this->getCertificateSubjectBySubject($tblCertificate, $tblSubject, $tblTechnicalCourse ?: null)) {
                    $resultList[$tblSubject->getAcronym()] = $tblSubject->getAcronym();
                }
            }
        }

        return $resultList;
    }

    /**
     * @param TblPrepareCertificate $tblPrepare
     * @param $certificateNameList
     * @param $hasMissingLanguage
     *
     * @return array
     */
    public function getCheckCertificateSubjectsForDivisionSubject(TblPrepareCertificate $tblPrepare, $certificateNameList, &$hasMissingLanguage): array
    {
        if (($tblSetting = ConsumerSetting::useService()->getSetting('Api', 'Education', 'Certificate', 'ProfileAcronym'))
            && ($value = $tblSetting->getValue())
        ) {
            $tblProfileSubject = Subject::useService()->getSubjectByAcronym($value);
        } else {
            $tblProfileSubject  = false;
        }
        if (($tblSetting = ConsumerSetting::useService()->getSetting('Api', 'Education', 'Certificate', 'OrientationAcronym'))
            && ($value = $tblSetting->getValue())
        ) {
            $tblOrientationSubject = Subject::useService()->getSubjectByAcronym($value);
        } else {
            $tblOrientationSubject  = false;
        }

        $subjectList = array();
        if ($tblPrepare->getServiceTblAppointedDateTask()
            && ($tblDivisionCourse = $tblPrepare->getServiceTblDivision())
            && ($tblSubjectList = DivisionCourse::useService()->getSubjectListByDivisionCourse($tblDivisionCourse))
        ) {
            if (($tblSubjectProfileAll = Subject::useService()->getSubjectProfileAll())){
                $hasProfiles = true;
            } else {
                $hasProfiles = false;
            }
            if (($tblSubjectOrientationAll = Subject::useService()->getSubjectOrientationAll())){
                $hasOrientations = true;
            } else {
                $hasOrientations = false;
            }
            if (($tblForeignLanguagesAll = Subject::useService()->getSubjectForeignLanguageAll())) {
                $hasForeignLanguages = true;
            } else {
                $hasForeignLanguages = false;
            }
            foreach ($tblSubjectList  as $tblSubject) {
                    // Profile ignorieren
                    if ($tblProfileSubject) {
                        if ($tblProfileSubject->getId() == $tblSubject->getId()) {
                            continue;
                        }
                    } elseif ($hasProfiles && isset($tblSubjectProfileAll[$tblSubject->getId()])) {
                        continue;
                    }

                    // Neigungskurse orientieren
                    if ($tblOrientationSubject) {
                        if ($tblOrientationSubject->getId() == $tblSubject->getId()) {
                            continue;
                        }
                    } elseif ($hasOrientations && isset($tblSubjectOrientationAll[$tblSubject->getId()])) {
                        continue;
                    }

                    // bei Fremdsprache I-Icon mit ToolTip
                    if ($hasForeignLanguages && isset($tblForeignLanguagesAll[$tblSubject->getId()])) {
//                        $isForeignLanguage = true;
                        $hasMissingLanguage = true;
                    } /** @noinspection PhpStatementHasEmptyBodyInspection */ else {
//                        $isForeignLanguage = false;
                    }

                    foreach ($certificateNameList as $certificateId => $name) {
                        if (($tblCertificate = Setting::useService()->getCertificateById($certificateId))
                            // Abitur Fächerprüfung ignorieren
                            && $tblCertificate->getCertificate() != 'GymAbitur'
                            && $tblCertificate->getCertificate() != 'BGymAbitur'
                            && $tblCertificate->getCertificate() != 'BGymKurshalbjahreszeugnis'
                            && $tblCertificate->getCertificate() != 'BGymAbgSekII'
                            && !Setting::useService()->getCertificateSubjectIgnoreTechnicalCourseBySubject($tblCertificate, $tblSubject)
                        ) {
                            $subjectList[$tblSubject->getAcronym()] = $tblSubject->getAcronym();
//                                .($isForeignLanguage
//                                    ? ' ' . new ToolTip(new \SPHERE\Common\Frontend\Icon\Repository\Info(),
//                                        'Bei Fremdsprachen kann die Warnung unter Umständen ignoriert werden,
//                                         bitte prüfen Sie die Detailansicht unter Bearbeiten.') : '');
                        }
                    }
            }
        }

        return $subjectList;
    }
}
