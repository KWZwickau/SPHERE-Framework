<?php

namespace SPHERE\System\Debugger\Logger;

use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Common\Frontend\Layout\Repository\Label;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\System\Debugger\DebuggerFactory;

/**
 * Class FileLogger
 * @package SPHERE\System\Debugger\Logger
 */
class FileLogger extends AbstractLogger implements LoggerInterface
{
    /**
     * @param string $Content
     *
     * @return LoggerInterface
     */
    public function addLog($Content)
    {
        $Content = strip_tags( $Content );

//        (new DebuggerFactory())->createLogger(new BenchmarkLogger())
//            ->addLog(new Label('File', Label::LABEL_TYPE_INFO) . ' ' . new Muted($Content));

        try {
            $pattern = array(
                'Start Timer',
                'Cache Session Update',
//                '192.168.202.40',
                'CacheFactory',
                'Fallback',
                'Clear Memory'
            );
            $pattern = array_map(function($v){return preg_quote($v,'!');},$pattern);
            if( preg_match( '!('.implode('|',$pattern).')!is', $Content ) ) {
                return $this;
            }

            $file = new FilePointer('log', 'SPHERE-LogFile', false, FilePointer::TYPE_DATE);
            if( !$file->getFileExists() ) {
                $file->saveFile();
            }
            $file->loadFile();
//            $Tail = $file->getFileContent();
//            $file->setFileContent($Tail . '#' . date('d.m.Y-H:i:s') . '#' . $_SERVER['REMOTE_ADDR'] . '#' . $Content . "\n\r");
//            $file->saveFile();
            file_put_contents( $file->getRealPath(), date('d.m.Y-H:i:s') . '#' . $_SERVER['REMOTE_ADDR'] . '#' . $Content . "\n\r", FILE_APPEND);

        } catch (\Exception $exception) {
//            (new DebuggerFactory())->createLogger(new ErrorLogger())
//                ->addLog(new Label('File Access Failed',
//                        Label::LABEL_TYPE_INFO) . ' ' . new Muted($exception->getMessage()));
        }

        return $this;
    }
}
