<?php

namespace SPHERE\Application\Transfer\Import\Competence;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Education\Competence\Skill\Skill;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\School\Course\Course;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Service
{
    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile|null $File
     * @param null $Data
     *
     * @return string
     */
    public function createSkillGridsFromFile(IFormInterface $Form = null, UploadedFile $File = null, $Data = null): string
    {
        /**
         * Skip to Frontend
         */
        if (null === $File) {
            return $Form;
        }

        if (!($tblSchoolType = Type::useService()->getTypeById($Data['TypeId']))) {
            $Form->setError('Data[TypeId]', 'Bitte geben Sie eine Schulart an');
            return $Form;
        }

        if ($File->getError()) {
            $Form->setError('File', 'Fehler');
        } else {
            /**
             * Prepare
             */
            $File = $File->move($File->getPath(), $File->getFilename() . '.' . $File->getClientOriginalExtension());
            /**
             * Read
             */
            /** @var PhpExcel $Document */
            $Document = Document::getDocument($File->getPathname());

//                $X = $Document->getSheetColumnCount();
            $Y = $Document->getSheetRowCount();

            $errors = [];
            $data = [];
            $countSkillGrid = 0;
            $countSkillArea = 0;
            $countSkill = 0;

            /**
             * Import
             */
            for ($RunY = 0; $RunY < $Y; $RunY++) {
                $column1 = trim($Document->getValue($Document->getCell(0, $RunY)));
                $column2 = trim($Document->getValue($Document->getCell(1, $RunY)));
                $column3 = trim($Document->getValue($Document->getCell(2, $RunY)));
                $column4 = trim($Document->getValue($Document->getCell(3, $RunY)));

                // leere Zeilen ignorieren
                if ($column1 === '' && $column2 === '' && $column3 === '') {
                    continue;
                }

                // Gültigkeitsbereich
                if (str_starts_with($column1, 'Fach')) {
                    $countSkillGrid++;
                    $countSkillArea = 0;
                    $tblSubject = null;
                    $level = null;
                    $tblCourse = null;
                    $tblSupportFocusType = null;
                    $subjectAcronym = trim(str_replace(['Fach', ':'], '', $column1));
                    if ($subjectAcronym !== '' && !(($tblSubject = Subject::useService()->getSubjectByVariantAcronym($subjectAcronym)))) {
                        $errors[] = $this->getError($RunY, "Fach: $subjectAcronym nicht gefunden");
                    }
                    if (str_starts_with($column2, 'Klassenstufe')) {
                        $level = trim(str_replace(['Klassenstufe', ':'], '', $column2));
                        // positive Zahl
                        if (!ctype_digit($level)) {
                            $errors[] = $this->getError($RunY, "Kein gültige Klassenstufe gefunden ($level)");
                        }
                    } else {
                        $errors[] = $this->getError($RunY, "Keine Klassenstufe gefunden ($column2)");
                    }
                    if (str_starts_with($column3, 'Bildungsgang')) {
                        $course = trim(str_replace(['Bildungsgang', ':'], '', $column3));
                        if ($course !== '' && !(($tblCourse = Course::useService()->getCourseByName($course)))) {
                            $errors[] = $this->getError($RunY, "Bildungsgang: $course nicht gefunden");
                        }
                    }
                    if (str_starts_with($column4, 'Primärer Förderschwerpunkt')) {
                        $supportFocusType = trim(str_replace(['Primärer Förderschwerpunkt', ':'], '', $column4));
                        if ($supportFocusType !== '' && !(($tblSupportFocusType = Student::useService()->getSupportFocusTypeByName($supportFocusType)))) {
                            $errors[] = $this->getError($RunY, "Primärer Förderschwerpunkt: $supportFocusType nicht gefunden");
                        }
                    }

                    $data[$countSkillGrid] = [
                        'name' => "Importiert Klassenstufe $level " . ($tblSubject ? " im Fach " . $tblSubject->getName() : 'Fächerübergreifend'),
                        'isAverage' => false,
                        'tblSubject' => $tblSubject,
                        'level' => $level,
                        'tblCourse' => $tblCourse,
                        'tblSupportFocusType' => $tblSupportFocusType,
                        'tblScoreType' => null,
                        'SkillAreas' => []
                    ];

                // Kompetenzbereich
                } elseif (str_starts_with($column1, 'Kompetenzbereich')) {
                    // fehlender Gültigkeitsbereich
                    if (!isset($data[1])) {
                        $errors[] = $this->getError($RunY, "Fehlender Gültigkeitsbereich.");
                    }

                    $countSkillArea++;
                    $countSkill = 0;
                    $skillArea = trim(str_replace(['Kompetenzbereich', ':', '„', '“', '"'], '', $column1));

                    $data[$countSkillGrid]['SkillAreas'][$countSkillArea] = [
                        'name' => $skillArea ?: null,
                        'sortOrder' => $countSkillArea,
                        'Skills' => []
                    ];

                // Kompetenz
                } else {
                    $countSkill++;
                    $skillLevel = trim(str_replace(['•'], '', $column1));
                    $skill = trim(str_replace(['•'], '', $column2));
                    if ($skillLevel && $skill === '') {
                        $skill = $skillLevel;
                        $skillLevel = '';
                    }

                    $data[$countSkillGrid]['SkillAreas'][$countSkillArea]['Skills'][$countSkill] = [
                        'level' => $skillLevel,
                        'skill' => $skill,
                        'sortOrder' => $countSkill,
                    ];
                }
            }

            if ($errors) {
                return new Panel('Fehler (Kompetenzraster wurden nicht importiert)', $errors, Panel::PANEL_TYPE_DANGER);
            }

            Skill::useService()->insertSkillGrid($tblSchoolType, $data);

            return new Success("$countSkillGrid Kompetenzraster wurden erfolgreich importiert", new \SPHERE\Common\Frontend\Icon\Repository\Success());
        }

        return new Danger('File nicht gefunden');
    }

    private function getError(int $RunY, string $message): string
    {
        return 'Zeile ' . ($RunY + 1) . ': ' . $message;
    }
}