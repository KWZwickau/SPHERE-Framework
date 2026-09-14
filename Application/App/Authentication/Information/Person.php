<?php
namespace SPHERE\Application\App\Authentication\Information;

use SPHERE\Application\App\Dispatcher;
use SPHERE\Application\App\ModuleInterface;
use SPHERE\Application\App\Response\Code\Response200;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Main;
use Symfony\Component\HttpFoundation\JsonResponse;

class Person implements ModuleInterface
{

    /**
     * @return void
     */
    public static function registerModule(): void
    {

        /** @var Dispatcher $dispatcher */
        $dispatcher = Main::getDispatcher();
        $route = $dispatcher::createRoute(__NAMESPACE__ . '/Person/Load', __CLASS__ . '::getPersonData');
        $dispatcher::registerRoute($route);
    }

    public static function useService()
    {
        // TODO: Implement useService() method.
    }

    /**
     * @return JsonResponse
     */
    public static function getPersonData(): JsonResponse
    {
        $result = [];
        $tblPerson = false;
        $Person = array();
        $Type = array();
        $searchStudent = false;
        $PersonList = array();
        if (($tblAccount = Account::useService()->getAccountBySession())) {
            if(($tblPersonList = Account::useService()->getPersonALlByAccount($tblAccount)))  {
                $tblPerson = current($tblPersonList);
            }
        }

        if($tblPerson){
            /** @var $tblPerson TblPerson */
            $Person['id'] = $tblPerson->getId();
            $Person['firstName'] = $tblPerson->getFirstName();
            $Person['lastName'] = $tblPerson->getLastName();

            if($tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_CUSTODY)){
                if(Group::useService()->getMemberByPersonAndGroup($tblPerson, $tblGroup)){
                    $Type[TblGroup::META_TABLE_CUSTODY] = 'Sorgeberechtigt';
                    // looking for students
                    $searchStudent = true;
                }
            }
            if($tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_TEACHER)){
                if(Group::useService()->getMemberByPersonAndGroup($tblPerson, $tblGroup)){
                    $Type[TblGroup::META_TABLE_TEACHER] = 'Lehrer';
                }
            }
            if($tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_STAFF)){
                if(Group::useService()->getMemberByPersonAndGroup($tblPerson, $tblGroup)){
                    $Type[TblGroup::META_TABLE_STAFF] = 'Mitarbeiter';
                }
            }
            if($tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_STUDENT)){
                if(Group::useService()->getMemberByPersonAndGroup($tblPerson, $tblGroup)){
                    $Type[TblGroup::META_TABLE_STUDENT] = 'Schüler';
                }

            }
            if($searchStudent && ($tblToPersonList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson))){
                foreach($tblToPersonList as $tblToPerson){
                    if(($tblPersonStudent = $tblToPerson->getServiceTblPersonTo())){
                        $YearList = array();
                        $YearCurrentList = array();
                        if(($tblStudentEducationList =  DivisionCourse::useService()->getStudentEducationListByPerson($tblPersonStudent))){
                            foreach($tblStudentEducationList as $tblStudentEducation){
                                if(($tblYear = $tblStudentEducation->getServiceTblYear())){
                                    $YearList[$tblYear->getId()] = $tblYear->getYear()
                                        .($tblYear->getDescription() ? ' '.$tblYear->getDescription() : '');
                                }
                            }
                        }
                        if(($tblYearCurrentList = Term::useService()->getYearByNow())){
                            foreach($tblYearCurrentList as $tblYearCurrent){
                                $YearCurrentList[$tblYearCurrent->getId()] = $tblYearCurrent->getYear()
                                    .($tblYearCurrent->getDescription() ? ' '.$tblYearCurrent->getDescription() : '');
                            }
                        }
                        $PersonList['id'] = $tblPersonStudent->getId();
                        $PersonList['name'] = $tblPersonStudent->getFirstName().' '.$tblPersonStudent->getLastName();
                        $PersonList['years'] = $YearList;
                        $PersonList['currentyears'] = $YearCurrentList;
                    }
                }
            }

            $result['account'] = array(
                'person' => $Person,
                'types' => $Type
            );
            $result['persons'] = $PersonList;
        }

        return new Response200($result);
    }
}