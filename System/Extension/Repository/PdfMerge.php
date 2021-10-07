<?php

namespace SPHERE\System\Extension\Repository;

use PDFMerger\PDFMerger;
use SPHERE\Application\Document\Storage\FilePointer;

/**
 * Class PdfMerge
 * @package SPHERE\System\Extension\Repository
 */
class PdfMerge
{
    const STREAM_DOWNLOAD = 'download';
    const STREAM_BROWSER = 'browser';
    const STREAM_FILE = 'file';
    /** @var null|PDFMerger $Instance */
    private $Instance = null;

    /**
     * PdfMerge constructor.
     */
    public function __construct()
    {
        require_once(
            __DIR__
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . 'Library'
            . DIRECTORY_SEPARATOR . 'PdfMerger'
            . DIRECTORY_SEPARATOR . 'vendor'
            . DIRECTORY_SEPARATOR . 'autoload.php'
        );
        require_once(
            __DIR__
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . 'Library'
            . DIRECTORY_SEPARATOR . 'PdfMerger'
            . DIRECTORY_SEPARATOR . 'PDFMerger.php'
        );

        $this->Instance = new PDFMerger();
    }

    /**
     * @param FilePointer $filePointer
     * @return $this
     */
    public function addPdf(FilePointer $filePointer)
    {
        $this->Instance->addPDF($filePointer->getRealPath());
        return $this;
    }

    /**
     * @param FilePointer $filePointer
     * @param string $Output
     * @return string|bool
     */
    public function mergePdf(FilePointer $filePointer, $Output = PdfMerge::STREAM_FILE)
    {
        $filePointer->saveFile();
        return $this->Instance->merge($Output, $filePointer->getRealPath());
    }
}
