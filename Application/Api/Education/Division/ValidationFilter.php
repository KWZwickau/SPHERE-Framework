<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 26.06.2018
 * Time: 08:28
 */

namespace SPHERE\Application\Api\Education\Division;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Filter\Service;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Application\Setting\Consumer\Service\Entity\TblSetting;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Layout\Repository\Accordion;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\System\Extension\Extension;

class ValidationFilter extends Extension implements IApiInterface
{
    use ApiTrait;

    /**
     * @param string $Method Callable Method
     *
     * @return string
     */
    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);

        $Dispatcher->registerMethod('getContent');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param string $Content
     *
     * @return BlockReceiver
     */
    public static function receiverUsed($Content = '')
    {
        return (new BlockReceiver($Content))->setIdentifier('UsedReceiver');
    }

    /**
     *
     * @param bool $ShowAll
     *
     * @return Warning|Success|false
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public static function getContent($ShowAll = false)
    {
        $validationTable = array();

        if (!$ShowAll) {
            // Letzten Status aus der DB laden
            if (($tblSettingDate = Consumer::useService()->getSetting(
                    'Education', 'Lesson', 'Division', 'InterfaceFilterMessageDate'))
                && $tblSettingDate->getValue()
            ) {
                $date = $tblSettingDate->getValue();
                if (($tblSettingCount = Consumer::useService()->getSetting(
                        'Education', 'Lesson', 'Division', 'InterfaceFilterMessageCount'))
                ) {
                    $count = $tblSettingCount->getValue();
                } else {
                    $count = 0;
                }

                $message = 'Letzte Aktualisierung: ' . $date . ' Es wurden ' . new Bold($count) . ' Meldungen registriert.';

                $content = new Exclamation()
                    . new Bold(' Folgende Einstellungen stimmen nicht mit der Personenverwaltung überein:')
                    . '</br>'
                    . ($message ? $message : '')
                    . '</br></br>'
                    . (new Standard('Laden', ''))->ajaxPipelineOnClick(self::pipelineLoad());

                return $count > 0 ? new Warning($content) : new Success($content);
            }
        }

        $accordion = false;
        // Validierung mit dem Bildungsmodul
        $tblDivisionList = array();
        $tblYearList = Term::useService()->getYearByNow();
        if ($tblYearList) {
            foreach ($tblYearList as $tblYear) {
                $TempList = Division::useService()->getDivisionByYear($tblYear);
                if ($TempList) {
                    foreach ($TempList as $Temp) {
                        $tblDivisionList[] = $Temp;
                    }
                }
            }
        }
        if (!empty($tblDivisionList)) {
            $totalCount = 0;
            foreach ($tblDivisionList as $tblDivision) {
                if (($table = Service::getDivisionMessageTable($tblDivision, true, $totalCount))) {
//                    if (!$ShowAll) {
//                        $button = (new Standard('Laden', ''))->ajaxPipelineOnClick(self::pipelineLoad());
//
//                        return $button;
//                    }

                    $validationTable[$tblDivision->getDisplayName()] = $table;
                }
            }

            // save date and count in database
            $date = (new \DateTime('now'))->format('d.m.Y');
            if (($tblSettingDate = Consumer::useService()->getSetting(
                'Education', 'Lesson', 'Division', 'InterfaceFilterMessageDate'))
            ) {
                Consumer::useService()->updateSetting($tblSettingDate, $date);
            } else {
                Consumer::useService()->createSetting(
                    'Education',
                    'Lesson',
                    'Division',
                    'InterfaceFilterMessageDate',
                    TblSetting::TYPE_STRING,
                    $date
                );
            }
            if (($tblSettingCount = Consumer::useService()->getSetting(
                'Education', 'Lesson', 'Division', 'InterfaceFilterMessageCount'))
            ) {
                Consumer::useService()->updateSetting($tblSettingCount, $totalCount);
            } else {
                Consumer::useService()->createSetting(
                    'Education',
                    'Lesson',
                    'Division',
                    'InterfaceFilterMessageCount',
                    TblSetting::TYPE_INTEGER,
                    $totalCount
                );
            }
        }

        if (!empty($validationTable)) {
            $accordion = new Accordion();
            ksort($validationTable, SORT_NATURAL);
            foreach ($validationTable as $divisionId => $item) {
                if (isset($item['Header']) && isset($item['Content'])) {
                    $accordion->addItem($item['Header'], $item['Content']);
                }
            }

            $accordion = new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                new Warning(
                    new Exclamation()
                    . new Bold(' Folgende Einstellungen stimmen nicht mit der Personenverwaltung überein:')
                    . $accordion
                )
            ))));
        }

        return $accordion;
    }

    /**
     * @return Pipeline
     */
    public static function pipelineLoad()
    {

        $Pipeline = new Pipeline();

        // refresh Content
        $Emitter = new ServerEmitter(self::receiverUsed(), self::getEndpoint());
        $Emitter->setPostPayload(array(
            self::API_TARGET => 'getContent',
            'ShowAll' => true
        ));
        $Pipeline->setLoadingMessage('Bitte warten');
        $Pipeline->setSuccessMessage('Ist geladen');
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }
}