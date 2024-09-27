<?php

namespace SPHERE\Application\RestApi\Education\Absence;

use DateTime;
use SPHERE\Application\Education\Absence\Absence;
use SPHERE\Application\Education\Absence\Service\Entity\TblAbsence;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\ParentStudentAccess\OnlineAbsence\OnlineAbsence;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Main;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiAbsence
{
    public static function registerApi(): void
    {
        Main::getRestApiDispatcher()->registerRoute(Main::getRestApiDispatcher()->createRoute(
            __NAMESPACE__ . '/Load', __CLASS__ . '::getAbsenceLoad',
        ));
        Main::getRestApiDispatcher()->registerRoute(Main::getRestApiDispatcher()->createRoute(
            __NAMESPACE__ . '/Add', __CLASS__ . '::getAbsenceAdd',
        ));
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
                    && ($tableData = Absence::useService()->getStudentAbsenceDataForParentStudentAccess($tblPerson, $tblStudentEducation, $hasAbsenceTypeOptions))
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
                    'Division' => DivisionCourse::useService()->getCurrentMainCoursesByPersonAndDate($tblPerson),
                    'AbsenceList' => $absenceList,
                    'LinkList' => array(
                        array(
                            'Name' => 'Fehlzeit hinzufügen',
                            'Link' => 'https://' . $_SERVER['HTTP_HOST'] . '/RestApi/Education/Absence/Add',
                            'Parameters' => array(
                                // todo remove AccountId after extern API
                                'AccountId' => ($tblAccount = Account::useService()->getAccountBySession()) ? $tblAccount->getId() : null,
                                'PersonId' => $tblPerson->getId()
                            )
                        ),
                    )
                );
            }
        }

        return new JsonResponse($result, Response::HTTP_OK);
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

            if (($errorList = Absence::useService()->checkFormOnlineAbsence($Data, $tblPerson, $source, false))) {
                return new JsonResponse(array('success' => false, 'message' => 'Invalid Data', 'Data' => $errorList), Response::HTTP_NOT_ACCEPTABLE);
            } else {
                if (Absence::useService()->createOnlineAbsence($Data, $tblPerson, $source)) {
                    return new JsonResponse(array('success' => true, 'message' => 'Fehlzeit erfolgreich hinzugefügt'), Response::HTTP_OK);
                } else {
                    return new JsonResponse(array('success' => false, 'message' => 'Fehlzeit konnte nicht hinzugefügt werden'), Response::HTTP_OK);
                }
            }
        } else {
            return new JsonResponse(array('success' => false, 'message' => 'Zugriff verweigert'), Response::HTTP_FORBIDDEN);
        }
    }
}