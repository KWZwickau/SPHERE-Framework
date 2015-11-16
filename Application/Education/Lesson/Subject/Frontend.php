<?php
namespace SPHERE\Application\Education\Lesson\Subject;

use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblCategory;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblGroup;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Headline;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Education\Lesson\Subject
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param null|array $Subject
     *
     * @return Stage
     */
    public function frontendCreateSubject($Subject = null)
    {

        $Stage = new Stage('Fächer', 'erstellen / bearbeiten');
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Subject', new ChevronLeft()));

        $tblSubjectAll = Subject::useService()->getSubjectAll();
        array_walk($tblSubjectAll, function (TblSubject &$tblSubject) {

            $tblSubject->Option = new Standard('', '/Education/Lesson/Subject/Change/Subject', new Pencil(),
                    array('Id' => $tblSubject->getId()))
                . (Subject::useService()->getSubjectActiveState($tblSubject) === false ?
                    new Standard('', '/Education/Lesson/Subject/Destroy/Subject', new Remove(),
                        array('Id' => $tblSubject->getId()))
                    : '');
        });

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($tblSubjectAll, null, array(
                                'Acronym' => 'Kürzel',
                                'Name' => 'Name',
                                'Description' => 'Beschreibung',
                                'Option' => 'Optionen',
                            ))
                        )
                    ), new Title('Bestehende Fächer')
                ),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            Subject::useService()->createSubject(
                                $this->formSubject()
                                    ->appendFormButton(new Primary('Fach erstellen'))
                                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                , $Subject
                            )
                        )
                    ), new Title('Fach erstellen')
                )
            ))
        );

        return $Stage;
    }

    /**
     * @param null|TblSubject $tblSubject
     *
     * @return Form
     */
    public function formSubject(TblSubject $tblSubject = null)
    {

        $tblSubjectAll = Subject::useService()->getSubjectAll();
        $acAcronymAll = array();
        $acNameAll = array();
        array_walk($tblSubjectAll, function (TblSubject $tblSubject) use (&$acAcronymAll, &$acNameAll) {

            if (!in_array($tblSubject->getAcronym(), $acAcronymAll)) {
                array_push($acAcronymAll, $tblSubject->getAcronym());
            }
            if (!in_array($tblSubject->getName(), $acNameAll)) {
                array_push($acNameAll, $tblSubject->getName());
            }
        });

        $Global = $this->getGlobal();
        if (!isset($Global->POST['Subject']) && $tblSubject) {
            $Global->POST['Subject']['Acronym'] = $tblSubject->getAcronym();
            $Global->POST['Subject']['Name'] = $tblSubject->getName();
            $Global->POST['Subject']['Description'] = $tblSubject->getDescription();
            $Global->savePost();
        }

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Fach',
                            array(
                                new AutoCompleter('Subject[Acronym]', 'Kürzel', 'z.B: DE', $acAcronymAll, new Pencil()),
                                new AutoCompleter('Subject[Name]', 'Name', 'z.B: Deutsch', $acNameAll, new Pencil()),
                            ), Panel::PANEL_TYPE_INFO
                        ), 6),
                    new FormColumn(
                        new Panel('Sonstiges',
                            new TextField('Subject[Description]', 'zb: für Fortgeschrittene', 'Beschreibung',
                                new Pencil())
                            , Panel::PANEL_TYPE_INFO
                        ), 6),
                )),
            ))
        );
    }

    /**
     * @param $Id
     * @param $Subject
     *
     * @return Stage
     */
    public function frontendChangeSubject($Id, $Subject)
    {

        $Stage = new Stage('Fach', 'bearbeiten');
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Subject/Create/Subject', new ChevronLeft()));
        $tblSubject = Subject::useService()->getSubjectById($Id);
        $Global = $this->getGlobal();
        if (!isset($Global->POST['Id']) && $tblSubject) {
            $Global->POST['Subject']['Acronym'] = $tblSubject->getAcronym();
            $Global->POST['Subject']['Name'] = $tblSubject->getName();
            $Global->POST['Subject']['Description'] = $tblSubject->getDescription();
            $Global->savePost();
        }
        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Headline('Fach bearbeiten: ' . $tblSubject->getAcronym() . ' ' . $tblSubject->getName()),
                            Subject::useService()->changeSubject($this->formSubject($tblSubject)
                                ->appendFormButton(new Primary('Änderung speichern'))
                                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                , $Subject, $Id)
                        ))
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @param null|array $Category
     *
     * @return Stage
     */
    public function frontendCreateCategory($Category = null)
    {

        $Stage = new Stage('Kategorien', 'erstellen / bearbeiten');
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Subject', new ChevronLeft()));

        $tblCategoryAll = Subject::useService()->getCategoryAll();
        array_walk($tblCategoryAll, function (TblCategory &$tblCategory) {

            $tblCategory->Option = new Standard('', '/Education/Lesson/Subject/Change/Category', new Pencil(),
                    array('Id' => $tblCategory->getId()))
                . ($tblCategory->getIsLocked() ? ''
                    : new Standard('', '/Education/Lesson/Subject/Destroy/Category', new Remove(),
                        array('Id' => $tblCategory->getId())));
        });

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($tblCategoryAll, null, array(
                                'Name' => 'Name',
                                'Description' => 'Beschreibung',
                                'Option' => 'Optionen',
                            ))
                        )
                    ), new Title('Bestehende Kategorien')
                ),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            Subject::useService()->createCategory(
                                $this->formCategory()
                                    ->appendFormButton(new Primary('Kategorie erstellen'))
                                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                , $Category
                            )
                        )
                    ), new Title('Kategorie erstellen')
                )
            ))
        );

        return $Stage;
    }

    /**
     * @param null|TblCategory $tblCategory
     *
     * @return Form
     */
    public function formCategory(TblCategory $tblCategory = null)
    {

        $tblCategoryAll = Subject::useService()->getCategoryAll();
        $acAcronymAll = array();
        $acNameAll = array();
        array_walk($tblCategoryAll, function (TblCategory $tblCategory) use (&$acAcronymAll, &$acNameAll) {

            if (!in_array($tblCategory->getName(), $acNameAll)) {
                array_push($acNameAll, $tblCategory->getName());
            }
        });

        $Global = $this->getGlobal();
        if (!isset($Global->POST['Category']) && $tblCategory) {
            $Global->POST['Category']['Name'] = $tblCategory->getName();
            $Global->POST['Category']['Description'] = $tblCategory->getDescription();
            $Global->savePost();
        }

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Kategorie',
                            array(
                                new AutoCompleter('Category[Name]', 'Name',
                                    'z.B: Soziales und gesellschaftliches Handeln', $acNameAll, new Pencil()),
                            ), Panel::PANEL_TYPE_INFO
                        ), 6),
                    new FormColumn(
                        new Panel('Sonstiges',
                            new TextField('Category[Description]', 'zb: enthält nur Vertiefungskurse', 'Beschreibung',
                                new Pencil())
                            , Panel::PANEL_TYPE_INFO
                        ), 6),
                )),
            ))
        );
    }

    /**
     * @param $Id
     * @param $Category
     *
     * @return Stage
     */
    public function frontendChangeCategory($Id, $Category)
    {

        $Stage = new Stage('Kategorie', 'bearbeiten');
        $Stage->addButton(new Standard('Zurück', '/Education/Lesson/Subject/Create/Category', new ChevronLeft()));
        $tblCategory = Subject::useService()->getCategoryById($Id);
        $Global = $this->getGlobal();
        if (!isset($Global->POST['Id']) && $tblCategory) {
            $Global->POST['Subject']['Name'] = $tblCategory->getName();
            $Global->POST['Subject']['Description'] = $tblCategory->getDescription();
            $Global->savePost();
        }
        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(array(
                            new Headline('Kategorie bearbeiten: ' . $tblCategory->getName() . ' ' . $tblCategory->getDescription()),
                            Subject::useService()->changeCategory($this->formCategory($tblCategory)
            ->appendFormButton(new Primary('Änderung speichern'))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                , $Category, $Id)
                        ))
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @param int $Id
     * @param null|array $Category
     *
     * @return Stage
     */
    public function frontendLinkCategory($Id, $Category = null)
    {

        $Stage = new Stage('Kategorien', 'Verknüpfung');

        $tblGroup = Subject::useService()->getGroupById($Id);

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel('Gruppe',
                                $tblGroup->getName(),
                                Panel::PANEL_TYPE_SUCCESS,
                                new Standard('Zurück zum Dashboard', '/Education/Lesson/Subject', new ChevronLeft())
                            )
                        )
                    ),
                )),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            Subject::useService()->changeGroupCategory(
                                $this->formLinkCategory($tblGroup)
                                    ->appendFormButton(new Primary('Zusammensetzung speichern'))
                                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                , $tblGroup, $Category
                            )
                        )
                    ), new Title($tblGroup->getName() . ' enthält')
                )
            ))
        );

        return $Stage;
    }

    /**
     * @param null|TblGroup $tblGroup
     *
     * @return Form
     */
    public function formLinkCategory(TblGroup $tblGroup = null)
    {

        $tblCategoryAllSelected = $tblGroup->getTblCategoryAll();
        if ($tblCategoryAllSelected) {
            $Global = $this->getGlobal();
            array_walk($tblCategoryAllSelected, function (TblCategory &$tblCategory) use (&$Global) {

                $Global->POST['Category'][$tblCategory->getId()] = $tblCategory->getId();
            });
            $Global->savePost();
        }

        $tblCategoryAllAvailable = Subject::useService()->getCategoryAll();
        array_walk($tblCategoryAllAvailable, function (TblCategory &$tblCategory) {

            $tblSubjectAll = $tblCategory->getTblSubjectAll();
            if ($tblSubjectAll) {
                array_walk($tblSubjectAll, function (TblSubject &$tblSubject) {

                    $tblSubject = $tblSubject->getAcronym();
                });
                $tblSubjectAll = '(' . implode(', ', $tblSubjectAll) . ')';
            } else {
                $tblSubjectAll = '';
            }

            $tblCategory = new CheckBox(
                'Category[' . $tblCategory->getId() . ']',
                $tblCategory->getName() . ' ' . new Muted($tblCategory->getDescription() . ' ' . new Small($tblSubjectAll)),
                $tblCategory->getId()
            );
        });

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Kategorien', $tblCategoryAllAvailable, Panel::PANEL_TYPE_INFO)
                    ),
                )),
            ))
        );
    }

    /**
     * @param int $Id
     * @param null|array $Subject
     *
     * @return Stage
     */
    public function frontendLinkSubject($Id, $Subject = null)
    {

        $Stage = new Stage('Fächer', 'Verknüpfung');

        $tblCategory = Subject::useService()->getCategoryById($Id);

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel('Kategorie',
                                $tblCategory->getName(),
                                Panel::PANEL_TYPE_SUCCESS,
                                new Standard('Zurück zum Dashboard', '/Education/Lesson/Subject', new ChevronLeft())
                            )
                        )
                    ),
                )),
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            Subject::useService()->changeCategorySubject(
                                $this->formLinkSubject($tblCategory)
                                    ->appendFormButton(new Primary('Zusammensetzung speichern'))
                                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert')
                                , $tblCategory, $Subject
                            )
                        )
                    ), new Title($tblCategory->getName() . ' enthält')
                )
            ))
        );

        return $Stage;
    }

    /**
     * @param null|TblCategory $tblCategory
     *
     * @return Form
     */
    public function formLinkSubject(TblCategory $tblCategory = null)
    {

        $tblSubjectAllSelected = $tblCategory->getTblSubjectAll();
        if ($tblSubjectAllSelected) {
            $Global = $this->getGlobal();
            array_walk($tblSubjectAllSelected, function (TblSubject &$tblSubject) use (&$Global) {

                $Global->POST['Subject'][$tblSubject->getId()] = $tblSubject->getId();
            });
            $Global->savePost();
        }

        $tblSubjectAllAvailable = Subject::useService()->getSubjectAll();
        $tblSubjectAllAvailable = $this->getSorter($tblSubjectAllAvailable)->sortObjectList('Name');
        array_walk($tblSubjectAllAvailable, function (TblSubject &$tblSubject) {

            $tblSubject = new CheckBox(
                'Subject[' . $tblSubject->getId() . ']',
                $tblSubject->getName() . ' ' . new Muted($tblSubject->getDescription()),
                $tblSubject->getId()
            );
        });

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Fächer', $tblSubjectAllAvailable, Panel::PANEL_TYPE_INFO)
                    ),
                )),
            ))
        );
    }

    /**
     * @param $Id
     *
     * @return Stage|string
     */
    public function frontendDestroySubject($Id)
    {
        // TODO: Confirmation
        $Stage = new Stage('Fach', 'entfernen');
        $tblSubject = Subject::useService()->getSubjectById($Id);
        if ($tblSubject) {
            $Stage->setContent(Subject::useService()->destroySubject($tblSubject));
        } else {
            return $Stage . new Warning('Fach nicht gefunden!')
            . new Redirect('/Education/Lesson/Subject/Create/Subject');
        }
        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage|string
     */
    public function frontendDestroyCategory($Id)
    {
        // TODO: Confirmation
        $Stage = new Stage('Kategorie', 'entfernen');
        $tblCategory = Subject::useService()->getCategoryById($Id);
        if ($tblCategory) {
            $Stage->setContent(Subject::useService()->destroyCategory($tblCategory));
        } else {
            return $Stage . new Warning('Kategorie nicht gefunden!')
            . new Redirect('/Education/Lesson/Subject/Create/Subject');
        }
        return $Stage;
    }
}
