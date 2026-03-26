<?php

namespace SPHERE\Application\Education\Competence\ScoreType;

use SPHERE\Application\Education\Competence\ScoreType\Service\Data;
use SPHERE\Application\Education\Competence\ScoreType\Service\Entity\TblScoreType;
use SPHERE\Application\Education\Competence\ScoreType\Service\Entity\TblScoreTypeItem;
use SPHERE\Application\Education\Competence\ScoreType\Service\Setup;
use SPHERE\Common\Frontend\Form\IFormInterface;
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
        return (new Data($this->getBinding()))->getScoreTypeById($id);
    }

    /**
     * @return TblScoreType[]
     */
    public function getScoreTypeAll(): array
    {
        return (new Data($this->getBinding()))->getScoreTypeAll();
    }

    /**
     * @param $Data
     * @param TblScoreType|null $tblScoreType
     *
     * @return IFormInterface|string
     */
    public function updateScoreType($Data, ?TblScoreType $tblScoreType): IFormInterface|string
    {
        $hasErrors = false;
        $ErrorList = [];
        if (empty($Data['Name'])) {
            $ErrorList[] = [
                'Name' => 'Data[Name]',
                'Message' => 'Bitte geben Sie einen Namen an'
            ];
            $hasErrors = true;
        }
        if (isset($Data['ScoreTypeItems'])) {
            foreach ($Data['ScoreTypeItems'] as $ranking => $itemArray) {
                if (!empty($itemArray['Value']) || !empty($itemArray['Name']) || !empty($itemArray['Description'])) {
                    if (empty($itemArray['Value'])) {
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

        if ($tblScoreType) {
            (new Data($this->getBinding()))->updateScoreType($tblScoreType, $Data['Name'], $Data['Description']);

            // erstmal alle löschen
            $this->destroyScoreTypeItemsByScoreType($tblScoreType);

            $tblScoreTypeNew = $tblScoreType;
        } else {
            $tblScoreTypeNew = (new Data($this->getBinding()))->createScoreType($Data['Name'], $Data['Description']);
        }

        if (isset($Data['ScoreTypeItems'])) {
            foreach ($Data['ScoreTypeItems'] as $array) {
                if (!empty($array['Value'])) {
                    (new Data($this->getBinding()))->createScoreTypeItem($tblScoreTypeNew, $array['Value'], $array['Name'], $array['Description'] ?: null);
                }
            }
        }

        return new Success('Die Daten wurden erfolgreich gespeichert', new \SPHERE\Common\Frontend\Icon\Repository\Success())
            . new Redirect('/Education/Competence/ScoreType', Redirect::TIMEOUT_SUCCESS);
    }

    /**
     * @param TblScoreType $tblScoreType
     *
     * @return TblScoreTypeItem[]
     */
    public function getScoreTypeItemsByScoreType(TblScoreType $tblScoreType): array
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
}