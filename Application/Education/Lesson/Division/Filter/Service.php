<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 07.06.2018
 * Time: 15:44
 */

namespace SPHERE\Application\Education\Lesson\Division\Filter;

use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionSubject;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Window\Redirect;

/**
 * Class Service
 *
 * @package SPHERE\Application\Education\Lesson\Division\Filter
 */
class Service
{

    /**
     * @param IFormInterface $form
     * @param TblDivisionSubject $tblDivisionSubject
     * @param null $Data
     *
     * @return IFormInterface|string
     */
    public static function setFilter(IFormInterface $form, TblDivisionSubject $tblDivisionSubject, $Data = null)
    {

        /**
         * Skip to Frontend
         */
        if ($Data === null) {
            return $form;
        }

        $filter = new Filter($tblDivisionSubject);
        $filter->setFilter($Data);
        $filter->save();

        return new Success('Die verfügbaren Schüler werden gefiltert.', new \SPHERE\Common\Frontend\Icon\Repository\Success())
            . new Redirect('/Education/Lesson/Division/SubjectStudent/Add', Redirect::TIMEOUT_SUCCESS, array(
                'Id' => ($tblDivision = $tblDivisionSubject->getTblDivision()) ? $tblDivision->getId() : 0,
                'DivisionSubjectId' => $tblDivisionSubject->getId()
            ));
    }

    /**
     * @param TblDivision $tblDivision
     * @param bool $isAccordion
     *
     * @return array|bool|Warning
     */
    public static function getDivisionMessageTable(TblDivision $tblDivision, $isAccordion = false)
    {

        $list = array();
        if (($tblDivisionSubjectAll = Division::useService()->getDivisionSubjectByDivision($tblDivision))) {
            foreach ($tblDivisionSubjectAll as $tblDivisionSubject) {
                if ($tblDivisionSubject->getTblSubjectGroup()) {
                    $filter = new Filter($tblDivisionSubject);
                    $filter->load();

                    $list = $filter->getPersonAllWhereFilterIsNotFulfilled($list);
                }
            }
        }

        if (!empty($list)) {
            $contentTable = array();
            $count = 1;
            $countMessages = 0;

            list($contentTable, $countMessages) = self::formatFilterListMessages($list, $contentTable, $count,
                $countMessages);

            if ($isAccordion) {
                return array(
                    'Header' => 'Klasse ' . $tblDivision->getDisplayName() . ' (' . $countMessages . ' Meldungen)',
                    'Content' => new TableData(
                    $contentTable,
                    null,
                    array(
                        'Name' => 'Schüler',
                        'Field' => 'Eigenschaft / Feld',
                        'Value' => 'Personenverwaltung',
                        'DivisionSubjects' => 'Bildungsmodul'
                    ),
                    false
                    )
                );
            } else {
                return new Warning(
                    new Exclamation() . new Bold(' Folgende Einstellungen stimmen nicht mit der Personenverwaltung überein:')
                    . '</br></br>'
                    . new TableData(
                        $contentTable,
                        null,
                        array(
                            'Name' => 'Schüler',
                            'Field' => 'Eigenschaft / Feld',
                            'Value' => 'Personenverwaltung',
                            'DivisionSubjects' => 'Bildungsmodul'
                        ),
                        false
                    )
                );
            }
        }

        return false;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|Warning
     */
    public static function getPersonMessageTable(TblPerson $tblPerson)
    {
        if (($tblStudent = $tblPerson->getStudent())
            && ($tblDivisionList = $tblStudent->getCurrentDivisionList())
        ) {
            $list = array();
            foreach ($tblDivisionList as $tblDivision) {
                if (($tblDivisionSubjectAll = Division::useService()->getDivisionSubjectByDivision($tblDivision))) {
                    foreach ($tblDivisionSubjectAll as $tblDivisionSubject) {
                        if (($tblSubject = $tblDivisionSubject->getServiceTblSubject())
                            && ($tblSubjectGroup = $tblDivisionSubject->getTblSubjectGroup())
                        ){
                            $filter = new Filter($tblDivisionSubject);
                            $filter->load();

                            $list = $filter->getIsNotFulfilledByPerson(
                                $tblPerson,
                                $tblSubject,
                                $tblSubjectGroup,
                                $tblDivisionSubject,
                                $list,
                                true
                            );
                        }
                    }
                }
            }

            if (!empty($list)) {
                $contentTable = array();
                $count = 1;
                $countMessages = 0;

                list($contentTable, $countMessages) = self::formatFilterListMessages($list, $contentTable, $count,
                    $countMessages);

                return new Warning(
                    new Exclamation() . new Bold(' Folgende Einstellungen stimmen nicht mit der Personenverwaltung überein:')
                    . '</br></br>'
                    . new TableData(
                        $contentTable,
                        null,
                        array(
                            'Field' => 'Eigenschaft / Feld',
                            'Value' => 'Personenverwaltung',
                            'DivisionSubjects' => 'Bildungsmodul'
                        ),
                        false
                    )
                );
            }
        }

        return false;
    }

    /**
     * @param $list
     * @param $contentTable
     * @param $count
     * @param $countMessages
     *
     * @return array
     */
    private static function formatFilterListMessages($list, $contentTable, &$count, $countMessages)
    {

        foreach ($list as $personId => $filters) {
            if (($tblPerson = Person::useService()->getPersonById($personId))
                && is_array($filters)
            ) {
                foreach ($filters as $identifier => $filterArray) {
                    if (is_array($filterArray)) {
                        foreach ($filterArray as $item) {
                            $contentTable[$count]['Name'] = $tblPerson->getLastFirstName();

                            if (isset($item['Field'])) {
                                $contentTable[$count]['Field'] = $item['Field'];
                            } else {
                                $contentTable[$count]['Field'] = '';
                            }

                            if (isset($item['Value'])) {
                                $contentTable[$count]['Value'] = $item['Value'];
                            } else {
                                $contentTable[$count]['Value'] = '';
                            }

                            // todo links zu gruppen
                            if (isset($item['DivisionSubjects']) && is_array($item['DivisionSubjects'])) {
                                foreach ($item['DivisionSubjects'] as $divisionSubjectId => $text) {
                                    $countMessages++;
                                    if (isset($contentTable[$count]['DivisionSubjects'])) {
                                        $contentTable[$count]['DivisionSubjects'] .= new Container($text);
                                    } else {
                                        $contentTable[$count]['DivisionSubjects'] = new Container($text);
                                    }
                                }
                            } else {
                                $contentTable[$count]['DivisionSubjects'] = '';
                            }

                            $count++;
                        }
                    }
                }
            }
        }

        return array($contentTable, $countMessages);
    }
}