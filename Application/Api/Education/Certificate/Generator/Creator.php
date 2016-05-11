<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator;

use MOC\V\Component\Document\Component\Bridge\Repository\DomPdf;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Api\Response;
use SPHERE\Application\Document\Explorer\Storage\Storage;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\People\Person\Person;

class Creator
{

    public function createPdf($Person = 0, $Division = 0, $Certificate = 0, $Data = array())
    {

        if (!$tblDivision = Division::useService()->getDivisionById($Division)) {
            return (new Response())->addError('Division not found', 'Parameter: '.$Division, 0);
        }
        if (!$tblPerson = Person::useService()->getPersonById($Person)) {
            return (new Response())->addError('Person not found', 'Parameter: '.$Person, 0);
        }
        if (!$tblCertificate = Generator::useService()->getCertificateById($Certificate)) {
            return (new Response())->addError('Certificate not found', 'Parameter: '.$Certificate, 0);
        }
        // TODO: Toggle Sample
        $Template = $tblCertificate->getDocument($tblPerson, $tblDivision, true);

        \SPHERE\Application\Document\Storage\Storage::useService()->saveCertificateRevision($tblDivision->getServiceTblYear(),
            $tblPerson, $Template, $Data);
        throw new \Exception('Stop');

        $FileLocation = Storage::useWriter()->getTemporary('pdf', 'Zeugnis-Test-'.date('Ymd-His'));
        /** @var DomPdf $Document */
        $Document = Document::getPdfDocument($FileLocation->getFileLocation());
        $Document->setContent($Template->createCertificate($Data));
        $Document->saveFile(new FileParameter($FileLocation->getFileLocation()));

        return FileSystem::getDownload(
            $FileLocation->getRealPath(),
            "Zeugnis-Test-".date("Y-m-d H:i:s").".pdf"
        )->__toString();
    }
}
