<?php
namespace SPHERE\Application\Api\Education\Graduation\Certificate;

use SPHERE\Application\Api\Education\Graduation\Certificate\Repository\MsAbg;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\People\Person\Person;

class Creator
{

    public function createPdf()
    {

        $Content = new MsAbg(
            Person::useService()->getPersonById(1),
            Division::useService()->getDivisionById(29)
        );

        print '<div class="cleanslate">'.$Content->createCertificate()->getContent().'</div>';

//        $FileLocation = Storage::useWriter()->getTemporary('pdf', 'Zeugnistest-'.date('Ymd-His'));
//        /** @var DomPdf $Document */
//        $Document = Document::getPdfDocument($FileLocation->getFileLocation());
//        $Document->setContent($Content->createCertificate());
//
//        $Document->saveFile(new FileParameter($FileLocation->getFileLocation()));
//
//        return FileSystem::getDownload(
//            $FileLocation->getRealPath(),
//            "Zeugnistest ".date("Y-m-d H:i:s").".pdf"
//        )->__toString();
    }
}
