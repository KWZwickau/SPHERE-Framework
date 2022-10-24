<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse\Frontend;

use SPHERE\Application\Api\Education\DivisionCourse\ApiDivisionCourseMember;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMemberType;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\MinusSign;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\ResizeVertical;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullLeft;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Repository\Title;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Strikethrough;
use SPHERE\Common\Frontend\Text\Repository\Warning as WarningText;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class FrontendMember extends Extension implements IFrontendInterface
{
    const PAGE_LENGTH = 15;

    protected function getInteractiveLeft(): array
    {
        return array(
            'columnDefs' => array(
                array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 0),
                array('orderable' => false, 'width' => '1%', 'targets' => -1),
            ),
            'pageLength' => self::PAGE_LENGTH,
            'responsive' => false
        );
    }

    protected function getInteractiveRight(): array
    {
        return array(
            'columnDefs' => array(
                array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 0),
                array('orderable' => false, 'width' => '50%', 'targets' => -1),
            ),
            'pageLength' => self::PAGE_LENGTH,
            'responsive' => false
        );
    }

    /**
     * @param null $DivisionCourseId
     * @param null $Filter
     *
     * @return Stage
     */
    public function frontendDivisionCourseDivisionTeacher($DivisionCourseId = null, $Filter = null): Stage
    {
        $stage = new Stage('Klassenlehrer', '');
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            $stage->setTitle($tblDivisionCourse->getDivisionTeacherName());
            $stage->addButton((new Standard('Zurück', '/Education/Lesson/DivisionCourse/Show', new ChevronLeft(),
                array('DivisionCourseId' => $tblDivisionCourse->getId(), 'Filter' => $Filter))));
            $text = $tblDivisionCourse->getTypeName() . ' ' . new Bold($tblDivisionCourse->getName());
            $stage->setDescription('der ' . $text . ' Schuljahr ' . new Bold($tblDivisionCourse->getYearName()));
            if ($tblDivisionCourse->getDescription()) {
                $stage->setMessage($tblDivisionCourse->getDescription());
            }

            $stage->setContent(
                DivisionCourse::useService()->getDivisionCourseHeader($tblDivisionCourse)
                . ApiDivisionCourseMember::receiverBlock($this->loadDivisionTeacherContent($DivisionCourseId), 'DivisionTeacherContent')
            );
        } else {
            $stage->addButton((new Standard('Zurück', '/Education/Lesson/DivisionCourse', new ChevronLeft())));
            $stage->setContent(new Warning('Kurs nicht gefunden', new Exclamation()));
        }

        return $stage;
    }

    /**
     * @param $DivisionCourseId
     *
     * @return string
     */
    public function loadDivisionTeacherContent($DivisionCourseId): string
    {
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            $text = $tblDivisionCourse->getDivisionTeacherName();
            $selectedList = array();
            if (($tblMemberList =  DivisionCourse::useService()->getDivisionCourseMemberListBy($tblDivisionCourse,
                TblDivisionCourseMemberType::TYPE_DIVISION_TEACHER, false, false))
            ) {
                foreach ($tblMemberList as $tblMember) {
                    if (($tblPerson = $tblMember->getServiceTblPerson())) {
                        $selectedList[$tblPerson->getId()] = array(
                            'Name' => $tblPerson->getLastFirstName(),
                            'Address' => ($tblAddress = $tblPerson->fetchMainAddress()) ? $tblAddress->getGuiString() : new WarningText('Keine Adresse hinterlegt'),
                            'Description' => $tblMember->getDescription(),
                            'Option' => (new Standard('', ApiDivisionCourseMember::getEndpoint(), new MinusSign(), array(),  $text . ' entfernen'))
                                ->ajaxPipelineOnClick(ApiDivisionCourseMember::pipelineRemoveDivisionTeacher($tblDivisionCourse->getId(), $tblMember->getId()))
                        );
                    }
                }
            }

            $availableList = array();
            if (($tblGroup = Group::useService()->getGroupByMetaTable('TEACHER'))
                && ($tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup))
            ) {
                foreach ($tblPersonList as $tblPerson) {
                    if (!isset($selectedList[$tblPerson->getId()])) {
                        $availableList[$tblPerson->getId()] = array(
                            'Name' => $tblPerson->getLastFirstName(),
                            'Address' => ($tblAddress = $tblPerson->fetchMainAddress()) ? $tblAddress->getGuiString() : new WarningText('Keine Adresse hinterlegt'),
                            'Option' => (new Form(
                                new FormGroup(
                                    new FormRow(array(
                                        new FormColumn(
                                            new TextField('Data[Description]', 'z.B.: Stellvertreter')
                                            , 9),
                                        new FormColumn(
                                            (new Standard('', ApiDivisionCourseMember::getEndpoint(), new PlusSign(), array(), 'Hinzufügen'))
                                                ->ajaxPipelineOnClick(ApiDivisionCourseMember::pipelineAddDivisionTeacher($DivisionCourseId, $tblPerson->getId()))
                                            , 3)
                                    ))
                                )
                            ))->__toString()
                        );
                    }
                }
            }

            $columns = array(
                'Name' => 'Name',
                'Address' => 'Adresse',
                'Description' => 'Beschreibung',
                'Option' => ''
            );
            if ($selectedList) {
                $left = (new TableData($selectedList, new Title('Ausgewählte', $text), $columns, $this->getInteractiveLeft()))
                    ->setHash(__NAMESPACE__ . 'DivisionTeacherSelected');
            } else {
                $left = new Info('Keine ' . $text . ' ausgewählt');
            }
            if ($availableList) {
                unset($columns['Description']);
                $right = (new TableData($availableList, new Title('Verfügbare', $text), $columns, $this->getInteractiveRight()))
                    ->setHash(__NAMESPACE__ . 'DivisionTeacherAvailable');
            } else {
                $right = new Info('Keine weiteren ' . $text . ' verfügbar');
            }

            return new Layout(new LayoutGroup(array(new LayoutRow(array(
                new LayoutColumn($left, 6),
                new LayoutColumn($right, 6)
            )))));
        }

        return new Danger('Kurs nicht gefunden!', new Exclamation());
    }

    /**
     * @param null $DivisionCourseId
     * @param null $Filter
     *
     * @return Stage
     */
    public function frontendDivisionCourseRepresentative($DivisionCourseId = null, $Filter = null): Stage
    {
        $stage = new Stage('Schülersprecher', '');
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            $stage->addButton((new Standard('Zurück', '/Education/Lesson/DivisionCourse/Show', new ChevronLeft(),
                array('DivisionCourseId' => $tblDivisionCourse->getId(), 'Filter' => $Filter))));
            $text = $tblDivisionCourse->getTypeName() . ' ' . new Bold($tblDivisionCourse->getName());
            $stage->setDescription('der ' . $text . ' Schuljahr ' . new Bold($tblDivisionCourse->getYearName()));
            if ($tblDivisionCourse->getDescription()) {
                $stage->setMessage($tblDivisionCourse->getDescription());
            }

            $stage->setContent(
                DivisionCourse::useService()->getDivisionCourseHeader($tblDivisionCourse)
                . ApiDivisionCourseMember::receiverBlock($this->loadRepresentativeContent($DivisionCourseId), 'RepresentativeContent')
            );
        } else {
            $stage->addButton((new Standard('Zurück', '/Education/Lesson/DivisionCourse', new ChevronLeft())));
            $stage->setContent(new Warning('Kurs nicht gefunden', new Exclamation()));
        }

        return $stage;
    }

    /**
     * @param $DivisionCourseId
     *
     * @return string
     */
    public function loadRepresentativeContent($DivisionCourseId): string
    {
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            $text = 'Schülersprecher';
            $selectedList = array();
            if (($tblMemberList = DivisionCourse::useService()->getDivisionCourseMemberListBy($tblDivisionCourse,
                TblDivisionCourseMemberType::TYPE_REPRESENTATIVE, false, false))
            ) {
                foreach ($tblMemberList as $tblMember) {
                    if (($tblPerson = $tblMember->getServiceTblPerson())) {
                        $selectedList[$tblPerson->getId()] = array(
                            'Name' => $tblPerson->getLastFirstName(),
                            'Address' => ($tblAddress = $tblPerson->fetchMainAddress()) ? $tblAddress->getGuiString() : new WarningText('Keine Adresse hinterlegt'),
                            'Description' => $tblMember->getDescription(),
                            'Option' => (new Standard('', ApiDivisionCourseMember::getEndpoint(), new MinusSign(), array(),  $text . ' entfernen'))
                                ->ajaxPipelineOnClick(ApiDivisionCourseMember::pipelineRemoveRepresentative($tblDivisionCourse->getId(), $tblMember->getId()))
                        );
                    }
                }
            }

            $availableList = array();
            if (($tblPersonList = $tblDivisionCourse->getStudents())) {
                foreach ($tblPersonList as $tblPerson) {
                    if (!isset($selectedList[$tblPerson->getId()])) {
                        $availableList[$tblPerson->getId()] = array(
                            'Name' => $tblPerson->getLastFirstName(),
                            'Address' => ($tblAddress = $tblPerson->fetchMainAddress()) ? $tblAddress->getGuiString() : new WarningText('Keine Adresse hinterlegt'),
                            'Option' => (new Form(
                                new FormGroup(
                                    new FormRow(array(
                                        new FormColumn(
                                            new TextField('Data[Description]', 'z.B.: Stellvertreter')
                                            , 9),
                                        new FormColumn(
                                            (new Standard('', ApiDivisionCourseMember::getEndpoint(), new PlusSign(), array(), 'Hinzufügen'))
                                                ->ajaxPipelineOnClick(ApiDivisionCourseMember::pipelineAddRepresentative($DivisionCourseId, $tblPerson->getId()))
                                            , 3)
                                    ))
                                )
                            ))->__toString()
                        );
                    }
                }
            }

            $columns = array(
                'Name' => 'Name',
                'Address' => 'Adresse',
                'Description' => 'Beschreibung',
                'Option' => ''
            );
            if ($selectedList) {
                $left = (new TableData($selectedList, new Title('Ausgewählte', $text), $columns, $this->getInteractiveLeft()))
                    ->setHash(__NAMESPACE__ . 'RepresentativeSelected');
            } else {
                $left = new Info('Keine ' . $text . ' ausgewählt');
            }
            if ($availableList) {
                unset($columns['Description']);
                $right = (new TableData($availableList, new Title('Verfügbare', $text), $columns, $this->getInteractiveRight()))
                    ->setHash(__NAMESPACE__ . 'RepresentativeAvailable');
            } else {
                $right = new Info('Keine weiteren ' . $text . ' verfügbar');
            }

            return new Layout(new LayoutGroup(array(new LayoutRow(array(
                new LayoutColumn($left, 6),
                new LayoutColumn($right, 6)
            )))));
        }

        return new Danger('Kurs nicht gefunden!', new Exclamation());
    }

    /**
     * @param null $DivisionCourseId
     * @param null $Filter
     *
     * @return Stage
     */
    public function frontendDivisionCourseCustody($DivisionCourseId = null, $Filter = null): Stage
    {
        $stage = new Stage('Elternvertreter', '');
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            $stage->addButton((new Standard('Zurück', '/Education/Lesson/DivisionCourse/Show', new ChevronLeft(),
                array('DivisionCourseId' => $tblDivisionCourse->getId(), 'Filter' => $Filter))));
            $text = $tblDivisionCourse->getTypeName() . ' ' . new Bold($tblDivisionCourse->getName());
            $stage->setDescription('der ' . $text . ' Schuljahr ' . new Bold($tblDivisionCourse->getYearName()));
            if ($tblDivisionCourse->getDescription()) {
                $stage->setMessage($tblDivisionCourse->getDescription());
            }

            $stage->setContent(
                DivisionCourse::useService()->getDivisionCourseHeader($tblDivisionCourse)
                . ApiDivisionCourseMember::receiverBlock($this->loadCustodyContent($DivisionCourseId), 'CustodyContent')
            );
        } else {
            $stage->addButton((new Standard('Zurück', '/Education/Lesson/DivisionCourse', new ChevronLeft())));
            $stage->setContent(new Warning('Kurs nicht gefunden', new Exclamation()));
        }

        return $stage;
    }

    /**
     * @param $DivisionCourseId
     *
     * @return string
     */
    public function loadCustodyContent($DivisionCourseId): string
    {
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            $text = 'Elternvertreter';
            $selectedList = array();
            if (($tblMemberList = DivisionCourse::useService()->getDivisionCourseMemberListBy($tblDivisionCourse,
                TblDivisionCourseMemberType::TYPE_CUSTODY, false, false))
            ) {
                foreach ($tblMemberList as $tblMember) {
                    if (($tblPerson = $tblMember->getServiceTblPerson())) {
                        $selectedList[$tblPerson->getId()] = array(
                            'Name' => $tblPerson->getLastFirstName(),
                            'Address' => ($tblAddress = $tblPerson->fetchMainAddress()) ? $tblAddress->getGuiString() : new WarningText('Keine Adresse hinterlegt'),
                            'Description' => $tblMember->getDescription(),
                            'Option' => (new Standard('', ApiDivisionCourseMember::getEndpoint(), new MinusSign(), array(),  $text . ' entfernen'))
                                ->ajaxPipelineOnClick(ApiDivisionCourseMember::pipelineRemoveCustody($tblDivisionCourse->getId(), $tblMember->getId()))
                        );
                    }
                }
            }

            $availableList = array();
            if (($tblRelationshipType = Relationship::useService()->getTypeByName('Sorgeberechtigt'))
                && ($tblPersonList = $tblDivisionCourse->getStudents())
            ) {
                foreach ($tblPersonList as $tblPerson) {
                    if (($tblRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, $tblRelationshipType))) {
                        foreach ($tblRelationshipList as $tblToPerson) {
                            if (($tblPersonCustody = $tblToPerson->getServiceTblPersonFrom())) {
                                if (!isset($selectedList[$tblPersonCustody->getId()])) {
                                    $availableList[$tblPersonCustody->getId()] = array(
                                        'Name' => $tblPersonCustody->getLastFirstName(),
                                        'Address' => ($tblAddress = $tblPersonCustody->fetchMainAddress())
                                            ? $tblAddress->getGuiString() : new WarningText('Keine Adresse hinterlegt'),
                                        'Option' => (new Form(
                                            new FormGroup(
                                                new FormRow(array(
                                                    new FormColumn(
                                                        new TextField('Data[Description]', 'z.B.: Stellvertreter')
                                                        , 9),
                                                    new FormColumn(
                                                        (new Standard('', ApiDivisionCourseMember::getEndpoint(), new PlusSign(), array(), 'Hinzufügen'))
                                                            ->ajaxPipelineOnClick(ApiDivisionCourseMember::pipelineAddCustody($DivisionCourseId, $tblPersonCustody->getId()))
                                                        , 3)
                                                ))
                                            )
                                        ))->__toString()
                                    );
                                }
                            }
                        }
                    }
                }
            }

            $columns = array(
                'Name' => 'Name',
                'Address' => 'Adresse',
                'Description' => 'Beschreibung',
                'Option' => ''
            );
            if ($selectedList) {
                $left = (new TableData($selectedList, new Title('Ausgewählte', $text), $columns, $this->getInteractiveLeft()))
                    ->setHash(__NAMESPACE__ . 'CustodySelected');
            } else {
                $left = new Info('Keine ' . $text . ' ausgewählt');
            }
            if ($availableList) {
                unset($columns['Description']);
                $right = (new TableData($availableList, new Title('Verfügbare', $text), $columns, $this->getInteractiveRight()))
                    ->setHash(__NAMESPACE__ . 'CustodyAvailable');
            } else {
                $right = new Info('Keine weiteren ' . $text . ' verfügbar');
            }

            return new Layout(new LayoutGroup(array(new LayoutRow(array(
                new LayoutColumn($left, 6),
                new LayoutColumn($right, 6)
            )))));
        }

        return new Danger('Kurs nicht gefunden!', new Exclamation());
    }

    /**
     * @param null $DivisionCourseId
     * @param string $MemberTypeIdentifier
     * @param null $Filter
     *
     * @return Stage
     */
    public function frontendMemberSort($DivisionCourseId = null, string $MemberTypeIdentifier = '', $Filter = null): Stage
    {
        if (($tblDivisionCourseMemberType = DivisionCourse::useService()->getDivisionCourseMemberTypeByIdentifier($MemberTypeIdentifier))) {
            $member = $tblDivisionCourseMemberType->getName();
        } else {
            $member = 'Mitglieder';
        }

        $stage = new Stage($member . ' sortieren', '');
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            if ($MemberTypeIdentifier == TblDivisionCourseMemberType::TYPE_DIVISION_TEACHER) {
                $stage->setTitle($tblDivisionCourse->getDivisionTeacherName());
            }
            $text = $tblDivisionCourse->getTypeName() . ' ' . new Bold($tblDivisionCourse->getName());
            $stage->setDescription('der ' . $text . ' Schuljahr ' . new Bold($tblDivisionCourse->getYearName()));
            if ($tblDivisionCourse->getDescription()) {
                $stage->setMessage($tblDivisionCourse->getDescription());
            }

            $buttonList[] = (new Standard('Zurück', '/Education/Lesson/DivisionCourse/Show', new ChevronLeft(),
                array('DivisionCourseId' => $tblDivisionCourse->getId(), 'Filter' => $Filter)));
            $buttonList[] = (new Standard(
                'Sortierung alphabetisch', ApiDivisionCourseMember::getEndpoint(), new ResizeVertical()))
                ->ajaxPipelineOnClick(ApiDivisionCourseMember::pipelineOpenSortMemberModal($tblDivisionCourse->getId(), $MemberTypeIdentifier,  'Sortierung alphabetisch'));
            $buttonList[] = (new Standard(
                'Sortierung Geschlecht (alphabetisch)', ApiDivisionCourseMember::getEndpoint(), new ResizeVertical()))
                ->ajaxPipelineOnClick(ApiDivisionCourseMember::pipelineOpenSortMemberModal($tblDivisionCourse->getId(), $MemberTypeIdentifier, 'Sortierung Geschlecht (alphabetisch)'));

            $stage->setContent(
                ApiDivisionCourseMember::receiverModal()
                . DivisionCourse::useService()->getDivisionCourseHeader($tblDivisionCourse)
                . new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn($buttonList),
                            new LayoutColumn(
                                ApiDivisionCourseMember::receiverBlock($this->loadSortMemberContent($DivisionCourseId, $MemberTypeIdentifier), 'SortMemberContent')
                            )
                        ))
                    ))
                ))
            );
        } else {
            $stage->addButton((new Standard('Zurück', '/Education/Lesson/DivisionCourse', new ChevronLeft())));
            $stage->setContent(new Warning('Kurs nicht gefunden', new Exclamation()));
        }

        return $stage;
    }

    /**
     * @param $DivisionCourseId
     * @param string $MemberTypeIdentifier
     *
     * @return string
     */
    public function loadSortMemberContent($DivisionCourseId, string $MemberTypeIdentifier): string
    {
        $memberTable = array();
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && $tblMemberList = DivisionCourse::useService()->getDivisionCourseMemberListBy($tblDivisionCourse, $MemberTypeIdentifier, true, false)
        ) {
            $count = 0;
            foreach ($tblMemberList as $tblMember) {
                if (($tblPerson = $tblMember->getServiceTblPerson())){
                    $count++;
                    $isInActive = $tblMember->isInActive();

                    $name = new ResizeVertical() . ' ' . $tblPerson->getLastFirstName();
                    $address = ($tblAddress = $tblPerson->fetchMainAddress())
                        ? $tblAddress->getGuiString()
                        : new WarningText('Keine Adresse hinterlegt');
                    $birthday = '';
                    $gender = '';
                    if (($tblCommon = Common::useService()->getCommonByPerson($tblPerson))) {
                        if ($tblCommon->getTblCommonBirthDates()) {
                            $birthday = $tblCommon->getTblCommonBirthDates()->getBirthday();
                            if ($tblGender = $tblCommon->getTblCommonBirthDates()->getTblCommonGender()) {
                                $gender = $tblGender->getShortName();
                            }
                        }
                    }

                    $description = $tblMember->getDescription();

                    $memberTable[] = array(
                        'Number' => $isInActive ? new Strikethrough($count) : $count,
                        'Name' => new PullClear(new PullLeft($isInActive ? new Strikethrough($name) : $name)),
                        'Description' => $isInActive ? new Strikethrough($description) : $description,
                        'Gender' => $isInActive ? new Strikethrough($gender) : $gender,
                        'Birthday' => $isInActive ? new Strikethrough($birthday) : $birthday,
                        'Address' => $isInActive ? new Strikethrough($address) : $address,
                    );
                }
            }
        }

        $columns = array(
            'Number'        => '#',
            'Name'          => 'Name',
            'Description'   => 'Be&shy;schreib&shy;ung',
            'Gender'        => 'Ge&shy;schlecht',
            'Birthday'      => 'Geburts&shy;datum',
            'Address'       => 'Adresse'
        );

        if ($MemberTypeIdentifier == TblDivisionCourseMemberType::TYPE_STUDENT) {
            unset($columns['Description']);
        }

        return new TableData($memberTable, null, $columns,
            array(
                'rowReorderColumn' => 1,
                'ExtensionRowReorder' => array(
                    'Enabled' => true,
                    'Url'     => '/Api/Education/ClassRegister/Reorder',
                    'Data'    => array('DivisionCourseId' => $DivisionCourseId, 'MemberTypeIdentifier' => $MemberTypeIdentifier)
                ),
                'columnDefs' => array(
                    array('type'  => Consumer::useService()->getGermanSortBySetting(), 'targets' => 1),
                    array('width' => '40%', 'targets' => -1),
                ),
                'pageLength' => -1,
                'paging' => false,
                'info' => false,
                'searching' => false,
                'responsive' => false
            )
        );
    }
}