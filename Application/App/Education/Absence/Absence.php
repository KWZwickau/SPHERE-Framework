<?php
namespace SPHERE\Application\App\Education\Absence;

use SPHERE\Application\App\AppException;
use SPHERE\Application\App\Dispatcher;
use SPHERE\Application\App\ModuleInterface;
use SPHERE\Application\App\Response\Code\Response200;
use SPHERE\Application\App\Response\Code\Response403;
use SPHERE\Application\App\Response\Code\Response422;
use SPHERE\Application\Education\Absence\Absence as AbsenceEducation;
use SPHERE\Application\Education\Absence\Service\Entity\TblAbsence;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\ParentStudentAccess\OnlineAbsence\OnlineAbsence;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Main;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 *
 */
class Absence implements ModuleInterface
{
    /**
     * @throws AppException
     */
    public static function registerModule(): void
    {

        /** @var Dispatcher $dispatcher */
        $dispatcher = Main::getDispatcher();
        $route = $dispatcher::createRoute(__NAMESPACE__ . '/Load', __CLASS__ . '::getAbsenceLoad');
        $dispatcher::registerRoute($route);
        $route = $dispatcher::createRoute(__NAMESPACE__ . '/Add', __CLASS__ . '::getAbsenceAdd');
        $dispatcher::registerRoute($route);
    }

    public static function useService()
    {
        // TODO: Implement useService() method.
    }

    /**
     * @return JsonResponse
     */
    public static function getAbsenceLoad(): JsonResponse
    {
        list($tblPersonList, $source) = OnlineAbsence::useService()->getPersonListAndSourceFromAccountBySession();

        $result = array();
        if ($tblPersonList) {
            /** @var TblPerson $tblPerson */
            foreach ($tblPersonList as $tblPerson) {
                $hasAbsenceTypeOptions = false;
                $absenceList = array();
                if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndDate($tblPerson))
                    && ($tableData = AbsenceEducation::useService()->getStudentAbsenceDataForParentStudentAccess($tblPerson, $tblStudentEducation, $hasAbsenceTypeOptions))
                ) {
                    foreach ($tableData as $item) {
                        $temp = [
                            'FromDate' => (new DateTime($item['FromDate']))->format('c'),
                            'ToDate' => $item['ToDate'] === false ? null : (new DateTime($item['ToDate']))->format('c'),
                            'DaysCount' => $item['DaysCount'],
//                            'LessonsCount' => $item['LessonsCount'],
                            'Lessons' => $item['Lessons'],
                            'StatusShort' => $item['StatusShort'],
                            'PersonCreator' => $item['PersonCreator'],
                            'IsCertificateRelevant' => $item['IsCertificateRelevant']
                        ];
                        if ($hasAbsenceTypeOptions) {
                            $temp['Type'] = $item['Type'];
                        }

                        $absenceList[] = $temp;
                    }
                }


                $result[] = array(
                    'Person' => $tblPerson->getLastFirstName(),
                    'DivisionCourse' => DivisionCourse::useService()->getCurrentMainCoursesByPersonAndDate($tblPerson),
                    'AbsenceList' => $absenceList,
                    'Links' => [
                        'loadContent' => [
                            'Method' => 'GET',
//                            'Name' => 'Fehlzeit hinzufügen',
                            'Url' => 'https://' . $_SERVER['HTTP_HOST'] . '/RestApi/Education/Absence/Add',
                            'Parameters' => [
                                'AccountId' => ($tblAccount = Account::useService()->getAccountBySession()) ? $tblAccount->getId() : null,
                                'PersonId' => $tblPerson->getId()
                            ]
                        ]
                    ]
                );
            }
        }

        return new Response200($result);
    }

    /**
     * @param null $PersonId
     * @param null $FromDate
     * @param null $ToDate
     * @param null $IsFullDay
     * @param null $Remark
     * @param null $Type
     * @param null $UE
     *
     * @return JsonResponse
     */
    public static function getAbsenceAdd($PersonId = null, $FromDate = null, $ToDate = null, $IsFullDay = null, $Remark = null, $Type = null, $UE = null): JsonResponse
    {
        list($tblPersonList, $source) = OnlineAbsence::useService()->getPersonListAndSourceFromAccountBySession();

        // prüfen ob Fehlzeiten vom angemeldeten Account für diese Person angelegt werden dürfen
        if (isset($tblPersonList[$PersonId])
            && ($tblPerson = Person::useService()->getPersonById($PersonId))
        ) {
            $Data['FromDate'] = $FromDate;
            $Data['ToDate'] = $ToDate;
            $Data['IsFullDay'] = $IsFullDay;
            // Unterrichtseinheiten falls, vorhanden richtig formatieren
            if (!empty($UE)) {
                $temp = json_decode($UE);
                if (is_array($temp)) {
                    foreach ($temp as $item) {
                        $Data['UE'][$item] = $item;
                    }
                }
            }
            $Data['Remark'] = $Remark ?? '';
            // umwandeln von Theorie und Praxis in entsprechenden int
            if ($Type == 'Theorie') {
                $Data['Type'] = TblAbsence::VALUE_TYPE_THEORY;
            } elseif ($Type == 'Praxis') {
                $Data['Type'] = TblAbsence::VALUE_TYPE_PRACTICE;
            } else {
                $Data['Type'] = null;
            }

            if (($errorList = AbsenceEducation::useService()->checkFormOnlineAbsence($Data, $tblPerson, $source, false))) {
                return new Response422(array('error' => 'Invalid Data', 'Data' => $errorList));
            } else {
                if (AbsenceEducation::useService()->createOnlineAbsence($Data, $tblPerson, $source)) {
                    return new Response200(array('message' => 'Fehlzeit erfolgreich hinzugefügt'));
                } else {
                    return new Response422(array('message' => 'Fehlzeit konnte nicht hinzugefügt werden'));
                }
            }
        } else {
            return new Response403(array('message' => 'Zugriff verweigert'));
        }
    }
}