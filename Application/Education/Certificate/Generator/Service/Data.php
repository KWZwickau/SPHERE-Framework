<?php
namespace SPHERE\Application\Education\Certificate\Generator\Service;

use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateField;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateGrade;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateLevel;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateSubject;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateType;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblLevel;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\School\Course\Course;
use SPHERE\Application\Education\School\Course\Service\Entity\TblCourse;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 *
 * @package SPHERE\Application\Education\Certificate\Generator\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

        $tblCertificateTypeHalfYear = $this->createCertificateType('Halbjahresinformation/Halbjahreszeugnis', 'HALF_YEAR');
        $tblCertificateTypeYear = $this->createCertificateType('Jahreszeugnis', 'YEAR');
        $tblCertificateTypeGradeInformation = $this->createCertificateType('Noteninformation', 'GRADE_INFORMATION');
        $tblCertificateTypeRecommendation = $this->createCertificateType('Bildungsempfehlung', 'RECOMMENDATION');
        $tblCertificateTypeLeave = $this->createCertificateType('Abgangszeugnis', 'LEAVE');
        $tblCertificateTypeDiploma = $this->createCertificateType('Abschlusszeugnis', 'DIPLOMA');
        $tblCertificateTypeMidTermCourse = $this->createCertificateType('Kurshalbjahreszeugnis', 'MID_TERM_COURSE');

        $tblSchoolTypePrimary = Type::useService()->getTypeByName('Grundschule');
        $tblSchoolTypeSecondary = Type::useService()->getTypeByName('Mittelschule / Oberschule');
        $tblSchoolTypeGym = Type::useService()->getTypeByName('Gymnasium');

        $tblCourseMain = Course::useService()->getCourseByName('Hauptschule');
        $tblCourseReal = Course::useService()->getCourseByName('Realschule');

        $tblCertificate = $this->createCertificate('Bildungsempfehlung', 'Grundschule Klasse 4', 'BeGs');
        if ($tblCertificate) {
            if ($tblSchoolTypePrimary) {
                $this->updateCertificate($tblCertificate, $tblCertificateTypeRecommendation, $tblSchoolTypePrimary);
                if (!$this->getCertificateLevelAllByCertificate($tblCertificate)){
                    if ($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '4')) {
                        $this->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
        }
        if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
            $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
            $this->setCertificateSubject($tblCertificate, 'SU', 1, 2);
            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
        }

        // SSW-1981 Deaktivierung Bildungsempfehlung Klasse 5/6
        if (($tblCertificate = $this->getCertificateByCertificateClassName('BeMi'))) {
            $this->destroyCertificate($tblCertificate);
        }

        $tblCertificate = $this->createCertificate('Bildungsempfehlung', '§ 34 Abs. 3 SOFS', 'BeSOFS');
        if ($tblCertificate) {
            if ($tblSchoolTypeSecondary && $tblCourseMain) {
                $this->updateCertificate($tblCertificate, $tblCertificateTypeRecommendation, $tblSchoolTypeSecondary,
                    $tblCourseMain);
                if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '7'))) {
                        $this->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '8'))) {
                        $this->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '9'))) {
                        $this->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
        }
        if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
            $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
            $this->setCertificateSubject($tblCertificate, 'GE', 1, 2);
            $this->setCertificateSubject($tblCertificate, 'GEO', 1, 3);
            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $this->setCertificateSubject($tblCertificate, 'BI', 2, 2);
            $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
            $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
        }

        $tblCertificate = $this->createCertificate('Grundschule Halbjahresinformation', '', 'GsHjInformation');
        if ($tblCertificate) {
            if ($tblSchoolTypePrimary) {
                $this->updateCertificate($tblCertificate, $tblCertificateTypeHalfYear, $tblSchoolTypePrimary, null, true);
                if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '2'))) {
                        $this->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '3'))) {
                        $this->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '4'))) {
                        $this->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $this->createCertificateField($tblCertificate, $FieldName, 1200);
            }
        }
        if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
            $this->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {

            $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
            $this->setCertificateSubject($tblCertificate, 'SU', 1, 2);
            $this->setCertificateSubject($tblCertificate, 'EN', 1, 3);
            $this->setCertificateSubject($tblCertificate, 'KU', 1, 4);
            $this->setCertificateSubject($tblCertificate, 'MU', 1, 5);

            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $this->setCertificateSubject($tblCertificate, 'SPO', 2, 2, true);
            $this->setCertificateSubject($tblCertificate, 'RELI', 2, 3);
            $this->setCertificateSubject($tblCertificate, 'WK', 2, 4);
        }

        $tblCertificate = $this->createCertificate('Grundschule Halbjahresinformation', 'der ersten Klasse',
            'GsHjOneInfo');
        if ($tblCertificate) {
            if ($tblSchoolTypePrimary) {
                $this->updateCertificate($tblCertificate, $tblCertificateTypeHalfYear, $tblSchoolTypePrimary, null, true);
                if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '1'))) {
                        $this->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Begrenzung des Bemerkungsfeld
            // erste Klasse nicht, wegen Enter
//            $FieldName = 'Remark';
//            if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
//                $this->createCertificateField($tblCertificate, $FieldName, 4000);
//            }
        }

        $tblCertificate = $this->createCertificate('Grundschule Jahreszeugnis', '', 'GsJa');
        if ($tblCertificate) {
            if ($tblSchoolTypePrimary) {
                $this->updateCertificate($tblCertificate, $tblCertificateTypeYear, $tblSchoolTypePrimary);
                if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '2'))) {
                        $this->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '3'))) {
                        $this->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '4'))) {
                        $this->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Begrenzung des Bemerkungsfelds
            $FieldName = 'Remark';
            if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $this->createCertificateField($tblCertificate, $FieldName, 700);
            }
            // Begrenzung des Einschätzungfelds
            $FieldName = 'Rating';
            if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $this->createCertificateField($tblCertificate, $FieldName, 600);
            }
        }
        if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
            $this->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {

            $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
            $this->setCertificateSubject($tblCertificate, 'SU', 1, 2);
            $this->setCertificateSubject($tblCertificate, 'EN', 1, 3);
            $this->setCertificateSubject($tblCertificate, 'KU', 1, 4);
            $this->setCertificateSubject($tblCertificate, 'MU', 1, 5);

            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $this->setCertificateSubject($tblCertificate, 'SPO', 2, 2, true);
            $this->setCertificateSubject($tblCertificate, 'RELI', 2, 3);
            $this->setCertificateSubject($tblCertificate, 'WK', 2, 4);
        }

        $tblCertificate = $this->createCertificate('Grundschule Jahreszeugnis', 'der ersten Klasse', 'GsJOne');
        if ($tblCertificate) {
            if ($tblSchoolTypePrimary) {
                $this->updateCertificate($tblCertificate, $tblCertificateTypeYear, $tblSchoolTypePrimary);
                if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '1'))) {
                        $this->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Begrenzung des Bemerkungsfeld
            // erste Klasse nicht, wegen Enter
//            $FieldName = 'Remark';
//            if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
//                $this->createCertificateField($tblCertificate, $FieldName, 4000);
//            }
        }

        $tblCertificate = $this->createCertificate('Gymnasium Halbjahresinformation', '', 'GymHjInfo');
        if ($tblCertificate) {
            if ($tblSchoolTypeGym) {
                $this->updateCertificate($tblCertificate, $tblCertificateTypeHalfYear, $tblSchoolTypeGym, null, true);
                if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeGym, '5'))) {
                        $this->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeGym, '6'))) {
                        $this->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeGym, '7'))) {
                        $this->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeGym, '8'))) {
                        $this->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeGym, '9'))) {
                        $this->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $this->createCertificateField($tblCertificate, $FieldName, 600);
            }
        }
        if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
            $this->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
            $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
            $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
            // 1,3 freilassen für Fremdsprache
            $this->setCertificateSubject($tblCertificate, 'KU', 1, 4);
            $this->setCertificateSubject($tblCertificate, 'MU', 1, 5);
            $this->setCertificateSubject($tblCertificate, 'GE', 1, 6);
            $this->setCertificateSubject($tblCertificate, 'GRW', 1, 7);
            $this->setCertificateSubject($tblCertificate, 'GEO', 1, 8);

            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
            $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
            $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
            $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
            $this->setCertificateSubject($tblCertificate, 'RELI', 2, 6);
            $this->setCertificateSubject($tblCertificate, 'TC', 2, 7);
            $this->setCertificateSubject($tblCertificate, 'IN', 2, 8);
        }

        $tblCertificate = $this->createCertificate('Gymnasium Halbjahreszeugnis', '', 'GymHj');
        if ($tblCertificate) {
            if ($tblSchoolTypeGym) {
                $this->updateCertificate($tblCertificate, $tblCertificateTypeHalfYear, $tblSchoolTypeGym);
                if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeGym, '10'))) {
                        $this->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $this->createCertificateField($tblCertificate, $FieldName, 600);
            }
        }
        if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
            $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
            $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
            // 1,3 freilassen für Fremdsprache
            $this->setCertificateSubject($tblCertificate, 'KU', 1, 4);
            $this->setCertificateSubject($tblCertificate, 'MU', 1, 5);
            $this->setCertificateSubject($tblCertificate, 'GE', 1, 6);
            $this->setCertificateSubject($tblCertificate, 'GRW', 1, 7);
            $this->setCertificateSubject($tblCertificate, 'GEO', 1, 8);

            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
            $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
            $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
            $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
            $this->setCertificateSubject($tblCertificate, 'RELI', 2, 6);
            $this->setCertificateSubject($tblCertificate, 'TC', 2, 7);
            $this->setCertificateSubject($tblCertificate, 'IN', 2, 8);
        }

        $tblCertificate = $this->createCertificate('Gymnasium Jahreszeugnis', '', 'GymJ');
        if ($tblCertificate) {
            if ($tblSchoolTypeGym) {
                $this->updateCertificate($tblCertificate, $tblCertificateTypeYear, $tblSchoolTypeGym);
                if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeGym, '5'))) {
                        $this->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeGym, '6'))) {
                        $this->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeGym, '7'))) {
                        $this->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeGym, '8'))) {
                        $this->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeGym, '9'))) {
                        $this->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeGym, '10'))) {
                        $this->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Begrenzung des Einschätzungfelds
            $FieldName = 'Rating';
            if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $this->createCertificateField($tblCertificate, $FieldName, 200);
            }
            // Begrenzung des Bemerkungsfelds
            $FieldName = 'Remark';
            if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $this->createCertificateField($tblCertificate, $FieldName, 300);
            }
        }
        if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
            $this->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
            $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
            $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
            // 1,3 freilassen für Fremdsprache
            $this->setCertificateSubject($tblCertificate, 'KU', 1, 4);
            $this->setCertificateSubject($tblCertificate, 'MU', 1, 5);
            $this->setCertificateSubject($tblCertificate, 'GE', 1, 6);
            $this->setCertificateSubject($tblCertificate, 'GRW', 1, 7);
            $this->setCertificateSubject($tblCertificate, 'GEO', 1, 8);

            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
            $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
            $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
            $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
            $this->setCertificateSubject($tblCertificate, 'RELI', 2, 6);
            $this->setCertificateSubject($tblCertificate, 'TC', 2, 7);
            $this->setCertificateSubject($tblCertificate, 'IN', 2, 8);
        }

        $tblCertificate = $this->createCertificate('Mittelschule Abschlusszeugnis', 'Hauptschule', 'MsAbsHs');
        if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
            $this->setCertificateSubject($tblCertificate, 'DE', 1, 1);
            $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
            $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
            $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
            $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
            $this->setCertificateSubject($tblCertificate, 'GK', 1, 6);
            $this->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
            $this->setCertificateSubject($tblCertificate, 'WTH', 1, 8);

            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
            $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
            $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
            $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
            $this->setCertificateSubject($tblCertificate, 'REV', 2, 6);
            $this->setCertificateSubject($tblCertificate, 'IN', 2, 7);
        }
        if ($tblCertificate) {
            if ($tblSchoolTypeSecondary && $tblCourseMain) {
                $this->updateCertificate($tblCertificate, $tblCertificateTypeDiploma, $tblSchoolTypeSecondary,
                    $tblCourseMain);
                if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '9'))) {
                        $this->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
        }

        $tblCertificate = $this->createCertificate('Mittelschule Abschlusszeugnis', 'Hauptschule qualifiziert',
            'MsAbsHsQ');
        if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
            $this->setCertificateSubject($tblCertificate, 'DE', 1, 1);
            $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
            $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
            $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
            $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
            $this->setCertificateSubject($tblCertificate, 'GK', 1, 6);
            $this->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
            $this->setCertificateSubject($tblCertificate, 'WTH', 1, 8);

            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
            $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
            $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
            $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
            $this->setCertificateSubject($tblCertificate, 'REV', 2, 6);
            $this->setCertificateSubject($tblCertificate, 'IN', 2, 7);
        }
        if ($tblCertificate) {
            if ($tblSchoolTypeSecondary && $tblCourseMain) {
                $this->updateCertificate($tblCertificate, $tblCertificateTypeDiploma, $tblSchoolTypeSecondary,
                    $tblCourseMain);
                if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '9'))) {
                        $this->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
        }

        $tblCertificate = $this->createCertificate('Mittelschule Abschlusszeugnis', 'Realschule', 'MsAbsRs');
        if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
            $this->setCertificateSubject($tblCertificate, 'DE', 1, 1);
            $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
            $this->setCertificateSubject($tblCertificate, 'KU', 1, 3, false);
            $this->setCertificateSubject($tblCertificate, 'MU', 1, 4, false);
            $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
            $this->setCertificateSubject($tblCertificate, 'GEO', 1, 6);
            $this->setCertificateSubject($tblCertificate, 'GK', 1, 7);

            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
            $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
            $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
            $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
            $this->setCertificateSubject($tblCertificate, 'REV', 2, 6);
            $this->setCertificateSubject($tblCertificate, 'IN', 2, 7);
        }
        if ($tblCertificate) {
            if ($tblSchoolTypeSecondary && $tblCourseReal) {
                $this->updateCertificate($tblCertificate, $tblCertificateTypeDiploma, $tblSchoolTypeSecondary,
                    $tblCourseReal);
                if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '10'))) {
                        $this->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
        }

        $tblCertificate = $this->createCertificate('Mittelschule Halbjahresinformation', 'Hauptschule', 'MsHjInfoHs');
        if ($tblCertificate) {
            if ($tblSchoolTypeSecondary && $tblCourseMain) {
                $this->updateCertificate($tblCertificate, $tblCertificateTypeHalfYear, $tblSchoolTypeSecondary,
                    $tblCourseMain, true);
                if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '7'))) {
                        $this->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '8'))) {
                        $this->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $this->createCertificateField($tblCertificate, $FieldName, 700);
            }
        }
        if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
            $this->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
            $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
            $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
            $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
            $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
            $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
            $this->setCertificateSubject($tblCertificate, 'GK', 1, 6);
            $this->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
            $this->setCertificateSubject($tblCertificate, 'WTH', 1, 8);

            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
            $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
            $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
            $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
            $this->setCertificateSubject($tblCertificate, 'RELI', 2, 6);
            $this->setCertificateSubject($tblCertificate, 'TC', 2, 7);
            $this->setCertificateSubject($tblCertificate, 'IN', 2, 8);
        }

        $tblCertificate = $this->createCertificate('Mittelschule Halbjahresinformation', 'Klasse 5-6', 'MsHjInfo');
        if ($tblCertificate) {
            if ($tblSchoolTypeSecondary) {
                $this->updateCertificate($tblCertificate, $tblCertificateTypeHalfYear, $tblSchoolTypeSecondary, null, true);
                if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '5'))) {
                        $this->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '6'))) {
                        $this->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $this->createCertificateField($tblCertificate, $FieldName, 700);
            }
        }
        if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
            $this->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
            $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
            $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
            $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
            $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
            $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
            $this->setCertificateSubject($tblCertificate, 'GK', 1, 6);
            $this->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
            $this->setCertificateSubject($tblCertificate, 'WTH', 1, 8);

            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
            $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
            $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
            $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
            $this->setCertificateSubject($tblCertificate, 'RELI', 2, 6);
            $this->setCertificateSubject($tblCertificate, 'TC', 2, 7);
            $this->setCertificateSubject($tblCertificate, 'IN', 2, 8);
        }

        $tblCertificate = $this->createCertificate('Mittelschule Halbjahresinformation', 'Realschule', 'MsHjInfoRs');
        if ($tblCertificate) {
            if ($tblSchoolTypeSecondary && $tblCourseReal) {
                $this->updateCertificate($tblCertificate, $tblCertificateTypeHalfYear, $tblSchoolTypeSecondary,
                    $tblCourseReal, true);
                if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '7'))) {
                        $this->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '8'))) {
                        $this->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '9'))) {
                        $this->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $this->createCertificateField($tblCertificate, $FieldName, 700);
            }
        }
        if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
            $this->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
            $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
            $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
            $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
            $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
            $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
            $this->setCertificateSubject($tblCertificate, 'GK', 1, 6);
            $this->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
            $this->setCertificateSubject($tblCertificate, 'WTH', 1, 8);

            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
            $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
            $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
            $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
            $this->setCertificateSubject($tblCertificate, 'RELI', 2, 6);
            $this->setCertificateSubject($tblCertificate, 'TC', 2, 7);
            $this->setCertificateSubject($tblCertificate, 'IN', 2, 8);
        }

        $tblCertificate = $this->createCertificate('Mittelschule Halbjahreszeugnis', 'Hauptschule', 'MsHjHs');
        if ($tblCertificate) {
            if ($tblSchoolTypeSecondary && $tblCourseMain) {
                $this->updateCertificate($tblCertificate, $tblCertificateTypeHalfYear, $tblSchoolTypeSecondary,
                    $tblCourseMain);
                if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '9'))) {
                        $this->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $this->createCertificateField($tblCertificate, $FieldName, 700);
            }
        }
        if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
            $this->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
            $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
            $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
            $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
            $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
            $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
            $this->setCertificateSubject($tblCertificate, 'GK', 1, 6);
            $this->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
            $this->setCertificateSubject($tblCertificate, 'WTH', 1, 8);

            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
            $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
            $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
            $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
            $this->setCertificateSubject($tblCertificate, 'RELI', 2, 6);
            $this->setCertificateSubject($tblCertificate, 'TC', 2, 7);
            $this->setCertificateSubject($tblCertificate, 'IN', 2, 8);
        }

        // wird aktuell nicht benötigt
        // $tblCertificate = $this->createCertificate('Mittelschule Halbjahreszeugnis', 'Klasse 5-6', 'MsHj');
        if (($tblCertificate = $this->getCertificateByCertificateClassName('MsHj'))) {
            $this->destroyCertificate($tblCertificate);
        }

        $tblCertificate = $this->createCertificate('Mittelschule Halbjahreszeugnis', 'Realschule', 'MsHjRs');
        if ($tblCertificate) {
            if ($tblSchoolTypeSecondary && $tblCourseReal) {
                $this->updateCertificate($tblCertificate, $tblCertificateTypeHalfYear, $tblSchoolTypeSecondary,
                    $tblCourseReal);
                if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '10'))) {
                        $this->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $this->createCertificateField($tblCertificate, $FieldName, 700);
            }
        }
        if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
            $this->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
            $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
            $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
            $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
            $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
            $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
            $this->setCertificateSubject($tblCertificate, 'GK', 1, 6);
            $this->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
            $this->setCertificateSubject($tblCertificate, 'WTH', 1, 8);

            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
            $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
            $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
            $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
            $this->setCertificateSubject($tblCertificate, 'RELI', 2, 6);
            $this->setCertificateSubject($tblCertificate, 'TC', 2, 7);
            $this->setCertificateSubject($tblCertificate, 'IN', 2, 8);
        }

        $tblCertificate = $this->createCertificate('Mittelschule Jahreszeugnis', 'Hauptschule', 'MsJHs');
        if ($tblCertificate) {
            if ($tblSchoolTypeSecondary && $tblCourseMain) {
                $this->updateCertificate($tblCertificate, $tblCertificateTypeYear, $tblSchoolTypeSecondary,
                    $tblCourseMain);
                if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '7'))) {
                        $this->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '8'))) {
                        $this->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Begrenzung des Einschätzungfelds
            $FieldName = 'Rating';
            if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $this->createCertificateField($tblCertificate, $FieldName, 300);
            }
            // Begrenzung des Bemerkungsfelds
            $FieldName = 'Remark';
            if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $this->createCertificateField($tblCertificate, $FieldName, 300);
            }
        }
        if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
            $this->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
            $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
            $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
            $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
            $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
            $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
            $this->setCertificateSubject($tblCertificate, 'GK', 1, 6);
            $this->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
            $this->setCertificateSubject($tblCertificate, 'WTH', 1, 8);

            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
            $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
            $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
            $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
            $this->setCertificateSubject($tblCertificate, 'RELI', 2, 6);
            $this->setCertificateSubject($tblCertificate, 'TC', 2, 7);
            $this->setCertificateSubject($tblCertificate, 'IN', 2, 8);
        }

        $tblCertificate = $this->createCertificate('Mittelschule Jahreszeugnis', 'Klasse 5-6', 'MsJ');
        if ($tblCertificate) {
            if ($tblSchoolTypeSecondary) {
                $this->updateCertificate($tblCertificate, $tblCertificateTypeYear, $tblSchoolTypeSecondary);
                if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '5'))) {
                        $this->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '6'))) {
                        $this->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Begrenzung des Einschätzungfelds
            $FieldName = 'Rating';
            if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $this->createCertificateField($tblCertificate, $FieldName, 300);
            }
            // Begrenzung des Bemerkungsfelds
            $FieldName = 'Remark';
            if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $this->createCertificateField($tblCertificate, $FieldName, 300);
            }
        }
        if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
            $this->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
            $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
            $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
            $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
            $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
            $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
            $this->setCertificateSubject($tblCertificate, 'GK', 1, 6);
            $this->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
            $this->setCertificateSubject($tblCertificate, 'WTH', 1, 8);

            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
            $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
            $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
            $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
            $this->setCertificateSubject($tblCertificate, 'RELI', 2, 6);
            $this->setCertificateSubject($tblCertificate, 'TC', 2, 7);
            $this->setCertificateSubject($tblCertificate, 'IN', 2, 8);
        }

        $tblCertificate = $this->createCertificate('Mittelschule Jahreszeugnis', 'Realschule', 'MsJRs');
        if ($tblCertificate) {
            if ($tblSchoolTypeSecondary && $tblCourseReal) {
                $this->updateCertificate($tblCertificate, $tblCertificateTypeYear, $tblSchoolTypeSecondary,
                    $tblCourseReal);
                if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                    if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '7'))) {
                        $this->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '8'))) {
                        $this->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                    if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '9'))) {
                        $this->createCertificateLevel($tblCertificate, $tblLevel);
                    }
                }
            }
            // Begrenzung des Einschätzungfelds
            $FieldName = 'Rating';
            if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $this->createCertificateField($tblCertificate, $FieldName, 300);
            }
            // Begrenzung des Bemerkungsfelds
            $FieldName = 'Remark';
            if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $this->createCertificateField($tblCertificate, $FieldName, 300);
            }
        }
        if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
            $this->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
            $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
            $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
            $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
            $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
            $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
            $this->setCertificateSubject($tblCertificate, 'GK', 1, 6);
            $this->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
            $this->setCertificateSubject($tblCertificate, 'WTH', 1, 8);

            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
            $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
            $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
            $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
            $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
            $this->setCertificateSubject($tblCertificate, 'RELI', 2, 6);
            $this->setCertificateSubject($tblCertificate, 'TC', 2, 7);
            $this->setCertificateSubject($tblCertificate, 'IN', 2, 8);
        }

        /*
         * Noteninformation
         */
        $tblCertificate = $this->createCertificate('Noteninformation', '',
            'GradeInformation', null, true);
        if ($tblCertificate) {
            $this->updateCertificate($tblCertificate, $tblCertificateTypeGradeInformation, null, null, true);
        }
        if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
            $this->setCertificateGradeAllStandard($tblCertificate);
        }
        if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
            if (($tblConsumer = Consumer::useService()->getConsumerBySession())
                && $tblConsumer->getAcronym() == 'ESZC'
            ) {
                $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
                $this->setCertificateSubject($tblCertificate, 'MA', 1, 2);
                $this->setCertificateSubject($tblCertificate, 'EN', 1, 3);
                $this->setCertificateSubject($tblCertificate, 'BIO', 1, 4);
                $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
                $this->setCertificateSubject($tblCertificate, 'GEO', 1, 6);
                $this->setCertificateSubject($tblCertificate, 'TC', 1, 7);
                $this->setCertificateSubject($tblCertificate, 'KU', 1, 8);
                $this->setCertificateSubject($tblCertificate, 'MU', 1, 9);
                $this->setCertificateSubject($tblCertificate, 'RELI', 1, 10);
                $this->setCertificateSubject($tblCertificate, 'SPO', 1, 11);
            } else {
                $this->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                $this->setCertificateSubject($tblCertificate, 'MA', 1, 2);
                $this->setCertificateSubject($tblCertificate, 'EN', 1, 3);
                $this->setCertificateSubject($tblCertificate, 'BI', 1, 4);
                $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
                $this->setCertificateSubject($tblCertificate, 'GEO', 1, 6);
                $this->setCertificateSubject($tblCertificate, 'IN', 1, 7);
                $this->setCertificateSubject($tblCertificate, 'KU', 1, 8);
                $this->setCertificateSubject($tblCertificate, 'MU', 1, 9);
                $this->setCertificateSubject($tblCertificate, 'REV', 1, 10);
                $this->setCertificateSubject($tblCertificate, 'SPO', 1, 11);
            }
        }

        // Kurshalbjahreszeugnis
        $tblCertificate = $this->createCertificate('Gymnasium Kurshalbjahreszeugnis', '', 'GymKurshalbjahreszeugnis');
        if ($tblCertificate) {
            if ($tblSchoolTypeGym && $tblCertificateTypeMidTermCourse) {
                $this->updateCertificate($tblCertificate, $tblCertificateTypeMidTermCourse, $tblSchoolTypeGym,
                   null);
            }
            // Begrenzung des Bemerkungsfeld
            $FieldName = 'Remark';
            if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                $this->createCertificateField($tblCertificate, $FieldName, 270);
            }
        }
        if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
            $row = 1;
            $this->setCertificateSubject($tblCertificate, 'DE', $row, 1);
            $this->setCertificateSubject($tblCertificate, 'SOR', $row, 2);

            $this->setCertificateSubject($tblCertificate, 'EN', $row, 3, false);
            $this->setCertificateSubject($tblCertificate, 'EN2', $row, 4, false);
            $this->setCertificateSubject($tblCertificate, 'FR', $row, 5, false);
            $this->setCertificateSubject($tblCertificate, 'RU', $row, 6, false);
            $this->setCertificateSubject($tblCertificate, 'LA', $row, 7, false);
            $this->setCertificateSubject($tblCertificate, 'SPA', $row, 8, false);

            $this->setCertificateSubject($tblCertificate, 'KU', $row, 9, false);
            $this->setCertificateSubject($tblCertificate, 'MU', $row, 10, false);
            $this->setCertificateSubject($tblCertificate, 'GE', $row, 11);
            $this->setCertificateSubject($tblCertificate, 'GEO', $row, 12);
            $this->setCertificateSubject($tblCertificate, 'GRW', $row, 13);

            $row = 2;
            $this->setCertificateSubject($tblCertificate, 'MA', $row, 1);
            $this->setCertificateSubject($tblCertificate, 'BIO', $row, 2);
            $this->setCertificateSubject($tblCertificate, 'CH', $row, 3);
            $this->setCertificateSubject($tblCertificate, 'PH', $row, 4);
            $this->setCertificateSubject($tblCertificate, 'REE', $row, 5, false);
            $this->setCertificateSubject($tblCertificate, 'REK', $row, 6, false);
            $this->setCertificateSubject($tblCertificate, 'ETH', $row, 7, false);
            $this->setCertificateSubject($tblCertificate, 'SPO', $row, 8);
        }

        // Alt-Last löschen
        if (($tblCertificate = $this->getCertificateByCertificateClassName('GsJ'))) {
            $this->destroyCertificate($tblCertificate);
        }

        /**
         * Abgangszeugnisse - Leave
         */
        // Alt-Last löschen - Delete alte Abgangszeugnisse
        if (($tblCertificate = Generator::useService()->getCertificateByCertificateClassName('GymAbgHs'))) {
            $this->destroyCertificate($tblCertificate);
        }
        if (($tblCertificate = Generator::useService()->getCertificateByCertificateClassName('GymAbgRs'))) {
            $this->destroyCertificate($tblCertificate);
        }
        if (($tblCertificate = Generator::useService()->getCertificateByCertificateClassName('MsAbgHs'))) {
            $this->destroyCertificate($tblCertificate);
        }
        if (($tblCertificate = Generator::useService()->getCertificateByCertificateClassName('MsAbgRs'))) {
            $this->destroyCertificate($tblCertificate);
        }
        // create Abgangzeugnisse
        $tblCertificate = $this->createCertificate('Mittelschule Abgangszeugnis', '', 'MsAbg',
            null, false, false, false, $tblCertificateTypeLeave, $tblSchoolTypeSecondary);
        if ($tblCertificate) {
            if (!$this->getCertificateSubjectAll($tblCertificate)) {
                $row = 1;
                $column = 1;
                $this->setCertificateSubject($tblCertificate, 'DE', $row, $column++);
                $this->setCertificateSubject($tblCertificate, 'EN', $row, $column++);
                $this->setCertificateSubject($tblCertificate, 'KU', $row, $column++);
                $this->setCertificateSubject($tblCertificate, 'MU', $row, $column++);
                $this->setCertificateSubject($tblCertificate, 'GE', $row, $column++);
                $this->setCertificateSubject($tblCertificate, 'GK', $row, $column++);
                $this->setCertificateSubject($tblCertificate, 'GEO', $row, $column++);
                $this->setCertificateSubject($tblCertificate, 'WTH', $row, $column);

                $row = 2;
                $column = 1;
                $this->setCertificateSubject($tblCertificate, 'MA', $row, $column++);
                $this->setCertificateSubject($tblCertificate, 'BIO', $row, $column++);
                $this->setCertificateSubject($tblCertificate, 'CH', $row, $column++);
                $this->setCertificateSubject($tblCertificate, 'PH', $row, $column++);
                $this->setCertificateSubject($tblCertificate, 'SPO', $row, $column++);
                $this->setCertificateSubject($tblCertificate, 'REE', $row, $column++, false);
                $this->setCertificateSubject($tblCertificate, 'REK', $row, $column++, false);
                $this->setCertificateSubject($tblCertificate, 'ETH', $row, $column++, false);
                $this->setCertificateSubject($tblCertificate, 'IN', $row, $column);
            }
        }
        $tblCertificate = $this->createCertificate('Gymnasium Abgangszeugnis', 'Sekundarstufe I', 'GymAbgSekI',
            null, false, false, false, $tblCertificateTypeLeave, $tblSchoolTypeGym);
        if ($tblCertificate) {
            if (!$this->getCertificateSubjectAll($tblCertificate)) {
                $row = 1;
                $column = 1;
                $this->setCertificateSubject($tblCertificate, 'DE', $row, $column++);
                $this->setCertificateSubject($tblCertificate, 'EN', $row, $column++);
                $column++;
                $this->setCertificateSubject($tblCertificate, 'KU', $row, $column++);
                $this->setCertificateSubject($tblCertificate, 'MU', $row, $column++);
                $this->setCertificateSubject($tblCertificate, 'GE', $row, $column++);
                $this->setCertificateSubject($tblCertificate, 'GRW', $row, $column++);
                $this->setCertificateSubject($tblCertificate, 'GEO', $row, $column);

                $row = 2;
                $column = 1;
                $this->setCertificateSubject($tblCertificate, 'MA', $row, $column++);
                $this->setCertificateSubject($tblCertificate, 'BIO', $row, $column++);
                $this->setCertificateSubject($tblCertificate, 'CH', $row, $column++);
                $this->setCertificateSubject($tblCertificate, 'PH', $row, $column++);
                $this->setCertificateSubject($tblCertificate, 'SPO', $row, $column++);
                $this->setCertificateSubject($tblCertificate, 'REE', $row, $column++, false);
                $this->setCertificateSubject($tblCertificate, 'REK', $row, $column++, false);
                $this->setCertificateSubject($tblCertificate, 'ETH', $row, $column++, false);
                $this->setCertificateSubject($tblCertificate, 'TC', $row, $column++);
                $this->setCertificateSubject($tblCertificate, 'IN', $row, $column);
            }
        }
        $this->createCertificate('Gymnasium Abgangszeugnis', 'Sekundarstufe II', 'GymAbgSekII',
            null, false, false, false, $tblCertificateTypeLeave, $tblSchoolTypeGym);

        /*
         * Abitur Zeugnis
         */
        $this->createCertificate('Gymnasium Abitur', '', 'GymAbitur',
            null, false, false, false, $tblCertificateTypeDiploma, $tblSchoolTypeGym);

        $tblConsumer = Consumer::useService()->getConsumerBySession();
        if ($tblConsumer) {
            if ($tblConsumer->getAcronym() == 'ESZC' || $tblConsumer->getAcronym() == 'DEMO') {
                $tblConsumerCertificate = Consumer::useService()->getConsumerByAcronym('ESZC');
                if ($tblConsumerCertificate) {
//
//                    $tblCertificate = $this->createCertificate(
//                        'Bildungsempfehlung', 'Klassenstufe 4', 'ESZC\CheBeGs', $tblConsumerCertificate
//                    );
//                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
//
//                        $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
//                        $this->setCertificateSubject($tblCertificate, 'SU', 1, 2);
//
//                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
//                    }
//
//                    $tblCertificate = $this->createCertificate(
//                        'Bildungsempfehlung', 'Gymnasium', 'ESZC\CheBeGym', $tblConsumerCertificate
//                    );
//                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
//
//                        $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
//                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
//
//                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
//                    }
//
//                    $tblCertificate = $this->createCertificate(
//                        'Bildungsempfehlung', 'Mittelschule', 'ESZC\CheBeMi', $tblConsumerCertificate
//                    );
//                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
//
//                        $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
//                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
//
//                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
//                    }
//
//                    $tblCertificate = $this->createCertificate(
//                        'Halbjahresinformation', 'Hauptschule', 'ESZC\CheHjInfoHs', $tblConsumerCertificate
//                    );
//                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
//                        $this->setCertificateGradeAllStandard($tblCertificate);
//                    }
//                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
//                        $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
//                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
//                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
//                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
//                        $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
//                        $this->setCertificateSubject($tblCertificate, 'GK', 1, 6);
//                        $this->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
//                        $this->setCertificateSubject($tblCertificate, 'WTH', 1, 8);
//
//                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
//                        $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
//                        $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
//                        $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
//                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
//                        $this->setCertificateSubject($tblCertificate, 'RELI', 2, 6, false);
//                        $this->setCertificateSubject($tblCertificate, 'TC', 2, 7);
//                        $this->setCertificateSubject($tblCertificate, 'IN', 2, 8);
//                    }
//
//                    $tblCertificate = $this->createCertificate(
//                        'Halbjahresinformation', 'Klasse 5-6', 'ESZC\CheHjInfo', $tblConsumerCertificate
//                    );
//                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
//                        $this->setCertificateGradeAllStandard($tblCertificate);
//                    }
//                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
//                        $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
//                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
//                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
//                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
//                        $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
//                        $this->setCertificateSubject($tblCertificate, 'GK', 1, 6);
//                        $this->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
//                        $this->setCertificateSubject($tblCertificate, 'WTH', 1, 8);
//
//                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
//                        $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
//                        $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
//                        $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
//                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
//                        $this->setCertificateSubject($tblCertificate, 'RELI', 2, 6, false);
//                        $this->setCertificateSubject($tblCertificate, 'TC', 2, 7);
//                        $this->setCertificateSubject($tblCertificate, 'IN', 2, 8);
//                    }
//
//                    $tblCertificate = $this->createCertificate(
//                        'Halbjahresinformation', 'Realschule', 'ESZC\CheHjInfoRs', $tblConsumerCertificate
//                    );
//                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
//                        $this->setCertificateGradeAllStandard($tblCertificate);
//                    }
//                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
//                        $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
//                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
//                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
//                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
//                        $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
//                        $this->setCertificateSubject($tblCertificate, 'GK', 1, 6);
//                        $this->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
//                        $this->setCertificateSubject($tblCertificate, 'WTH', 1, 8);
//
//                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
//                        $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
//                        $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
//                        $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
//                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
//                        $this->setCertificateSubject($tblCertificate, 'RELI', 2, 6, false);
//                        $this->setCertificateSubject($tblCertificate, 'TC', 2, 7);
//                        $this->setCertificateSubject($tblCertificate, 'IN', 2, 8);
//                    }
//
//                    $tblCertificate = $this->createCertificate(
//                        'Halbjahresinformation', 'Gymnasium', 'ESZC\CheHjGymInfo', $tblConsumerCertificate
//                    );
//                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
//                        $this->setCertificateGradeAllStandard($tblCertificate);
//                    }
//                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
//                        $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
//                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
//                        $this->setCertificateSubject($tblCertificate, 'FRZ', 1, 3);
//                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 4);
//                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 5);
//                        $this->setCertificateSubject($tblCertificate, 'GE', 1, 6);
//                        $this->setCertificateSubject($tblCertificate, 'GRW', 1, 7);
//                        $this->setCertificateSubject($tblCertificate, 'GEO', 1, 8);
//
//                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
//                        $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
//                        $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
//                        $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
//                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
//                        $this->setCertificateSubject($tblCertificate, 'RELI', 2, 6, false);
//                        $this->setCertificateSubject($tblCertificate, 'TC', 2, 7);
//                        $this->setCertificateSubject($tblCertificate, 'IN', 2, 8);
//                    }
//
//                    $tblCertificate = $this->createCertificate(
//                        'Halbjahreszeugnis', 'Gymnasium', 'ESZC\CheHjGym', $tblConsumerCertificate
//                    );
//                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
//                        $this->setCertificateGradeAllStandard($tblCertificate);
//                    }
//                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
//                        $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
//                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
//                        $this->setCertificateSubject($tblCertificate, 'FRZ', 1, 3);
//                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 4);
//                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 5);
//                        $this->setCertificateSubject($tblCertificate, 'GE', 1, 6);
//                        $this->setCertificateSubject($tblCertificate, 'GRW', 1, 7);
//                        $this->setCertificateSubject($tblCertificate, 'GEO', 1, 8);
//
//                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
//                        $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
//                        $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
//                        $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
//                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
//                        $this->setCertificateSubject($tblCertificate, 'RELI', 2, 6, false);
//                        $this->setCertificateSubject($tblCertificate, 'TC', 2, 7);
//                        $this->setCertificateSubject($tblCertificate, 'IN', 2, 8);
//                    }
//
//                    $tblCertificate = $this->createCertificate(
//                        'Halbjahreszeugnis', 'Hauptschule', 'ESZC\CheHjHs', $tblConsumerCertificate
//                    );
//                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
//                        $this->setCertificateGradeAllStandard($tblCertificate);
//                    }
//                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
//                        $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
//                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
//                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
//                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
//                        $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
//                        $this->setCertificateSubject($tblCertificate, 'GK', 1, 6);
//                        $this->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
//                        $this->setCertificateSubject($tblCertificate, 'WTH', 1, 8);
//
//                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
//                        $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
//                        $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
//                        $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
//                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
//                        $this->setCertificateSubject($tblCertificate, 'RELI', 2, 6, false);
//                        $this->setCertificateSubject($tblCertificate, 'TC', 2, 7);
//                        $this->setCertificateSubject($tblCertificate, 'IN', 2, 8);
//                    }
//
//                    $tblCertificate = $this->createCertificate(
//                        'Halbjahreszeugnis', 'Klasse 5-6', 'ESZC\CheHj', $tblConsumerCertificate
//                    );
//                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
//                        $this->setCertificateGradeAllStandard($tblCertificate);
//                    }
//                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
//                        $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
//                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
//                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
//                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
//                        $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
//                        $this->setCertificateSubject($tblCertificate, 'GK', 1, 6);
//                        $this->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
//                        $this->setCertificateSubject($tblCertificate, 'WTH', 1, 8);
//
//                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
//                        $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
//                        $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
//                        $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
//                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
//                        $this->setCertificateSubject($tblCertificate, 'RELI', 2, 6, false);
//                        $this->setCertificateSubject($tblCertificate, 'TC', 2, 7);
//                        $this->setCertificateSubject($tblCertificate, 'IN', 2, 8);
//                    }
//
//                    $tblCertificate = $this->createCertificate(
//                        'Halbjahreszeugnis', 'Realschule', 'ESZC\CheHjRs', $tblConsumerCertificate
//                    );
//                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
//                        $this->setCertificateGradeAllStandard($tblCertificate);
//                    }
//                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
//                        $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
//                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
//                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
//                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
//                        $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
//                        $this->setCertificateSubject($tblCertificate, 'GK', 1, 6);
//                        $this->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
//                        $this->setCertificateSubject($tblCertificate, 'WTH', 1, 8);
//
//                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
//                        $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
//                        $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
//                        $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
//                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
//                        $this->setCertificateSubject($tblCertificate, 'RELI', 2, 6, false);
//                        $this->setCertificateSubject($tblCertificate, 'TC', 2, 7);
//                        $this->setCertificateSubject($tblCertificate, 'IN', 2, 8);
//                    }
//
//                    $tblCertificate = $this->createCertificate(
//                        'Jahreszeugnis', 'Mittelschule', 'ESZC\CheJ', $tblConsumerCertificate
//                    );
//                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
//                        $this->setCertificateGradeAllStandard($tblCertificate);
//                    }
//                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
//                        $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
//                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
//                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
//                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
//                        $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
//                        $this->setCertificateSubject($tblCertificate, 'GK', 1, 6);
//                        $this->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
//                        $this->setCertificateSubject($tblCertificate, 'WTH', 1, 8);
//
//                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
//                        $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
//                        $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
//                        $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
//                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
//                        $this->setCertificateSubject($tblCertificate, 'RELI', 2, 6, false);
//                        $this->setCertificateSubject($tblCertificate, 'TC', 2, 7);
//                        $this->setCertificateSubject($tblCertificate, 'IN', 2, 8);
//                    }
//
//                    $tblCertificate = $this->createCertificate(
//                        'Jahreszeugnis', 'Gymnasium', 'ESZC\CheJGym', $tblConsumerCertificate
//                    );
//                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
//                        $this->setCertificateGradeAllStandard($tblCertificate);
//                    }
//                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
//                        $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
//                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
//                        $this->setCertificateSubject($tblCertificate, 'FRZ', 1, 3);
//                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 4);
//                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 5);
//                        $this->setCertificateSubject($tblCertificate, 'GE', 1, 6);
//                        $this->setCertificateSubject($tblCertificate, 'GRW', 1, 7);
//                        $this->setCertificateSubject($tblCertificate, 'GEO', 1, 8);
//
//                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
//                        $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
//                        $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
//                        $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
//                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
//                        $this->setCertificateSubject($tblCertificate, 'RELI', 2, 6, false);
//                        $this->setCertificateSubject($tblCertificate, 'TC', 2, 7);
//                        $this->setCertificateSubject($tblCertificate, 'IN', 2, 8);
//                    }
//
                    $tblCertificate = $this->createCertificate(
                        'Jahreszeugnis', 'Grundschule Klasse 2-4', 'ESZC\CheJGs', $tblConsumerCertificate
                    );
                    if ($tblCertificate) {
                        if ($tblSchoolTypePrimary) {
                            $this->updateCertificate($tblCertificate, $tblCertificateTypeYear, $tblSchoolTypePrimary);
                            if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '2'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '3'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '4'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                            }
                        }
                        // Begrenzung des Einschätzungfelds
                        $FieldName = 'Rating';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                            $this->createCertificateField($tblCertificate, $FieldName, 220);
                        }
                        // Begrenzung des Bemerkungsfelds
                        $FieldName = 'Remark';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                            $this->createCertificateField($tblCertificate, $FieldName, 500);
                        }
                    }
                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
                        $this->setCertificateGradeAllStandard($tblCertificate);
                    }
                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
                        $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
                        $this->setCertificateSubject($tblCertificate, 'SU', 1, 2);
                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 3);
                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 4);
                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 5);

                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 2);
                        $this->setCertificateSubject($tblCertificate, 'RELI', 2, 3);
                        $this->setCertificateSubject($tblCertificate, 'WK', 2, 4);
                    }

                    $tblCertificate = $this->createCertificate(
                        'Jahreszeugnis', 'Grundschule Klasse 1', 'ESZC\CheJGsOne', $tblConsumerCertificate
                    );
                    if ($tblCertificate) {
                        if ($tblSchoolTypePrimary) {
                            $this->updateCertificate($tblCertificate, $tblCertificateTypeYear, $tblSchoolTypePrimary);
                            if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '1'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                            }
                        }

                        // Begrenzung des Bemerkungsfelds
                        $FieldName = 'Remark';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                            $this->createCertificateField($tblCertificate, $FieldName, 1200);
                        }
                    }

                    $tblCertificate = $this->createCertificate(
                        'Habljahresinformation', 'Grundschule Klasse 2-4', 'ESZC\CheHjInfoGs', $tblConsumerCertificate
                    );
                    if ($tblCertificate) {
                        if ($tblSchoolTypePrimary) {
                            $this->updateCertificate($tblCertificate, $tblCertificateTypeHalfYear, $tblSchoolTypePrimary, null, true);
                            if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '2'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '3'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '4'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                            }
                        }

                        // Begrenzung des Bemerkungsfelds
                        $FieldName = 'Remark';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                            $this->createCertificateField($tblCertificate, $FieldName, 800);
                        }
                    }
                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
                        $this->setCertificateGradeAllStandard($tblCertificate);
                    }
                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
                        $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
                        $this->setCertificateSubject($tblCertificate, 'SU', 1, 2);
                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 3);
                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 4);
                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 5);

                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 2);
                        $this->setCertificateSubject($tblCertificate, 'RELI', 2, 3);
                        $this->setCertificateSubject($tblCertificate, 'WK', 2, 4);
                    }

                    $tblCertificate = $this->createCertificate(
                        'Habljahresinformation', 'Grundschule Klasse 1', 'ESZC\CheHjInfoGsOne', $tblConsumerCertificate
                    );
                    if ($tblCertificate) {
                        if ($tblSchoolTypePrimary) {
                            $this->updateCertificate($tblCertificate, $tblCertificateTypeHalfYear, $tblSchoolTypePrimary
                                , null, true);
                            if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '1'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                            }
                        }

                        // Begrenzung des Bemerkungsfelds
                        $FieldName = 'Remark';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                            $this->createCertificateField($tblCertificate, $FieldName, 1200);
                        }
                    }
                }
            }

            // Alt-Last löschen
            if (($tblCertificate = $this->getCertificateByCertificateClassName('ESZC\CheBeGs'))) {
                $this->destroyCertificate($tblCertificate);
            }
            if (($tblCertificate = $this->getCertificateByCertificateClassName('ESZC\CheBeGym'))) {
                $this->destroyCertificate($tblCertificate);
            }
            if (($tblCertificate = $this->getCertificateByCertificateClassName('ESZC\CheBeMi'))) {
                $this->destroyCertificate($tblCertificate);
            }
            if (($tblCertificate = $this->getCertificateByCertificateClassName('ESZC\CheHj'))) {
                $this->destroyCertificate($tblCertificate);
            }
            if (($tblCertificate = $this->getCertificateByCertificateClassName('ESZC\CheHjGym'))) {
                $this->destroyCertificate($tblCertificate);
            }
            if (($tblCertificate = $this->getCertificateByCertificateClassName('ESZC\CheHjGymInfo'))) {
                $this->destroyCertificate($tblCertificate);
            }
            if (($tblCertificate = $this->getCertificateByCertificateClassName('ESZC\CheHjHs'))) {
                $this->destroyCertificate($tblCertificate);
            }
            if (($tblCertificate = $this->getCertificateByCertificateClassName('ESZC\CheHjInfo'))) {
                $this->destroyCertificate($tblCertificate);
            }
            if (($tblCertificate = $this->getCertificateByCertificateClassName('ESZC\CheBeGym'))) {
                $this->destroyCertificate($tblCertificate);
            }
//            if (($tblCertificate = $this->getCertificateByCertificateClassName('ESZC\CheHjInfoGs'))) {
//                $this->destroyCertificate($tblCertificate);
//            }
//            if (($tblCertificate = $this->getCertificateByCertificateClassName('ESZC\CheHjInfoGsOne'))) {
//                $this->destroyCertificate($tblCertificate);
//            }
            if (($tblCertificate = $this->getCertificateByCertificateClassName('ESZC\CheHjInfoHs'))) {
                $this->destroyCertificate($tblCertificate);
            }
            if (($tblCertificate = $this->getCertificateByCertificateClassName('ESZC\CheHjInfoRs'))) {
                $this->destroyCertificate($tblCertificate);
            }
            if (($tblCertificate = $this->getCertificateByCertificateClassName('ESZC\CheHjRs'))) {
                $this->destroyCertificate($tblCertificate);
            }
            if (($tblCertificate = $this->getCertificateByCertificateClassName('ESZC\CheJ'))) {
                $this->destroyCertificate($tblCertificate);
            }
//            if (($tblCertificate = $this->getCertificateByCertificateClassName('ESZC\CheJGs'))) {
//                $this->destroyCertificate($tblCertificate);
//            }
//            if (($tblCertificate = $this->getCertificateByCertificateClassName('ESZC\CheJGsOne'))) {
//                $this->destroyCertificate($tblCertificate);
//            }
            if (($tblCertificate = $this->getCertificateByCertificateClassName('ESZC\CheJGym'))) {
                $this->destroyCertificate($tblCertificate);
            }

//            if (($tblCertificate = $this->getCertificateByCertificateClassName('ESS\EssGsHjOne'))) {
//                $this->destroyCertificate($tblCertificate);
//            }
//            if (($tblCertificate = $this->getCertificateByCertificateClassName('ESS\EssGsJOne'))) {
//                $this->destroyCertificate($tblCertificate);
//            }
//            if (($tblCertificate = $this->getCertificateByCertificateClassName('ESS\EssGsHjTwo'))) {
//                $this->destroyCertificate($tblCertificate);
//            }
//            if (($tblCertificate = $this->getCertificateByCertificateClassName('ESS\EssGsJTwo'))) {
//                $this->destroyCertificate($tblCertificate);
//            }
//            if (($tblCertificate = $this->getCertificateByCertificateClassName('ESS\EssGsHjThree'))) {
//                $this->destroyCertificate($tblCertificate);
//            }
//            if (($tblCertificate = $this->getCertificateByCertificateClassName('ESS\EssGsJThree'))) {
//                $this->destroyCertificate($tblCertificate);
//            }
//            if (($tblCertificate = $this->getCertificateByCertificateClassName('ESS\EssGsHjFour'))) {
//                $this->destroyCertificate($tblCertificate);
//            }
//            if (($tblCertificate = $this->getCertificateByCertificateClassName('ESS\EssGsJFour'))) {
//                $this->destroyCertificate($tblCertificate);
//            }

            if ($tblConsumer->getAcronym() == 'EVSC' || $tblConsumer->getAcronym() == 'DEMO') {
                $tblConsumerCertificate = Consumer::useService()->getConsumerByAcronym('EVSC');
                if ($tblConsumerCertificate) {

                    $tblCertificate = $this->createCertificate(
                        'Halbjahresinformation', 'Primarstufe', 'EVSC\CosHjPri', $tblConsumerCertificate
                    );
                    if ($tblCertificate) {
                        if ($tblSchoolTypePrimary) {
                            $this->updateCertificate($tblCertificate, $tblCertificateTypeHalfYear, $tblSchoolTypePrimary
                                , null, true);
                        }
                    }
                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
                        $this->setCertificateGradeAllStandard($tblCertificate);
                    }
                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
                        $this->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                        $this->setCertificateSubject($tblCertificate, 'SACH', 1, 2);
                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 5);

                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                        $this->setCertificateSubject($tblCertificate, 'WERK', 2, 2);
                        $this->setCertificateSubject($tblCertificate, 'REL', 2, 3);
                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 4);
                    }

                    $tblCertificate = $this->createCertificate(
                        'Halbjahresinformation', 'Sekundarstufe', 'EVSC\CosHjSek', $tblConsumerCertificate
                    );
                    if ($tblCertificate) {
                        if ($tblSchoolTypeSecondary) {
                            $this->updateCertificate($tblCertificate, $tblCertificateTypeHalfYear, $tblSchoolTypeSecondary,
                                null, true);
                            if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '5'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '6'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '7'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '8'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '9'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                            }
                        }
                    }
                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
                        $this->setCertificateGradeAllStandard($tblCertificate);
                    }
                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
                        $this->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                        $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
                        $this->setCertificateSubject($tblCertificate, 'GEO', 1, 7);

                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                        $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
                        $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
                        $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
                        $this->setCertificateSubject($tblCertificate, 'REL', 2, 6);
                        $this->setCertificateSubject($tblCertificate, 'INF', 2, 8);
                    }

                    $tblCertificate = $this->createCertificate(
                        'Halbjahreszeugnis', 'Sekundarstufe', 'EVSC\CosHjZSek', $tblConsumerCertificate
                    );
                    if ($tblCertificate) {
                        if ($tblSchoolTypeSecondary) {
                            $this->updateCertificate($tblCertificate, $tblCertificateTypeHalfYear, $tblSchoolTypeSecondary);
                            if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '10'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                            }
                        }
                    }
                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
                        $this->setCertificateGradeAllStandard($tblCertificate);
                    }
                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
                        $this->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                        $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
                        $this->setCertificateSubject($tblCertificate, 'GEO', 1, 7);

                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                        $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
                        $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
                        $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
                        $this->setCertificateSubject($tblCertificate, 'REL', 2, 6);
                        $this->setCertificateSubject($tblCertificate, 'INF', 2, 8);
                    }

                    $tblCertificate = $this->createCertificate(
                        'Jahreszeugnis', 'Primarstufe', 'EVSC\CosJPri', $tblConsumerCertificate
                    );
                    if ($tblCertificate) {
                        if ($tblSchoolTypePrimary) {
                            $this->updateCertificate($tblCertificate, $tblCertificateTypeYear, $tblSchoolTypePrimary);
                        }
                    }
                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
                        $this->setCertificateGradeAllStandard($tblCertificate);
                    }
                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
                        $this->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                        $this->setCertificateSubject($tblCertificate, 'SACH', 1, 2);
                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 5);

                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                        $this->setCertificateSubject($tblCertificate, 'WERK', 2, 2);
                        $this->setCertificateSubject($tblCertificate, 'REL', 2, 3);
                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 4);
                    }

                    $tblCertificate = $this->createCertificate(
                        'Jahreszeugnis', 'Sekundarstufe', 'EVSC\CosJSek', $tblConsumerCertificate
                    );
                    if ($tblCertificate) {
                        if ($tblSchoolTypeSecondary) {
                            $this->updateCertificate($tblCertificate, $tblCertificateTypeYear, $tblSchoolTypeSecondary);
                        }
                    }
                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
                        $this->setCertificateGradeAllStandard($tblCertificate);
                    }
                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
                        $this->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                        $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
                        $this->setCertificateSubject($tblCertificate, 'GEO', 1, 7);

                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                        $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
                        $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
                        $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
                        $this->setCertificateSubject($tblCertificate, 'REL', 2, 6);
                        $this->setCertificateSubject($tblCertificate, 'INF', 2, 8);
                    }
                }
            }

            if ($tblConsumer->getAcronym() == 'FESH' || $tblConsumer->getAcronym() == 'DEMO') {
                $tblConsumerCertificate = Consumer::useService()->getConsumerByAcronym('FESH');
                if ($tblConsumerCertificate) {
                    $tblCertificate = $this->createCertificate(
                        'Halbjahresinformation', '', 'FESH\HorHj', $tblConsumerCertificate
                    );
                    if ($tblCertificate) {
                        if ($tblSchoolTypePrimary) {
                            $this->updateCertificate($tblCertificate, $tblCertificateTypeHalfYear, $tblSchoolTypePrimary,
                                null, true);
                            if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '2'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '3'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '4'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                            }
                        }
                    }
                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
                        $this->setCertificateGradeAllStandard($tblCertificate);
                    }
                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
                        $this->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                        $this->setCertificateSubject($tblCertificate, 'SU', 1, 2);
                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 5);

                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                        $this->setCertificateSubject($tblCertificate, 'WE', 2, 2);
                        $this->setCertificateSubject($tblCertificate, 'REV', 2, 3);
                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 4);
                    }

                    $tblCertificate = $this->createCertificate(
                        'Halbjahresinformation', '1. Klasse', 'FESH\HorHjOne', $tblConsumerCertificate
                    );
                    if ($tblCertificate) {
                        if ($tblSchoolTypePrimary) {
                            $this->updateCertificate($tblCertificate, $tblCertificateTypeHalfYear, $tblSchoolTypePrimary,
                                null, true);
                            if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '1'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                            }
                        }
                    }

                    $tblCertificate = $this->createCertificate(
                        'Jahreszeugnis', '', 'FESH\HorJ', $tblConsumerCertificate
                    );
                    if ($tblCertificate) {
                        if ($tblSchoolTypePrimary) {
                            $this->updateCertificate($tblCertificate, $tblCertificateTypeYear, $tblSchoolTypePrimary);
                            if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '2'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '3'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '4'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                            }
                        }
                    }
                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
                        $this->setCertificateGradeAllStandard($tblCertificate);
                    }
                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
                        $this->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                        $this->setCertificateSubject($tblCertificate, 'SU', 1, 2);
                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 5);

                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                        $this->setCertificateSubject($tblCertificate, 'WE', 2, 2);
                        $this->setCertificateSubject($tblCertificate, 'REV', 2, 3);
                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 4);
                    }

                    $tblCertificate = $this->createCertificate(
                        'Jahreszeugnis', '1. Klasse', 'FESH\HorJOne', $tblConsumerCertificate
                    );
                    if ($tblCertificate) {
                        if ($tblSchoolTypePrimary) {
                            $this->updateCertificate($tblCertificate, $tblCertificateTypeYear, $tblSchoolTypePrimary);
                            if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '1'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                            }
                        }
                    }
                }
            }

//            if ($tblConsumer->getAcronym() == 'EVSR' || $tblConsumer->getAcronym() == 'DEMO') {
//                $tblConsumerCertificate = Consumer::useService()->getConsumerByAcronym('EVSR');
//                if ($tblConsumerCertificate) {
//
////                    $tblCertificate = $this->createCertificate(
////                        'Jahreszeugnis', '', 'EVSR\RadebeulJahreszeugnis', $tblConsumerCertificate
////                    );
////                    if ($tblCertificate) {
////                        if ($tblSchoolTypePrimary) {
////                            $this->updateCertificate($tblCertificate, $tblCertificateTypeYear, $tblSchoolTypePrimary);
////                            if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '3'))) {
////                                $this->createCertificateLevel($tblCertificate, $tblLevel);
////                            }
////                            if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '4'))) {
////                                $this->createCertificateLevel($tblCertificate, $tblLevel);
////                            }
////                        }
////                        // Begrenzung des Einschätzungfelds
////                        $FieldName = 'Rating';
////                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
////                            $this->createCertificateField($tblCertificate, $FieldName, 170);
////                        }
////                        // Begrenzung des Bemerkungsfelds
////                        $FieldName = 'Remark';
////                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
////                            $this->createCertificateField($tblCertificate, $FieldName, 600);
////                        }
////                    }
////                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
////                        $this->setCertificateGradeAllStandard($tblCertificate);
////                    }
////                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
////                        $this->setCertificateSubject($tblCertificate, 'DE', 1, 1);
////                        $this->setCertificateSubject($tblCertificate, 'SU', 1, 2);
////                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
////                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
////                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 5);
////
////                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
////                        $this->setCertificateSubject($tblCertificate, 'WE', 2, 2);
////                        $this->setCertificateSubject($tblCertificate, 'RELI', 2, 3);
////                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 4);
////                    }
////
////                    $tblCertificate = $this->createCertificate(
////                        'Halbjahresinformation', '', 'EVSR\RadebeulHalbjahresinformation', $tblConsumerCertificate
////                    );
////                    if ($tblCertificate) {
////                        if ($tblSchoolTypePrimary) {
////                            $this->updateCertificate($tblCertificate, $tblCertificateTypeHalfYear, $tblSchoolTypePrimary);
////                            if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '3'))) {
////                                $this->createCertificateLevel($tblCertificate, $tblLevel);
////                            }
////                            if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '4'))) {
////                                $this->createCertificateLevel($tblCertificate, $tblLevel);
////                            }
////                        }
////                        // Begrenzung des Einschätzungfelds
////                        $FieldName = 'Rating';
////                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
////                            $this->createCertificateField($tblCertificate, $FieldName, 170);
////                        }
////                        // Begrenzung des Bemerkungsfelds
////                        $FieldName = 'Remark';
////                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
////                            $this->createCertificateField($tblCertificate, $FieldName, 600);
////                        }
////                    }
////                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
////                        $this->setCertificateGradeAllStandard($tblCertificate);
////                    }
////                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
////                        $this->setCertificateSubject($tblCertificate, 'DE', 1, 1);
////                        $this->setCertificateSubject($tblCertificate, 'SU', 1, 2);
////                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
////                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
////                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 5);
////
////                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
////                        $this->setCertificateSubject($tblCertificate, 'WE', 2, 2);
////                        $this->setCertificateSubject($tblCertificate, 'RELI', 2, 3);
////                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 4);
////                    }
////
////                    $tblCertificate = $this->createCertificate(
////                        'Bildungsempfehlung', 'Klassenstufe 4', 'EVSR\RadebeulBildungsempfehlung',
////                        $tblConsumerCertificate
////                    );
////                    if ($tblCertificate){
////                        $this->updateCertificate($tblCertificate, $tblCertificateTypeRecommendation, $tblSchoolTypePrimary);
////                    }
////                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
////
////                        $this->setCertificateSubject($tblCertificate, 'DE', 1, 1);
////                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
////
////                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
////                    }
//
////                    $tblCertificate = $this->createCertificate(
////                        'Kinderbrief', '', 'EVSR\RadebeulKinderbrief', $tblConsumerCertificate
////                    );
////                    if ($tblCertificate) {
////                        if ($tblSchoolTypePrimary) {
////                            $this->updateCertificate($tblCertificate, $tblCertificateTypeHalfYear, $tblSchoolTypePrimary);
////                            if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '1'))) {
////                                $this->createCertificateLevel($tblCertificate, $tblLevel);
////                            }
////                            if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '2'))) {
////                                $this->createCertificateLevel($tblCertificate, $tblLevel);
////                            }
////                        }
////                    }
//
////                    $tblCertificate = $this->createCertificate(
////                        'Lernentwicklungsbericht', '', 'EVSR\RadebeulLernentwicklungsbericht', $tblConsumerCertificate
////                    );
////                    if ($tblCertificate) {
////                        if ($tblSchoolTypePrimary) {
////                            $this->updateCertificate($tblCertificate, $tblCertificateTypeYear, $tblSchoolTypePrimary);
////                            if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '1'))) {
////                                $this->createCertificateLevel($tblCertificate, $tblLevel);
////                            }
////                            if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '2'))) {
////                                $this->createCertificateLevel($tblCertificate, $tblLevel);
////                            }
////                        }
////                    }
//                }
//            }

            if ($tblConsumer->getAcronym() == 'ESS' || $tblConsumer->getAcronym() == 'DEMO') {
                $tblConsumerCertificate = Consumer::useService()->getConsumerByAcronym('ESS');
                if ($tblConsumerCertificate) {
                    $tblCertificate = $this->createCertificate(
                        'Halbjahresinformation', '1. Klasse', 'ESS\EssGsHjOne', $tblConsumerCertificate
                    );
                    if ($tblCertificate) {
                        if ($tblSchoolTypePrimary) {
                            $this->updateCertificate($tblCertificate, $tblCertificateTypeHalfYear,
                                $tblSchoolTypePrimary,
                                null, true);
                            if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '1'))) {
                                $this->createCertificateLevel($tblCertificate, $tblLevel);
                            }
                        }
                        // Begrenzung des Bemerkungsfelds
                        $FieldName = 'Remark';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                            $this->createCertificateField($tblCertificate, $FieldName, 2300);
                        }
                    }
                    $tblCertificate = $this->createCertificate(
                        'Jahreszeugnis', '1. Klasse', 'ESS\EssGsJOne', $tblConsumerCertificate
                    );
                    if ($tblCertificate) {
                        if ($tblSchoolTypePrimary) {
                            $this->updateCertificate($tblCertificate, $tblCertificateTypeYear,
                                $tblSchoolTypePrimary,
                                null, false);
                            if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '1'))) {
                                $this->createCertificateLevel($tblCertificate, $tblLevel);
                            }
                        }
                        // Begrenzung des Bemerkungsfelds
                        $FieldName = 'Remark';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                            $this->createCertificateField($tblCertificate, $FieldName, 1600);
                        }
                    }
                    $tblCertificate = $this->createCertificate(
                        'Halbjahresinformation', '2. Klasse', 'ESS\EssGsHjTwo', $tblConsumerCertificate
                    );
                    if ($tblCertificate) {
                        if ($tblSchoolTypePrimary) {
                            $this->updateCertificate($tblCertificate, $tblCertificateTypeHalfYear,
                                $tblSchoolTypePrimary,
                                null, true);
                            if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '2'))) {
                                $this->createCertificateLevel($tblCertificate, $tblLevel);
                            }
                        }
                        // Begrenzung des Bemerkungsfelds
                        $FieldName = 'Rating';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                            $this->createCertificateField($tblCertificate, $FieldName, 750);
                        }
                        $FieldName = 'TechnicalRating';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                            $this->createCertificateField($tblCertificate, $FieldName, 1050);
                        }
                    }
                    $tblCertificate = $this->createCertificate(
                        'Jahreszeugnis', '2. Klasse', 'ESS\EssGsJTwo', $tblConsumerCertificate
                    );
                    if ($tblCertificate) {
                        if ($tblSchoolTypePrimary) {
                            $this->updateCertificate($tblCertificate, $tblCertificateTypeYear,
                                $tblSchoolTypePrimary,
                                null, false);
                            if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '2'))) {
                                $this->createCertificateLevel($tblCertificate, $tblLevel);
                            }
                        }
                        // Begrenzung des Bemerkungsfelds
                        $FieldName = 'Rating';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                            $this->createCertificateField($tblCertificate, $FieldName, 750);
                        }
                        $FieldName = 'TechnicalRating';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                            $this->createCertificateField($tblCertificate, $FieldName, 950);
                        }
                    }
                    $tblCertificate = $this->createCertificate(
                        'Halbjahresinformation', '3. Klasse', 'ESS\EssGsHjThree', $tblConsumerCertificate
                    );
                    if ($tblCertificate) {
                        if ($tblSchoolTypePrimary) {
                            $this->updateCertificate($tblCertificate, $tblCertificateTypeHalfYear,
                                $tblSchoolTypePrimary,
                                null, true);
                            if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '3'))) {
                                $this->createCertificateLevel($tblCertificate, $tblLevel);
                            }
                            // Begrenzung des Bemerkungsfelds
                            $FieldName = 'Rating';
                            if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                                $this->createCertificateField($tblCertificate, $FieldName, 270);
                            }
                        }
                        // Kopfnoten Setzen
                        if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
                            $this->setCertificateGradeAllStandard($tblCertificate);
                        }
                        //Fächer setzen
                        if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
                            $this->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                            $this->setCertificateSubject($tblCertificate, 'SU', 1, 2);
                            $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
                            $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                            $this->setCertificateSubject($tblCertificate, 'EN', 1, 5);

                            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                            $this->setCertificateSubject($tblCertificate, 'WE', 2, 2);
                            $this->setCertificateSubject($tblCertificate, 'REV', 2, 3);
                            $this->setCertificateSubject($tblCertificate, 'SPO', 2, 4);
                        }
                    }
                    $tblCertificate = $this->createCertificate(
                        'Jahreszeugnis', '3. Klasse', 'ESS\EssGsJThree', $tblConsumerCertificate
                    );
                    if ($tblCertificate) {
                        if ($tblSchoolTypePrimary) {
                            $this->updateCertificate($tblCertificate, $tblCertificateTypeYear,
                                $tblSchoolTypePrimary,
                                null, false);
                            if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '3'))) {
                                $this->createCertificateLevel($tblCertificate, $tblLevel);
                            }
                        }
                        // Begrenzung des Bemerkungsfelds
                        $FieldName = 'Rating';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                            $this->createCertificateField($tblCertificate, $FieldName, 400);
                        }
                        $FieldName = 'TechnicalRating';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                            $this->createCertificateField($tblCertificate, $FieldName, 500);
                        }

                        // Kopfnoten Setzen
                        if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
                            $this->setCertificateGradeAllStandard($tblCertificate);
                        }
                        //Fächer setzen
                        if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
                            $this->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                            $this->setCertificateSubject($tblCertificate, 'SU', 1, 2);
                            $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
                            $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                            $this->setCertificateSubject($tblCertificate, 'EN', 1, 5);

                            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                            $this->setCertificateSubject($tblCertificate, 'WE', 2, 2);
                            $this->setCertificateSubject($tblCertificate, 'REV', 2, 3);
                            $this->setCertificateSubject($tblCertificate, 'SPO', 2, 4);
                        }
                    }
//                    //ToDO Zeugnissvorlagen der Klasse 4 erstellen
////                    $tblCertificate = $this->createCertificate(
////                        'Halbjahresinformation', '4. Klasse', 'ESS\EssGsHjFour', $tblConsumerCertificate
////                    );
////                    if ($tblCertificate) {
////                        if ($tblSchoolTypePrimary) {
////                            $this->updateCertificate($tblCertificate, $tblCertificateTypeHalfYear,
////                                $tblSchoolTypePrimary,
////                                null, true);
////                            if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '4'))) {
////                                $this->createCertificateLevel($tblCertificate, $tblLevel);
////                            }
////                        }
////                        // Kopfnoten setzen
////                        if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
////                            $this->setCertificateGradeAllStandard($tblCertificate);
////                        }
////                        //Fächer setzen
////                        if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
////                            $this->setCertificateSubject($tblCertificate, 'DE', 1, 1);
////                            $this->setCertificateSubject($tblCertificate, 'SU', 1, 2);
////                            $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
////                            $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
////                            $this->setCertificateSubject($tblCertificate, 'EN', 1, 5);
////
////                            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
////                            $this->setCertificateSubject($tblCertificate, 'WE', 2, 2);
////                            $this->setCertificateSubject($tblCertificate, 'REV', 2, 3);
////                            $this->setCertificateSubject($tblCertificate, 'SPO', 2, 4);
////                        }
////                    }
////
////                    $tblCertificate = $this->createCertificate(
////                        'Jahreszeugnis', '4. Klasse', 'ESS\EssGsJFour', $tblConsumerCertificate
////                    );
////                    if ($tblCertificate) {
////                        if ($tblSchoolTypePrimary) {
////                            $this->updateCertificate($tblCertificate, $tblCertificateTypeYear,
////                                $tblSchoolTypePrimary,
////                                null, false);
////                            if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '4'))) {
////                                $this->createCertificateLevel($tblCertificate, $tblLevel);
////                            }
////                        }
////                        // Kopfnoten setzen
////                        if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
////                            $this->setCertificateGradeAllStandard($tblCertificate);
////                        }
////
////                        //Fächer setzen
////                        if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
////                            $this->setCertificateSubject($tblCertificate, 'DE', 1, 1);
////                            $this->setCertificateSubject($tblCertificate, 'SU', 1, 2);
////                            $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
////                            $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
////                            $this->setCertificateSubject($tblCertificate, 'EN', 1, 5);
////
////                            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
////                            $this->setCertificateSubject($tblCertificate, 'WE', 2, 2);
////                            $this->setCertificateSubject($tblCertificate, 'REV', 2, 3);
////                            $this->setCertificateSubject($tblCertificate, 'SPO', 2, 4);
////                        }
////                    }
                }
            }

            if ($tblConsumer->getAcronym() == 'EVAMTL' || $tblConsumer->getAcronym() == 'DEMO') {
                $tblConsumerCertificate = Consumer::useService()->getConsumerByAcronym('EVAMTL');
                if ($tblConsumerCertificate) {
                    $tblCertificate = $this->createCertificate('Gymnasium Jahreszeugnis', '', 'EVAMTL\MulGymJ',
                        $tblConsumerCertificate);
                    if ($tblCertificate) {
                        if ($tblSchoolTypeGym) {
                            if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeGym, '5'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeGym, '6'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeGym, '7'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeGym, '8'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeGym, '9'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeGym, '10'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeGym, '11'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                            }
                        }
                        // Begrenzung des Einschätzungfelds
                        $FieldName = 'Rating';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                            $this->createCertificateField($tblCertificate, $FieldName, 200);
                        }
                        // Begrenzung des Bemerkungsfelds
                        $FieldName = 'Remark';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                            $this->createCertificateField($tblCertificate, $FieldName, 300);
                        }
                    }
                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
                        $this->setCertificateGradeAllStandard($tblCertificate);
                    }
                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
                        $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
                        // 1,3 freilassen für Fremdsprache
                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 4);
                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 5);
                        $this->setCertificateSubject($tblCertificate, 'GE', 1, 6);
                        $this->setCertificateSubject($tblCertificate, 'GRW', 1, 7);
                        $this->setCertificateSubject($tblCertificate, 'GEO', 1, 8);

                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                        $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
                        $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
                        $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
                        $this->setCertificateSubject($tblCertificate, 'RELI', 2, 6);
                        $this->setCertificateSubject($tblCertificate, 'TC', 2, 7);
                        $this->setCertificateSubject($tblCertificate, 'IN', 2, 8);
                    }
                }
            }

            if ($tblConsumer->getAcronym() == 'ESRL' || $tblConsumer->getAcronym() == 'DEMO') {
                // declare active Consumer
                $tblConsumerCertificate = Consumer::useService()->getConsumerByAcronym('ESRL');
                if ($tblConsumerCertificate) {
                    $tblCertificate = $this->createCertificate('Grundschule Halbjahresinformation', 'der ersten Klasse',
                        'ESRL\EsrlGsHjOne',
                        $tblConsumerCertificate);
                    if ($tblCertificate) {
                        if ($tblSchoolTypePrimary) {
                            $this->updateCertificate($tblCertificate, $tblCertificateTypeHalfYear,
                                $tblSchoolTypePrimary,
                                null, true);
                            if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '1'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                            }
                        }
                        // Begrenzung des Bemerkungsfelds
                        $FieldName = 'Remark';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                            $this->createCertificateField($tblCertificate, $FieldName, 2700);
                        }
                    }

                    $tblCertificate = $this->createCertificate('Grundschule Jahreszeugnis', 'der ersten Klasse',
                        'ESRL\EsrlGsJOne',
                        $tblConsumerCertificate);
                    if ($tblCertificate) {
                        if ($tblSchoolTypePrimary) {
                            $this->updateCertificate($tblCertificate, $tblCertificateTypeYear, $tblSchoolTypePrimary,
                                null, false);
                            if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '1'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                            }
                        }
                        // Begrenzung des Bemerkungsfelds
                        $FieldName = 'Remark';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                            $this->createCertificateField($tblCertificate, $FieldName, 2700);
                        }
                    }

                    $tblCertificate = $this->createCertificate('Grundschule Halbjahresinformation', 'Klasse 2-4',
                        'ESRL\EsrlGsHj',
                        $tblConsumerCertificate);
                    if ($tblCertificate) {
                        if ($tblSchoolTypePrimary) {
                            $this->updateCertificate($tblCertificate, $tblCertificateTypeHalfYear,
                                $tblSchoolTypePrimary,
                                null, true);
                            if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '2'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '3'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '4'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                            }
                        }
                        // Begrenzung des Bemerkungsfelds
                        $FieldName = 'Remark';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                            $this->createCertificateField($tblCertificate, $FieldName, 1500);
                        }
                    }
                    // Kopfnoten
                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
                        $this->setCertificateGradeAllStandard($tblCertificate);
                    }
                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
                        $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
                        $this->setCertificateSubject($tblCertificate, 'SU', 1, 2);
                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 3);
                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 4);
                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 5);
                        $this->setCertificateSubject($tblCertificate, 'SG', 1, 6);

                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                        $this->setCertificateSubject($tblCertificate, 'SP', 2, 2);
                        $this->setCertificateSubject($tblCertificate, 'REL', 2, 3);
                        $this->setCertificateSubject($tblCertificate, 'WE', 2, 4);
                        $this->setCertificateSubject($tblCertificate, 'RHY', 2, 5);
                        $this->setCertificateSubject($tblCertificate, 'CHOR', 2, 6);
                    }

                    $tblCertificate = $this->createCertificate('Grundschule Jahreszeugnis', 'Klasse 2-4',
                        'ESRL\EsrlGsJ',
                        $tblConsumerCertificate);
                    if ($tblCertificate) {
                        if ($tblSchoolTypePrimary) {
                            $this->updateCertificate($tblCertificate, $tblCertificateTypeYear, $tblSchoolTypePrimary,
                                null, false);
                            if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '2'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '3'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '4'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                            }
                        }
                        // Begrenzung des Bemerkungsfelds
                        $FieldName = 'Remark';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                            $this->createCertificateField($tblCertificate, $FieldName, 1500);
                        }
                    }
                    // Kopfnoten
                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
                        $this->setCertificateGradeAllStandard($tblCertificate);
                    }
                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
                        $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
                        $this->setCertificateSubject($tblCertificate, 'SU', 1, 2);
                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 3);
                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 4);
                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 5);
                        $this->setCertificateSubject($tblCertificate, 'SG', 1, 6);

                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                        $this->setCertificateSubject($tblCertificate, 'SP', 2, 2);
                        $this->setCertificateSubject($tblCertificate, 'REL', 2, 3);
                        $this->setCertificateSubject($tblCertificate, 'WE', 2, 4);
                        $this->setCertificateSubject($tblCertificate, 'RHY', 2, 5);
                        $this->setCertificateSubject($tblCertificate, 'CHOR', 2, 6);
                    }
                }
            }
            if ($tblConsumer->getAcronym() == 'CMS' || $tblConsumer->getAcronym() == 'DEMO') {
                // declare active Consumer
                $tblConsumerCertificate = Consumer::useService()->getConsumerByAcronym('CMS');
                if ($tblConsumerCertificate) {
                    $tblCertificate = $this->createCertificate('Grundschule Halbjahresinformation', 'Klasse 1-2',
                        'CMS\CmsGsHjOneTwo', $tblConsumerCertificate, false, true);
                    if ($tblCertificate) {
                        if ($tblSchoolTypePrimary) {
                            $this->updateCertificate($tblCertificate, $tblCertificateTypeHalfYear,
                                $tblSchoolTypePrimary,
                                null, true);
                            if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '1'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '2'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                            }
                        }
                        // Begrenzung des Bemerkungsfelds
                        $FieldName = 'Remark';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                            $this->createCertificateField($tblCertificate, $FieldName, 2600);
                        }
                    }
                    //
                    $tblCertificate = $this->createCertificate('Grundschule Halbjahresinformation (2 Seiten)',
                        'Klasse 1-2',
                        'CMS\CmsGsHjOneTwoExt', $tblConsumerCertificate, false, true, true);
                    if ($tblCertificate) {
                        if ($tblSchoolTypePrimary) {
                            $this->updateCertificate($tblCertificate, $tblCertificateTypeHalfYear,
                                $tblSchoolTypePrimary,
                                null, true);
                            if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '1'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '2'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                            }
                        }
                        // Begrenzung des Bemerkungsfelds
                        $FieldName = 'Remark';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                            $this->createCertificateField($tblCertificate, $FieldName, 3600);
                        }
                        // Begrenzung des Bemerkungsfelds der 2.ten Seite
                        $FieldName = 'SecondRemark';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                            $this->createCertificateField($tblCertificate, $FieldName, 3700);
                        }
                    }
                    //
                    $tblCertificate = $this->createCertificate('Grundschule Jahreszeugnis', 'Klasse 1-2',
                        'CMS\CmsGsJOneTwo', $tblConsumerCertificate);
                    if ($tblCertificate) {
                        if ($tblSchoolTypePrimary) {
                            $this->updateCertificate($tblCertificate, $tblCertificateTypeYear, $tblSchoolTypePrimary);
                            if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '1'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '2'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                            }
                        }
                        // Begrenzung des Bemerkungsfelds
                        $FieldName = 'Remark';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                            $this->createCertificateField($tblCertificate, $FieldName, 2600);
                        }
                    }
                    //
                    $tblCertificate = $this->createCertificate('Grundschule Jahreszeugnis (2 Seiten)', 'Klasse 1-2',
                        'CMS\CmsGsJOneTwoExt', $tblConsumerCertificate, false, false, true);
                    if ($tblCertificate) {
                        if ($tblSchoolTypePrimary) {
                            $this->updateCertificate($tblCertificate, $tblCertificateTypeYear, $tblSchoolTypePrimary);
                            if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '1'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '2'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                            }
                        }
                        // Begrenzung des Bemerkungsfelds
                        $FieldName = 'Remark';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                            $this->createCertificateField($tblCertificate, $FieldName, 3600);
                        }
                        // Begrenzung des Bemerkungsfelds der 2.ten Seite
                        $FieldName = 'SecondRemark';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                            $this->createCertificateField($tblCertificate, $FieldName, 3500);
                        }
                    }
                    //
                    $tblCertificate = $this->createCertificate('Grundschule Halbjahresinformation', 'Klasse 3-4',
                        'CMS\CmsGsHj', $tblConsumerCertificate, false, true);
                    if ($tblCertificate) {
                        if ($tblSchoolTypePrimary) {
                            $this->updateCertificate($tblCertificate, $tblCertificateTypeHalfYear,
                                $tblSchoolTypePrimary,
                                null, true);
                            if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '3'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '4'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                            }
                        }
                        // Begrenzung des Bemerkungsfelds
                        $FieldName = 'Remark';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                            $this->createCertificateField($tblCertificate, $FieldName, 2100);
                        }
                        // Kopfnoten
                        if (!$this->getCertificateGradeAll($tblCertificate)) {
                            $this->setCertificateGradeAllStandard($tblCertificate);
                        }
                        if (!$this->getCertificateSubjectAll($tblCertificate)) {
                            $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
                            $this->setCertificateSubject($tblCertificate, 'SU', 1, 2);

                            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                        }
                    }
                    //
                    $tblCertificate = $this->createCertificate('Grundschule Halbjahresinformation (2 Seiten)',
                        'Klasse 3-4',
                        'CMS\CmsGsHjExt', $tblConsumerCertificate, false, true, true);
                    if ($tblCertificate) {
                        if ($tblSchoolTypePrimary) {
                            $this->updateCertificate($tblCertificate, $tblCertificateTypeHalfYear,
                                $tblSchoolTypePrimary,
                                null, true);
                            if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '3'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '4'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                            }
                        }
                        // Begrenzung des Bemerkungsfelds
                        $FieldName = 'Remark';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                            $this->createCertificateField($tblCertificate, $FieldName, 2900);
                        }
                        // Begrenzung des Bemerkungsfelds der 2.ten Seite
                        $FieldName = 'SecondRemark';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                            $this->createCertificateField($tblCertificate, $FieldName, 3700);
                        }
                        // Kopfnoten
                        if (!$this->getCertificateGradeAll($tblCertificate)) {
                            $this->setCertificateGradeAllStandard($tblCertificate);
                        }
                        if (!$this->getCertificateSubjectAll($tblCertificate)) {
                            $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
                            $this->setCertificateSubject($tblCertificate, 'SU', 1, 2);

                            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                        }
                    }
                    //
                    $tblCertificate = $this->createCertificate('Jahreszeugnis Grundschule ', 'Klasse 3-4',
                        'CMS\CmsGsJ', $tblConsumerCertificate);
                    if ($tblCertificate) {
                        if ($tblSchoolTypePrimary) {
                            $this->updateCertificate($tblCertificate, $tblCertificateTypeYear, $tblSchoolTypePrimary);
                            if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '3'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '4'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                            }
                        }
                        // Begrenzung des Bemerkungsfelds
                        $FieldName = 'Remark';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                            $this->createCertificateField($tblCertificate, $FieldName, 1900);
                        }
                        // Kopfnoten
                        if (!$this->getCertificateGradeAll($tblCertificate)) {
                            $this->setCertificateGradeAllStandard($tblCertificate);
                        }
                        if (!$this->getCertificateSubjectAll($tblCertificate)) {
                            $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
                            $this->setCertificateSubject($tblCertificate, 'SU', 1, 2);

                            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                        }
                    }
                    //
                    $tblCertificate = $this->createCertificate('Jahreszeugnis Grundschule (2 Seiten)', 'Klasse 3-4',
                        'CMS\CmsGsJExt', $tblConsumerCertificate, false, false, true);
                    if ($tblCertificate) {
                        if ($tblSchoolTypePrimary) {
                            $this->updateCertificate($tblCertificate, $tblCertificateTypeYear, $tblSchoolTypePrimary);
                            if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '3'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypePrimary, '4'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                            }
                        }
                        // Begrenzung des Bemerkungsfelds
                        $FieldName = 'Remark';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                            $this->createCertificateField($tblCertificate, $FieldName, 2900);
                        }
                        // Begrenzung des Bemerkungsfelds der 2.ten Seite
                        $FieldName = 'SecondRemark';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                            $this->createCertificateField($tblCertificate, $FieldName, 3500);
                        }
                        // Kopfnoten
                        if (!$this->getCertificateGradeAll($tblCertificate)) {
                            $this->setCertificateGradeAllStandard($tblCertificate);
                        }
                        if (!$this->getCertificateSubjectAll($tblCertificate)) {
                            $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
                            $this->setCertificateSubject($tblCertificate, 'SU', 1, 2);

                            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                        }
                    }
                    //
                    $tblCertificate = $this->createCertificate('Oberschule Halbjahresinformation', 'Klasse 5-9',
                        'CMS\CmsMsHj', $tblConsumerCertificate, false, true);
                    if ($tblCertificate) {
                        if ($tblSchoolTypeSecondary) {
                            $this->updateCertificate($tblCertificate, $tblCertificateTypeHalfYear,
                                $tblSchoolTypeSecondary,
                                null, true);
                            if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '5'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '6'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '7'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '8'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '9'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                            }
                        }
                        // Begrenzung des Bemerkungsfelds
                        $FieldName = 'Remark';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                            $this->createCertificateField($tblCertificate, $FieldName, 700);
                        }
                        // Kopfnoten
                        if (!$this->getCertificateGradeAll($tblCertificate)) {
                            $this->setCertificateGradeAllStandard($tblCertificate);
                        }
                        if (!$this->getCertificateSubjectAll($tblCertificate)) {
                            $this->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                            $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
                            $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
                            $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                            $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
                            $this->setCertificateSubject($tblCertificate, 'GEO', 1, 6);
                            $this->setCertificateSubject($tblCertificate, 'WTH', 1, 7);
                            $this->setCertificateSubject($tblCertificate, 'FR', 1, 8);

                            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                            $this->setCertificateSubject($tblCertificate, 'BI', 2, 2);
                            $this->setCertificateSubject($tblCertificate, 'TC', 2, 3);
                            $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
                            $this->setCertificateSubject($tblCertificate, 'REV', 2, 5, false);
                            $this->setCertificateSubject($tblCertificate, 'RKA', 2, 6, false);
                            $this->setCertificateSubject($tblCertificate, 'ETH', 2, 7, false);
                            $this->setCertificateSubject($tblCertificate, 'SPO', 2, 8);
                            $this->setCertificateSubject($tblCertificate, 'CH', 2, 9);
                        }
                    }
                    //
                    $tblCertificate = $this->createCertificate('Oberschule Halbjahresinformation (2 Seiten)',
                        'Klasse 5-9',
                        'CMS\CmsMsHjExt', $tblConsumerCertificate, false, true, true);
                    if ($tblCertificate) {
                        if ($tblSchoolTypeSecondary) {
                            $this->updateCertificate($tblCertificate, $tblCertificateTypeHalfYear,
                                $tblSchoolTypeSecondary,
                                null, true);
                            if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '5'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '6'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '7'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '8'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '9'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                            }
                        }
                        // Begrenzung des Bemerkungsfelds
                        $FieldName = 'Remark';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                            $this->createCertificateField($tblCertificate, $FieldName, 1600);
                        }
                        // Begrenzung des Bemerkungsfelds
                        $FieldName = 'SecondRemark';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                            $this->createCertificateField($tblCertificate, $FieldName, 3700);
                        }
                        // Kopfnoten
                        if (!$this->getCertificateGradeAll($tblCertificate)) {
                            $this->setCertificateGradeAllStandard($tblCertificate);
                        }
                        if (!$this->getCertificateSubjectAll($tblCertificate)) {
                            $this->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                            $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
                            $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
                            $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                            $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
                            $this->setCertificateSubject($tblCertificate, 'GEO', 1, 6);
                            $this->setCertificateSubject($tblCertificate, 'WTH', 1, 7);
                            $this->setCertificateSubject($tblCertificate, 'FR', 1, 8);

                            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                            $this->setCertificateSubject($tblCertificate, 'BI', 2, 2);
                            $this->setCertificateSubject($tblCertificate, 'TC', 2, 3);
                            $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
                            $this->setCertificateSubject($tblCertificate, 'REV', 2, 5, false);
                            $this->setCertificateSubject($tblCertificate, 'RKA', 2, 6, false);
                            $this->setCertificateSubject($tblCertificate, 'ETH', 2, 7, false);
                            $this->setCertificateSubject($tblCertificate, 'SPO', 2, 8);
                            $this->setCertificateSubject($tblCertificate, 'CH', 2, 9);
                        }
                    }
                    //
                    $tblCertificate = $this->createCertificate('Oberschule Halbjahreszeugnis', 'Klasse 9-10',
                        'CMS\CmsMsHjZ', $tblConsumerCertificate);
                    if ($tblCertificate) {
                        if ($tblSchoolTypeSecondary) {
                            $this->updateCertificate($tblCertificate, $tblCertificateTypeHalfYear,
                                $tblSchoolTypeSecondary);
                            if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '9'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '10'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                            }
                        }
                        // Begrenzung des Bemerkungsfelds
                        $FieldName = 'Remark';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                            $this->createCertificateField($tblCertificate, $FieldName, 700);
                        }
                        // Kopfnoten
                        if (!$this->getCertificateGradeAll($tblCertificate)) {
                            $this->setCertificateGradeAllStandard($tblCertificate);
                        }
                        if (!$this->getCertificateSubjectAll($tblCertificate)) {
                            $this->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                            $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
                            $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
                            $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                            $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
                            $this->setCertificateSubject($tblCertificate, 'GEO', 1, 6);
                            $this->setCertificateSubject($tblCertificate, 'WTH', 1, 7);
                            $this->setCertificateSubject($tblCertificate, 'FR', 1, 8);

                            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                            $this->setCertificateSubject($tblCertificate, 'BI', 2, 2);
                            $this->setCertificateSubject($tblCertificate, 'TC', 2, 3);
                            $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
                            $this->setCertificateSubject($tblCertificate, 'REV', 2, 5, false);
                            $this->setCertificateSubject($tblCertificate, 'RKA', 2, 6, false);
                            $this->setCertificateSubject($tblCertificate, 'ETH', 2, 7, false);
                            $this->setCertificateSubject($tblCertificate, 'SPO', 2, 8);
                            $this->setCertificateSubject($tblCertificate, 'CH', 2, 9);
                        }
                    }
                    //
                    $tblCertificate = $this->createCertificate('Oberschule Halbjahreszeugnis (2 Seiten)', 'Klasse 9-10',
                        'CMS\CmsMsHjZExt', $tblConsumerCertificate, false, false, true);
                    if ($tblCertificate) {
                        if ($tblSchoolTypeSecondary) {
                            $this->updateCertificate($tblCertificate, $tblCertificateTypeHalfYear,
                                $tblSchoolTypeSecondary);
                            if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '9'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '10'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                            }
                        }
                        // Begrenzung des Bemerkungsfelds
                        $FieldName = 'Remark';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                            $this->createCertificateField($tblCertificate, $FieldName, 1600);
                        }
                        // Begrenzung des Bemerkungsfelds
                        $FieldName = 'SecondRemark';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                            $this->createCertificateField($tblCertificate, $FieldName, 3700);
                        }
                        // Kopfnoten
                        if (!$this->getCertificateGradeAll($tblCertificate)) {
                            $this->setCertificateGradeAllStandard($tblCertificate);
                        }
                        if (!$this->getCertificateSubjectAll($tblCertificate)) {
                            $this->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                            $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
                            $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
                            $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                            $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
                            $this->setCertificateSubject($tblCertificate, 'GEO', 1, 6);
                            $this->setCertificateSubject($tblCertificate, 'WTH', 1, 7);
                            $this->setCertificateSubject($tblCertificate, 'FR', 1, 8);

                            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                            $this->setCertificateSubject($tblCertificate, 'BI', 2, 2);
                            $this->setCertificateSubject($tblCertificate, 'TC', 2, 3);
                            $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
                            $this->setCertificateSubject($tblCertificate, 'REV', 2, 5, false);
                            $this->setCertificateSubject($tblCertificate, 'RKA', 2, 6, false);
                            $this->setCertificateSubject($tblCertificate, 'ETH', 2, 7, false);
                            $this->setCertificateSubject($tblCertificate, 'SPO', 2, 8);
                            $this->setCertificateSubject($tblCertificate, 'CH', 2, 9);
                        }
                    }
                    //
                    $tblCertificate = $this->createCertificate('Oberschule Jahreszeugnis', 'Klasse 5-9',
                        'CMS\CmsMsJ', $tblConsumerCertificate);
                    if ($tblCertificate) {
                        if ($tblSchoolTypeSecondary) {
                            $this->updateCertificate($tblCertificate, $tblCertificateTypeYear, $tblSchoolTypeSecondary);
                            if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '5'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '6'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '7'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '8'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '9'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                            }
                        }
                        // Begrenzung des Bemerkungsfelds
                        $FieldName = 'Remark';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                            $this->createCertificateField($tblCertificate, $FieldName, 500);
                        }
                        // Kopfnoten
                        if (!$this->getCertificateGradeAll($tblCertificate)) {
                            $this->setCertificateGradeAllStandard($tblCertificate);
                        }
                        if (!$this->getCertificateSubjectAll($tblCertificate)) {
                            $this->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                            $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
                            $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
                            $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                            $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
                            $this->setCertificateSubject($tblCertificate, 'GEO', 1, 6);
                            $this->setCertificateSubject($tblCertificate, 'WTH', 1, 7);
                            $this->setCertificateSubject($tblCertificate, 'FR', 1, 8);

                            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                            $this->setCertificateSubject($tblCertificate, 'BI', 2, 2);
                            $this->setCertificateSubject($tblCertificate, 'TC', 2, 3);
                            $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
                            $this->setCertificateSubject($tblCertificate, 'REV', 2, 5, false);
                            $this->setCertificateSubject($tblCertificate, 'RKA', 2, 6, false);
                            $this->setCertificateSubject($tblCertificate, 'ETH', 2, 7, false);
                            $this->setCertificateSubject($tblCertificate, 'SPO', 2, 8);
                            $this->setCertificateSubject($tblCertificate, 'CH', 2, 9);
                        }
                    }
                    //
                    $tblCertificate = $this->createCertificate('Oberschule Jahreszeugnis (2 Seiten)', 'Klasse 5-9',
                        'CMS\CmsMsJExt', $tblConsumerCertificate, false, false, true);
                    if ($tblCertificate) {
                        if ($tblSchoolTypeSecondary) {
                            $this->updateCertificate($tblCertificate, $tblCertificateTypeYear, $tblSchoolTypeSecondary);
                            if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '5'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '6'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '7'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '8'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '9'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                            }
                        }
                        // Begrenzung des Bemerkungsfelds
                        $FieldName = 'Remark';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                            $this->createCertificateField($tblCertificate, $FieldName, 1600);
                        }
                        // Begrenzung des Bemerkungsfelds
                        $FieldName = 'SecondRemark';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                            $this->createCertificateField($tblCertificate, $FieldName, 3500);
                        }
                        // Kopfnoten
                        if (!$this->getCertificateGradeAll($tblCertificate)) {
                            $this->setCertificateGradeAllStandard($tblCertificate);
                        }
                        if (!$this->getCertificateSubjectAll($tblCertificate)) {
                            $this->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                            $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
                            $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
                            $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                            $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
                            $this->setCertificateSubject($tblCertificate, 'GEO', 1, 6);
                            $this->setCertificateSubject($tblCertificate, 'WTH', 1, 7);
                            $this->setCertificateSubject($tblCertificate, 'FR', 1, 8);

                            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                            $this->setCertificateSubject($tblCertificate, 'BI', 2, 2);
                            $this->setCertificateSubject($tblCertificate, 'TC', 2, 3);
                            $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
                            $this->setCertificateSubject($tblCertificate, 'REV', 2, 5, false);
                            $this->setCertificateSubject($tblCertificate, 'RKA', 2, 6, false);
                            $this->setCertificateSubject($tblCertificate, 'ETH', 2, 7, false);
                            $this->setCertificateSubject($tblCertificate, 'SPO', 2, 8);
                            $this->setCertificateSubject($tblCertificate, 'CH', 2, 9);
                        }
                    }
                }
            }
            if ($tblConsumer->getAcronym() == 'EZSH' || $tblConsumer->getAcronym() == 'DEMO') {
                // declare active Consumer
                $tblConsumerCertificate = Consumer::useService()->getConsumerByAcronym('EZSH');
                if ($tblConsumerCertificate) {
                    $tblCertificate = $this->createCertificate('Oberschule Halbjahresinformation', 'Klasse 5-9',
                        'EZSH\EzshMsHj', $tblConsumerCertificate, false, true);
                    if ($tblCertificate) {
                        if ($tblSchoolTypeSecondary) {
                            $this->updateCertificate($tblCertificate, $tblCertificateTypeHalfYear,
                                $tblSchoolTypeSecondary);
                            if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '5'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '6'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '7'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '8'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '9'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                            }
                        }
                        // Begrenzung der Einschätzung
                        $FieldName = 'Rating';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                            $this->createCertificateField($tblCertificate, $FieldName, 2500);
                        }
                        // Begrenzung Bemerkungsfeld
                        $FieldName = 'RemarkWithoutTeam';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                            $this->createCertificateField($tblCertificate, $FieldName, 1000);
                        }
                        if (!$this->getCertificateSubjectAll($tblCertificate)) {
                            $this->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                            $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
                            $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
                            $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                            $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
                            $this->setCertificateSubject($tblCertificate, 'GRW', 1, 6);
                            $this->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
                            $this->setCertificateSubject($tblCertificate, 'WTH', 1, 8);

                            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                            $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
                            $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
                            $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
                            $this->setCertificateSubject($tblCertificate, 'INF', 2, 5);
                            $this->setCertificateSubject($tblCertificate, 'TUC', 2, 6);
                            $this->setCertificateSubject($tblCertificate, 'REE', 2, 7);
                            $this->setCertificateSubject($tblCertificate, 'SPO', 2, 8);
                        }
                    }
                    $tblCertificate = $this->createCertificate('Gymnasium Halbjahresinformation', 'Klasse 5-9',
                        'EZSH\EzshGymHj', $tblConsumerCertificate, false, true);
                    if ($tblCertificate) {
                        if ($tblSchoolTypeGym) {
                            $this->updateCertificate($tblCertificate, $tblCertificateTypeHalfYear,
                                $tblSchoolTypeGym);
                            if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeGym, '5'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeGym, '6'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeGym, '7'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeGym, '8'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeGym, '9'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                            }
                        }
                        // Begrenzung der Einschätzung
                        $FieldName = 'Rating';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                            $this->createCertificateField($tblCertificate, $FieldName, 2500);
                        }
                        // Begrenzung Bemerkungsfeld
                        $FieldName = 'RemarkWithoutTeam';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                            $this->createCertificateField($tblCertificate, $FieldName, 1000);
                        }
                        if (!$this->getCertificateSubjectAll($tblCertificate)) {
                            $this->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                            $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);

                            $this->setCertificateSubject($tblCertificate, 'TSCN', 1, 3, false);
                            $this->setCertificateSubject($tblCertificate, 'LA', 1, 4, false);
                            // lücke für Fremdsprachen
                            $this->setCertificateSubject($tblCertificate, 'KU', 1, 6);
                            $this->setCertificateSubject($tblCertificate, 'MU', 1, 7);
                            $this->setCertificateSubject($tblCertificate, 'GE', 1, 8);
                            $this->setCertificateSubject($tblCertificate, 'WTH', 1, 9);
                            $this->setCertificateSubject($tblCertificate, 'GEO', 1, 10);

                            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                            $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
                            $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
                            $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
                            $this->setCertificateSubject($tblCertificate, 'INF', 2, 5);
                            $this->setCertificateSubject($tblCertificate, 'TUC', 2, 6);
                            $this->setCertificateSubject($tblCertificate, 'REE', 2, 7);
                            $this->setCertificateSubject($tblCertificate, 'SPO', 2, 8);
                        }
                    }
                    $tblCertificate = $this->createCertificate('Gymnasium Halbjahreszeugnis', 'Klasse 10',
                        'EZSH\EzshGymHjZ', $tblConsumerCertificate);
                    if ($tblCertificate) {
                        if ($tblSchoolTypeGym) {
                            $this->updateCertificate($tblCertificate, $tblCertificateTypeHalfYear,
                                $tblSchoolTypeGym);
                            if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeGym, '10'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                            }
                        }
                        // Begrenzung der Einschätzung
                        $FieldName = 'Rating';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                            $this->createCertificateField($tblCertificate, $FieldName, 2500);
                        }
                        // Begrenzung Bemerkungsfeld
                        $FieldName = 'RemarkWithoutTeam';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                            $this->createCertificateField($tblCertificate, $FieldName, 1000);
                        }
                        if (!$this->getCertificateSubjectAll($tblCertificate)) {
                            $this->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                            $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
                            $this->setCertificateSubject($tblCertificate, 'TSCN', 1, 3, false);
                            $this->setCertificateSubject($tblCertificate, 'LA', 1, 4, false);
                            // lücke für Fremdsprachen
                            $this->setCertificateSubject($tblCertificate, 'KU', 1, 6);
                            $this->setCertificateSubject($tblCertificate, 'MU', 1, 7);
                            $this->setCertificateSubject($tblCertificate, 'GE', 1, 8);
                            $this->setCertificateSubject($tblCertificate, 'WTH', 1, 9);
                            $this->setCertificateSubject($tblCertificate, 'GEO', 1, 10);

                            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                            $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
                            $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
                            $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
                            $this->setCertificateSubject($tblCertificate, 'INF', 2, 5);
                            $this->setCertificateSubject($tblCertificate, 'TUC', 2, 6);
                            $this->setCertificateSubject($tblCertificate, 'REE', 2, 7);
                            $this->setCertificateSubject($tblCertificate, 'SPO', 2, 8);
                        }
                    }
                    // weitere Zeugnisse EZSH
                    $tblCertificate = $this->createCertificate('Gymnasium Jahreszeugnis', '',
                        'EZSH\EzshGymJ', $tblConsumerCertificate, false, false, false, $tblCertificateTypeYear, $tblSchoolTypeGym);
                    if ($tblCertificate) {
                        if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                            if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeGym, '5'))) {
                                $this->createCertificateLevel($tblCertificate, $tblLevel);
                            }
                            if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeGym, '6'))) {
                                $this->createCertificateLevel($tblCertificate, $tblLevel);
                            }
                            if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeGym, '7'))) {
                                $this->createCertificateLevel($tblCertificate, $tblLevel);
                            }
                            if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeGym, '8'))) {
                                $this->createCertificateLevel($tblCertificate, $tblLevel);
                            }
                            if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeGym, '9'))) {
                                $this->createCertificateLevel($tblCertificate, $tblLevel);
                            }
                            if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeGym, '10'))) {
                                $this->createCertificateLevel($tblCertificate, $tblLevel);
                            }
                        }
                        // Begrenzung der Einschätzung
                        $FieldName = 'Rating';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                            $this->createCertificateField($tblCertificate, $FieldName, 2300);
                        }
                        // Begrenzung Bemerkungsfeld
                        $FieldName = 'RemarkWithoutTeam';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                            $this->createCertificateField($tblCertificate, $FieldName, 1000);
                        }
                        if (!$this->getCertificateSubjectAll($tblCertificate)) {
                            $this->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                            $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);

                            $this->setCertificateSubject($tblCertificate, 'TSCN', 1, 3, false);
                            $this->setCertificateSubject($tblCertificate, 'LA', 1, 4, false);
                            // lücke für Fremdsprachen
                            $this->setCertificateSubject($tblCertificate, 'KU', 1, 6);
                            $this->setCertificateSubject($tblCertificate, 'MU', 1, 7);
                            $this->setCertificateSubject($tblCertificate, 'GE', 1, 8);
                            $this->setCertificateSubject($tblCertificate, 'WTH', 1, 9);
                            $this->setCertificateSubject($tblCertificate, 'GEO', 1, 10);

                            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                            $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
                            $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
                            $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
                            $this->setCertificateSubject($tblCertificate, 'INF', 2, 5);
                            $this->setCertificateSubject($tblCertificate, 'TUC', 2, 6);
                            $this->setCertificateSubject($tblCertificate, 'REE', 2, 7);
                            $this->setCertificateSubject($tblCertificate, 'SPO', 2, 8);
                        }
                    }
                    $tblCertificate = $this->createCertificate('Oberschule Jahreszeugnis', '',
                        'EZSH\EzshMsJ', $tblConsumerCertificate, false, false, false, $tblCertificateTypeYear, $tblSchoolTypeSecondary);
                    if ($tblCertificate) {
                        if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                            if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '5'))) {
                                $this->createCertificateLevel($tblCertificate, $tblLevel);
                            }
                            if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '6'))) {
                                $this->createCertificateLevel($tblCertificate, $tblLevel);
                            }
                            if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '7'))) {
                                $this->createCertificateLevel($tblCertificate, $tblLevel);
                            }
                            if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '8'))) {
                                $this->createCertificateLevel($tblCertificate, $tblLevel);
                            }
                            if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '9'))) {
                                $this->createCertificateLevel($tblCertificate, $tblLevel);
                            }
                            if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '10'))) {
                                $this->createCertificateLevel($tblCertificate, $tblLevel);
                            }
                        }
                        // Begrenzung der Einschätzung
                        $FieldName = 'Rating';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                            $this->createCertificateField($tblCertificate, $FieldName, 2300);
                        }
                        // Begrenzung Bemerkungsfeld
                        $FieldName = 'RemarkWithoutTeam';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                            $this->createCertificateField($tblCertificate, $FieldName, 1000);
                        }
                        if (!$this->getCertificateSubjectAll($tblCertificate)) {
                            $this->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                            $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
                            $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
                            $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                            $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
                            $this->setCertificateSubject($tblCertificate, 'GRW', 1, 6);
                            $this->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
                            $this->setCertificateSubject($tblCertificate, 'WTH', 1, 8);

                            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                            $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
                            $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
                            $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
                            $this->setCertificateSubject($tblCertificate, 'INF', 2, 5);
                            $this->setCertificateSubject($tblCertificate, 'TUC', 2, 6);
                            $this->setCertificateSubject($tblCertificate, 'REE', 2, 7);
                            $this->setCertificateSubject($tblCertificate, 'SPO', 2, 8);
                        }
                    }
                    $tblCertificate = $this->createCertificate('Gymnasium Abgangszeugnis', 'Klasse 10',
                        'EZSH\EzshGymAbg', $tblConsumerCertificate, false, false, false, $tblCertificateTypeLeave, $tblSchoolTypeGym);
                    if ($tblCertificate) {
                        if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                            if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeGym, '10'))) {
                                $this->createCertificateLevel($tblCertificate, $tblLevel);
                            }
                        }
                        // Begrenzung Bemerkungsfeld
                        $FieldName = 'RemarkWithoutTeam';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                            $this->createCertificateField($tblCertificate, $FieldName, 1000);
                        }
                        if (!$this->getCertificateSubjectAll($tblCertificate)) {
                            $this->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                            $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);

                            $this->setCertificateSubject($tblCertificate, 'TSCN', 1, 3, false);
                            $this->setCertificateSubject($tblCertificate, 'LA', 1, 4, false);
                            // lücke für Fremdsprachen
                            $this->setCertificateSubject($tblCertificate, 'KU', 1, 6);
                            $this->setCertificateSubject($tblCertificate, 'MU', 1, 7);
                            $this->setCertificateSubject($tblCertificate, 'GE', 1, 8);
                            $this->setCertificateSubject($tblCertificate, 'WTH', 1, 9);
                            $this->setCertificateSubject($tblCertificate, 'GEO', 1, 10);

                            $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                            $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
                            $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
                            $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
                            $this->setCertificateSubject($tblCertificate, 'INF', 2, 5);
                            $this->setCertificateSubject($tblCertificate, 'TUC', 2, 6);
                            $this->setCertificateSubject($tblCertificate, 'REE', 2, 7);
                            $this->setCertificateSubject($tblCertificate, 'SPO', 2, 8);
                        }
                    }
                }
            }

            if ($tblConsumer->getAcronym() == 'CSW' || $tblConsumer->getAcronym() == 'DEMO') {
                // declare active Consumer
                $tblConsumerCertificate = Consumer::useService()->getConsumerByAcronym('CSW');
                if ($tblConsumerCertificate) {
                    $tblCertificate = $this->createCertificate('Mittelschule Halbjahresinformation', 'Klasse 5-6',
                        'CSW\CswMsHalbjahresinformation', $tblConsumerCertificate);
                    if ($tblCertificate) {
                        if ($tblSchoolTypeSecondary) {
                            $this->updateCertificate($tblCertificate, $tblCertificateTypeHalfYear,
                                $tblSchoolTypeSecondary, null, true);
                            if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '5'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '6'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                            }
                        }
                        // Begrenzung des Bemerkungsfeld
                        $FieldName = 'Remark';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)) {
                            $this->createCertificateField($tblCertificate, $FieldName, 600);
                        }
                    }
                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
                        $this->setCertificateGradeAllStandard($tblCertificate);
                    }
                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
                        $this->setCertificateSubject($tblCertificate, 'D', 1, 1);
                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                        $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
                        $this->setCertificateSubject($tblCertificate, 'GK', 1, 6);
                        $this->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
                        $this->setCertificateSubject($tblCertificate, 'WTH', 1, 8);

                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                        $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
                        $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
                        $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
                        $this->setCertificateSubject($tblCertificate, 'RELI', 2, 6);
                        $this->setCertificateSubject($tblCertificate, 'TC', 2, 7);
                        $this->setCertificateSubject($tblCertificate, 'IN', 2, 8);
                    }

                    $tblCertificate = $this->createCertificate('Mittelschule Jahreszeugnis', 'Klasse 5-6',
                        'CSW\CswMsJahreszeugnis', $tblConsumerCertificate);
                    if ($tblCertificate) {
                        if ($tblSchoolTypeSecondary) {
                            $this->updateCertificate($tblCertificate, $tblCertificateTypeYear,
                                $tblSchoolTypeSecondary, null, false);
                            if (!$this->getCertificateLevelAllByCertificate($tblCertificate)) {
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '5'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                                if (($tblLevel = Division::useService()->getLevelBy($tblSchoolTypeSecondary, '6'))) {
                                    $this->createCertificateLevel($tblCertificate, $tblLevel);
                                }
                            }
                        }
                        // Begrenzung des Einschätzungfelds
                        $FieldName = 'Rating';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                            $this->createCertificateField($tblCertificate, $FieldName, 300);
                        }
                        // Begrenzung des Bemerkungsfelds
                        $FieldName = 'Remark';
                        if (!$this->getCertificateFieldByCertificateAndField($tblCertificate, $FieldName)){
                            $this->createCertificateField($tblCertificate, $FieldName, 300);
                        }
                    }
                    if ($tblCertificate && !$this->getCertificateGradeAll($tblCertificate)) {
                        $this->setCertificateGradeAllStandard($tblCertificate);
                    }
                    if ($tblCertificate && !$this->getCertificateSubjectAll($tblCertificate)) {
                        $this->setCertificateSubject($tblCertificate, 'DE', 1, 1);
                        $this->setCertificateSubject($tblCertificate, 'EN', 1, 2);
                        $this->setCertificateSubject($tblCertificate, 'KU', 1, 3);
                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                        $this->setCertificateSubject($tblCertificate, 'MU', 1, 4);
                        $this->setCertificateSubject($tblCertificate, 'GE', 1, 5);
                        $this->setCertificateSubject($tblCertificate, 'GK', 1, 6);
                        $this->setCertificateSubject($tblCertificate, 'GEO', 1, 7);
                        $this->setCertificateSubject($tblCertificate, 'WTH', 1, 8);

                        $this->setCertificateSubject($tblCertificate, 'MA', 2, 1);
                        $this->setCertificateSubject($tblCertificate, 'BIO', 2, 2);
                        $this->setCertificateSubject($tblCertificate, 'CH', 2, 3);
                        $this->setCertificateSubject($tblCertificate, 'PH', 2, 4);
                        $this->setCertificateSubject($tblCertificate, 'SPO', 2, 5);
                        $this->setCertificateSubject($tblCertificate, 'RELI', 2, 6);
                        $this->setCertificateSubject($tblCertificate, 'TC', 2, 7);
                        $this->setCertificateSubject($tblCertificate, 'IN', 2, 8);
                    }
                }
            }
        }
    }

    /**
     * @param string $Name
     * @param string $Description
     * @param string $Certificate
     * @param TblConsumer|null $tblConsumer
     * @param bool $IsGradeInformation
     * @param bool $IsInformation
     * @param bool $IsChosenDefault
     * @param TblCertificateType|null $tblCertificateType
     * @param TblType|null $tblSchoolType
     * @param TblCourse|null $tblCourse
     *
     * @return TblCertificate
     */
    public function createCertificate(
        $Name,
        $Description,
        $Certificate,
        TblConsumer $tblConsumer = null,
        $IsGradeInformation = false,
        $IsInformation = false,
        $IsChosenDefault = false,
        TblCertificateType $tblCertificateType = null,
        TblType $tblSchoolType = null,
        TblCourse $tblCourse = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblCertificate')->findOneBy(array(
            TblCertificate::ATTR_CERTIFICATE => $Certificate
        ));

        if (null === $Entity) {
            $Entity = new TblCertificate();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setCertificate($Certificate);
            $Entity->setServiceTblConsumer($tblConsumer);
            $Entity->setIsGradeInformation($IsGradeInformation);
            $Entity->setIsInformation($IsInformation);
            $Entity->setIsChosenDefault($IsChosenDefault);
            $Entity->setTblCertificateType($tblCertificateType);
            $Entity->setServiceTblSchoolType($tblSchoolType);
            $Entity->setServiceTblCourse($tblCourse);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param int $LaneIndex
     * @param int $LaneRanking
     * @param TblGradeType $tblGradeType
     *
     * @return null|object|TblCertificateGrade
     */
    public function createCertificateGrade(
        TblCertificate $tblCertificate,
        $LaneIndex,
        $LaneRanking,
        TblGradeType $tblGradeType
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblCertificateGrade')->findOneBy(array(
            TblCertificateGrade::ATTR_TBL_CERTIFICATE => $tblCertificate->getId(),
            TblCertificateGrade::ATTR_LANE => $LaneIndex,
            TblCertificateGrade::ATTR_RANKING => $LaneRanking
        ));
        if (null === $Entity) {
            $Entity = new TblCertificateGrade();
            $Entity->setTblCertificate($tblCertificate);
            $Entity->setLane($LaneIndex);
            $Entity->setRanking($LaneRanking);
            $Entity->setServiceTblGradeType($tblGradeType);
            $Entity->setEssential(false);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblCertificateGrade $tblCertificateGrade
     * @param TblGradeType $tblGradeType
     *
     * @return bool
     */
    public function updateCertificateGrade(TblCertificateGrade $tblCertificateGrade, TblGradeType $tblGradeType)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblCertificateGrade $Entity */
        $Entity = $Manager->getEntityById('TblCertificateGrade', $tblCertificateGrade->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setServiceTblGradeType($tblGradeType);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param int $LaneIndex
     * @param int $LaneRanking
     * @param TblSubject $tblSubject
     * @param bool $IsEssential
     *
     * @return TblCertificateSubject
     */
    public function createCertificateSubject(
        TblCertificate $tblCertificate,
        $LaneIndex,
        $LaneRanking,
        TblSubject $tblSubject,
        $IsEssential = false
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblCertificateSubject')->findOneBy(array(
            TblCertificateSubject::ATTR_TBL_CERTIFICATE => $tblCertificate->getId(),
            TblCertificateSubject::ATTR_LANE => $LaneIndex,
            TblCertificateSubject::ATTR_RANKING => $LaneRanking
        ));
        if (null === $Entity) {
            $Entity = new TblCertificateSubject();
            $Entity->setTblCertificate($tblCertificate);
            $Entity->setLane($LaneIndex);
            $Entity->setRanking($LaneRanking);
            $Entity->setServiceTblSubject($tblSubject);
            $Entity->setEssential($IsEssential);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblCertificateSubject $tblCertificateSubject
     * @param TblSubject $tblSubject
     * @param bool $IsEssential
     *
     * @return bool
     */
    public function updateCertificateSubject(
        TblCertificateSubject $tblCertificateSubject,
        TblSubject $tblSubject,
        $IsEssential = false
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblCertificateSubject $Entity */
        $Entity = $Manager->getEntityById('TblCertificateSubject', $tblCertificateSubject->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setServiceTblSubject($tblSubject);
            $Entity->setEssential($IsEssential);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblCertificateSubject $tblCertificateSubject
     *
     * @return bool
     */
    public function removeCertificateSubject(TblCertificateSubject $tblCertificateSubject)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblCertificateSubject $Entity */
        $Entity = $Manager->getEntityById('TblCertificateSubject', $tblCertificateSubject->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param null|TblConsumer $tblConsumer
     *
     * @return bool|TblCertificate[]
     */
    public function getCertificateAllByConsumer(TblConsumer $tblConsumer = null)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblCertificate', array(
                TblCertificate::SERVICE_TBL_CONSUMER => ($tblConsumer ? $tblConsumer->getId() : null),
                TblCertificate::ATTR_IS_GRADE_INFORMATION => false
            )
        );
    }

    /**
     * @return bool|TblCertificate[]
     */
    public function getCertificateAll()
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCertificate',
            array(
                TblCertificate::ATTR_IS_GRADE_INFORMATION => false
            )
        );
    }

    /**
     * @return false|TblCertificate[]
     */
    public function getTemplateAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCertificate');
    }

    /**
     * @param null|TblConsumer $tblConsumer
     *
     * @return bool|TblCertificate[]
     */
    public function getTemplateAllByConsumer(TblConsumer $tblConsumer = null)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblCertificate', array(
                TblCertificate::SERVICE_TBL_CONSUMER => ($tblConsumer ? $tblConsumer->getId() : null)
            )
        );
    }

    /**
     * @return bool|TblCertificate[]
     */
    public function getGradeInformationTemplateAll()
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCertificate',
            array(
                TblCertificate::ATTR_IS_GRADE_INFORMATION => true
            )
        );
    }

    /**
     * @param null|TblConsumer $tblConsumer
     *
     * @return bool|TblCertificate[]
     */
    public function getGradeInformationTemplateAllByConsumer(TblConsumer $tblConsumer = null)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblCertificate', array(
                TblCertificate::SERVICE_TBL_CONSUMER => ($tblConsumer ? $tblConsumer->getId() : null),
                TblCertificate::ATTR_IS_GRADE_INFORMATION => true
            )
        );
    }


    /**
     * @param $Id
     *
     * @return bool|TblCertificate
     */
    public function getCertificateById($Id)
    {

        /** @var TblCertificate $Entity */
        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblCertificate', $Id);
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @param string $Class
     *
     * @return bool|TblCertificate
     */
    public function getCertificateByCertificateClassName($Class)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblCertificate', array(
                TblCertificate::ATTR_CERTIFICATE => $Class
            )
        );
    }

    /**
     * @param $Id
     *
     * @return bool|TblCertificateSubject
     */
    public function getCertificateSubjectById($Id)
    {

        /** @var TblCertificateSubject $Entity */
        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblCertificateSubject', $Id);
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param TblSubject $tblSubject
     *
     * @return false|TblCertificateSubject
     */
    public function getCertificateSubjectBySubject(TblCertificate $tblCertificate, TblSubject $tblSubject)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCertificateSubject',
            array(
                TblCertificateSubject::ATTR_TBL_CERTIFICATE => $tblCertificate->getId(),
                TblCertificateSubject::SERVICE_TBL_SUBJECT => $tblSubject->getId()
            )
        );
    }

    /**
     * @param TblCertificate $tblCertificate
     *
     * @return bool|TblCertificateSubject[]
     */
    public function getCertificateSubjectAll(TblCertificate $tblCertificate)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblCertificateSubject', array(
                TblCertificateSubject::ATTR_TBL_CERTIFICATE => $tblCertificate->getId()
            ));
    }

    /**
     * @param TblCertificate $tblCertificate
     *
     * @return bool|TblCertificateGrade[]
     */
    public function getCertificateGradeAll(TblCertificate $tblCertificate)
    {

        return $this->getForceEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblCertificateGrade', array(
                TblCertificateGrade::ATTR_TBL_CERTIFICATE => $tblCertificate->getId()
            ));
    }


    /**
     * @param TblCertificate $tblCertificate
     * @param int $LaneIndex
     * @param int $LaneRanking
     *
     * @return bool|TblCertificateSubject
     */
    public function getCertificateSubjectByIndex(TblCertificate $tblCertificate, $LaneIndex, $LaneRanking)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblCertificateSubject', array(
                TblCertificateSubject::ATTR_TBL_CERTIFICATE => $tblCertificate->getId(),
                TblCertificateSubject::ATTR_LANE => $LaneIndex,
                TblCertificateSubject::ATTR_RANKING => $LaneRanking
            ));
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param int $LaneIndex
     * @param int $LaneRanking
     *
     * @return bool|TblCertificateGrade
     */
    public function getCertificateGradeByIndex(TblCertificate $tblCertificate, $LaneIndex, $LaneRanking)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblCertificateGrade', array(
                TblCertificateGrade::ATTR_TBL_CERTIFICATE => $tblCertificate->getId(),
                TblCertificateGrade::ATTR_LANE => $LaneIndex,
                TblCertificateGrade::ATTR_RANKING => $LaneRanking
            ));
    }

    /**
     * @param $Id
     *
     * @return bool|TblCertificateGrade
     */
    public function getCertificateGradeById($Id)
    {

        /** @var TblCertificateGrade $Entity */
        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblCertificateGrade', $Id);
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param $SubjectAcronym
     * @param $LaneIndex
     * @param $LaneRanking
     * @param bool|true $IsEssential
     */
    private function setCertificateSubject(
        TblCertificate $tblCertificate,
        $SubjectAcronym,
        $LaneIndex,
        $LaneRanking,
        $IsEssential = true
    ) {

        // Chemnitz abweichende Fächer
        if ($SubjectAcronym == 'DE' || $SubjectAcronym == 'D') {
            $tblSubject = Subject::useService()->getSubjectByAcronym('DE');
            if (!$tblSubject) {
                $tblSubject = Subject::useService()->getSubjectByAcronym('D');
            }
        } elseif ($SubjectAcronym == 'BI' || $SubjectAcronym == 'BIO') {
            $tblSubject = Subject::useService()->getSubjectByAcronym('BI');
            if (!$tblSubject) {
                $tblSubject = Subject::useService()->getSubjectByAcronym('BIO');
            }
        } elseif ($SubjectAcronym == 'REV' || $SubjectAcronym == 'RELI' || $SubjectAcronym == 'REE') {
            $tblSubject = Subject::useService()->getSubjectByAcronym('REV');
            if (!$tblSubject) {
                $tblSubject = Subject::useService()->getSubjectByAcronym('RELI');
            }
            if (!$tblSubject) {
                $tblSubject = Subject::useService()->getSubjectByAcronym('REE');
            }
        } elseif ($SubjectAcronym == 'IN' || $SubjectAcronym == 'INFO' || $SubjectAcronym == 'INF') {
            $tblSubject = Subject::useService()->getSubjectByAcronym('INF');
            if (!$tblSubject) {
                $tblSubject = Subject::useService()->getSubjectByAcronym('IN');
            }
            if (!$tblSubject) {
                $tblSubject = Subject::useService()->getSubjectByAcronym('INFO');
            }
        } else {
            $tblSubject = Subject::useService()->getSubjectByAcronym($SubjectAcronym);
        }

        if ($tblSubject){
            $this->createCertificateSubject($tblCertificate, $LaneIndex, $LaneRanking, $tblSubject,
                $IsEssential);
        }
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param $GradeTypeAcronym
     * @param $LaneIndex
     * @param $LaneRanking
     */
    private function setCertificateGrade(
        TblCertificate $tblCertificate,
        $GradeTypeAcronym,
        $LaneIndex,
        $LaneRanking
    ) {

        if (($tblGradeType = Gradebook::useService()->getGradeTypeByCode($GradeTypeAcronym))) {
            $this->createCertificateGrade($tblCertificate, $LaneIndex, $LaneRanking, $tblGradeType);
        }
    }

    /**
     * @param TblCertificate $tblCertificate
     */
    private function setCertificateGradeAllStandard(
        TblCertificate $tblCertificate
    ) {
        $this->setCertificateGrade($tblCertificate, 'KBE', 1, 1);
        $this->setCertificateGrade($tblCertificate, 'KFL', 1, 2);

        $this->setCertificateGrade($tblCertificate, 'KMI', 2, 1);
        $this->setCertificateGrade($tblCertificate, 'KOR', 2, 2);
    }


    /**
     * @param $Identifier
     *
     * @return bool|TblCertificateType
     */
    public function getCertificateTypeByIdentifier($Identifier)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCertificateType',
            array(
                TblCertificateType::ATTR_IDENTIFIER => strtoupper($Identifier)
            )
        );
    }

    /**
     * @param $Id
     *
     * @return bool|TblCertificateType
     */
    public function getCertificateTypeById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCertificateType',
            $Id
        );
    }

    /**
     * @return false|TblCertificateType[]
     */
    public function getCertificateTypeAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCertificateType', array(
            TblCertificateType::ATTR_NAME => self::ORDER_ASC
        ));
    }

    /**
     * @param $Name
     * @param $Identifier
     * @param bool $IsAutomaticallyApproved
     *
     * @return null|TblCertificateType
     */
    public function createCertificateType($Name, $Identifier, $IsAutomaticallyApproved = false)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblCertificateType')
            ->findOneBy(array(TblCertificateType::ATTR_IDENTIFIER => $Identifier));

        if (null === $Entity) {
            $Entity = new TblCertificateType();
            $Entity->setName($Name);
            $Entity->setIdentifier(strtoupper($Identifier));
            $Entity->setAutomaticallyApproved($IsAutomaticallyApproved);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblCertificate          $tblCertificate
     * @param TblCertificateType|null $tblCertificateType
     * @param TblType|null            $tblSchoolType
     * @param TblCourse|null          $tblCourse
     * @param bool                    $IsInformation (Halbjahres Information)
     *
     * @return bool
     */
    public function updateCertificate(
        TblCertificate $tblCertificate,
        TblCertificateType $tblCertificateType = null,
        TblType $tblSchoolType = null,
        TblCourse $tblCourse = null,
        $IsInformation = false
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblCertificate $Entity */
        $Entity = $Manager->getEntityById('TblCertificate', $tblCertificate->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {

            $Entity->setTblCertificateType($tblCertificateType);
            $Entity->setServiceTblSchoolType($tblSchoolType);
            $Entity->setServiceTblCourse($tblCourse);
            $Entity->setIsInformation($IsInformation);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param TblLevel $tblLevel
     *
     * @return TblCertificateLevel
     */
    public function createCertificateLevel(TblCertificate $tblCertificate, TblLevel $tblLevel)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblCertificateLevel')
            ->findOneBy(array(
                    TblCertificateLevel::ATTR_TBL_CERTIFICATE => $tblCertificate->getId(),
                    TblCertificateLevel::SERVICE_TBL_LEVEL => $tblLevel->getId()
                )
            );

        if (null === $Entity) {
            $Entity = new TblCertificateLevel();
            $Entity->setTblCertificate($tblCertificate);
            $Entity->setServiceTblLevel($tblLevel);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblCertificate $tblCertificate
     *
     * @return bool
     */
    public function destroyCertificate(TblCertificate $tblCertificate)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblCertificate $Entity */
        $Entity = $Manager->getEntity('TblCertificate')->findOneBy(array('Id' => $tblCertificate->getId()));
        if (null !== $Entity) {
            // Foreign-Key Verknüpfungen löschen
            if (($tblCertificateGradeList = $this->getCertificateGradeAll($Entity))) {
                foreach ($tblCertificateGradeList as $tblCertificateGrade) {
                    $this->destroyCertificateGrade($tblCertificateGrade);
                }
            }
            if (($tblCertificateSubjectList = $this->getCertificateSubjectAll($Entity))) {
                foreach ($tblCertificateSubjectList as $tblCertificateSubject) {
                    $this->destroyCertificateSubject($tblCertificateSubject);
                }
            }
            if (($tblCertificateLevelList = $this->getCertificateLevelAllByCertificate($Entity))){
                foreach ($tblCertificateLevelList as $tblCertificateLevel) {
                    $this->destroyCertificateLevel($tblCertificateLevel);
                }
            }
            if (($tblCertificateFieldList = $this->getCertificateFieldAllByCertificate($Entity))){
                foreach ($tblCertificateFieldList as $tblCertificateField) {
                    $this->destroyCertificateField($tblCertificateField);
                }
            }

            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblCertificateGrade $tblCertificateGrade
     *
     * @return bool
     */
    public function destroyCertificateGrade(TblCertificateGrade $tblCertificateGrade)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblCertificateGrade')->findOneBy(array('Id' => $tblCertificateGrade->getId()));
        if (null !== $Entity) {
            /** @var \SPHERE\System\Database\Fitting\Element $Entity */
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblCertificateSubject $tblCertificateSubject
     *
     * @return bool
     */
    public function destroyCertificateSubject(TblCertificateSubject $tblCertificateSubject)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblCertificateSubject')->findOneBy(array('Id' => $tblCertificateSubject->getId()));
        if (null !== $Entity) {
            /** @var \SPHERE\System\Database\Fitting\Element $Entity */
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param null|TblConsumer $tblConsumer
     * @param TblCertificateType $tblCertificateType
     * @param TblType $tblSchoolType
     *
     * @return bool|Entity\TblCertificate[]
     */
    public function getCertificateAllBy(
        TblConsumer $tblConsumer = null,
        TblCertificateType $tblCertificateType = null,
        TblType $tblSchoolType = null
    ) {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblCertificate', array(
                TblCertificate::SERVICE_TBL_CONSUMER => ($tblConsumer ? $tblConsumer->getId() : null),
                TblCertificate::ATTR_TBL_CERTIFICATE_TYPE => ($tblCertificateType ? $tblCertificateType->getId() : null),
                TblCertificate::SERVICE_TBL_SCHOOL_TYPE => ($tblSchoolType ? $tblSchoolType->getId() : null),
            )
        );
    }

    /**
     * @param TblCertificate $tblCertificate
     *
     * @return false|TblCertificateLevel[]
     */
    public function getCertificateLevelAllByCertificate(TblCertificate $tblCertificate)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblCertificateLevel', array(
                TblCertificateLevel::ATTR_TBL_CERTIFICATE => $tblCertificate->getId()
            )
        );
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param string $FieldName
     * @param bool $HasTeamInRemark
     *
     * @return false|int
     */
    public function getCharCountByCertificateAndField(TblCertificate $tblCertificate, $FieldName, $HasTeamInRemark = true)
    {

        $tblCertificateField = $this->getCertificateFieldByCertificateAndField(
            $tblCertificate, $FieldName
        );

        if ($tblCertificateField) {
            // 3 Zeile (300 Zeichen) für Arbeitsgemeinschaften und Abstand abziehen
            if ($FieldName == 'Remark' && $HasTeamInRemark){
                $count = $tblCertificateField->getCharCount();
                return  $count > 300 ? $count - 300 : $count;
                // Abstand abziehen
            } elseif ($FieldName == 'Remark'){
                $count = $tblCertificateField->getCharCount();
                return  $count > 100 ? $count - 100 : $count;
            } else {
                return $tblCertificateField->getCharCount();
            }
        }

        return false;
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param $FieldName
     * @return false|TblCertificateField
     */
    public function getCertificateFieldByCertificateAndField(TblCertificate $tblCertificate, $FieldName)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblCertificateField', array(
                TblCertificateField::ATTR_TBL_CERTIFICATE => $tblCertificate->getId(),
                TblCertificateField::ATTR_FIELD_NAME => $FieldName
            )
        );
    }


    /**
     * @param TblCertificate $tblCertificate
     * @param string $FieldName
     * @param integer $CharCount
     *
     * @return TblCertificateField
     */
    public function createCertificateField(TblCertificate $tblCertificate, $FieldName, $CharCount)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblCertificateField')
            ->findOneBy(array(
                TblCertificateField::ATTR_TBL_CERTIFICATE => $tblCertificate->getId(),
                TblCertificateField::ATTR_FIELD_NAME => $FieldName
            ));

        if (null === $Entity) {
            $Entity = new TblCertificateField();
            $Entity->setTblCertificate($tblCertificate);
            $Entity->setFieldName($FieldName);
            $Entity->setCharCount($CharCount);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblCertificateLevel $tblCertificateLevel
     *
     * @return bool
     */
    public function destroyCertificateLevel(TblCertificateLevel $tblCertificateLevel)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblCertificateLevel')->findOneBy(array('Id' => $tblCertificateLevel->getId()));
        if (null !== $Entity) {
            /** @var \SPHERE\System\Database\Fitting\Element $Entity */
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblCertificateField $tblCertificateField
     *
     * @return bool
     */
    public function destroyCertificateField(TblCertificateField $tblCertificateField)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblCertificateField')->findOneBy(array('Id' => $tblCertificateField->getId()));
        if (null !== $Entity) {
            /** @var \SPHERE\System\Database\Fitting\Element $Entity */
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblCertificate $tblCertificate
     *
     * @return false|TblCertificateField[]
     */
    public function getCertificateFieldAllByCertificate(TblCertificate $tblCertificate)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCertificateField',
            array(TblCertificateField::ATTR_TBL_CERTIFICATE => $tblCertificate->getId())
        );
    }

    /**
     * @param TblCertificateType $tblCertificateType
     *
     * @return false|TblCertificate[]
     */
    public function getCertificateAllByType(
        TblCertificateType $tblCertificateType
    ) {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblCertificate', array(
                TblCertificate::ATTR_TBL_CERTIFICATE_TYPE => $tblCertificateType->getId()
            )
        );
    }

    /**
     * @param TblGradeType $tblGradeType
     *
     * @return bool
     */
    public function isGradeTypeUsed(TblGradeType $tblGradeType)
    {

        return $this->getCachedEntityBy(
            __METHOD__,
            $this->getConnection()->getEntityManager(),
            'TblCertificateGrade',
            array(
                TblCertificateGrade::SERVICE_TBL_GRADE_TYPE => $tblGradeType->getId()
            )
        ) ? true : false;
    }

    /**
     * @param TblCertificateType $tblCertificateType
     * @param $Identifier
     * @param $Name
     * @param $IsAutomaticallyApproved
     *
     * @return bool
     */
    public function updateCertificateType(
        TblCertificateType $tblCertificateType,
        $Identifier,
        $Name,
        $IsAutomaticallyApproved
    ) {

        $Manager = $this->getEntityManager();
        /** @var TblCertificateType $Entity */
        $Entity = $Manager->getEntityById('TblCertificateType', $tblCertificateType->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {

            $Entity->setIdentifier($Identifier);
            $Entity->setName($Name);
            $Entity->setAutomaticallyApproved($IsAutomaticallyApproved);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblCertificate $tblCertificate
     * @param TblLevel $tblLevel
     *
     * @return false|TblCertificateLevel
     */
    public function getCertificateLevelBy(TblCertificate $tblCertificate, TblLevel $tblLevel)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblCertificateLevel', array(
            TblCertificateLevel::ATTR_TBL_CERTIFICATE => $tblCertificate->getId(),
            TblCertificateLevel::SERVICE_TBL_LEVEL => $tblLevel->getId()
        ));
    }
}
