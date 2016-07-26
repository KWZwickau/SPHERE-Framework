<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator;

use MOC\V\Component\Document\Component\Bridge\Repository\DomPdf;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Api\Response;
use SPHERE\Application\Document\Storage\DummyFile;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Window\Stage;

class Creator
{

    /** @var null|TblDivision $tblDivision */
    private $tblDivision = null;
    /** @var null|TblPerson $tblPerson */
    private $tblPerson = null;
    /** @var null|TblCertificate $tblCertificate */
    private $tblCertificate = null;

    /**
     * @param int   $Person
     * @param int   $Division
     * @param int   $Certificate
     * @param array $Data
     *
     * @return Response|string
     */
//    public function createPdf($Person = 0, $Division = 0, $Certificate = 0, $Data = array())
//    {
//
//        if (true !== ( $Response = $this->loadEntities($Person, $Division, $Certificate) )) {
//            return $Response;
//        }
//
//        $Certificate = $this->tblCertificate->getDocument($this->tblPerson, $this->tblDivision, false);
//        $File = $this->buildDummyFile($Certificate, $Data);
//
//        // ToDo: Change Frontend, then uncomment
//        // Storage::useService()->saveCertificateRevision($this->tblPerson, $this->tblDivision, $Certificate, $File);
//
//        return $this->buildDownloadFile($File);
//    }

    /**
     * @param null $PrepareId
     * @param null $PersonId
     *
     * @return Stage|string
     */
    public function createPdf($PrepareId = null, $PersonId = null)
    {

        if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
            && ($tblPerson = Person::useService()->getPersonById($PersonId))
        ){

            if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson))) {
                if (($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())) {
                    $CertificateClass = '\SPHERE\Application\Api\Education\Certificate\Generator\Repository\\' . $tblCertificate->getCertificate();
                    if (class_exists($CertificateClass)) {

                        /** @var \SPHERE\Application\Api\Education\Certificate\Generator\Certificate $Certificate */
                        $Certificate = new $CertificateClass();

                        // get Content
                        $Content = Prepare::useService()->getCertificateContent($tblPrepare, $tblPerson);

                        $File = $this->buildDummyFile($Certificate, $Content);

                        $FileName = "Zeugnis Muster " . $tblPerson->getLastFirstName() . ' ' .date("Y-m-d H:i:s").".pdf";

                        return $this->buildDownloadFile($File, $FileName);
                    }
                }
            }

        }

        return new Stage('Zeugnis', 'Nicht gefunden');

    }

    /**
     * @param int $Person
     * @param int $Division
     * @param int $Certificate
     *
     * @return Response|true
     */
    private function loadEntities($Person = 0, $Division = 0, $Certificate = 0)
    {

        if (!$this->tblDivision = Division::useService()->getDivisionById($Division)) {
            return (new Response())->addError('Division not found', 'Parameter: '.$Division, 0);
        }
        if (!$this->tblPerson = Person::useService()->getPersonById($Person)) {
            return (new Response())->addError('Person not found', 'Parameter: '.$Person, 0);
        }
        if (!$this->tblCertificate = Generator::useService()->getCertificateById($Certificate)) {
            return (new Response())->addError('Certificate not found', 'Parameter: '.$Certificate, 0);
        }
        return true;
    }

    /**
     * @param Certificate $Certificate
     * @param array       $Data
     *
     * @return DummyFile
     */
    private function buildDummyFile(Certificate $Certificate, $Data = array())
    {

//        $tblYear = $this->tblDivision->getServiceTblYear();
//        $Prefix = md5($tblYear->getYear().$this->tblPerson->getLastFirstName().$this->tblPerson->getId());

        $tblYear = isset($Data['Division']['Data']['Year']) ? $Data['Division']['Data']['Year'] : '';
        $personName = '';
        if (isset($Data['Person']['Data']['Name']['First']) && isset($Data['Person']['Data']['Name']['Last'])){
            $personName = $Data['Person']['Data']['Name']['Last'] . ', ' . $Data['Person']['Data']['Name']['First'];
        }
        $Prefix = md5($tblYear.$personName.(isset($Data['Person']['Student']['Id']) ? $Data['Person']['Student']['Id'] : ''));

        // Create Tmp
        $File = new DummyFile('pdf', $Prefix);
        /** @var DomPdf $Document */
        $Document = Document::getPdfDocument($File->getFileLocation());
        $Document->setContent($Certificate->createCertificate($Data));
        $Document->saveFile(new FileParameter($File->getFileLocation()));

        return $File;
    }

    /**
     * @param DummyFile $File
     * @param string $FileName
     *
     * @return string
     */
    private function buildDownloadFile(DummyFile $File, $FileName = '')
    {

        return FileSystem::getDownload(
            $File->getRealPath(),
            $FileName ? $FileName : "Zeugnis-Test-".date("Y-m-d H:i:s").".pdf"
        )->__toString();
    }

    /**
     * @param int   $Person
     * @param int   $Division
     * @param int   $Certificate
     * @param array $Data
     *
     * @return Response|string
     */
    public function previewPdf($Person = 0, $Division = 0, $Certificate = 0, $Data = array())
    {

        if (true !== ( $Response = $this->loadEntities($Person, $Division, $Certificate) )) {
            return $Response;
        }

        $Certificate = $this->tblCertificate->getDocument($this->tblPerson, $this->tblDivision, true);
        $File = $this->buildDummyFile($Certificate, $Data);

        return $this->buildDownloadFile($File);
    }
}
