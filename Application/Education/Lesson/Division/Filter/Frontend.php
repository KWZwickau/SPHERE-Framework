<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 07.06.2018
 * Time: 13:38
 */

namespace SPHERE\Application\Education\Lesson\Division\Filter;


use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\School\Course\Course;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Filter as FilterIcon;
use SPHERE\Common\Frontend\Layout\Repository\Panel;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Education\Lesson\Division\Filter
 */
class Frontend
{
    /**
     * @param TblType|null $tblSchoolType
     *
     * @return Form
     */
    public static function getFilterForm(TblType $tblSchoolType = null)
    {

        $tblCourseAll = Course::useService()->getCourseAll();
        $tblGroupAll = Group::useService()->getGroupAll();
        $tblGenderAll = Common::useService()->getCommonGenderAll();
        $tblReligionAll = Subject::useService()->getSubjectReligionAll();
        $tblProfileAll = Subject::useService()->getSubjectProfileAll();
        $tblOrientationAll = Subject::useService()->getSubjectOrientationAll();
        $tblElectiveAll = Subject::useService()->getSubjectElectiveAll();
        $tblForeignLanguageAll = Subject::useService()->getSubjectForeignLanguageAll();

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Personengruppe', array(
                            new SelectBox('Data[Group]', '', array('Name' => $tblGroupAll))
                        ),
                            Panel::PANEL_TYPE_INFO)
                        , 3),
                    new FormColumn(
                        new Panel('Personendaten: Geschlecht', array(
                            new SelectBox('Data[Gender]', '', array('Name' => $tblGenderAll))
                        ),
                            Panel::PANEL_TYPE_INFO)
                        , 3),
                    $tblSchoolType && $tblSchoolType->getName() == 'Mittelschule / Oberschule'
                        ? new FormColumn(
                        new Panel('Schülerakte: Bildungsgang', array(
                            new SelectBox('Data[Course]', '', array('Name' => $tblCourseAll))
                        ),
                            Panel::PANEL_TYPE_INFO)
                        , 3)
                        : null,
                    $tblSchoolType && $tblSchoolType->getName() == 'Gymnasium'
                    ? new FormColumn(
                        new Panel('Schülerakte: Profil', array(
                            new SelectBox('Data[SubjectProfile]', '', array('{{ Acronym }} - {{ Name }}' => $tblProfileAll))
                        ),
                            Panel::PANEL_TYPE_INFO)
                        , 3)
                    : null,

                    $tblSchoolType && $tblSchoolType->getName() == 'Mittelschule / Oberschule'
                        ? new FormColumn(
                        new Panel('Schülerakte: Neigungskurs', array(
                            new SelectBox('Data[SubjectOrientation]', '', array('{{ Acronym }} - {{ Name }}' => $tblOrientationAll))
                        ),
                            Panel::PANEL_TYPE_INFO)
                        , 3)
                        : null,
                    new FormColumn(
                        new Panel('Schülerakte: Fremdsprache', array(
                            new SelectBox('Data[SubjectForeignLanguage]', '', array('{{ Acronym }} - {{ Name }}' => $tblForeignLanguageAll))
                        ),
                            Panel::PANEL_TYPE_INFO)
                        , 3),
                    new FormColumn(
                        new Panel('Schülerakte: Religion', array(
                            new SelectBox('Data[SubjectReligion]', '', array('{{ Acronym }} - {{ Name }}' => $tblReligionAll))
                        ),
                            Panel::PANEL_TYPE_INFO)
                        , 3),
                    new FormColumn(
                        new Panel('Schülerakte: Wahlfach', array(
                            new SelectBox('Data[SubjectElective]', '', array('{{ Acronym }} - {{ Name }}' => $tblElectiveAll))
                        ),
                            Panel::PANEL_TYPE_INFO)
                        , 3)
                )),
                new FormRow(
                    new FormColumn(
                        new Primary('Filtern', new FilterIcon())
                    )
                )
            ))
        );
    }
}