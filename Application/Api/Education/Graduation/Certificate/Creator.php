<?php
namespace SPHERE\Application\Api\Education\Graduation\Certificate;

use MOC\V\Component\Document\Component\Bridge\Repository\DomPdf;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Document\Explorer\Storage\Storage;
use SPHERE\Application\Education\Graduation\Certificate\Repository\Frame;

class Creator
{

    public function createPdf($Data = array())
    {

        $Content = new Frame();

        $Content->setData($Data);

        $FileLocation = Storage::useWriter()->getTemporary('pdf', 'Zeugnistest-'.date('Ymd-His'), false);
        /** @var DomPdf $Document */
        $Document = Document::getPdfDocument($FileLocation->getFileLocation());

        $Document->setContent($Content->getTemplate());

        $Document->saveFile(new FileParameter($FileLocation->getFileLocation()));

        return FileSystem::getDownload(
            $FileLocation->getRealPath(),
            "Zeugnistest ".date("Y-m-d H:i:s").".pdf"
        )->__toString();
    }
}
