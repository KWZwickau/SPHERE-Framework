<?php

namespace SPHERE\Application\Transfer\Indiware\Export\AppointmentGrade;

use SPHERE\Application\People\Person\Person;
use SPHERE\Application\Transfer\Gateway\Converter\AbstractConverter;
use SPHERE\Application\Transfer\Gateway\Converter\FieldPointer;

class AppointmentGradeGateway extends AbstractConverter
{
    private $ResultList = array();
    private $ImportList = array();
    private $StudentCourseList = array();

    /**
     * LectureshipGateway constructor.
     *
     * @param string                  $File SpUnterricht.csv
     * @param AppointmentGradeControl $Control
     */
    public function __construct($File, AppointmentGradeControl $Control, $isSekI)
    {
        $this->loadFile($File);

        $ColumnList = $Control->getScanResult();
//        Debugger::screenDump($Control->getScanResult());

        $this->addSanitizer(array($this, 'sanitizeFullTrim'));

        $maxCount = 17;
        if ($isSekI) {
            $identifier = 'EinfFach';
//            $maxCount = 26;
        } else {
            $identifier = 'Fach';

        }

        // Fächerangabe
        $SubjectList = array();
        for ($i = 1; $i <= $maxCount; $i++) {
            $SubjectList[$i] = $ColumnList[$identifier . $i];
        }

        foreach ($SubjectList as $Key => $FieldPosition) {
            $this->setPointer(new FieldPointer($FieldPosition, 'FileSubject'.$Key));
        }


        $this->setPointer(new FieldPointer($ColumnList['Vorname'], 'FirstName'));
        $this->setPointer(new FieldPointer($ColumnList['Name'], 'LastName'));
        $this->setPointer(new FieldPointer($ColumnList['Geburtsdatum'], 'Birthday'));

        $this->scanFile(1);
    }

    /**
     * @return array
     */
    public function getResultList()
    {
        return $this->ResultList;
    }

    /**
     * @return array
     */
    public function getImportList()
    {
        return $this->ImportList;
    }

    /**
     * @return array
     */
    public function getLectureship()
    {
        return $this->StudentCourseList;
    }

    /**
     * @param array $Row
     *
     * @return void
     */
    public function runConvert($Row)
    {
        $Result = array();
        foreach ($Row as $Part) {
            $Result = array_merge($Result, $Part);
        }

//        if (!$this->IsError) {
        $serviceTblPerson = null;
        if (isset($Result['FirstName']) && isset($Result['LastName']) && isset($Result['Birthday'])) {
            $serviceTblPerson = Person::useService()->getPersonByName($Result['FirstName'],
                $Result['LastName'],
                $Result['Birthday']);
        }

//        // Importe nur mit gültigen Personen
//        if ($serviceTblPerson) {
        // Import auch ohne gültige Person (zur Fehlerbeseitigung / Kontrolle)
        $ImportRow = array(
            'FirstName'        => $Result['FirstName'],
            'LastName'         => $Result['LastName'],
            'Birthday'         => $Result['Birthday'],
            'serviceTblPerson' => $serviceTblPerson,
            'FileSubject1'     => $Result['FileSubject1'],
            'FileSubject2'     => $Result['FileSubject2'],
            'FileSubject3'     => $Result['FileSubject3'],
            'FileSubject4'     => $Result['FileSubject4'],
            'FileSubject5'     => $Result['FileSubject5'],
            'FileSubject6'     => $Result['FileSubject6'],
            'FileSubject7'     => $Result['FileSubject7'],
            'FileSubject8'     => $Result['FileSubject8'],
            'FileSubject9'     => $Result['FileSubject9'],
            'FileSubject10'    => $Result['FileSubject10'],
            'FileSubject11'    => $Result['FileSubject11'],
            'FileSubject12'    => $Result['FileSubject12'],
            'FileSubject13'    => $Result['FileSubject13'],
            'FileSubject14'    => $Result['FileSubject14'],
            'FileSubject15'    => $Result['FileSubject15'],
            'FileSubject16'    => $Result['FileSubject16'],
            'FileSubject17'    => $Result['FileSubject17'],
        );
        $this->ImportList[] = $ImportRow;
//        }
        $this->ResultList[] = $Result;
    }
}