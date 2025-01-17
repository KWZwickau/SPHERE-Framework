<?php
namespace SPHERE\Application\Education\Certificate\Setting;

use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\School\Course\Course;
use SPHERE\Application\Education\School\Course\Service\Entity\TblTechnicalCourse;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\HiddenField;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Repository\Title as FormTitle;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Document;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\Icon\Repository\Star;
use SPHERE\Common\Frontend\Icon\Repository\Success as SuccessIcon;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Accordion;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Link\Repository\Success as SuccessLink;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Sorter;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Education\Certificate\Setting
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param int $Certificate
     * @param array $Grade
     * @param array $Subject
     * @param array $Data
     * @param null $tblTechnicalCourseId
     * @param bool $loadStandardFromNoConsumer
     *
     * @return Stage|string
     */
    public function frontendCertificateSetting($Certificate = 0, $Grade = array(), $Subject = array(), $Data = null,
        $tblTechnicalCourseId = null, $loadStandardFromNoConsumer = false
    ) {

        $Stage = new Stage('Einstellungen', 'Vorlage bearbeiten');
        $Stage->addButton(new Standard('Zurück', '/Education/Certificate/Setting/Template', new ChevronLeft()));

        $tblTechnicalCourse = null;
        if(null !== $tblTechnicalCourseId){
            if(!($tblTechnicalCourse = Course::useService()->getTechnicalCourseById($tblTechnicalCourseId))){
                // if false -> set no null
                $tblTechnicalCourse = null;
            }
        }

        if (( $tblCertificate = Generator::useService()->getCertificateById($Certificate) )) {

            if ($tblTechnicalCourse
                && !$loadStandardFromNoConsumer
                && !Generator::useService()->getCertificateSubjectAll($tblCertificate, $tblTechnicalCourse) // für den Bildungsgang dürfen noch keine Fächer eingestellt sein
                && Generator::useService()->getCertificateSubjectAll($tblCertificate) // es muss Fächereinstellungen ohne Bildungsgang geben
            ) {
                $Stage->addButton(new Standard(
                    'Fächer von ohne Bildungsgang / Berufsbezeichnung / Ausbildung laden',
                    '/Education/Certificate/Setting/Configuration',
                    null,
                    array(
                        'Certificate' => $Certificate,
                        'tblTechnicalCourseId' => $tblTechnicalCourseId,
                        'loadStandardFromNoConsumer' => true
                    )
                ));
            }

            // Spezial Fall Abiturzeugnis
            if ($tblCertificate->getCertificate() == 'GymAbitur'
                || $tblCertificate->getCertificate() == 'BGymAbitur'
            ) {
                if (($tblCertificateReferenceForLanguagesList = Generator::useService()->getCertificateReferenceForLanguagesAllByCertificate($tblCertificate))) {
                    $global = $this->getGlobal();
                    foreach ($tblCertificateReferenceForLanguagesList as $tblCertificateReferenceForLanguages) {
                        $global->POST['Data'][$tblCertificateReferenceForLanguages->getLanguageRanking()]['ToLevel10'] = $tblCertificateReferenceForLanguages->getToLevel10();
                        $global->POST['Data'][$tblCertificateReferenceForLanguages->getLanguageRanking()]['AfterBasicCourse'] = $tblCertificateReferenceForLanguages->getAfterBasicCourse();
                        $global->POST['Data'][$tblCertificateReferenceForLanguages->getLanguageRanking()]['AfterAdvancedCourse'] = $tblCertificateReferenceForLanguages->getAfterAdvancedCourse();
                    }
                    $global->savePost();
                }

                $formRows = array();
                for ($i = 1; $i < 4; $i++) {
                    $formRows[] = new FormRow(new FormColumn(
                        new Panel(
                            $i . '. Fremdsprache',
                            array(
                                new Layout(new LayoutGroup(new LayoutRow(array(
                                    new LayoutColumn(
                                        new TextField('Data[' . $i . '][ToLevel10]', '', 'Bis Klasse '
                                            . ($tblCertificate->getCertificate() == 'BGymAbitur' ? '11' : '10')
                                        )
                                    , 4),
                                    new LayoutColumn(
                                        new TextField('Data[' . $i . '][AfterBasicCourse]', '', 'Nach Grundkurs')
                                    , 4),
                                    new LayoutColumn(
                                        new TextField('Data[' . $i . '][AfterAdvancedCourse]', '', 'Nach Leistungskurs')
                                    , 4),
                                ))))
                            ),
                            Panel::PANEL_TYPE_INFO
                        )
                    ));
                }

                $tblSubjectAll = Subject::useService()->getSubjectAll();

                $form = new Form(
                    array(
                        new FormGroup(
                            new FormRow(array(
                                new FormColumn($this->getSubject($tblCertificate, $tblSubjectAll, 1, 1)),
                                new FormColumn($this->getSubject($tblCertificate, $tblSubjectAll, 2, 1)),
                            )),
                            new FormTitle('Zusätzliche Fächer')
                        ),
                        new FormGroup($formRows, new FormTitle('Gemeinsamer Europäischer Referenzrahmen für Sprachen'))
                    ),
                    new Primary('Speichern')
                );

                $Stage->setContent(
                    new Panel('Zeugnisvorlage', array($tblCertificate->getName(), $tblCertificate->getDescription()),
                        Panel::PANEL_TYPE_INFO)
                    . new Well(Generator::useService()->updateCertificateReferenceForLanguages($form, $tblCertificate, $Data, $Subject))
                );

            } elseif(preg_match('!Berufsfachschule!', $tblCertificate->getName())) {

                // Nur wenn TechnicalCourse verwendet wird
                if($tblTechnicalCourseList = Course::useService()->getTechnicalCourseAll()){
                    if(!$tblTechnicalCourse){
                        return $this->getTechnicalSchoolSelection($Certificate, $tblTechnicalCourseList);
                    }
                }

                // Fach-Noten-Definition
                $tblSubjectAll = Subject::useService()->getSubjectAll();
                // Erstmal bis 20
                $haveToAcrossSubject = 4; // (4 * 2) = 8 Fächer (3 Zusatzplatzhalter füü z.B. Religion auf der rechten Seite)
                $haveToBaseSubject = 12; // (8 * 2) = 16 LF (14 Ist Standard, 15 passen auf das Zeugnis)
                $chosenSubject = 14; // (2 * 2) = 4 Wahlfächer (3 Wahlfächer passen auf das Zeugnis)
                $praktSubject = 15; // (2 * 2) = 4 Berufspraktische Ausbildung (1 Fach)

                // Berufsübergreifende Pflichtfächer
                $SubjectLaneAcrossLeft = array();
                $SubjectLaneAcrossRight = array();
                for ($Run = 1; $Run <= $haveToAcrossSubject; $Run++) {
                    array_push($SubjectLaneAcrossLeft,
                        $this->getSubject($tblCertificate, $tblSubjectAll, 1, $Run,
                            ($Run == 1 ? 'Linke Zeugnis-Spalte' : ''), 'Subject', '', $tblTechnicalCourse, $loadStandardFromNoConsumer)
                    );
                    array_push($SubjectLaneAcrossRight,
                        $this->getSubject($tblCertificate, $tblSubjectAll, 2, $Run,
                            ($Run == 1 ? 'Rechte Zeugnis-Spalte' : ''), 'Subject', '', $tblTechnicalCourse, $loadStandardFromNoConsumer)
                    );
                }
                // Berufsbezogene Pflichtfächer
                $SubjectLaneBaseLeft = array();
                $SubjectLaneBaseRight = array();
                $countLF = 1;
                for ($Run = ($haveToAcrossSubject + 1); $Run <= $haveToBaseSubject; $Run++) {
                    array_push($SubjectLaneBaseLeft,
                        $this->getSubject($tblCertificate, $tblSubjectAll, 1, $Run, '', 'Subject', 'LF'.$countLF++,
                            $tblTechnicalCourse, $loadStandardFromNoConsumer)
                    );
                    array_push($SubjectLaneBaseRight,
                        $this->getSubject($tblCertificate, $tblSubjectAll, 2, $Run, '', 'Subject', 'LF'.$countLF++,
                            $tblTechnicalCourse, $loadStandardFromNoConsumer)
                    );
                }
                // Wahlfächer
                $SubjectLaneChosenLeft = array();
                $SubjectLaneChosenRight = array();
                for ($Run = ($haveToBaseSubject + 1); $Run <= $chosenSubject; $Run++) {
                    array_push($SubjectLaneChosenLeft,
                        $this->getSubject($tblCertificate, $tblSubjectAll, 1, $Run, '', 'Subject', '',
                            $tblTechnicalCourse, $loadStandardFromNoConsumer)
                    );
                    array_push($SubjectLaneChosenRight,
                        $this->getSubject($tblCertificate, $tblSubjectAll, 2, $Run, '', 'Subject', '',
                            $tblTechnicalCourse, $loadStandardFromNoConsumer)
                    );
                }
                // Berufspratische Ausbildung
                $SubjectPrakt = array();
                for ($Run = ($chosenSubject + 1); $Run <= $praktSubject; $Run++) {
                    array_push($SubjectPrakt,
                        $this->getSubject($tblCertificate, $tblSubjectAll, 1, $Run, '', 'Subject', '',
                            $tblTechnicalCourse, $loadStandardFromNoConsumer)
                    );
                }


                $Stage->setContent(
                    new Panel('Zeugnisvorlage', array($tblCertificate->getName(), $tblCertificate->getDescription()
                    , ($tblTechnicalCourse ? $tblTechnicalCourse->getName() : '')),
                        Panel::PANEL_TYPE_INFO)
                    . ($loadStandardFromNoConsumer && $tblTechnicalCourse
                        ? new Warning('Es wurden die Fächereinstellungen von ohne "Bildungsgang / Berufsbezeichnung / Ausbildung" 
                            voreingetragen. Bitte Speichern Sie diese Fächereinstellung am Ende der Seite (unten) um die
                            Fächereinstellung für: ' . $tblTechnicalCourse->getName() . ' zu übernehmen.')
                        : ''
                    )
                    . Generator::useService()->createCertificateSetting(
                        new Form(array(
                            new FormGroup(array(
                                new FormRow(array(
                                    new FormColumn($SubjectLaneAcrossLeft, 6),
                                    new FormColumn($SubjectLaneAcrossRight, 6),
                                )),
                            ), new FormTitle('Pflicht Berufsübergreifender Bereich (Reihenfolge Links -> Rechts auf dem Zeugnis untereinander)')),
                            new FormGroup(array(
                                new FormRow(array(
                                    new FormColumn($SubjectLaneBaseLeft, 6),
                                    new FormColumn($SubjectLaneBaseRight, 6),
                                )),
                            ), new FormTitle('Pflicht Berufsbezogener Bereich (LF Sortiert auf dem Zeugnis untereinander)')),
                            new FormGroup(array(
                                new FormRow(array(
                                    new FormColumn($SubjectLaneChosenLeft, 6),
                                    new FormColumn($SubjectLaneChosenRight, 6),
                                )),
                            ), new FormTitle('Wahlpflichtbereich  (Reihenfolge Links -> Rechts auf dem Zeugnis untereinander)')),
                            new FormGroup(array(
                                new FormRow(array(
                                    new FormColumn($SubjectPrakt, 6)
                                )),
                            ), new FormTitle('Berufspraktische Ausbildung')),
                        ), new Primary('Speichern')), $tblCertificate, $Grade, $Subject, $tblTechnicalCourse)
                );
            }elseif(preg_match('!Fachschule!', $tblCertificate->getName())) {

                // Nur wenn TechnicalCourse verwendet wird
                if($tblTechnicalCourseList = Course::useService()->getTechnicalCourseAll()){
                    if(!$tblTechnicalCourse){
                        return $this->getTechnicalSchoolSelection($Certificate, $tblTechnicalCourseList);
                    }
                }

                // Fach-Noten-Definition
                $tblSubjectAll = Subject::useService()->getSubjectAll();
                // Erstmal bis 20
                $haveToAcrossSubject = 4; // (4 * 2) = 8 Fächer (3 Zusatzplatzhalter für z.B. Religion auf der rechten Seite)
                $haveToBaseSubject = 14; // (10 * 2) = 20 LF (14 Ist Standard, 15 passen auf das Zeugnis)
                $chosenSubject = 16; // (2 * 2) = 4 Wahlfächer (3 Wahlfächer passen auf das Zeugnis)
                $praktSubject = 17; // (1 * 2) = 2 Berufspraktische Ausbildung (1 Fach)
//                $educationSubject = 18; // (1 * 2) = 2 Erwerb der Fachhochschulreife (1 Fach)

                // Berufsübergreifende Pflichtfächer
                $SubjectLaneAcrossLeft = array();
                $SubjectLaneAcrossRight = array();
                for ($Run = 1; $Run <= $haveToAcrossSubject; $Run++) {
                    array_push($SubjectLaneAcrossLeft,
                        $this->getSubject($tblCertificate, $tblSubjectAll, 1, $Run, '', 'Subject', '',
                            $tblTechnicalCourse, $loadStandardFromNoConsumer)
                    );
                    array_push($SubjectLaneAcrossRight,
                        $this->getSubject($tblCertificate, $tblSubjectAll, 2, $Run, '', 'Subject', '',
                            $tblTechnicalCourse, $loadStandardFromNoConsumer)
                    );
                }
                // Berufsbezogene Pflichtfächer
                $SubjectLaneBaseLeft = array();
                $SubjectLaneBaseRight = array();
                $countLF = 1;
                for ($Run = ($haveToAcrossSubject + 1); $Run <= $haveToBaseSubject; $Run++) {
                    array_push($SubjectLaneBaseLeft,
                        $this->getSubject($tblCertificate, $tblSubjectAll, 1, $Run, '', 'Subject', 'LF'.$countLF++,
                            $tblTechnicalCourse, $loadStandardFromNoConsumer)
                    );
                    array_push($SubjectLaneBaseRight,
                        $this->getSubject($tblCertificate, $tblSubjectAll, 2, $Run, '', 'Subject', 'LF'.$countLF++,
                            $tblTechnicalCourse, $loadStandardFromNoConsumer)
                    );
                }
                // Wahlfächer
                $SubjectLaneChosenLeft = array();
                $SubjectLaneChosenRight = array();
                for ($Run = ($haveToBaseSubject + 1); $Run <= $chosenSubject; $Run++) {
                    array_push($SubjectLaneChosenLeft,
                        $this->getSubject($tblCertificate, $tblSubjectAll, 1, $Run, '', 'Subject', '',
                            $tblTechnicalCourse, $loadStandardFromNoConsumer)
                    );
                    array_push($SubjectLaneChosenRight,
                        $this->getSubject($tblCertificate, $tblSubjectAll, 2, $Run, '', 'Subject', '',
                            $tblTechnicalCourse, $loadStandardFromNoConsumer)
                    );
                }
                // Berufspratische Ausbildung
                $SubjectPrakt = array();
                for ($Run = ($chosenSubject + 1); $Run <= $praktSubject; $Run++) {
                    array_push($SubjectPrakt,
                        $this->getSubject($tblCertificate, $tblSubjectAll, 1, $Run, '', 'Subject', '',
                            $tblTechnicalCourse, $loadStandardFromNoConsumer)
                    );
                }
//                // Zusatzausbildung zum Erwerb der Fachhochschulreife
//                $SubjectEducation = array();
//                for ($Run = ($chosenSubject + 2); $Run <= $educationSubject; $Run++) {
//                    array_push($SubjectEducation,
//                        $this->getSubject($tblCertificate, $tblSubjectAll, 1, $Run, '', 'Subject', '',
//                           $tblTechnicalCourse, $loadStandardFromNoConsumer)
//                    );
//                }

                $Stage->setContent(
                    new Panel('Zeugnisvorlage', array($tblCertificate->getName(), $tblCertificate->getDescription()
                    , ($tblTechnicalCourse ? $tblTechnicalCourse->getName(): '')),
                        Panel::PANEL_TYPE_INFO)
                    . ($loadStandardFromNoConsumer && $tblTechnicalCourse
                        ? new Warning('Es wurden die Fächereinstellungen von ohne "Bildungsgang / Berufsbezeichnung / Ausbildung" 
                            voreingetragen. Bitte Speichern Sie diese Fächereinstellung am Ende der Seite (unten) um die
                            Fächereinstellung für: ' . $tblTechnicalCourse->getName() . ' zu übernehmen.')
                        : ''
                    )
                    . Generator::useService()->createCertificateSetting(
                        new Form(array(
                            new FormGroup(array(
                                new FormRow(array(
                                    new FormColumn($SubjectLaneAcrossLeft, 6),
                                    new FormColumn($SubjectLaneAcrossRight, 6),
                                )),
                            ), new FormTitle('Pflicht Fachrichtungsübergreifender Bereich (Sortiert auf dem Zeugnis untereinander)')),
                            new FormGroup(array(
                                new FormRow(array(
                                    new FormColumn($SubjectLaneBaseLeft, 6),
                                    new FormColumn($SubjectLaneBaseRight, 6),
                                )),
                            ), new FormTitle('Pflicht Fachrichtungsbezogener Bereich (LF Sortiert auf dem Zeugnis untereinander)')),
                            new FormGroup(array(
                                new FormRow(array(
                                    new FormColumn($SubjectLaneChosenLeft, 6),
                                    new FormColumn($SubjectLaneChosenRight, 6),
                                )),
                            ), new FormTitle('Wahlpflichtbereich (Reihenfolge Links -> Rechts)')),
                            new FormGroup(array(
                                new FormRow(array(
                                    new FormColumn($SubjectPrakt, 6)
                                )),
                            ), new FormTitle('Berufspraktische Ausbildung')),
//                            new FormGroup(array(
//                                new FormRow(array(
//                                    new FormColumn($SubjectEducation, 6)
//                                )),
//                            ), new FormTitle('Zusatzausbildung zum Erwerb der Fachhochschulreife')),
                        ), new Primary('Speichern')), $tblCertificate, $Grade, $Subject, $tblTechnicalCourse)
                );
            } elseif (preg_match('!Berufliches Gymnasium!', $tblCertificate->getName())) {

                // Fach-Noten-Definition
                $tblSubjectAll = Subject::useService()->getSubjectAll();
                $chosenSubject = 20;

                // Pflichtbereich
                $SubjectLaneAcrossLeft = array();
                $SubjectLaneAcrossRight = array();
                for ($Run = 1; $Run < $chosenSubject; $Run++) {
                    array_push($SubjectLaneAcrossLeft,
                        $this->getSubject($tblCertificate, $tblSubjectAll, 1, $Run, '', 'Subject', '',
                            $tblTechnicalCourse, $loadStandardFromNoConsumer)
                    );
                    array_push($SubjectLaneAcrossRight,
                        $this->getSubject($tblCertificate, $tblSubjectAll, 2, $Run, '', 'Subject', '',
                            $tblTechnicalCourse, $loadStandardFromNoConsumer)
                    );
                }

                // Wahlbereich
                $SubjectLaneChosenLeft = array();
                $SubjectLaneChosenRight = array();
                for ($Run = $chosenSubject; $Run <= $chosenSubject + 6; $Run++) {
                    array_push($SubjectLaneChosenLeft,
                        $this->getSubject($tblCertificate, $tblSubjectAll, 1, $Run, '', 'Subject', '',
                            $tblTechnicalCourse, $loadStandardFromNoConsumer)
                    );
                    array_push($SubjectLaneChosenRight,
                        $this->getSubject($tblCertificate, $tblSubjectAll, 2, $Run, '', 'Subject', '',
                            $tblTechnicalCourse, $loadStandardFromNoConsumer)
                    );
                }

                $Stage->setContent(
                    new Panel('Zeugnisvorlage', array($tblCertificate->getName(), $tblCertificate->getDescription()
                    , ($tblTechnicalCourse ? $tblTechnicalCourse->getName(): '')),
                        Panel::PANEL_TYPE_INFO)
                    . ($loadStandardFromNoConsumer && $tblTechnicalCourse
                        ? new Warning('Es wurden die Fächereinstellungen von ohne "Bildungsgang / Berufsbezeichnung / Ausbildung" 
                            voreingetragen. Bitte Speichern Sie diese Fächereinstellung am Ende der Seite (unten) um die
                            Fächereinstellung für: ' . $tblTechnicalCourse->getName() . ' zu übernehmen.')
                        : ''
                    )
                    . Generator::useService()->createCertificateSetting(
                        new Form(array(
                            new FormGroup(array(
                                new FormRow(array(
                                    new FormColumn($SubjectLaneAcrossLeft, 6),
                                    new FormColumn($SubjectLaneAcrossRight, 6),
                                )),
                            ), new FormTitle('Pflichtbereich')),
                            new FormGroup(array(
                                new FormRow(array(
                                    new FormColumn($SubjectLaneChosenLeft, 6),
                                    new FormColumn($SubjectLaneChosenRight, 6),
                                )),
                            ), new FormTitle('Wahlbereich')),
                        ), new Primary('Speichern')), $tblCertificate, $Grade, $Subject, $tblTechnicalCourse)
                );
            } elseif ($tblCertificate->getCertificate() == @'HOGA\BgjAbs' || $tblCertificate->getCertificate() == @'HOGA\BgjHjInfo') {
                $tblTechnicalCourse = null;
                // Fach-Noten-Definition
                $tblSubjectAll = Subject::useService()->getSubjectAll();

                $haveToAcrossSubject = 4;
                $haveToBaseSubject = 10;
                $chosenSubject = 15;

                // Berufsübergreifender Bereich
                $SubjectLaneAcrossLeft = array();
                $SubjectLaneAcrossRight = array();
                for ($Run = 1; $Run <= $haveToAcrossSubject; $Run++) {
                    array_push($SubjectLaneAcrossLeft,
                        $this->getSubject($tblCertificate, $tblSubjectAll, 1, $Run, '', 'Subject', '',
                            $tblTechnicalCourse, $loadStandardFromNoConsumer)
                    );
                    array_push($SubjectLaneAcrossRight,
                        $this->getSubject($tblCertificate, $tblSubjectAll, 2, $Run, '', 'Subject', '',
                            $tblTechnicalCourse, $loadStandardFromNoConsumer)
                    );
                }

                // Berufsbezogener Bereich - fachtheoretischer Unterricht
                $SubjectLaneBaseLeft = array();
                $SubjectLaneBaseRight = array();
                $countLF = 1;
                for ($Run = ($haveToAcrossSubject + 1); $Run <= $haveToBaseSubject; $Run++) {
                    array_push($SubjectLaneBaseLeft,
                        $this->getSubject($tblCertificate, $tblSubjectAll, 1, $Run, '', 'Subject', 'LF'.$countLF++,
                            $tblTechnicalCourse, $loadStandardFromNoConsumer)
                    );
                    array_push($SubjectLaneBaseRight,
                        $this->getSubject($tblCertificate, $tblSubjectAll, 2, $Run, '', 'Subject', 'LF'.$countLF++,
                            $tblTechnicalCourse, $loadStandardFromNoConsumer)
                    );
                }

                // Berufsbezogener Bereich – fachpraktischer Unterricht
                $SubjectLaneChosenLeft = array();
                $SubjectLaneChosenRight = array();
                for ($Run = ($haveToBaseSubject + 1); $Run <= $chosenSubject; $Run++) {
                    array_push($SubjectLaneChosenLeft,
                        $this->getSubject($tblCertificate, $tblSubjectAll, 1, $Run, '', 'Subject', '',
                            $tblTechnicalCourse, $loadStandardFromNoConsumer)
                    );
                    array_push($SubjectLaneChosenRight,
                        $this->getSubject($tblCertificate, $tblSubjectAll, 2, $Run, '', 'Subject', '',
                            $tblTechnicalCourse, $loadStandardFromNoConsumer)
                    );
                }

                $Stage->setContent(
                    new Panel('Zeugnisvorlage', array($tblCertificate->getName(), $tblCertificate->getDescription()
                    , ($tblTechnicalCourse ? $tblTechnicalCourse->getName(): '')),
                        Panel::PANEL_TYPE_INFO)
                    . ($loadStandardFromNoConsumer && $tblTechnicalCourse
                        ? new Warning('Es wurden die Fächereinstellungen von ohne "Bildungsgang / Berufsbezeichnung / Ausbildung" 
                            voreingetragen. Bitte Speichern Sie diese Fächereinstellung am Ende der Seite (unten) um die
                            Fächereinstellung für: ' . $tblTechnicalCourse->getName() . ' zu übernehmen.')
                        : ''
                    )
                    . Generator::useService()->createCertificateSetting(
                        new Form(array(
                            new FormGroup(array(
                                new FormRow(array(
                                    new FormColumn($SubjectLaneAcrossLeft, 6),
                                    new FormColumn($SubjectLaneAcrossRight, 6),
                                )),
                            ), new FormTitle('Pflicht Berufsübergreifender Bereich')),
                            new FormGroup(array(
                                new FormRow(array(
                                    new FormColumn($SubjectLaneBaseLeft, 6),
                                    new FormColumn($SubjectLaneBaseRight, 6),
                                )),
                            ), new FormTitle('Pflicht Berufsbezogener Bereich - fachtheoretischer Unterricht (LF Reihenfolge Links -> Rechts)')),
                            new FormGroup(array(
                                new FormRow(array(
                                    new FormColumn($SubjectLaneChosenLeft, 6),
                                    new FormColumn($SubjectLaneChosenRight, 6),
                                )),
                            ), new FormTitle('Pflicht Berufsbezogener Bereich - fachpraktischer Unterricht (LF Reihenfolge Links -> Rechts)')),
                        ), new Primary('Speichern')), $tblCertificate, $Grade, $Subject, $tblTechnicalCourse)
                );
            } else {

                // Kopf-Noten-Definition
                $tblGradeTypeBehavior = Grade::useService()->getGradeTypeList(true);

                // Fach-Noten-Definition
                $tblSubjectAll = Subject::useService()->getSubjectAll();

                if ($tblCertificate->isGradeInformation()) {
                    // bei Noteninformationen stehen alle Fächer auf der linken Seite
                    $LaneLength = 25;
                } else {
                    if ($tblSubjectAll) {
                        $LaneLength = ceil(count($tblSubjectAll) / 2);
                        if ($LaneLength > 25) {
                            $LaneLength = 25;
                        }
                    } else {
                        $LaneLength = 2;
                    }
                }

                $SubjectLaneLeft = array();
                $SubjectLaneRight = array();
                for ($Run = 1; $Run <= $LaneLength; $Run++) {
                    array_push($SubjectLaneLeft,
                        $this->getSubject($tblCertificate, $tblSubjectAll, 1, $Run,
                            ($Run == 1 ? 'Linke Zeugnis-Spalte' : ''), 'Subject', '', $tblTechnicalCourse, $loadStandardFromNoConsumer)
                    );
                    array_push($SubjectLaneRight,
                        $this->getSubject($tblCertificate, $tblSubjectAll, 2, $Run,
                            ($Run == 1 ? 'Rechte Zeugnis-Spalte' : ''), 'Subject', '', $tblTechnicalCourse, $loadStandardFromNoConsumer)
                    );
                }

                $Stage->setContent(
                    new Panel('Zeugnisvorlage', array($tblCertificate->getName(), $tblCertificate->getDescription()),
                        Panel::PANEL_TYPE_INFO)
                    . Generator::useService()->createCertificateSetting(
                        new Form(array(
                            new FormGroup(array(
                                new FormRow(array(
                                    new FormColumn(array(
                                        $this->getGrade($tblCertificate, $tblGradeTypeBehavior, 1, 1, 'Kopfnote',
                                            'Linke Zeugnis-Spalte'),
                                        $this->getGrade($tblCertificate, $tblGradeTypeBehavior, 1, 2, 'Kopfnote')
                                    ), 6),
                                    new FormColumn(array(
                                        $this->getGrade($tblCertificate, $tblGradeTypeBehavior, 2, 1, 'Kopfnote',
                                            'Rechte Zeugnis-Spalte'),
                                        $this->getGrade($tblCertificate, $tblGradeTypeBehavior, 2, 2, 'Kopfnote')
                                    ), 6),
                                ))
                            ), new FormTitle('Kopfnoten')),
                            new FormGroup(array(
                                new FormRow(array(
                                    new FormColumn($SubjectLaneLeft, 6),
                                    new FormColumn($SubjectLaneRight, 6),
                                )),
                            ), new FormTitle('Fachnoten')),
                        ), new Primary('Speichern')), $tblCertificate, $Grade, $Subject)
                );
            }

        } else {
            return $Stage
                . new Danger('Die Zeugnisvorlage wurde nicht gefunden', new Exclamation())
                . new Redirect('/Education/Certificate/Setting/Template', Redirect::TIMEOUT_ERROR);
        }

        return $Stage;
    }

    /**
     * @param int                  $Certificate
     * @param TblTechnicalCourse[] $tblTechnicalCourseList
     *
     * @return Stage
     */
    public function getTechnicalSchoolSelection($Certificate, $tblTechnicalCourseList)
    {

        $Stage = new Stage('Einstellungen', 'Vorlage bearbeiten');
        $Stage->addButton(new Standard('Zurück', '/Education/Certificate/Setting/Template', new ChevronLeft()));
        $ButtonList = array();
        $tblTechnicalCourseList = (new Sorter($tblTechnicalCourseList))->sortObjectBy('Name');
        foreach($tblTechnicalCourseList as $tblTechnicalCourse){
            $ButtonList[] = new Standard($tblTechnicalCourse->getName(), '/Education/Certificate/Setting/Configuration'
                , null, array(
                    'Certificate' =>$Certificate,
                    'tblTechnicalCourseId' => $tblTechnicalCourse->getId()
                ));
        }
        $CertificateName = 'Zeugbnisvorlage nicht erkannt';
        if(($tblCertificate = Setting::useService()->getCertificateById($Certificate))){
            $CertificateName = $tblCertificate->getName().' '.new Muted($tblCertificate->getDescription());
        }
        $Stage->setContent(
            new Info('Bitte wählen Sie die gewünschte "Bildungsgang / Berufsbezeichnung / Ausbildung" des Zeungnisses aus.')
            .new Panel($CertificateName, $ButtonList, Panel::PANEL_TYPE_INFO)
        );
        return $Stage;
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param TblSubject[] $tblSubjectAll
     * @param int $LaneIndex [1..n]
     * @param int $LaneRanking [1..n]
     * @param string $LaneTitle
     * @param string $FieldName
     * @param string $PreSubject
     * @param TblTechnicalCourse|null $tblTechnicalCourse
     * @param bool $loadStandardFromNoConsumer
     *
     * @return Panel
     */
    private function getSubject(
        TblCertificate $tblCertificate,
        $tblSubjectAll,
        $LaneIndex,
        $LaneRanking,
        $LaneTitle = '',
        $FieldName = 'Subject',
        $PreSubject = '',
        $tblTechnicalCourse = null,
        $loadStandardFromNoConsumer = false
    ) {

        $Global = $this->getGlobal();
        if (!isset( $Global->POST[$FieldName][$LaneIndex][$LaneRanking] )) {
            if (( $tblCertificateSubject = Generator::useService()->getCertificateSubjectByIndex(
                $tblCertificate, $LaneIndex, $LaneRanking, $loadStandardFromNoConsumer ? null : $tblTechnicalCourse
            ) )
            ) {
                $Global->POST[$FieldName][$LaneIndex][$LaneRanking]['Subject'] =
                    ( $tblCertificateSubject->getServiceTblSubject()
                        ? $tblCertificateSubject->getServiceTblSubject()->getId()
                        : 0
                    );
                $Global->POST[$FieldName][$LaneIndex][$LaneRanking]['IsEssential'] =
                    ( $tblCertificateSubject->isEssential()
                        ? 1
                        : 0
                    );
            };
            $Global->savePost();
        }

        return new Panel($LaneTitle, array(
            new SelectBox($FieldName.'['.$LaneIndex.']['.$LaneRanking.'][Subject]', ($PreSubject? $PreSubject.' ' : '').'Fach',
                array('{{ Acronym }} - {{ Name }}' => $tblSubjectAll)
            ),
            new CheckBox($FieldName.'['.$LaneIndex.']['.$LaneRanking.'][IsEssential]',
                'Muss immer ausgewiesen werden', 1),
        ));
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param TblGradeType[] $tblGradeTypeAll
     * @param int            $LaneIndex   [1..n]
     * @param int            $LaneRanking [1..n]
     * @param string         $LabelName
     * @param string         $LaneTitle
     * @param string         $FieldName
     *
     * @return Panel
     */
    private function getGrade(
        TblCertificate $tblCertificate,
        $tblGradeTypeAll,
        $LaneIndex,
        $LaneRanking,
        $LabelName = 'Betragen',
        $LaneTitle = '',
        $FieldName = 'Grade'
    ) {

        $Global = $this->getGlobal();
        if (!isset( $Global->POST[$FieldName][$LaneIndex][$LaneRanking] )) {
            if (( $tblCertificateGrade = Generator::useService()->getCertificateGradeByIndex(
                $tblCertificate, $LaneIndex, $LaneRanking
            ) )
            ) {
                $Global->POST[$FieldName][$LaneIndex][$LaneRanking]['GradeType'] = $tblCertificateGrade->getServiceTblGradeType()->getId();
            };
            $Global->savePost();
        }

        return new Panel($LaneTitle, array(
            new SelectBox($FieldName.'['.$LaneIndex.']['.$LaneRanking.'][GradeType]', $LabelName,
                array('{{ Code }} - {{ Name }}' => $tblGradeTypeAll)
            )
        ));
    }

    /**
     * @return Stage
     */
    public function frontendSelectCertificate()
    {

        $Stage = new Stage('Einstellungen', 'Zeugnisvorlage auswählen');
        $Stage = self::setSettingMenue($Stage, 'Template');

        // Find Certificate-Templates
        $tblConsumer = Consumer::useService()->getConsumerBySession();
//        if ($tblConsumer && $tblConsumer->getAcronym() == 'DEMO') {
//            $tblTemplateAll = Generator::useService()->getTemplateAll();
//        } else {
        // holt alle ohne Mandant
        $tblTemplateAll = Generator::useService()->getTemplateAllByConsumer(null);
//        }
        if ($tblConsumer) {
            if (!$tblTemplateAll){
                $tblTemplateAll = array();
            }

            $tblTemplateConsumer = Generator::useService()->getTemplateAllByConsumer($tblConsumer);
            if ($tblTemplateConsumer) {
                $tblTemplateAll = array_merge($tblTemplateConsumer, $tblTemplateAll);
            }

            $TemplateTable = array();
            array_walk($tblTemplateAll,
                function (TblCertificate $tblCertificate) use (&$TemplateTable) {
                    $hasOption = true;
                    $name = $tblCertificate->getName();
                    if ($name == 'Berufliches Gymnasium Abgangszeugnis'
                        || $name == 'Berufliches Gymnasium Kurshalbjahreszeugnis'
                    ) {
                        $hasOption = false;
                    }

                    $TemplateTable[] = array_merge($tblCertificate->__toArray(), array(
                            'Typ'    => '<div class="text-center">'.( $tblCertificate->getServiceTblConsumer()
                                    ? new Small(new Muted($tblCertificate->getServiceTblConsumer()->getAcronym())).'<br/>'.new Star()
                                    : new Document().'<br/>'.new Small(new Muted('Standard'))
                                ).'</div>',
                            'Category' => $tblCertificate->getDisplayCategory(),
                            'CertificateNumber' => $tblCertificate->getCertificateNumber(),
                            'Option' => $hasOption
                                ? new Standard(
                                    '', '/Education/Certificate/Setting/Configuration', new Select(),
                                    array(
                                        'Certificate' => $tblCertificate->getId()
                                    ),
                                    'Zeugnisvorlage auswählen'
                                )
                                : ''
                        )
                    );
                });

            $Content = new TableData($TemplateTable, null, array(
                'Typ'               => 'Typ',
                'Category'          => 'Kategorie',
                'Name'              => 'Name',
                'Description'       => 'Beschreibung',
                'CertificateNumber' => 'Anlage',
                'Option'            => ''
            ), array(
                'order'      => array(array(0, 'asc'), array(1, 'asc'), array(2, 'asc'), array(3, 'asc')),
                'columnDefs' => array(
                    array('width' => '1%', 'targets' => 0),
                    array('width' => '1%', 'targets' => 4),
                )
            ));

            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn($Content)
                        ), new Title('Verfügbare Vorlagen')
                    )
                )
            );

        }

        return $Stage;
    }

    /**
     * @param null $Data
     *
     * @return Stage
     */
    public function frontendApproval($Data = null)
    {

        $Stage = new Stage('Einstellungen', 'Automatische Freigabe');
        $Stage = self::setSettingMenue($Stage, 'Approval');

        $certificateTypeList = array();
        if (($tblCertificateTypeAll = Generator::useService()->getCertificateTypeAll())) {
            $Global = $this->getGlobal();
            foreach ($tblCertificateTypeAll as $tblCertificateType) {
                $Global->POST['Data'][$tblCertificateType->getId()] = $tblCertificateType->isAutomaticallyApproved() ? 1 : 0;
            }
            $Global->savePost();

            foreach ($tblCertificateTypeAll as $tblCertificateType) {
                if ($tblCertificateType->getIdentifier() !== 'GRADE_INFORMATION') {
                    $certificateTypeList[] = new CheckBox('Data[' . $tblCertificateType->getId() . ']',
                        $tblCertificateType->getName(), 1);
                }
            }
        }

        $form = new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        new Panel(
                            'Zeugnistypen automatisch freigeben',
                            $certificateTypeList,
                            Panel::PANEL_TYPE_PRIMARY
                        )
                    ),
                    new FormColumn(new HiddenField('Data[IsSubmit]'))
                ))
            )
        );
        $form->appendFormButton(new Primary('Speichern', new Save()));

        $Stage->setContent(new Well(Generator::useService()->updateCertificateType($form, $Data)));

        return $Stage;
    }

    public function frontendImplement()
    {

        $Stage = new Stage('Einstellungen', 'Zeugnisvorlagen installieren');
        $Stage = self::setSettingMenue($Stage, '');
        $LayoutRowList = array();

        $showAll = false;
        if(($tblConsumer = Consumer::useService()->getConsumerBySession())){
            if($tblConsumer->getAcronym() == 'REF'
            || $tblConsumer->getAcronym() == 'DEMO'){
                $showAll = true;
            }
        }


        if($showAll || ($tblType = Type::useService()->getTypeByName(TblType::IDENT_GRUND_SCHULE))
            && School::useService()->getSchoolByType($tblType)){
            $LayoutRowList[] = $this->getCertificateInstallAccordion(TblCertificate::CERTIFICATE_TYPE_PRIMARY,
                'Grundschule', 'GsJa', count($LayoutRowList));
        }
        if($showAll || ($tblType = Type::useService()->getTypeByName(TblType::IDENT_OBER_SCHULE))
            && School::useService()->getSchoolByType($tblType)){
            $LayoutRowList[] = $this->getCertificateInstallAccordion(TblCertificate::CERTIFICATE_TYPE_SECONDARY,
                'Oberschule', 'MsJ', count($LayoutRowList));
        }
        if($showAll || ($tblType = Type::useService()->getTypeByName(TblType::IDENT_GYMNASIUM))
            && School::useService()->getSchoolByType($tblType)){
            $LayoutRowList[] = $this->getCertificateInstallAccordion(TblCertificate::CERTIFICATE_TYPE_GYM,
                'Gymnasium', 'GymJ', count($LayoutRowList));
        }
        if($showAll
            || (($tblType = Type::useService()->getTypeByName(TblType::IDENT_BERUFLICHES_GYMNASIUM))
                && School::useService()->getSchoolByType($tblType))
        ) {
            $LayoutRowList[] = $this->getCertificateInstallAccordion(TblCertificate::CERTIFICATE_TYPE_B_GYM,
                'Berufliches Gymnasium', 'BGymJ', count($LayoutRowList));
        }
        if($showAll || ($tblType = Type::useService()->getTypeByName(TblType::IDENT_ALLGEMEIN_BILDENDE_FOERDERSCHULE))
            && School::useService()->getSchoolByType($tblType)){
            $LayoutRowList[] = $this->getCertificateInstallAccordion(TblCertificate::CERTIFICATE_TYPE_FOERDERSCHULE,
                'Förderschule', 'FoesJGeistigeEntwicklung', count($LayoutRowList));
        }
        if($showAll || ($tblType = Type::useService()->getTypeByName(TblType::IDENT_BERUFS_FACH_SCHULE))
            && School::useService()->getSchoolByType($tblType)){
            $LayoutRowList[] = $this->getCertificateInstallAccordion(TblCertificate::CERTIFICATE_TYPE_BERUFSFACHSCHULE,
                'Berufsfachschule', 'BfsJ', count($LayoutRowList));
        }
        if($showAll || ($tblType = Type::useService()->getTypeByName(TblType::IDENT_FACH_SCHULE))
            && School::useService()->getSchoolByType($tblType)){
            $LayoutRowList[] = $this->getCertificateInstallAccordion(TblCertificate::CERTIFICATE_TYPE_FACHSCHULE,
                'Fachschule', 'FsJ', count($LayoutRowList));
        }
        if(empty($LayoutRowList)){
            $LayoutRowList[] = new LayoutRow(new LayoutColumn(
                new Warning('Der Mandant hat keine Schulen(Schulart) hinterlegt. [Einstellungen -> Mandant -> Schulen]')
            ));
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(new Title('Auswahl'))
                    )
                )),
                new LayoutGroup(
                // nur Sachsen
                    Consumer::useService()->getConsumerBySessionIsConsumerType(TblConsumer::TYPE_SACHSEN)
                        ? $LayoutRowList
                        : new LayoutRow(new LayoutColumn(''))
                )
            ))
        );

        return $Stage;
    }

    /**
     * @param string $Type
     * @param string $Name
     * @param string $CertificateClass
     *
     * @return LayoutRow
     */
    private function getCertificateInstallAccordion($Type, $Name, $CertificateClass, $count)
    {
        // Grundschule
        $GsList = array(
            'Grundschule Halbjahresinformation 1' => 'GsHjOneInfo',
            'Grundschule Jahreszeugnis 1' => 'GsJOne',
            'Grundschule Halbjahresinformation' => 'GsHjInformation',
            'Grundschule Jahreszeugnis' => 'GsJa',
            'Bildungsempfehlung' => 'BeGs'
        );
        // Oberschule
        $OsList = array(
            'Bildungsempfehlung' => 'BeSOFS',
            'Oberschule Halbjahresinformation' => 'MsHjInfo',
            'Oberschule Halbjahresinformation HS' => 'MsHjInfoHs',
            'Oberschule Halbjahresinformation RS' => 'MsHjInfoRs',
            'Oberschule Halbjahresinformation Lernen' => 'MsHjInfoFsLernen',
            'Oberschule Halbjahresinformation Geistige Entwicklung' => 'MsHjInfoFsGeistigeEntwicklung',

//            'Oberschule Halbjahreszeugnis ' => 'MsHj',
            'Oberschule Halbjahreszeugnis HS' => 'MsHjHs',
            'Oberschule Halbjahreszeugnis RS' => 'MsHjRs',
            'Oberschule Halbjahreszeugnis Lernen' => 'MsHjFsLernen',
            'Oberschule Halbjahreszeugnis Geistige Entwicklung' => 'MsHjFsGeistigeEntwicklung',

            'Oberschule Jahreszeugnis' => 'MsJ',
            'Oberschule Jahreszeugnis HS' => 'MsJHs',
            'Oberschule Jahreszeugnis RS' => 'MsJRs',
            'Oberschule Jahreszeugnis Lernen' => 'MsJFsLernen',
            'Oberschule Jahreszeugnis Geistige Entwicklung' => 'MsJFsGeistigeEntwicklung',

//            'Oberschule Abschlusszeugnis' => 'MsAbs',
            'Oberschule Abschlusszeugnis HS' => 'MsAbsHs',
            'Oberschule Abschlusszeugnis HS Qualifiziert' => 'MsAbsHsQ',
            'Oberschule Abschlusszeugnis RS' => 'MsAbsRs',
//            'Oberschule Abschlusszeugnis RS' => 'MsAbsRs',
            'Oberschule Abschlusszeugnis Hauptschulbildungsgang Lernen' => 'MsAbsLernenHs',
            'Oberschule Abschlusszeugnis Hauptschulabschluss gleichgestellt Lernen' => 'MsAbsLernenEquatedHs',
            'Oberschule Abschlusszeugnis' => 'MsAbsLernen',

            'Oberschule Abgangszeugnis' => 'MsAbg',
            'Oberschule Abgangszeugnis Geistige Entwicklung' => 'MsAbgGeistigeEntwicklung'
        );
        // Gymnasium
        $GymList = array(
            'Gymnasium Halbjahresinformation' => 'GymHjInfo',
            'Gymnasium Halbjahreszeugnis' => 'GymHj',
            'Gymnasium Jahreszeugnis' => 'GymJ',
            'Gymnasium Kurshalbjahreszeugnis' => 'GymKurshalbjahreszeugnis',
            'Gymnasium Abschlusszeugnis' => 'GymAbitur',
            'Gymnasium Abgangszeugnis Sek I' => 'GymAbgSekI',
            'Gymnasium Abgangszeugnis Sek II' => 'GymAbgSekII'
        );
        // Berufliches Gymnasium
        $BGymList = array(
            'Berufliches Gymnasium Halbjahreszeugnis' => 'BGymHjZ',
            'Berufliches Gymnasium Jahreszeugnis' => 'BGymJ',
            'Berufliches Gymnasium Kurshalbjahreszeugnis' => 'BGymKurshalbjahreszeugnis',
            'Berufliches Gymnasium Abschlusszeugnis' => 'BGymAbitur',
            'Berufliches Gymnasium Abgangszeugnis Sek II' => 'BGymAbgSekII'
        );
        $BfsList = array(
            'Berufsfachschule Halbjahresinformation' => 'BfsHjInfo',
            'Berufsfachschule Halbjahreszeugnis' => 'BfsHj',
            'Berufsfachschule Jahreszeugnis' => 'BfsJ',
            'Berufsfachschule Abschlusszeugnis' => 'BfsAbs',
            'Berufsfachschule Abschlusszeugnis mit mittleren Schulabschluss' => 'BfsAbsMs',
            'Berufsfachschule Abgangszeugnis' => 'BfsAbg',
            'Berufsfachschule Abgangszeugnis Generalistik' => 'BfsAbgGeneralistik',
            'Berufsfachschule Jahreszeugnis Pflege' => 'BfsPflegeJ'
        );
        $FsList = array(
            'Fachschule Halbjahresinformation' => 'FsHjInfo',
            'Fachschule Halbjahreszeugnis' => 'FsHj',
            'Fachschule Jahreszeugnis' => 'FsJ',
            'Fachschule Abschlusszeugnis FHR' => 'FsAbsFhr',
            'Fachschule Abschlusszeugnis' => 'FsAbs',
            'Fachschule Abgangszeugnis' => 'FsAbg'
        );
        $FoesList = array(
            'Förderschule Halbjahresinformation' => 'FoesHjInfoGeistigeEntwicklung',
            'Förderschule Halbjahreszeugnis' => 'FoesHjGeistigeEntwicklung',
            'Förderschule Jahreszeugnis' => 'FoesJGeistigeEntwicklung',
            'Förderschule Abgangszeugnis' => 'FoesAbgGeistigeEntwicklung',
            'Förderschule Abschlusszeugnis' => 'FoesAbsGeistigeEntwicklung'
        );

        $LayoutRow = new LayoutRow(array(new LayoutColumn('', 3)));
        $List = array();
        switch ($Type) {
            case TblCertificate::CERTIFICATE_TYPE_PRIMARY:
                $List = $GsList;
                break;
            case TblCertificate::CERTIFICATE_TYPE_SECONDARY:
                $List = $OsList;
                break;
            case TblCertificate::CERTIFICATE_TYPE_GYM:
                $List = $GymList;
                break;
            case TblCertificate::CERTIFICATE_TYPE_B_GYM:
                $List = $BGymList;
                break;
            case TblCertificate::CERTIFICATE_TYPE_BERUFSFACHSCHULE:
                $List = $BfsList;
                break;
            case TblCertificate::CERTIFICATE_TYPE_FACHSCHULE:
                $List = $FsList;
                break;
            case TblCertificate::CERTIFICATE_TYPE_FOERDERSCHULE:
                $List = $FoesList;
                break;
        }
        $showContent = false;
        if($count == 0){
            $showContent = true;
        }
        if(Generator::useService()->getCertificateByCertificateClassName($CertificateClass)){
            $Button = (new SuccessLink($Name,'/Education/Certificate/Setting/ImplementCertificate',
                new Ok(), array('Type' => $Type),
                'Installation wiederholen (eventuell fehlende / neue ergänzen)'));
            $Content = $this->getCertificateInstallationFeedback($List);
//            array_unshift($Content, $Button);
            $Accordion = new Accordion();
            $Accordion->addItem(new PullClear("Installierte / verfügbare Zeugnisse $Name".new PullRight($Button)), new Listing($Content), $showContent);
            $LayoutRow->addColumn(new LayoutColumn($Accordion, 6));
        } else {
            $Button = new Standard($Name, '/Education/Certificate/Setting/ImplementCertificate',
                new Save(), array('Type' => $Type));
            $Content = $this->getCertificateInstallationFeedback($List, true);
//            array_unshift($Content, $Button);
            $Accordion = new Accordion();
            $Accordion->addItem(new PullClear("Nicht installierte Zeugnisse $Name".new PullRight($Button)), new Listing($Content), $showContent);
            $LayoutRow->addColumn(new LayoutColumn($Accordion, 6));
        }
        return $LayoutRow;
    }

    /**
     * @param $ClassList
     *
     * @return array
     */
    private function getCertificateInstallationFeedback($ClassList, $IsDisable = false)
    {

        $ContentArray = array();
        foreach($ClassList as $Name => $Class){
            if($IsDisable){
                $ContentArray[] = new Muted($Name);
                continue;
            }
            if(Generator::useService()->getCertificateByCertificateClassName($Class)){
                $ContentArray[] = new Success(new SuccessIcon()." $Name installiert ");
            } else {
                $ContentArray[] = new DangerText(new Disable()." $Name nicht installiert ");
            }
        }
        return $ContentArray;
    }

    public function frontendImplementCertificate($Type = '')
    {

        return Generator::useService()->insertCertificate($Type);
    }

    public function frontendDashboard()
    {

        $Stage = new Stage('Einstellungen', 'Auswählen');
        $Stage = self::setSettingMenue($Stage, '');

        return $Stage;
    }

    /**
     * @param Stage $Stage
     * @param string $Route
     *
     * @return Stage
     */
    private static function setSettingMenue(Stage $Stage, $Route = 'Template')
    {

        $text = 'Zeugnisvorlagen';
        $Stage->addButton(new Standard($Route == 'Template' ? new Edit() . ' ' . $text : $text,
            '/Education/Certificate/Setting/Template', null, null,
            'Den Zeugnisvorlagen Fächer zuordnen'));

        $text = 'Automatische Freigabe';
        $Stage->addButton(new Standard($Route == 'Approval' ? new Edit() . ' ' . $text : $text,
            '/Education/Certificate/Setting/Approval', null, null,
            'Automatische Freigaben setzen'));

        $text = 'Zeugnisvorlagen installieren';
        $Stage->addButton(new Standard($Route == 'Implement' ? new Edit() . ' ' . $text : $text,
            '/Education/Certificate/Setting/Implement', null, null,
            'Standardzeugnisse hinzufügen'));

        return $Stage;
    }
}
