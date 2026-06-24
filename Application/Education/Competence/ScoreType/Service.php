<?php

namespace SPHERE\Application\Education\Competence\ScoreType;

use SPHERE\Application\Education\Competence\ScoreType\Service\Data;
use SPHERE\Application\Education\Competence\ScoreType\Service\Entity\TblScoreType;
use SPHERE\Application\Education\Competence\ScoreType\Service\Entity\TblScoreTypeConversion;
use SPHERE\Application\Education\Competence\ScoreType\Service\Entity\TblScoreTypeItem;
use SPHERE\Application\Education\Competence\ScoreType\Service\Setup;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

class Service extends AbstractService
{
    /**
     * @param $doSimulation
     * @param $withData
     * @param $UTF8
     *
     * @return string
     */
    public function setupService($doSimulation, $withData, $UTF8): string
    {
        $Protocol = '';
        if (!$withData) {
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
        }
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param $id
     *
     * @return TblScoreType|false
     */
    public function getScoreTypeById($id): TblScoreType|false
    {
        if ($id == -1) {
            return $this->getVirtuellScoreTypePercent();
        }

        return (new Data($this->getBinding()))->getScoreTypeById($id);
    }

    /**
     * @return TblScoreType
     */
    public function getVirtuellScoreTypePercent(): TblScoreType
    {
        $tblScoreType = new TblScoreType();
        $tblScoreType->setId(-1);
        $tblScoreType->setName('Prozent');
        $tblScoreType->setDescription('0% - 100%');

        return $tblScoreType;
    }

    /**
     * @return TblScoreType[]
     */
    public function getScoreTypeAll(bool $withVirtuellPercent = true): array
    {
        $list = (new Data($this->getBinding()))->getScoreTypeAll();
        if ($withVirtuellPercent) {
            array_unshift($list, $this->getVirtuellScoreTypePercent());
        }

        return $list;
    }

    /**
     * @param $Data
     * @param TblScoreType|null $tblScoreType
     *
     * @return IFormInterface|string
     */
    public function updateScoreType($Data, ?TblScoreType $tblScoreType): IFormInterface|string
    {
        $isPercent = $tblScoreType?->getId() == -1;
        $hasErrors = false;
        $ErrorList = [];
        if (!$isPercent && empty($Data['Name'])) {
            $ErrorList[] = [
                'Name' => 'Data[Name]',
                'Message' => 'Bitte geben Sie einen Namen an'
            ];
            $hasErrors = true;
        }
        if (isset($Data['ScoreTypeItems'])) {
            foreach ($Data['ScoreTypeItems'] as $ranking => $itemArray) {
                // empty geht nicht da sonst Wert 0 nicht zulässig
                if ((isset($itemArray['Value']) && $itemArray['Value'] !== '') || !empty($itemArray['Name']) || !empty($itemArray['Description'])) {
                    // Wert prüfen
                    if (!$isPercent) {
                        if ($itemArray['Value'] === '') {
                            $name = "Data[ScoreTypeItems][$ranking][Value]";
                            $ErrorList[$name] = [
                                'Name' => $name,
                                'Message' => 'Bitte geben Sie einen Wert an'
                            ];
                            $hasErrors = true;
                            // Prüfung ob Zahl
                        } else {
                            // Tausenderpunkte entfernen, Komma → Punkt
                            $normalized = str_replace(['.', ','], ['', '.'], $itemArray['Value']);
                            if (!is_numeric($normalized)) {
                                $name = "Data[ScoreTypeItems][$ranking][Value]";
                                $ErrorList[$name] = [
                                    'Name' => $name,
                                    'Message' => 'Bitte geben Sie eine Zahl an'
                                ];
                                $hasErrors = true;
                            }
                        }
                    }

                    // Kurztext prüfen
                    if (empty($itemArray['Name'])) {
                        $name = "Data[ScoreTypeItems][$ranking][Name]";
                        $ErrorList[$name] = [
                            'Name' => $name,
                            'Message' => 'Bitte geben Sie einen Kurztext an'
                        ];
                        $hasErrors = true;
                    }
                }
            }
        }

        if ($hasErrors) {
            return new Well(ScoreType::useFrontend()->formScoreType(false, $tblScoreType?->getId(), $Data, $ErrorList));
        }

        $tblScoreTypeItemListExists = [];
        if ($tblScoreType) {
            if (!$isPercent) {
                (new Data($this->getBinding()))->updateScoreType($tblScoreType, $Data['Name'], $Data['Description']);
            }

//            Debugger::devDump($Data);
//            return '';
//            $this->destroyScoreTypeItemsByScoreType($tblScoreType);

            $tblScoreTypeItemListExists = $tblScoreType->getScoreTypeItems();

            $tblScoreTypeNew = $tblScoreType;
        } else {
            $tblScoreTypeNew = (new Data($this->getBinding()))->createScoreType($Data['Name'], $Data['Description']);
        }

        $scoreTypeItemIdList = [];
        if (isset($Data['ScoreTypeItems'])) {
            foreach ($Data['ScoreTypeItems'] as $array) {
                // empty geht nicht da sonst Wert 0 nicht zulässig
                if ($array['Name'] !== '' || (isset($array['Value']) && $array['Value'] !== '')) {
                    if (isset($array['ScoreTypeItemId'])
                        && ($tblScoreTypeItem = $this->getScoreTypeItemById($array['ScoreTypeItemId']))
                    ) {
                        (new Data($this->getBinding()))->updateScoreTypeItem(
                            $tblScoreTypeItem, $array['Value'] ?? '', $array['Name'], $array['Description'] ?: null);

                        $scoreTypeItemIdList[$tblScoreTypeItem->getId()] = 1;
                    } else {
                        (new Data($this->getBinding()))->createScoreTypeItem(
                            $isPercent ? null : $tblScoreTypeNew, $array['Value'] ?? '', $array['Name'], $array['Description'] ?: null);
                    }
                }
            }
        }
        // löschen items
        $destroyScoreTypeItemList = [];
        foreach ($tblScoreTypeItemListExists as $tblScoreTypeItemTemp) {
            if (!isset($scoreTypeItemIdList[$tblScoreTypeItemTemp->getId()])) {
                $destroyScoreTypeItemList[] = $tblScoreTypeItemTemp;
            }
        }
        if ($destroyScoreTypeItemList) {
            (new Data($this->getBinding()))->destroyScoreTypeItemBulkList($destroyScoreTypeItemList);
        }

        return new Success('Die Daten wurden erfolgreich gespeichert', new \SPHERE\Common\Frontend\Icon\Repository\Success())
            . new Redirect('/Education/Competence/ScoreType', Redirect::TIMEOUT_SUCCESS);
    }

    /**
     * @param $id
     *
     * @return false|TblScoreTypeItem
     */
    public function getScoreTypeItemById($id): false|TblScoreTypeItem
    {
        return (new Data($this->getBinding()))->getScoreTypeItemById($id);
    }

    /**
     * @param TblScoreType|null $tblScoreType
     *
     * @return TblScoreTypeItem[]
     */
    public function getScoreTypeItemsByScoreType(?TblScoreType $tblScoreType): array
    {
        return (new Data($this->getBinding()))->getScoreTypeItemListByScoreType($tblScoreType);
    }

    /**
     * @param TblScoreType $tblScoreType
     *
     * @return bool
     */
    public function destroyScoreType(TblScoreType $tblScoreType): bool
    {
        $this->destroyScoreTypeItemsByScoreType($tblScoreType);
        (new Data($this->getBinding()))->destroyScoreTypeConversionBulkList($tblScoreType->getScoreTypeConversions());

        return (new Data($this->getBinding()))->destroyScoreType($tblScoreType);
    }
    
    /**
     * @param TblScoreType $tblScoreType
     *
     * @return bool
     */
    public function destroyScoreTypeItemsByScoreType(TblScoreType $tblScoreType): bool
    {
        return (new Data($this->getBinding()))->destroyScoreTypeItemBulkList($tblScoreType->getScoreTypeItems());
    }

    /**
     * @param TblScoreType|null $tblScoreType
     *
     * @return TblScoreTypeConversion[]
     */
    public function getScoreTypeConversionListByScoreType(?TblScoreType $tblScoreType): array
    {
        return (new Data($this->getBinding()))->getScoreTypeConversionListByScoreType($tblScoreType);
    }

    /**
     * @param $Data
     * @param TblScoreType $tblScoreType
     *
     * @return Form|false
     */
    public function checkFormConversionScoreType($Data, TblScoreType $tblScoreType): Form|false
    {
        $error = false;
        $form = ScoreType::useFrontend()->formScoreTypeConversion(false, $tblScoreType);

        for ($i = 1; $i < 6; $i++) {
            $normalized = str_replace(['.', ','], ['', '.'], $Data[$i]);
            // Prüfung ob alle gefüllt und is_numeric
            if ($Data[$i] === '' || !is_numeric($normalized)) {
                $form->setError("Data[$i]", 'Bitte geben Sie eine Zahl an');
                $error = true;
            }
        }

        return $error ? $form : false;
    }

    /**
     * @param TblScoreType $tblScoreType
     * @param $Data
     *
     * @return bool
     */
    public function updateScoreTypeConversions(TblScoreType $tblScoreType, $Data): bool
    {
        // Spezialfall Prozente
        if ($tblScoreType->getId() < 1) {
            $tblScoreType = null;
        }
        foreach ($Data as $grade => $value) {
            (new Data($this->getBinding()))->updateScoreTypeConversion($tblScoreType, $grade, $value);
        }

        return true;
    }
}