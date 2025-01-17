<?php

namespace SPHERE\Application\Api\Reporting\Individual;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\AppTrait;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer as GatekeeperConsumer;
use SPHERE\Application\Reporting\Individual\Individual;
use SPHERE\Application\Reporting\Individual\Service\Entity\TblPreset;
use SPHERE\Application\Reporting\Individual\Service\Entity\TblWorkSpace;
use SPHERE\Application\Reporting\Individual\Service\Entity\ViewEducationStudent;
use SPHERE\Application\Reporting\Individual\Service\Entity\ViewGroup;
use SPHERE\Application\Reporting\Individual\Service\Entity\ViewGroupClub;
use SPHERE\Application\Reporting\Individual\Service\Entity\ViewGroupCustody;
use SPHERE\Application\Reporting\Individual\Service\Entity\ViewGroupProspect;
use SPHERE\Application\Reporting\Individual\Service\Entity\ViewGroupProspectTransfer;
use SPHERE\Application\Reporting\Individual\Service\Entity\ViewGroupStudentBasic;
use SPHERE\Application\Reporting\Individual\Service\Entity\ViewGroupStudentSpecialNeeds;
use SPHERE\Application\Reporting\Individual\Service\Entity\ViewGroupStudentSubject;
use SPHERE\Application\Reporting\Individual\Service\Entity\ViewGroupStudentTechnicalSchool;
use SPHERE\Application\Reporting\Individual\Service\Entity\ViewGroupStudentTransfer;
use SPHERE\Application\Reporting\Individual\Service\Entity\ViewGroupTeacher;
use SPHERE\Application\Reporting\Individual\Service\Entity\ViewPerson;
use SPHERE\Application\Reporting\Individual\Service\Entity\ViewPersonContact;
use SPHERE\Application\Reporting\Individual\Service\Entity\ViewProspectCustody;
use SPHERE\Application\Reporting\Individual\Service\Entity\ViewStudent;
use SPHERE\Application\Reporting\Individual\Service\Entity\ViewStudentAuthorized;
use SPHERE\Application\Reporting\Individual\Service\Entity\ViewStudentCustody;
//use SPHERE\Application\Reporting\Individual\Service\Entity\ViewStudentCustodyS1;
//use SPHERE\Application\Reporting\Individual\Service\Entity\ViewStudentCustodyS2;
//use SPHERE\Application\Reporting\Individual\Service\Entity\ViewStudentCustodyS3;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Common\Frontend\Ajax\Emitter\ClientEmitter;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary as Submit;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\HiddenField;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\ChevronRight;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\Filter;
use SPHERE\Common\Frontend\Icon\Repository\FolderClosed;
use SPHERE\Common\Frontend\Icon\Repository\FolderOpen;
use SPHERE\Common\Frontend\Icon\Repository\Minus;
use SPHERE\Common\Frontend\Icon\Repository\Person as PersonIcon;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Search;
use SPHERE\Common\Frontend\Layout\Repository\Dropdown;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\ProgressBar;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullLeft;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Scrollable;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Link\Structure\LinkGroup;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Code;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success as SuccessText;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Error;
use SPHERE\Common\Window\Navigation\Link\Route;
use SPHERE\System\Database\Binding\AbstractView;
use SPHERE\System\Debugger\Logger\QueryLogger;

/**
 * Class ApiIndividual
 * @package SPHERE\Application\Api\Reporting\Individual
 */
class ApiIndividual extends IndividualReceiver implements IApiInterface, IModuleInterface
{

    // Schränkt die Auswahl der Filterung auf "like" und "not like" ein
    private $reducedSelectBoxList = array(
//        'TblSalutation_Salutation',
        'TblCommonInformation_IsAssistance',
        'TblStudent_HasMigrationBackground',
        'TblStudent_IsInPreparationDivisionForMigrants',
        'TblLevel_Id',
        'TblGroup_Id',
    );

    // ummappen der Spaltennamen "_Id" auf "_Name"
    private $IdSearchList = array(
        'TblLevel_Id',
        'TblGroup_Id',
        'TblType_Id',
        'TblTechnicalType_Id',
        'TblTechnicalDiploma_Id',
    );

    // sortierung der Spalten nach Datum
    private $FieldNameSortByDate = array(
        'Grunddaten:_Schulpflicht_beginn',
        'Person:_Geburtsdatum',
        'Einschulung:_Datum',
        'Aufnahme:_Datum',
        'Abgabe:_Datum',
        'Allgemeines:_Taufedatum',
        'Inklusion:_Datum_F_oE_rderantrag_Beratung',
        'Inklusion:_Datum_F_oE_rderantrag',
        'Inklusion:_Datum_F_oE_rderbescheid_SBA',
        'Verein:_Eintrittsdatum',
        'Verein:_Austrittsdatum',
        'S1:_Geburtsdatum',
        'S2:_Geburtsdatum',
        'S3:_Geburtsdatum',
        'Bev:_Geburtsdatum',
        'Termin:_Eingangsdatum',
        'Termin:_Aufnahmegespr_aE_che',
        'Termin:_Schnuppertag',
        'Masern:_Datum'
    );

    // sortieren der Spalten nach GermanString
    private $FieldNameSortByGermanString = array(
        'Person:_Vorname',
        'Person:_Zweiter_Vorname',
        'Person:_Nachname',
        'Person:_Geburtsname',
        'S1:_Vorname',
        'S1:_Zweiter_Vorname',
        'S1:_Nachname',
        'S1:_Geburtsname',
        'S2:_Vorname',
        'S2:_Zweiter_Vorname',
        'S2:_Nachname',
        'S2:_Geburtsname',
        'S3:_Vorname',
        'S3:_Zweiter_Vorname',
        'S3:_Nachname',
        'S3:_Geburtsname',
    );

    // sortierung der Spalten nach einer natürlichen Zahl
    private $FieldNameSortByNumeric = array(
        'Bildung:_Klasse',
        'Bildung:_Klassenstufe'
    );

    use ApiTrait, AppTrait;

    public static function registerModule()
    {
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Download', __CLASS__.'::downloadFile'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/CSV/Download', __CLASS__.'::downloadCsvFile'
        ));
    }

    public static function useService()
    {
        // TODO: Implement useService() method.
    }

    public static function useFrontend()
    {
        // TODO: Implement useFrontend() method.
    }

    /**
     * @param string $Method
     *
     * @return string
     */
    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);
        $Dispatcher->registerMethod('changePublic');
        $Dispatcher->registerMethod('getNavigation');
        $Dispatcher->registerMethod('removeField');
        $Dispatcher->registerMethod('moveFieldLeft');
        $Dispatcher->registerMethod('moveFieldRight');
        $Dispatcher->registerMethod('changeFilterCount');
        $Dispatcher->registerMethod('removeFieldAll');
        $Dispatcher->registerMethod('getModalPreset');
        $Dispatcher->registerMethod('loadPreset');
        $Dispatcher->registerMethod('deletePreset');
        $Dispatcher->registerMethod('getModalSavePreset');
        $Dispatcher->registerMethod('createPreset');
        $Dispatcher->registerMethod('addField');
        $Dispatcher->registerMethod('getFilter');
        $Dispatcher->registerMethod('getSearchResult');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param int     $PresetId
     * @param boolean $IsPublic
     * @param string  $ViewType
     *
     * @return Pipeline
     */
    public static function pipelineChangePublic($PresetId, $IsPublic, $ViewType = TblWorkSpace::VIEW_TYPE_ALL)
    {

        $Pipeline = new Pipeline(false);
        $Emitter = new ServerEmitter(self::receiverService('', 'ChangePublic'), self::getEndpoint());
        $Emitter->setLoadingMessage('Veröffentlichung wird geändert');
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'changePublic'
        ));
        $Emitter->setPostPayload(array(
            'PresetId' => $PresetId,
            'IsPublic' => $IsPublic,
            'ViewType' => $ViewType,
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param int     $PresetId
     * @param boolean $IsPublic
     * @param string  $ViewType
     *
     * @return string
     */
    public function changePublic($PresetId, $IsPublic, $ViewType = TblWorkSpace::VIEW_TYPE_ALL)
    {

        if(($tblPreset = Individual::useService()->getPresetById($PresetId))){
            Individual::useService()->changePresetIsPublic($tblPreset, $IsPublic);
            return self::pipelinePresetShowModal('', $ViewType);
        }
        return new Danger('Error: Änderung der Veröffentlichung fehltgeschlagen');
    }

    /**
     * @param string $ViewType
     *
     * @return Pipeline
     */
    public static function pipelineDelete($ViewType = TblWorkSpace::VIEW_TYPE_ALL)
    {

        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverService(), self::getEndpoint());
        $Emitter->setLoadingMessage('Felder werden entfernt...');
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'removeFieldAll'
        ));
        $Emitter->setPostPayload(array(
            'ViewType' => $ViewType
        ));
        $Pipeline->appendEmitter($Emitter);
        $Emitter = new ServerEmitter(self::receiverNavigation(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getNavigation'
        ));
        $Emitter->setPostPayload(array(
            'ViewType' => $ViewType
        ));
        $Pipeline->appendEmitter($Emitter);
        // Refresh Filter
        $Emitter = new ServerEmitter(self::receiverFilter(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getFilter'
        ));
        $Emitter->setPostPayload(array(
            'ViewType' => $ViewType
        ));
        $Pipeline->appendEmitter($Emitter);

        $Emitter = new ClientEmitter( self::receiverResult(), new Muted( 'Es wurde bisher keine Suche durchgeführt' ) );
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param int|null $WorkSpaceId
     * @param string   $ViewType
     *
     * @return Pipeline
     */
    public static function pipelineDeleteFilterField($WorkSpaceId = null, $ViewType = TblWorkSpace::VIEW_TYPE_STUDENT)
    {

        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverService(), self::getEndpoint());
        $Emitter->setLoadingMessage('Feld wird entfernt...');
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'removeField'
        ));
        $Emitter->setPostPayload(array(
            'WorkSpaceId' => $WorkSpaceId,
            'ViewType' => $ViewType
        ));
        $Pipeline->appendEmitter($Emitter);
        $Emitter = new ServerEmitter(self::receiverNavigation(), self::getEndpoint());
        $Emitter->setLoadingMessage('Verfügbare Informationen werden aktualisiert...');
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getNavigation'
        ));
        $Emitter->setPostPayload(array(
            'ViewType' => $ViewType
        ));
        $Pipeline->appendEmitter($Emitter);
        // Refresh Filter
        $Emitter = new ServerEmitter(self::receiverFilter(), self::getEndpoint());
        $Emitter->setLoadingMessage('Filter wird aktualisiert...');
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getFilter'
        ));
        $Emitter->setPostPayload(array(
            'ViewType' => $ViewType
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param int|null $WorkSpaceId
     * @param string   $direction [left,right]
     * @param string   $ViewType
     *
     * @return Pipeline
     */
    public static function pipelineMoveFilterField($WorkSpaceId = null, $direction = '', $ViewType = TblWorkSpace::VIEW_TYPE_ALL)
    {

        $Pipeline = new Pipeline();
        if ($direction == 'left') {
            $Emitter = new ServerEmitter(self::receiverService(), self::getEndpoint());
            $Emitter->setLoadingMessage('Feld wird nach links verschoben...');
            $Emitter->setGetPayload(array(
                self::API_TARGET => 'moveFieldLeft'
            ));
            $Emitter->setPostPayload(array(
                'WorkSpaceId' => $WorkSpaceId,
                'ViewType' => $ViewType
            ));
            $Pipeline->appendEmitter($Emitter);
        } elseif ($direction == 'right') {
            $Emitter = new ServerEmitter(self::receiverService(), self::getEndpoint());
            $Emitter->setLoadingMessage('Feld wird nach rechts verschoben...');
            $Emitter->setGetPayload(array(
                self::API_TARGET => 'moveFieldRight'
            ));
            $Emitter->setPostPayload(array(
                'WorkSpaceId' => $WorkSpaceId,
                'ViewType' => $ViewType
            ));
            $Pipeline->appendEmitter($Emitter);
        }

        // Refresh Filter
        $Emitter = new ServerEmitter(self::receiverFilter(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getFilter'
        ));
        $Emitter->setPostPayload(array(
            'ViewType' => $ViewType
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param int|null $WorkSpaceId
     * @param string   $direction [plus,minus]
     * @param string   $ViewType
     *
     * @return Pipeline
     */
    public static function pipelineChangeFilterCount($WorkSpaceId = null, $direction = '', $ViewType = TblWorkSpace::VIEW_TYPE_ALL)
    {

        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverService(), self::getEndpoint());
        $Emitter->setLoadingMessage('Option wird '.($direction=='plus'?'hinzugefügt...':'entfernt...'));
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'changeFilterCount'
        ));
        $Emitter->setPostPayload(array(
            'WorkSpaceId' => $WorkSpaceId,
            'direction'   => $direction,
        ));
        $Pipeline->appendEmitter($Emitter);

        // Refresh Filter
        $Emitter = new ServerEmitter(self::receiverFilter(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getFilter'
        ));
        $Emitter->setPostPayload(array(
            'ViewType' => $ViewType
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $Info
     * @param string $ViewType
     *
     * @return Pipeline
     */
    public static function pipelinePresetShowModal($Info = '', $ViewType = TblWorkSpace::VIEW_TYPE_ALL)
    {

        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getModalPreset'
        ));
        $Emitter->setPostPayload(array(
            'Info' => $Info,
            'ViewType' => $ViewType
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param int|null $PresetId
     * @param string   $ViewType
     *
     * @return Pipeline
     */
    public static function pipelineLoadPreset($PresetId = null, $ViewType = TblWorkSpace::VIEW_TYPE_ALL)
    {

        $Pipeline = new Pipeline(false);
        $Emitter = new ServerEmitter(self::receiverService(), self::getEndpoint());
        $Emitter->setLoadingMessage('Vorlage wird geladen...');
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'loadPreset'
        ));
        $Emitter->setPostPayload(array(
            'PresetId' => $PresetId,
            'ViewType' => $ViewType
        ));
        $Pipeline->appendEmitter($Emitter);

        $Emitter =new ClientEmitter( self::receiverResult(), new Muted( 'Es wurde bisher keine Suche durchgeführt' ) );
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param int|null $PresetId
     * @param string   $ViewType
     *
     * @return Pipeline
     */
    public static function pipelineDeletePreset($PresetId = null, $ViewType = TblWorkSpace::VIEW_TYPE_ALL)
    {

        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverService(), self::getEndpoint());
        $Emitter->setLoadingMessage('Vorlage wird gelöscht...');
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'deletePreset'
        ));
        $Emitter->setPostPayload(array(
            'PresetId' => $PresetId,
            'ViewType' => $ViewType
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $Info
     * @param string $ViewType
     *
     * @return Pipeline
     */
    public static function pipelinePresetSaveModal($Info = '', $ViewType = TblWorkSpace::VIEW_TYPE_ALL)
    {

        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverModal('Als Vorlage Speichern'), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getModalSavePreset'
        ));
        $Emitter->setPostPayload(array(
            'Info' => $Info,
            'ViewType' => $ViewType
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $ViewType
     * @param array  $newPost
     *
     * @return Pipeline
     */
    public static function pipelinePresetSave($ViewType = TblWorkSpace::VIEW_TYPE_ALL, $newPost = array())
    {

        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverService(), self::getEndpoint());
        $Emitter->setLoadingMessage('Vorlage wird gespeichert...');
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'createPreset'
        ));
        $newPost = array_merge($newPost, array('ViewType' => $ViewType));
        $Emitter->setPostPayload($newPost);
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @return Pipeline
     */
    public static function pipelineCloseModal()
    {

        $Pipeline = new Pipeline(false);
        $Pipeline->appendEmitter((new CloseModal(self::receiverModal()))->getEmitter());

        return $Pipeline;
    }

    /**
     * @param bool   $isLoadFilter
     * @param string $ViewType
     * @param null   $PresetId
     *
     * @return Pipeline
     */
    public static function pipelineNavigation($isLoadFilter = true, $ViewType = TblWorkSpace::VIEW_TYPE_ALL, $PresetId = null)
    {

        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverNavigation(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getNavigation'
        ));
        $Emitter->setPostPayload(array(
            'ViewType' => $ViewType
        ));
        $Pipeline->appendEmitter($Emitter);
        if ($isLoadFilter) {
            // Refresh Filter
            $Emitter = new ServerEmitter(self::receiverFilter(), self::getEndpoint());
            $Emitter->setGetPayload(array(
                self::API_TARGET => 'getFilter'
            ));
            // ViewType übergeben
            $PostArray['ViewType'] = $ViewType;
            // Spezielles Feld vorbefüllen:
            // Nur für Schüler-View
            if($ViewType === TblWorkSpace::VIEW_TYPE_STUDENT){
                if(($tblWorkSpaceList = Individual::useService()->getWorkSpaceAll($ViewType))){
                    foreach($tblWorkSpaceList as $tblWorkSpace){
                        // aktuelles Schuljahr finden
                        if($tblWorkSpace->getField() === 'TblYear_Year'){
                            // Eintragung in das Post anhand des Kalenders
                            $Date = Term::useService()->getYearString();
                            // Speichern im Post
                            $PostArray['TblYear_Year[1]'] = $Date;
                        }
                    }
                }
            }
            // hinzufügen der Post's wenn es geladen wird
            if(null !== $PresetId && ($tblPreset = Individual::useService()->getPresetById($PresetId))){
                $PostArray = array_merge($PostArray, $tblPreset->getPostValue());
            }

            $Emitter->setPostPayload($PostArray);
            $Pipeline->appendEmitter($Emitter);
        }
        return $Pipeline;
    }

    /**
     * @param $Field
     * @param $View
     * @param $ViewType
     *
     * @return Pipeline
     */
    public static function pipelineAddField($Field, $View, $ViewType)
    {

        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverService(), self::getEndpoint());
        $Emitter->setLoadingMessage('Feld wird hinzugefügt...');
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'addField'
        ));
        $Emitter->setPostPayload(array(
            'Field' => $Field,
            'View'  => $View,
            'ViewType' => $ViewType
        ));
        $Pipeline->appendEmitter($Emitter);
        // Refresh Filter
        $Emitter = new ServerEmitter(self::receiverFilter(), self::getEndpoint());
        $Emitter->setLoadingMessage('Filter wird aktualisiert...');
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getFilter'
        ));
        // ViewType übergeben
        $PostArray['ViewType'] = $ViewType;

        // Spezielles Feld vorbefüllen:
        if($Field === 'TblYear_Year'){
            // Nur für Schüler-View
            if($ViewType === TblWorkSpace::VIEW_TYPE_STUDENT) {
                // Eintragung in das Post anhand des Kalenders
                $Date = Term::useService()->getYearString();
                // Speichern im Post
                $PostArray['TblYear_Year[1]'] = $Date;
            }
        }
        $Emitter->setPostPayload($PostArray);
        $Pipeline->appendEmitter($Emitter);
        $Emitter = new ServerEmitter(self::receiverNavigation(), self::getEndpoint());
        $Emitter->setLoadingMessage('Verfügbare Informationen werden aktualisiert...');
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getNavigation'
        ));
        $Emitter->setPostPayload(array(
            'ViewType' => $ViewType
        ));
        $Pipeline->appendEmitter($Emitter);
        return $Pipeline;
    }

    /**
     * @param string $ViewType
     *
     * @return Pipeline
     */
    public static function pipelineDisplayFilter($ViewType = TblWorkSpace::VIEW_TYPE_ALL)
    {
        $Pipeline = new Pipeline(false);
        $Emitter = new ServerEmitter(self::receiverFilter(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getFilter'
        ));

        // ViewType übergeben
        $PostArray['ViewType'] = $ViewType;
        // Spezielles Feld vorbefüllen:
        // Nur für Schüler-View
        if($ViewType === TblWorkSpace::VIEW_TYPE_STUDENT){
            if(($tblWorkSpaceList = Individual::useService()->getWorkSpaceAll($ViewType))){
                foreach($tblWorkSpaceList as $tblWorkSpace){
                    // aktuelles Schuljahr finden
                    if($tblWorkSpace->getField() === 'TblYear_Year'){
                        $Date = Term::useService()->getYearString();
                        // Speichern im Post
                        $PostArray['TblYear_Year[1]'] = $Date;
                    }
                }
            }
        }
        $Emitter->setPostPayload($PostArray);
        $Pipeline->appendEmitter($Emitter);
        return $Pipeline;
    }

    public function removeFieldAll($ViewType = TblWorkSpace::VIEW_TYPE_ALL)
    {

        Individual::useService()->removeWorkSpaceAll($ViewType);
    }

    /**
     * @param int|null $WorkSpaceId
     */
    public function removeField($WorkSpaceId = null)
    {

        $tblWorkSpace = Individual::useService()->getWorkSpaceById($WorkSpaceId);
        Individual::useService()->removeWorkspace($tblWorkSpace);
    }

    /**
     * @param null   $WorkSpaceId
     * @param string $ViewType
     */
    public function moveFieldLeft($WorkSpaceId = null, $ViewType = TblWorkSpace::VIEW_TYPE_ALL)
    {

        $tblWorkSpace = Individual::useService()->getWorkSpaceById($WorkSpaceId);
        if ($tblWorkSpace) {
            $pos = $tblWorkSpace->getPosition();
            $tblWorkSpaceList = Individual::useService()->getWorkSpaceAll($ViewType);
            /** @var TblWorkSpace|bool $closestWorkSpace */
            $closestWorkSpace = false;
            if ($tblWorkSpaceList) {
                foreach ($tblWorkSpaceList as $WorkSpace) {
                    if ($WorkSpace->getPosition() < $pos) {
                        if ($closestWorkSpace) {
                            if ($closestWorkSpace->getPosition() < $WorkSpace->getPosition()) {
                                $closestWorkSpace = $WorkSpace;
                            }
                        } else {
                            $closestWorkSpace = $WorkSpace;
                        }
                    }
                }
            }
            if ($tblWorkSpace && $closestWorkSpace) {
                $posTo = $closestWorkSpace->getPosition();
                Individual::useService()->changeWorkSpace($tblWorkSpace, $posTo);
                Individual::useService()->changeWorkSpace($closestWorkSpace, $pos);
            }
        }
    }

    /**
     * @param null   $WorkSpaceId
     * @param string $ViewType
     */
    public function moveFieldRight($WorkSpaceId = null, $ViewType = TblWorkSpace::VIEW_TYPE_ALL)
    {

        $tblWorkSpace = Individual::useService()->getWorkSpaceById($WorkSpaceId);
        if ($tblWorkSpace) {
            $pos = $tblWorkSpace->getPosition();
            $tblWorkSpaceList = Individual::useService()->getWorkSpaceAll($ViewType);
            /** @var TblWorkSpace|bool $closestWorkSpace */
            $closestWorkSpace = false;
            if ($tblWorkSpaceList) {
                foreach ($tblWorkSpaceList as $WorkSpace) {
                    if ($WorkSpace->getPosition() > $pos) {
                        if ($closestWorkSpace) {
                            if ($closestWorkSpace->getPosition() > $WorkSpace->getPosition()) {
                                $closestWorkSpace = $WorkSpace;
                            }
                        } else {
                            $closestWorkSpace = $WorkSpace;
                        }
                    }
                }
            }
            if ($tblWorkSpace && $closestWorkSpace) {
                $posTo = $closestWorkSpace->getPosition();
                Individual::useService()->changeWorkSpace($tblWorkSpace, $posTo);
                Individual::useService()->changeWorkSpace($closestWorkSpace, $pos);
            }
        }
    }

    /**
     * @param int|null $WorkSpaceId
     * @param string   $direction
     */
    public function changeFilterCount($WorkSpaceId = null, $direction = '')
    {
        $tblWorkSpace = Individual::useService()->getWorkSpaceById($WorkSpaceId);
        if ($tblWorkSpace) {
            $FieldCount = $tblWorkSpace->getFieldCount();
            if ($direction == 'plus') {
                $FieldCount++;
            } elseif ($direction == 'minus') {
                $FieldCount--;
            }
            if ($tblWorkSpace && $direction) {
                Individual::useService()->changeWorkSpace($tblWorkSpace, null, $FieldCount);
            }
        }
    }

    /**
     * @param string $Info
     * @param string $ViewType
     *
     * @return Layout
     */
    public function getModalPreset($Info = '', $ViewType = TblWorkSpace::VIEW_TYPE_ALL)
    {

        $tblPresetList = Individual::useService()->getPresetAll();
        $TableContent = array();

        if ($tblPresetList) {
            $tblAccount = Account::useService()->getAccountBySession();
            array_walk($tblPresetList, function (TblPreset $tblPreset) use (&$TableContent, $ViewType, $tblAccount) {

                $PostValue = $tblPreset->getPostValue();
                $Item['Name'] = $tblPreset->getName();
                $Item['IsPublic'] = ($tblPreset->getIsPublic() ? new SuccessText(new Check()) : new DangerText(new Disable()));
                $Item['IsPost'] = (!empty($PostValue) ? new SuccessText('Filterung hinterlegt') : new SuccessText('Filterung leer'));
                $Item['PersonCreator'] = $tblPreset->getPersonCreator();
                $Item['EntityCreate'] = $tblPreset->getEntityCreate();
                $Item['FieldCount'] = '';
                $Item['Option'] = '';
                $tblPresetSetting = Individual::useService()->getPresetSettingAllByPreset($tblPreset, $ViewType);
                $tblAccountPreset = $tblPreset->getServiceTblAccount();

                if ($tblPresetSetting) {
//                    $Item['FieldCount'] = count($tblPresetSetting);
                    $ViewTypeFound = $tblPresetSetting[0]->getViewType();

                    $Item['Option'] = (new Standard('', self::getEndpoint(), new Filter(), array(), 'Laden der Vorlage'))
                            ->ajaxPipelineOnClick(ApiIndividual::pipelineLoadPreset($tblPreset->getId(), $ViewTypeFound));
                    // Optionen nur für Besitzer
                    if($tblAccount
                        && $tblAccountPreset
                        && $tblAccount->getId() == $tblAccountPreset->getId()) {
                        // Freigabe bearbeiten
                        if($tblPreset->getIsPublic()){
                            $Item['Option'] .= (new Standard('', self::getEndpoint(), new Minus(), array(), 'Veröffentlichung sperren'))
                                ->ajaxPipelineOnClick(ApiIndividual::pipelineChangePublic($tblPreset->getId(), false, $ViewTypeFound));
                        } else {
                            $Item['Option'] .= (new Standard('', self::getEndpoint(), new EyeOpen(), array(), 'Veröffentlichen'))
                                ->ajaxPipelineOnClick(ApiIndividual::pipelineChangePublic($tblPreset->getId(), true, $ViewTypeFound));
                        }
                        // Vorlage löschen
                        $Item['Option'] .= new ToolTip((new Standard('', self::getEndpoint(), new Remove(), array()))
                            ->ajaxPipelineOnClick(ApiIndividual::pipelineDeletePreset($tblPreset->getId(), $ViewTypeFound))
                            , 'Löschen der Vorlage', false);
                    }
                }

                // display only Filter that match the ViewType
                if(isset($ViewTypeFound) && $ViewType == $ViewTypeFound){
                    array_push($TableContent, $Item);
                }
            });
        }

        if ($Info != '') {
            $Info = new Warning($Info);
        }

        $Content = new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(
                        self::receiverService('', 'ChangePublic')
                    ),
                    new LayoutColumn(
                        new Title('Vorhandene Vorlagen')
                    ),
                    new LayoutColumn(
                        new TableData($TableContent, null,
                            array(
                                'Name'          => 'Name der Vorlage',
                                'IsPost'        => 'Vorbelegung',
                                'IsPublic'      => 'Öffentlich',
                                'PersonCreator' => 'Ersteller',
                                'EntityCreate'  => 'Speicherdatum',
                                'Option'        => ''
                            ))
                        .$Info
                    )
                ))
            )
        );

        return $Content;
    }

    /**
     * @param null   $PresetId
     * @param string $ViewType
     *
     * @return Pipeline|string
     */
    public function loadPreset($PresetId = null, $ViewType = TblWorkSpace::VIEW_TYPE_ALL)
    {

        $tblPreset = Individual::useService()->getPresetById($PresetId);
        if ($tblPreset) {
            // destroy existing Workspace
            Individual::useService()->removeWorkSpaceAll($ViewType);
            $tblPresetSettingList = Individual::useService()->getPresetSettingAllByPreset($tblPreset, $ViewType);
            $ViewType = TblWorkSpace::VIEW_TYPE_ALL;
            if ($tblPresetSettingList) {
                foreach ($tblPresetSettingList as $tblPresetSetting) {
                    $FiledCount = 1;
                    $PostValue = $tblPreset->getPostValue();
                    if(!empty($PostValue)){
                        if(isset($PostValue[$tblPresetSetting->getField()])){
                            $FiledCount = count($PostValue[$tblPresetSetting->getField()]);
                        }
                    }

                    $ViewType = $tblPresetSetting->getViewType();
                    Individual::useService()->addWorkSpaceField(
                        $tblPresetSetting->getField(),
                        $tblPresetSetting->getView(),
                        $tblPresetSetting->getPosition(),
                        $tblPresetSetting->getViewType(),
                        $tblPreset,
                        ($FiledCount >= 1? $FiledCount : 1));
                }
            }

//            $Info = 'Laden erfolgreich';
            return ApiIndividual::pipelineNavigation(true, $ViewType, $PresetId)
                .ApiIndividual::pipelineCloseModal();
        }

        $Info = 'Vorlage nicht gefunden.';
        return ApiIndividual::pipelinePresetShowModal($Info, $ViewType);
    }

    /**
     * @param null   $PresetId
     * @param string $ViewType
     *
     * @return Pipeline|string
     */
    public function deletePreset($PresetId = null, $ViewType = TblWorkSpace::VIEW_TYPE_ALL)
    {

        $tblPreset = Individual::useService()->getPresetById($PresetId);
        if ($tblPreset) {
            if(($tblPresetSetting = Individual::useService()->getPresetSettingAllByPreset($tblPreset, $ViewType))){
                $tblWorkSpaceList = Individual::useService()->getWorkSpaceAll($tblPresetSetting[0]->getViewType());
                if ($tblWorkSpaceList) {
                    foreach ($tblWorkSpaceList as $tblWorkSpace) {
                        if (($tblWorkSpacePreset = $tblWorkSpace->getTblPreset()) && $tblWorkSpacePreset->getId() == $tblPreset->getId()) {
                            // remove foreignKey if exist
                            Individual::useService()->changeWorkSpacePreset($tblWorkSpace, null);
                        }
                    }
                }
            }

            Individual::useService()->removePreset($tblPreset, $ViewType);
//            $Info = 'Erfolgreich entfernt';
            return ApiIndividual::pipelinePresetShowModal('', $ViewType);
        }

        $Info = 'Vorlage nicht gefunden.';
        return ApiIndividual::pipelinePresetShowModal($Info, $ViewType);
    }

    /**
     * @param string $Info
     * @param string $ViewType
     *
     * @return Layout
     */
    public function getModalSavePreset($Info = '', $ViewType = TblWorkSpace::VIEW_TYPE_ALL)
    {

        $Global = $this->getGlobal();
        $Post = $Global->POST;

        $tblPresetList = Individual::useService()->getPresetAll();
        $TableContent = array();

        if ($tblPresetList) {
            array_walk($tblPresetList, function (TblPreset $tblPreset) use (&$TableContent, $ViewType) {
                $PostValue = $tblPreset->getPostValue();
                $Item['Name'] = $tblPreset->getName();
                $Item['IsPost'] = (!empty($PostValue) ? new SuccessText('hinterlegt') : new SuccessText('leer'));
                $Item['IsPublic'] = ($tblPreset->getIsPublic() ? new SuccessText(new Check()) : new DangerText(new Disable()));
                $Item['PersonCreator'] = $tblPreset->getPersonCreator();
                $Item['EntityCreate'] = $tblPreset->getEntityCreate();

                $tblPresetSettingList = Individual::useService()->getPresetSettingAllByPreset($tblPreset, $ViewType);
                $ViewTypeControl = '';
                if ($tblPresetSettingList) {
                    $ViewTypeControl = current($tblPresetSettingList)->getViewType();
                }
                if($ViewTypeControl == $ViewType){
                    array_push($TableContent, $Item);
                }
            });
        }

        $form = (new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Speichern', array(
                            new TextField('PresetName', 'Name', 'Name der Vorlage'),
                            new CheckBox('IsPublic', 'Vorlage Veröffentlichen', 1),
                            new CheckBox('WithPost', 'Filterinhalt speichern', 1)
                        ), Panel::PANEL_TYPE_INFO)
                    ),
                    new FormColumn((new Primary('Speichern', self::getEndpoint(), new Save()))
                        ->ajaxPipelineOnClick(ApiIndividual::pipelinePresetSave($ViewType, $Post))
                    )
                ))
            )
        ))->disableSubmitAction();

        if ($Info == 'Speicherung erfolgreich') {
            $Info = new Success($Info);
        } elseif ($Info != '') {
            $Info = new Warning($Info);
        }

        $Content = new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(
                        new Title('Vorhandene Vorlagen')
                    ),
                    new LayoutColumn(
                        new TableData($TableContent, null,
                            array(
                                'Name'          => 'Name der Vorlage',
                                'IsPublic'      => 'Öffentlich',
                                'IsPost'        => 'Filterung',
                                'PersonCreator' => 'Ersteller',
                                'EntityCreate'  => 'Speicherdatum'
//                                'Option'    => ''
                            ))
                    ),
                    new LayoutColumn(
                        $Info
                        .new Well($form)
                    )
                ))
            )
        );

        return $Content;
    }

    /**
     * @param        $PresetName
     * @param string $ViewType
     *
     * @return Pipeline|string
     */
    public function createPreset($PresetName, $ViewType = TblWorkSpace::VIEW_TYPE_ALL)
    {

        $tblWorkSpaceList = Individual::useService()->getWorkSpaceAll($ViewType);
        if ($tblWorkSpaceList && $PresetName) {
            $Global = $this->getGlobal();
            $Post = $Global->POST;
            $ShrinkPost = array();
            $IsPublic = false;
            if(!empty($Post)){
                foreach($Post as $Key => $value){
                    if(isset($Global->POST['WithPost'])) {
                        if (strpos($Key, "Tbl") !== false) {
                            $ShrinkPost[$Key] = $value;
                        }
                    }
                    if(isset($Global->POST['IsPublic'])){
                        $IsPublic = true;
                    }
                }
            }
            $tblPreset = Individual::useService()->createPreset($PresetName, $IsPublic, $ShrinkPost);
            foreach ($tblWorkSpaceList as $tblWorkSpace) {
                Individual::useService()->createPresetSetting($tblPreset, $tblWorkSpace);
            }
//            $Info = 'Speicherung erfolgreich';
            //laden das ergebnisses führt zu fehlerhaften darstellungen auf der Demo/Live wird außerdem nicht zwingend benötigt!
            return // ApiIndividual::pipelinePresetSaveModal($Info, $ViewType) .
                ApiIndividual::pipelineCloseModal();
        }

        $Info = 'Speicherung konnte nicht erfolgen bitte überprüfen Sie ihre Eingabe';
        return ApiIndividual::pipelinePresetSaveModal($Info, $ViewType);

    }

    /**
     * @param $Field
     * @param $View
     * @param $ViewType
     */
    public function addField($Field, $View, $ViewType)
    {

        $Position = 1;
        $tblWorkSpaceList = Individual::useService()->getWorkSpaceAll($ViewType);
        if ($tblWorkSpaceList) {
            foreach ($tblWorkSpaceList as $tblWorkSpace) {
                if ($tblWorkSpace->getPosition() >= $Position) {
                    $Position = $tblWorkSpace->getPosition();
                }
            }
            $Position++;
        }

        Individual::useService()->addWorkSpaceField($Field, $View, $Position, $ViewType);
    }

    /**
     * @param string $ViewType
     *
     * @return Layout
     */
    public function getNavigation($ViewType = TblWorkSpace::VIEW_TYPE_ALL)
    {

        // remove every entry that is already chosen
        $WorkSpaceList = array();
        $ViewList = array();
        $tblWorkSpaceList = Individual::useService()->getWorkSpaceAll($ViewType);
        if ($tblWorkSpaceList) {
            /** @var TblWorkSpace $tblWorkSpace */
            foreach ($tblWorkSpaceList as $tblWorkSpace) {
                $WorkSpaceList[] = $tblWorkSpace->getField();
                $ViewList[$tblWorkSpace->getView()] = $tblWorkSpace->getView();
            }
        }

        $AccordionList = array();

        switch ($ViewType) {
            case TblWorkSpace::VIEW_TYPE_ALL:
                $Block = $this->getPanelList(new ViewGroup(), $WorkSpaceList, TblWorkSpace::VIEW_TYPE_ALL);
                if( !empty( $Block ) ) {
                    if( isset($ViewList['ViewGroup']) ) {
                        $AccordionList[] = new Panel( 'Gruppe:', new Scrollable( $Block, 110 ));
                    } else {
                        $AccordionList[] = new Dropdown( 'Gruppe:', new Scrollable( $Block ) );
                    }
                }
                $Block = $this->getPanelList(new ViewPerson(), $WorkSpaceList, TblWorkSpace::VIEW_TYPE_ALL);
                if( !empty( $Block ) ) {
                    if( isset($ViewList['ViewPerson']) ) {
                        $AccordionList[] = new Panel( 'Person:', new Scrollable( $Block, 300 ));
                    } else {
                        $AccordionList[] = new Dropdown( 'Person:', new Scrollable( $Block ) );
                    }
                }
                $Block = $this->getPanelList(new ViewPersonContact(), $WorkSpaceList, TblWorkSpace::VIEW_TYPE_ALL);
                if( !empty( $Block ) ) {
                    if( isset($ViewList['ViewPersonContact']) ) {
                        $AccordionList[] = new Panel( 'Kontakt:', new Scrollable( $Block, 300 ));
                    } else {
                        $AccordionList[] = new Dropdown( 'Kontakt:', new Scrollable( $Block ) );
                    }
                }
            break;
            case TblWorkSpace::VIEW_TYPE_STUDENT:
                $Block = $this->getPanelList(new ViewPerson(), $WorkSpaceList, TblWorkSpace::VIEW_TYPE_STUDENT);
                if( !empty( $Block ) ) {
                    if( isset($ViewList['ViewPerson']) ) {
                        $AccordionList[] = new Panel( 'Person:', new Scrollable( $Block, 300 ));
                    } else {
                        $AccordionList[] = new Dropdown( 'Person:', new Scrollable( $Block ) );
                    }
                }
                $Block = $this->getPanelList(new ViewGroupStudentBasic(), $WorkSpaceList, TblWorkSpace::VIEW_TYPE_STUDENT);
                if( !empty( $Block ) ) {
                    if( isset($ViewList['ViewGroupStudentBasic']) ) {
                        $AccordionList[] = new Panel( 'Schüler Grunddaten:', new Scrollable( $Block, 128 ));
                    } else {
                        $AccordionList[] = new Dropdown( 'Schüler Grunddaten:', new Scrollable( $Block ) );
                    }
                }
                $Block = $this->getPanelList(new ViewGroupStudentTransfer(), $WorkSpaceList, TblWorkSpace::VIEW_TYPE_STUDENT);
                if( !empty( $Block ) ) {
                    if (isset($ViewList['ViewGroupStudentTransfer'])) {
                        $AccordionList[] = new Panel('Schüler Transfer:', new Scrollable($Block, 300));
                    } else {
                        $AccordionList[] = new Dropdown('Schüler Transfer:', new Scrollable($Block));
                    }
                }
                $Block = $this->getPanelList(new ViewStudent(), $WorkSpaceList, TblWorkSpace::VIEW_TYPE_STUDENT);
                if( !empty( $Block ) ) {
                    if( isset($ViewList['ViewStudent']) ) {
                        $AccordionList[] = new Panel( 'Schüler Allgemeines:', new Scrollable( $Block, 300 ));
                    } else {
                        $AccordionList[] = new Dropdown( 'Schüler Allgemeines:', new Scrollable( $Block ) );
                    }
                }
                $Block = $this->getPanelList(new ViewGroupStudentSubject(), $WorkSpaceList, TblWorkSpace::VIEW_TYPE_STUDENT);
                if( !empty( $Block ) ) {
                    if( isset($ViewList['ViewGroupStudentSubject']) ) {
                        $AccordionList[] = new Panel( 'Schüler Fächer:', new Scrollable( $Block, 300 ));
                    } else {
                        $AccordionList[] = new Dropdown( 'Schüler Fächer:', new Scrollable( $Block ) );
                    }
                }
//                $Block = $this->getPanelList(new ViewGroupStudentIntegration(), $WorkSpaceList, TblWorkSpace::VIEW_TYPE_STUDENT);
//                if( !empty( $Block ) ) {
//                    if( isset($ViewList['ViewGroupStudentIntegration']) ) {
//                        $AccordionList[] = new Panel( 'Schüler Integration:', new Scrollable( $Block, 300 ));
//                    } else {
//                        $AccordionList[] = new Dropdown( 'Schüler Integration:', new Scrollable( $Block ) );
//                    }
//                }
                $Block = $this->getPanelList(new ViewEducationStudent(), $WorkSpaceList, TblWorkSpace::VIEW_TYPE_STUDENT);
                if( !empty( $Block ) ) {
                    if( isset($ViewList['ViewEducationStudent']) ) {
                        $AccordionList[] = new Panel( 'Bildung:', new Scrollable( $Block, 245 ));
                    } else {
                        $AccordionList[] = new Dropdown( 'Bildung:', new Scrollable( $Block ) );
                    }
                }
                if (GatekeeperConsumer::useService()->getConsumerBySessionIsConsumer(TblConsumer::TYPE_SACHSEN, 'WVSZ')) {
                    $Block = $this->getPanelList(new ViewGroupStudentSpecialNeeds(), $WorkSpaceList, TblWorkSpace::VIEW_TYPE_STUDENT);
                    if( !empty( $Block ) ) {
                        if( isset($ViewList['ViewGroupStudentSpecialNeeds']) ) {
                            $AccordionList[] = new Panel( 'Förderschüler:', new Scrollable( $Block, 245 ));
                        } else {
                            $AccordionList[] = new Dropdown( 'Förderschüler:', new Scrollable( $Block ) );
                        }
                    }
                }

                // Ladebedingung -> muss Berufs-, Berufsfach- oder Fachschule eingestellt haben
                if (School::useService()->hasConsumerTechnicalSchool()){
                    $Block = $this->getPanelList(new ViewGroupStudentTechnicalSchool(), $WorkSpaceList,
                        TblWorkSpace::VIEW_TYPE_STUDENT);
                    if(!empty($Block)){
                        if(isset($ViewList['ViewGroupStudentTechnicalSchool'])){
                            $AccordionList[] = new Panel('Schüler Berufsbildende Schulen:', new Scrollable($Block, 300));
                        } else {
                            $AccordionList[] = new Dropdown('Schüler Berufsbildende Schulen:', new Scrollable($Block));
                        }
                    }
                }
                $Block = $this->getPanelList(new ViewPersonContact(), $WorkSpaceList, TblWorkSpace::VIEW_TYPE_STUDENT);
                if( !empty( $Block ) ) {
                    if( isset($ViewList['ViewPersonContact']) ) {
                        $AccordionList[] = new Panel( 'Kontakt:', new Scrollable( $Block, 300 ));
                    } else {
                        $AccordionList[] = new Dropdown( 'Kontakt:', new Scrollable( $Block ) );
                    }
                }
                // View only for Student
                $Block = $this->getPanelList(new ViewStudentCustody(), $WorkSpaceList, TblWorkSpace::VIEW_TYPE_STUDENT);
                if( !empty( $Block ) ) {
                    if( isset($ViewList['ViewStudentCustody']) ) {
                        $AccordionList[] = new Panel( 'Sorgeberechtigte:', new Scrollable( $Block, 300 ));
                    } else {
                        $AccordionList[] = new Dropdown( 'Sorgeberechtigte:', new Scrollable( $Block ) );
                    }
                }
                // View only for Student
                $Block = $this->getPanelList(new ViewStudentAuthorized(), $WorkSpaceList, TblWorkSpace::VIEW_TYPE_STUDENT);
                if( !empty( $Block ) ) {
                    if( isset($ViewList['ViewStudentAuthorized']) ) {
                        $AccordionList[] = new Panel( 'Bevollmächtigter:', new Scrollable( $Block, 300 ));
                    } else {
                        $AccordionList[] = new Dropdown( 'Bevollmächtigter:', new Scrollable( $Block ) );
                    }
                }
            break;
            case TblWorkSpace::VIEW_TYPE_PROSPECT:
                $Block = $this->getPanelList(new ViewPerson(), $WorkSpaceList, TblWorkSpace::VIEW_TYPE_PROSPECT);
                if( !empty( $Block ) ) {
                    if( isset($ViewList['ViewPerson']) ) {
                        $AccordionList[] = new Panel( 'Person:', new Scrollable( $Block, 300 ));
                    } else {
                        $AccordionList[] = new Dropdown( 'Person:', new Scrollable( $Block ) );
                    }
                }
                $Block = $this->getPanelList(new ViewGroupProspect(), $WorkSpaceList, TblWorkSpace::VIEW_TYPE_PROSPECT);
                if( !empty( $Block ) ) {
                    if( isset($ViewList['ViewGroupProspect']) ) {
                        $AccordionList[] = new Panel( 'Interessent:', new Scrollable( $Block, 300 ));
                    } else {
                        $AccordionList[] = new Dropdown( 'Interessent:', new Scrollable( $Block ) );
                    }
                }
                $Block = $this->getPanelList(new ViewGroupProspectTransfer(), $WorkSpaceList, TblWorkSpace::VIEW_TYPE_PROSPECT);
                if( !empty( $Block ) ) {
                    if( isset($ViewList['ViewGroupProspectTransfer']) ) {
                        $AccordionList[] = new Panel( 'Interessent Aufnahme:', new Scrollable( $Block, 300 ));
                    } else {
                        $AccordionList[] = new Dropdown( 'Interessent Aufnahme:', new Scrollable( $Block ) );
                    }
                }
                $Block = $this->getPanelList(new ViewPersonContact(), $WorkSpaceList, TblWorkSpace::VIEW_TYPE_PROSPECT);
                if( !empty( $Block ) ) {
                    if( isset($ViewList['ViewPersonContact']) ) {
                        $AccordionList[] = new Panel( 'Kontakt:', new Scrollable( $Block, 300 ));
                    } else {
                        $AccordionList[] = new Dropdown( 'Kontakt:', new Scrollable( $Block ) );
                    }
                }
                // View only for Prospect
                $Block = $this->getPanelList(new ViewProspectCustody(), $WorkSpaceList, TblWorkSpace::VIEW_TYPE_PROSPECT);
                if( !empty( $Block ) ) {
                    if( isset($ViewList['ViewProspectCustody']) ) {
                        $AccordionList[] = new Panel( 'Sorgeberechtigte:', new Scrollable( $Block, 300 ));
                    } else {
                        $AccordionList[] = new Dropdown( 'Sorgeberechtigte:', new Scrollable( $Block ) );
                    }
                }
            break;
            case TblWorkSpace::VIEW_TYPE_CUSTODY:
                $Block = $this->getPanelList(new ViewPerson(), $WorkSpaceList, TblWorkSpace::VIEW_TYPE_CUSTODY);
                if( !empty( $Block ) ) {
                    if( isset($ViewList['ViewPerson']) ) {
                        $AccordionList[] = new Panel( 'Person:', new Scrollable( $Block, 300 ));
                    } else {
                        $AccordionList[] = new Dropdown( 'Person:', new Scrollable( $Block ) );
                    }
                }
                $Block = $this->getPanelList(new ViewGroupCustody(), $WorkSpaceList, TblWorkSpace::VIEW_TYPE_CUSTODY);
                if( !empty( $Block ) ) {
                    if( isset($ViewList['ViewGroupCustody']) ) {
                        $AccordionList[] = new Panel( 'Sorgeberechtigte:', new Scrollable( $Block, 80 ));
                    } else {
                        $AccordionList[] = new Dropdown( 'Sorgeberechtigte:', new Scrollable( $Block ) );
                    }
                }
                $Block = $this->getPanelList(new ViewPersonContact(), $WorkSpaceList, TblWorkSpace::VIEW_TYPE_CUSTODY);
                if( !empty( $Block ) ) {
                    if( isset($ViewList['ViewPersonContact']) ) {
                        $AccordionList[] = new Panel( 'Kontakt:', new Scrollable( $Block, 300 ));
                    } else {
                        $AccordionList[] = new Dropdown( 'Kontakt:', new Scrollable( $Block ) );
                    }
                }
                break;
            case TblWorkSpace::VIEW_TYPE_TEACHER:
                $Block = $this->getPanelList(new ViewPerson(), $WorkSpaceList, TblWorkSpace::VIEW_TYPE_TEACHER);
                if( !empty( $Block ) ) {
                    if( isset($ViewList['ViewPerson']) ) {
                        $AccordionList[] = new Panel( 'Person:', new Scrollable( $Block, 300 ));
                    } else {
                        $AccordionList[] = new Dropdown( 'Person:', new Scrollable( $Block ) );
                    }
                }
                $Block = $this->getPanelList(new ViewGroupTeacher(), $WorkSpaceList, TblWorkSpace::VIEW_TYPE_TEACHER);
                if( !empty( $Block ) ) {
                    if( isset($ViewList['ViewGroupTeacher']) ) {
                        $AccordionList[] = new Panel( 'Lehrer:', new Scrollable( $Block, 190 ));
                    } else {
                        $AccordionList[] = new Dropdown( 'Lehrer:', new Scrollable( $Block ) );
                    }
                }
                $Block = $this->getPanelList(new ViewPersonContact(), $WorkSpaceList, TblWorkSpace::VIEW_TYPE_TEACHER);
                if( !empty( $Block ) ) {
                    if( isset($ViewList['ViewPersonContact']) ) {
                        $AccordionList[] = new Panel( 'Kontakt:', new Scrollable( $Block, 300 ));
                    } else {
                        $AccordionList[] = new Dropdown( 'Kontakt:', new Scrollable( $Block ) );
                    }
                }
                break;
            case TblWorkSpace::VIEW_TYPE_STAFF:
                $Block = $this->getPanelList(new ViewPerson(), $WorkSpaceList, TblWorkSpace::VIEW_TYPE_STAFF);
                if( !empty( $Block ) ) {
                    if( isset($ViewList['ViewPerson']) ) {
                        $AccordionList[] = new Panel( 'Person:', new Scrollable( $Block, 300 ));
                    } else {
                        $AccordionList[] = new Dropdown( 'Person:', new Scrollable( $Block ) );
                    }
                }
                $Block = $this->getPanelList(new ViewGroupTeacher(), $WorkSpaceList, TblWorkSpace::VIEW_TYPE_STAFF);
                if( !empty( $Block ) ) {
                    if( isset($ViewList['ViewGroupTeacher']) ) {
                        $AccordionList[] = new Panel( 'Mitarbeiter:', new Scrollable( $Block, 190 ));
                    } else {
                        $AccordionList[] = new Dropdown( 'Mitarbeiter:', new Scrollable( $Block ) );
                    }
                }
                $Block = $this->getPanelList(new ViewPersonContact(), $WorkSpaceList, TblWorkSpace::VIEW_TYPE_STAFF);
                if( !empty( $Block ) ) {
                    if( isset($ViewList['ViewPersonContact']) ) {
                        $AccordionList[] = new Panel( 'Kontakt:', new Scrollable( $Block, 300 ));
                    } else {
                        $AccordionList[] = new Dropdown( 'Kontakt:', new Scrollable( $Block ) );
                    }
                }
                break;
            case TblWorkSpace::VIEW_TYPE_CLUB:
                $Block = $this->getPanelList(new ViewPerson(), $WorkSpaceList, TblWorkSpace::VIEW_TYPE_CLUB);
                if( !empty( $Block ) ) {
                    if( isset($ViewList['ViewPerson']) ) {
                        $AccordionList[] = new Panel( 'Person:', new Scrollable( $Block, 300 ));
                    } else {
                        $AccordionList[] = new Dropdown( 'Person:', new Scrollable( $Block ) );
                    }
                }
                $Block = $this->getPanelList(new ViewGroupClub(), $WorkSpaceList, TblWorkSpace::VIEW_TYPE_CLUB);
                if( !empty( $Block ) ) {
                    if( isset($ViewList['ViewGroupClub']) ) {
                        $AccordionList[] = new Panel( 'Verein:', new Scrollable( $Block, 140 ));
                    } else {
                        $AccordionList[] = new Dropdown( 'Verein:', new Scrollable( $Block ) );
                    }
                }
                $Block = $this->getPanelList(new ViewPersonContact(), $WorkSpaceList, TblWorkSpace::VIEW_TYPE_CLUB);
                if( !empty( $Block ) ) {
                    if( isset($ViewList['ViewPersonContact']) ) {
                        $AccordionList[] = new Panel( 'Kontakt:', new Scrollable( $Block, 300 ));
                    } else {
                        $AccordionList[] = new Dropdown( 'Kontakt:', new Scrollable( $Block ) );
                    }
                }
                break;
        }

        return
//            (new Accordion())->addItem('Schüler Grunddaten',
            new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn(
                    new Title( 'Verfügbare Informationen' )
                ),
                new LayoutColumn(
                    $AccordionList
                )
            ))
//                , true)
        ));
    }

    /**
     * @param AbstractView   $View
     * @param TblWorkSpace[] $WorkSpaceList
     * @param $ViewType
     *
     * @return string
     */
    private function getPanelList(AbstractView $View, $WorkSpaceList, $ViewType)
    {
        $PanelString = '';

        $ViewBlockList = array();
        $ConstantList = $View::getConstants();    // auslesen der Konstanten
        if ($ConstantList) {
            foreach ($ConstantList as $Constant) {
                $Group = $View->getGroupDefinition($Constant);
                if ($Group) {
                    $ViewBlockList[$Group][] = $Constant;
                }
            }
        }
        if ($ViewBlockList) {
            foreach ($ViewBlockList as $Block => $FieldList) {

                $FieldListArray = array();
                if ($FieldList) {
                    foreach ($FieldList as $FieldTblName) {

                        $ViewFieldName = $FieldTblName;
                        $FieldName = $View->getNameDefinition($FieldTblName);

                        if (!in_array($FieldTblName, $WorkSpaceList)) {
                            $ViewName = $View->getViewObjectName();
                            $FieldListArray[$FieldTblName] = new PullClear($FieldName.new PullRight((new Link('',
                                    self::getEndpoint(), new Plus()))
                                    ->ajaxPipelineOnClick(self::pipelineAddField($ViewFieldName, $ViewName, $ViewType))));
                        }
                    }
                }

                if (!empty($FieldListArray)) {
                    if($Block !== '&nbsp;'){
                        array_unshift( $FieldListArray, new Bold( $Block ) );
                    }
                    $PanelString .= new Listing( $FieldListArray );
                }
            }
        }

        return $PanelString;
    }

    /**
     * @param string $ViewType
     *
     * @return Panel|string
     */
    public function getFilter($ViewType = TblWorkSpace::VIEW_TYPE_ALL)
    {

        $tblWorkSpaceList = Individual::useService()->getWorkSpaceAll($ViewType);
        $LayoutColumnAll = array();

        // TODO: Card Fold
//        $TS = Template::getTwigTemplateString('>> {{ Feld }} <<');


        if ($tblWorkSpaceList) {
            $Global = $this->getGlobal();
            // pre fill filter behavior with "like"
            foreach ($tblWorkSpaceList as $tblWorkSpace) {
                $FieldCount = $tblWorkSpace->getFieldCount();
                for($i = 1; $i <= $FieldCount; $i++){
                    if(!isset($Global->POST[$tblWorkSpace->getField().'_Radio'.$i])){
//                        if($i == 2){
//                            Debugger::screenDump($tblWorkSpace->getField().'_Radio'.$i);
//                        }
                        if(!isset($Global->POST[$tblWorkSpace->getField().'_Radio'.$i])){
                            $Global->POST[$tblWorkSpace->getField().'_Radio'.$i] = 1;
                        }
                    }
                }
            }
            $Global->savePost();

            foreach ($tblWorkSpaceList as $tblWorkSpace) {
                $FieldCount = $tblWorkSpace->getFieldCount();
                if ($FieldCount <= 1) {
                    $LinkGroup = (new LinkGroup())
                        ->addLink((new Link(new SuccessText(new Plus()), ApiIndividual::getEndpoint(), null, array(), 'ODER'))
                            ->ajaxPipelineOnClick(ApiIndividual::pipelineChangeFilterCount($tblWorkSpace->getId(),
                                'plus', $ViewType)));
                } else {
                    $LinkGroup = (new LinkGroup())
                        ->addLink((new Link(new SuccessText(new Plus()), ApiIndividual::getEndpoint(), null, array(), 'ODER'))
                            ->ajaxPipelineOnClick(ApiIndividual::pipelineChangeFilterCount($tblWorkSpace->getId(),
                                'plus', $ViewType)))
                        ->addLink((new Link(new DangerText( new Minus() ), ApiIndividual::getEndpoint(), null, array(),
                            ''))
                            ->ajaxPipelineOnClick(ApiIndividual::pipelineChangeFilterCount($tblWorkSpace->getId(),
                                'minus', $ViewType)));
                }

                $View = $this->instanceView( $tblWorkSpace );

                $FieldName = $View->getNameDefinition($tblWorkSpace->getField());

                $FilterInputList = array();
                for ($i = 1; $i <= $FieldCount; $i++) {
                    if ($View->getDisableDefinition($tblWorkSpace->getField())) {
                        $FilterInputList[] = new Muted(new Center('Kein durchsuchbares Feld (Zusatzinformation)'));
                        // No Actions
                        $LinkGroup = '';
                    } else {
                        /** @var AbstractView|ViewPersonContact $View */
                        $FilterInputList[] = ( $i == 1
                            ? new PullClear( $View->getFormField( $tblWorkSpace->getField(), 'Alle', null, null, false, $ViewType) )
                            .new Center($this->getBehaviorRadioBox($tblWorkSpace->getField().'_Radio'.$i))
                            : new PullClear( $View->getFormField( $tblWorkSpace->getField(), 'ODER', null, null, false, $ViewType ) )
                            .new Center($this->getBehaviorRadioBox($tblWorkSpace->getField().'_Radio'.$i))
//                            ? new TextField($tblWorkSpace->getField().'['.$i.']', 'Alle' )
//                            : new TextField($tblWorkSpace->getField().'['.$i.']', 'ODER')
                        );
                    }
                }
                $FilterInputList[] = new Center( new PullClear( new PullLeft(
                            (new Link('', ApiIndividual::getEndpoint(), new ChevronLeft(), array(),
                                'Feld&nbsp;nach&nbsp;links'))
                                ->ajaxPipelineOnClick(ApiIndividual::pipelineMoveFilterField($tblWorkSpace->getId(),
                                    'left', $ViewType))
                        )
                        .$LinkGroup
                        .new PullRight(
                            (new Link('', ApiIndividual::getEndpoint(), new ChevronRight(), array(),
                                'Feld&nbsp;nach&nbsp;rechts'))
                                ->ajaxPipelineOnClick(ApiIndividual::pipelineMoveFilterField($tblWorkSpace->getId(),
                                    'right', $ViewType))
                        ))
                );

                $Listing = new Listing(
                    array_merge(
                        array(
                            new PullClear(new Bold($FieldName)
                                .new PullRight(
                                    (new Link(new DangerText(new Disable())
                                        , ApiIndividual::getEndpoint()
                                        , null
                                        , array()
                                        , 'Feld&nbsp;entfernen'))
                                        ->ajaxPipelineOnClick(ApiIndividual::pipelineDeleteFilterField($tblWorkSpace->getId(), $ViewType))
                                )
                            )
                        ), $FilterInputList)
                );

                $LayoutColumnAll[$tblWorkSpace->getPosition()] = new LayoutColumn( $Listing, 3);
            }
            ksort($LayoutColumnAll);
        }

        $LayoutRowList = array();
        $LayoutRowCount = 0;
        $LayoutRow = null;
        if (!empty($LayoutColumnAll)) {
            /**
             * @var LayoutColumn $LayoutColumn
             */
            foreach ($LayoutColumnAll as $LayoutColumn) {
                if ($LayoutRowCount % 4 == 0) {
                    $LayoutRow = new LayoutRow(array());
                    $LayoutRowList[] = $LayoutRow;
                }
                $LayoutRow->addColumn($LayoutColumn);
                $LayoutRowCount++;
            }
            $LayoutRowList[] = new LayoutRow(new LayoutColumn(array(
//                new PullLeft(
//                    new ToolTip(new CheckBox('isDistinct', 'Distinct &nbsp;&nbsp;', true), 'Zusammenfassen doppelter Werte', false)
//                )
                (new Primary('Suchen', self::getEndpoint(),
                    new Search()))->ajaxPipelineOnClick(self::pipelineResult($ViewType))
//            ,
//                (new Danger('Filter entfernen', ApiIndividual::getEndpoint(), new Disable()))->ajaxPipelineOnClick(
//                    ApiIndividual::pipelineDelete())
            , new PullRight(
                    (new LinkGroup())->addLink(
                        (new Standard('Filter speichern', ApiIndividual::getEndpoint(), new FolderClosed(), array(),
                            'Speichern als Filtervorlage'))->ajaxPipelineOnClick(
                            ApiIndividual::pipelinePresetSaveModal('', $ViewType)
                        )
                    )->addLink(
                        (new Standard('Filter laden', ApiIndividual::getEndpoint(), new FolderOpen(), array(),
                            'Laden von Filtervorlagen'))->ajaxPipelineOnClick(
                            ApiIndividual::pipelinePresetShowModal('', $ViewType)
                        ))
                )
            )));
        }

        if (!empty($LayoutRowList)) {
            $Layout = (new Layout(
                new LayoutGroup(
                    $LayoutRowList
                )
            )); //->disableSubmitAction();
            $Panel = //new Panel(
                new Title(
                    'Filteroptionen',''.new PullRight(
                        (new Link(new DangerText( new Disable().'&nbsp;Alle Felder entfernen'), ApiIndividual::getEndpoint()))->ajaxPipelineOnClick(
                            ApiIndividual::pipelineDelete($ViewType))
                    ))
//                .'<div class="FilterCardStyle">'
//                .'<style type="text/css">div.FilterCardStyle div.form-group { margin-bottom: auto; }</style>'
                .$Layout;
//                .'</div>';
            //, Panel::PANEL_TYPE_INFO);
        }

        return (isset($Panel) ? $Panel : new Title('Filteroptionen').new Muted('Bitte wählen Sie Felder aus den verfügbaren Informationen oder laden Sie eine')
            .(new Standard('&nbsp;Filtervorlage', ApiIndividual::getEndpoint(), new FolderOpen(), array(),
                'Laden von Filtervorlagen'))->ajaxPipelineOnClick(
                ApiIndividual::pipelinePresetShowModal('', $ViewType)
            )
        );
    }

    /**
     * @param string $RadioBoxName
     *
     * @return string
     */
    private function getBehaviorRadioBox($RadioBoxName)
    {

        $RadioBoxList = array();
        foreach($this->reducedSelectBoxList as &$reducedSelectBox){
            $RadioBoxList[] = $reducedSelectBox.'_Radio1';
        }

        if(!empty($RadioBoxList) && in_array($RadioBoxName, $RadioBoxList)){
            // Field in List
            return new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(new ToolTip(
                            '<div class="alert alert-info" style="padding: 2px;margin: 0;width: 23px;height: 23px;">'
                            . (new RadioBox($RadioBoxName, '&nbsp;', 1, RadioBox::RADIO_BOX_TYPE_DEFAULT))->setTabIndex(-1)
                            . '</div>'
                            , 'equal (gleich)', false)
                        , 3),
                        new LayoutColumn(new ToolTip(
                            '<div class="alert alert-danger" style="padding: 2px;margin: 0;width: 23px;height: 23px;">'
                            . (new RadioBox($RadioBoxName, '&nbsp;', 2, RadioBox::RADIO_BOX_TYPE_DANGER))->setTabIndex(-1)
                            . '</div>'
                            , 'not equal (ungleich)', false)
                        , 3)
                    ))
                )
            );
        } elseif(preg_match('!^[\w]+_Radio[1]{1}$!is', $RadioBoxName)){
            return new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(new ToolTip(
                            '<div class="alert alert-info" style="padding: 2px;margin: 0;width: 23px;height: 23px;">'
                            .(new RadioBox($RadioBoxName, '&nbsp;', 1, RadioBox::RADIO_BOX_TYPE_DEFAULT))->setTabIndex(-1)
                            .'</div>'
                            , 'like (enthält)', false)
                        , 3),
                        new LayoutColumn(new ToolTip(
                            '<div class="alert alert-danger" style="padding: 2px;margin: 0;width: 23px;height: 23px;">'
                            .(new RadioBox($RadioBoxName, '&nbsp;', 2, RadioBox::RADIO_BOX_TYPE_DANGER))->setTabIndex(-1)
                            .'</div>'
                            , 'not like (enthält nicht)', false)
                        , 3),
                        new LayoutColumn(new ToolTip(
                            '<div class="alert alert-warning" style="padding: 2px;margin: 0;width: 23px;height: 23px;">'
                            .(new RadioBox($RadioBoxName, '&nbsp;', 3, RadioBox::RADIO_BOX_TYPE_WARNING))->setTabIndex(-1)
                            .'</div>'
                            , 'null (ist leer)', false)
                        , 3),
                        new LayoutColumn(new ToolTip(
                            '<div class="alert alert-success" style="padding: 2px;margin: 0;width: 23px;height: 23px;">'
                            .(new RadioBox($RadioBoxName, '&nbsp;', 4, RadioBox::RADIO_BOX_TYPE_SUCCESS))->setTabIndex(-1)
                            .'</div>'
                            , 'not null ( ist nicht leer)', false)
                        , 3),
                    ))
                )
            );
        } else {
            return new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(new ToolTip(
                            '<div class="alert alert-info" style="padding: 2px;margin: 0;width: 23px;height: 23px;">'
                            .(new RadioBox($RadioBoxName, '&nbsp;', 1, RadioBox::RADIO_BOX_TYPE_DEFAULT))->setTabIndex(-1)
                            .'</div>'
                            , 'like (enthält)', false)
                        , 3)
                    )
                )
            );
        }
    }

    /**
     * @param string $ViewType
     *
     * @return Pipeline
     */
    public static function pipelineResult($ViewType = TblWorkSpace::VIEW_TYPE_ALL)
    {
        $Pipeline = new Pipeline();
        $Emitter = new ClientEmitter(self::receiverResult(), new ProgressBar(0,100,0, 10).new Small('Daten werden verarbeitet...'));
        $Pipeline->appendEmitter($Emitter);
        $Emitter = new ServerEmitter(self::receiverResult(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getSearchResult'
        ));
        $Emitter->setPostPayload(array(
            'ViewType' => $ViewType
        ));
        $Pipeline->appendEmitter($Emitter);
        return $Pipeline;
    }

    /**
     * @param string $ViewType
     *
     * @return null|string
     * @throws \Exception
     */
    private function getDownloadForm($ViewType = TblWorkSpace::VIEW_TYPE_ALL) {
        $tblAccount = Account::useService()->getAccountBySession();
        if(!empty($tblAccount)) {
            $tblWorkspaceAll = Individual::useService()->getWorkSpaceAllByAccount($tblAccount, $ViewType);
            if (!empty($tblWorkspaceAll)) {
                $FieldList = array();
                /** @var TblWorkSpace $tblWorkSpace */
                foreach ($tblWorkspaceAll as $Index => $tblWorkSpace) {
                    // Add Condition to Parameter (if exists and is not empty)
                    $Filter = $this->getGlobal()->POST;
////                    if (isset($Filter[$tblWorkSpace->getField()])) {
//                        foreach ($Filter[$tblWorkSpace->getField()] as $Count => $Value) {
//                            if (!empty($Value)) {
//                                $FieldList[] = new HiddenField( $tblWorkSpace->getField().'['.$Count.']' );
//                            }
//                        }
////                    }
                    $FieldCount = $tblWorkSpace->getFieldCount();
                    for($i = 1; $i <= $FieldCount ; $i++) {

                        $FieldList[] = new HiddenField($tblWorkSpace->getField().'['.$i.']');
                        if( isset($Filter[$tblWorkSpace->getField().'_Radio'.$i])){
                            $FieldList[] = new HiddenField( $tblWorkSpace->getField().'_Radio'.$i );
                        }
                    }
                }

                return new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn(
                        new Form(new FormGroup(new FormRow(new FormColumn($FieldList)))
                            , new Submit( 'Excel Herunterladen', new Download(), true ), new Route(__NAMESPACE__.'/Download'), array('ViewType' => $ViewType)
                        )
                    , 2),
                    new LayoutColumn(
                        new Form(new FormGroup(new FormRow(new FormColumn($FieldList)))
                            , new Submit( 'CSV Herunterladen', new Download(), true ), new Route(__NAMESPACE__.'/CSV/Download'), array('ViewType' => $ViewType)
                        )
                    , 2)
                ))));
            }
        }
        return null;
    }

    /**
     * @param TblWorkSpace $tblWorkSpace
     * @param bool $buildInstance true
     * @return AbstractView|string
     */
    private function instanceView( TblWorkSpace $tblWorkSpace, $buildInstance = true ) {
        if( false === strpos( '\\', $tblWorkSpace->getView() )) {
            $ViewClass = 'SPHERE\Application\Reporting\Individual\Service\Entity\\' . $tblWorkSpace->getView();
        } else {
            $ViewClass = $tblWorkSpace->getView();
        }
        if( $buildInstance ) {
            return new $ViewClass();
        } else {
            return $ViewClass;
        }
    }

    /**
     * @param string $Name
     * @return string
     */
    private function encodeField($Name) {
        $EncoderPattern = array(
            '!ä!s' => '_aE_',
            '!ö!s' => '_oE_',
            '!ü!s' => '_uE_',
            '!Ä!s' => '_AE_',
            '!Ö!s' => '_OE_',
            '!Ü!s' => '_UE_',
            '!ß!s' => '_SS_',
            '!-!s' => '_HY_',
            '!/!s' => '_DASH_',
            '! !s' => '_',
//            '! !s' => '',
            '!:!s' => 'Doppelpunkt',

        );
        return preg_replace(array_keys( $EncoderPattern ), array_values( $EncoderPattern ), $Name );
    }

    /**
     * @param string $Name
     * @return string
     */
    private function decodeField($Name) {
        $DecoderPattern = array(
            '!_aE_!s' => 'ä',
            '!_oE_!s' => 'ö',
            '!_uE_!s' => 'ü',
            '!_AE_!s' => 'Ä',
            '!_OE_!s' => 'Ö',
            '!_UE_!s' => 'Ü',
            '!_SS_!s' => 'ß',
            '!_HY_!s' => '-',
            '!_DASH_!s' => '/',
            '!_!s' => ' ',
            '!Doppelpunkt!s' => ':',
        );
        return preg_replace(array_keys( $DecoderPattern ), array_values( $DecoderPattern ), $Name );
    }

    /**
     * @param string $ViewType
     * @param bool   $IsPersonLink
     * @param bool   $SqlReturn
     *
     * @return \Doctrine\ORM\Query|Code|null
     */
    private function buildSearchQuery($ViewType = TblWorkSpace::VIEW_TYPE_ALL, $IsPersonLink = false, $SqlReturn = false)
    {
        $Binding = Individual::useService()->getBinding();
        $Manager = $Binding->getEntityManager();
        $Builder = $Manager->getQueryBuilder();

        $tblAccount = Account::useService()->getAccountBySession();

        if(!empty($tblAccount)) {
            $tblWorkspaceAll = Individual::useService()->getWorkSpaceAllByAccount($tblAccount, $ViewType);
            if( !empty($tblWorkspaceAll) ) {
                $ViewList = array();
                $ParameterList = array();
                // Schülerauswertung: ehemalige Schüler werden nicht gezogen
                $extendStudentToArchive = false;
                // Filter Preload um Jahresfilter zu ziehen
                $FilterPre = $this->getGlobal()->POST;
                // Logik für ehemalige Schüler
                // Ist die Jahresabfrage der Bildung enthalten?
                foreach ($tblWorkspaceAll as $tblWorkSpace) {
                    if($tblWorkSpace->getField() == 'TblYear_Year'){
                        // Wird der Jahresfilter verwendet?
                        if(isset($FilterPre['TblYear_Year'])){
                            // entspricht der Jahresfilter dem aktuellen Jahr?
                            if(($tblYearList = Term::useService()->getYearByNow())){
                                $tblYear = current($tblYearList);
                                // Schuljahr entspricht nicht dem aktuellen Jahr -> ehemalige Schüler werden angezeigt.
                                if($tblYear->getYear() != current($FilterPre['TblYear_Year'])){
                                    $extendStudentToArchive = true;
                                    break;
                                }
                            } else {
                                // Kein Jahr -> ehemalige Schüler werden angezeigt.
                                $extendStudentToArchive = true;
                                break;
                            }
                        }
                    }
                }

                /** @var TblWorkSpace $tblWorkSpace */
                foreach ($tblWorkspaceAll as $Index => $tblWorkSpace) {

                    // Add View to Query (if not exists)
                    $ViewClass = $this->instanceView( $tblWorkSpace, false );
                    if (!in_array($tblWorkSpace->getView(), $ViewList)) {

                        if($Index == 0 ) {
                            // Eingrenzen der zur Verfügung stehenden Personen durch die spezifische Auswertung
                            $Builder = $this->setInitialView($Builder, $ViewType, $ViewList, $ParameterList, $extendStudentToArchive);
                        }

                        if (empty($ViewList)) {
                            $Builder->from($ViewClass, $tblWorkSpace->getView());
                        } else {
                            $Builder->leftJoin($ViewClass, $tblWorkSpace->getView(), Join::WITH,
                                current( $ViewList ).'.TblPerson_Id = '.$tblWorkSpace->getView().'.TblPerson_Id'
                            );
                        }
                        $ViewList[] = $tblWorkSpace->getView();
                    }
                    if($IsPersonLink){
                        $IsPersonLink = false;
                        $Builder->addSelect($tblWorkSpace->getView() . '.TblPerson_Id AS Link');
                    }

                    // Add Field to Select
                    $ViewClass = $this->instanceView( $tblWorkSpace );
                    $Alias = $this->encodeField( $ViewClass->getNameDefinition($tblWorkSpace->getField()) );
                    $FieldName = $tblWorkSpace->getField();
                    if(in_array($tblWorkSpace->getField(), $this->IdSearchList)) {
                        $FieldName = str_replace('_Id', '_Name', $tblWorkSpace->getField());
                    }
                     $Builder->addSelect($tblWorkSpace->getView().'.'.$FieldName.' AS '.$Alias
                    );

                    // Add Field to Sort except: (incompatible with Distinct)
                    if($FieldName != 'TblStudentTechnicalSchool_HasFinancialAid'
                    && $FieldName != 'TblStudent_SchoolAttendanceStartDate'
                    && $FieldName != 'TblStudent_HasMigrationBackground'
                    ) {
                        $Builder->addOrderBy($tblWorkSpace->getView().'.'.$FieldName);
                    }

                    // Add Condition to Parameter (if exists and is not empty)
                    $Filter = $this->getGlobal()->POST;
                    /** @var null|Orx $OrExp */
                    $OrExp = null;
                    if (isset($Filter[$tblWorkSpace->getField()]) && count($Filter[$tblWorkSpace->getField()]) > 1) {
                        // Multiple Values
                        foreach ($Filter[$tblWorkSpace->getField()] as $Count => $Value) {

                            //FilterBehavior 1 = like; 2 = not like; 3 = null; 4 = not null
                            $Behavior = 1;
                            if(isset($Filter[$tblWorkSpace->getField().'_Radio'.$Count])){
                                $Behavior = $Filter[$tblWorkSpace->getField().'_Radio'.$Count];
                            }

                            // Escape User Input
                            $Value = preg_replace( '/[^\p{L}\p{N}]/u', '_', $Value );
                            // If User Input exists
                            if (!empty($Value) || $Value === 0 || $Behavior == 3 || $Behavior == 4) {
                                $Parameter = ':Filter' . $Index . 'Value' . $Count;
                                if (!$OrExp) {

                                    if(in_array($tblWorkSpace->getField(), $this->reducedSelectBoxList)) {
                                        // Add AND Condition to Where (if filter is set)
                                        if($Behavior == 1){
                                            $OrExp = $Builder->expr()->orX(
                                                $Builder->expr()->eq($tblWorkSpace->getView().'.'.$tblWorkSpace->getField(),
                                                    $Parameter));
                                        } elseif($Behavior == 2) {
                                            $OrExp = $Builder->expr()->orX(
                                                $Builder->expr()->neq($tblWorkSpace->getView().'.'.$tblWorkSpace->getField(),
                                                    $Parameter));
                                        }
                                        $ParameterList[$Parameter] = $Value;
                                    } else {
                                        $OrExp = $Builder->expr()->orX(
                                            $this->chooseBehaviorMultiExpression(
                                                $Builder, $tblWorkSpace->getView(), $tblWorkSpace->getField(), $Value, $Parameter,
                                                $ParameterList, $Behavior)
                                        );
                                    }
                                } else {
                                    if(in_array($tblWorkSpace->getField(), $this->reducedSelectBoxList)) {
                                        // Add AND Condition to Where (if filter is set)
                                        if($Behavior == 1){
                                            $OrExp->add( $Builder->expr()->eq($tblWorkSpace->getView().'.'.$tblWorkSpace->getField(),
                                                $Parameter));
                                        } elseif($Behavior == 2) {
                                            $OrExp->add( $Builder->expr()->neq($tblWorkSpace->getView().'.'.$tblWorkSpace->getField(),
                                                $Parameter));
                                        }
                                        $ParameterList[$Parameter] = $Value;
                                    } else {
                                        $OrExp->add(
                                            $this->chooseBehaviorMultiExpression(
                                                $Builder, $tblWorkSpace->getView(), $tblWorkSpace->getField(), $Value, $Parameter,
                                                $ParameterList, $Behavior)
                                        );
                                    }
                                }
//                                $ParameterList[$Parameter] = $Value;
                            }
                        }
                        // Add AND Condition to Where (if filter is set)
                        if ($OrExp) {
                            $Builder->andWhere($OrExp);
                        }
                    } elseif (isset($Filter[$tblWorkSpace->getField()]) && count($Filter[$tblWorkSpace->getField()]) == 1) {

                        //FilterBehavior 1 = like; 2 = not like; 3 = null; 4 = not null
                        $Behavior = 1;
                        if(isset($Filter[$tblWorkSpace->getField().'_Radio1'])){
                            $Behavior = $Filter[$tblWorkSpace->getField().'_Radio1'];
                        }

                        // Single Value
                        foreach ($Filter[$tblWorkSpace->getField()] as $Count => $Value) {
                            if($Value || $Behavior == 3 || $Behavior == 4){
                                // Escape User Input
                                $Value = preg_replace( '/[^\p{L}\p{N}]/u', '_', $Value );
                                // If User Input exists
                                $Parameter = ':Filter' . $Index . 'Value' . $Count;
                                if (!empty($Value) || $Value === 0) {
//                                    // choose eq by List or like by text
                                    if(in_array($tblWorkSpace->getField(), $this->reducedSelectBoxList)) {
                                        // Add AND Condition to Where (if filter is set)
                                        if($Behavior == 1){
                                            $Builder->andWhere(
                                                $Builder->expr()->eq($tblWorkSpace->getView().'.'.$tblWorkSpace->getField(),
                                                    $Parameter)
                                            );
                                        } elseif($Behavior == 2) {
                                            $Builder->andWhere(
                                                $Builder->expr()->neq($tblWorkSpace->getView().'.'.$tblWorkSpace->getField(),
                                                    $Parameter)
                                            );
                                        }
                                        $ParameterList[$Parameter] = $Value;
                                    } elseif($Value || $Behavior == 3 || $Behavior == 4) {
                                        $this->chooseBehavior(
                                            $Builder, $tblWorkSpace->getView(), $tblWorkSpace->getField(), $Value, $Parameter,
                                            $ParameterList, $Behavior);
                                    }
                                    // Add AND Condition to Where (if filter is set)

                                } else {
                                    if(in_array($tblWorkSpace->getField(), $this->reducedSelectBoxList)) {
                                        // Add AND Condition to Where (if filter is set)
                                        if($Value === '' && $Behavior == 1){
                                            $Builder->andWhere(
                                                $Builder->expr()->like($tblWorkSpace->getView().'.'.$tblWorkSpace->getField(),
                                                    $Parameter)
                                            );
                                        } elseif($Value === '' && $Behavior == 2) {
                                            $Builder->andWhere(
                                                $Builder->expr()->notLike($tblWorkSpace->getView().'.'.$tblWorkSpace->getField(),
                                                    $Parameter)
                                            );
                                            $ParameterList[$Parameter] = $Value;
                                        }
                                    }
                                    $this->chooseBehavior(
                                        $Builder, $tblWorkSpace->getView(), $tblWorkSpace->getField(), $Value, $Parameter,
                                        $ParameterList, $Behavior);
                                }
                            }
                        }
                    }
//                    // Add Field to "Group By" to prevent duplicates
//                    //ToDO distinct as an option?
//                    if(isset($Filter['isDistinct']) && $Filter['isDistinct'] == 1){
//                        $Builder->distinct();
//                    }
                }

                // Bind Parameter to Query
                foreach ($ParameterList as $Parameter => $Value) {
//                    $Builder->setParameter((string)$Parameter, '%' . $Value . '%');
                    $Builder->setParameter((string)$Parameter, $Value);
                }

                $Query = $Builder->getQuery();

                if($SqlReturn){
                    return new Code($Query->getSQL(). print_r($ParameterList, true));
                }

                $Query->useQueryCache(true);
                // der Cache soll Testweise auskommentiert werden #SSW-103
//                $Query->useResultCache(true, 300);
                $Query->setMaxResults(10000);

                $this->getLogger( new QueryLogger() )->addLog( $Query->getSQL() );
                $this->getLogger( new QueryLogger() )->addLog( print_r( $Query->getParameters(), true ) );

                return $Query;
            }
        }
        return null;
    }

    /**
     * @param QueryBuilder $Builder
     * @param string       $ViewType
     * @param array        $ViewList
     * @param array        $ParameterList
     *
     * @return QueryBuilder
     */
    private function setInitialView(QueryBuilder $Builder, $ViewType, &$ViewList = array(), &$ParameterList = array(), $extendStudentToArchive = false)
    {
        switch ($ViewType) {
            case TblWorkSpace::VIEW_TYPE_STUDENT:
                $viewGroup = new ViewGroup();
                $Builder->from($viewGroup->getEntityFullName(),
                    $viewGroup->getViewObjectName());
                $Parameter = ':Filter'.'Initial'.'Value'.'MetaTable';
                $ParameterList[$Parameter] = TblGroup::META_TABLE_STUDENT;
                if($extendStudentToArchive){
                    $Builder->distinct();
                    $Parameter1 = ':Filter'.'Initial'.'Value'.'MetaTable1';
                    $ParameterList[$Parameter1] = TblGroup::META_TABLE_ARCHIVE;

                    $orStudent = $Builder->expr()->orX($Builder->expr()->eq('ViewGroup.TblGroup_MetaTable', $Parameter));
                    $orArchive = $Builder->expr()->orX($Builder->expr()->eq('ViewGroup.TblGroup_MetaTable', $Parameter1));
//                    $orAll = $Builder->expr()->orX();
//                    $orAll->add($Builder->expr()->eq('ViewGroup.TblGroup_MetaTable', $Parameter));
//                    $orAll->add($Builder->expr()->eq('ViewGroup.TblGroup_MetaTable', $Parameter1));
//                    $Builder->andWhere(
//                    );
                    $Builder->orWhere($orStudent, $orArchive);
                } else {
                    $Builder->andWhere(
                        $Builder->expr()->eq('ViewGroup.TblGroup_MetaTable',
                            $Parameter)
                    );
                }
                $ViewList[] = 'ViewGroup';
                break;
            case TblWorkSpace::VIEW_TYPE_PROSPECT:
                $viewGroup = new ViewGroup();
                $Parameter = ':Filter'.'Initial'.'Value'.'MetaTable';
                $Builder->from($viewGroup->getEntityFullName(),
                    $viewGroup->getViewObjectName());
                $Builder->andWhere(
                    $Builder->expr()->eq('ViewGroup.TblGroup_MetaTable',
                        $Parameter)
                );
                $ParameterList[$Parameter] = TblGroup::META_TABLE_PROSPECT;
                $ViewList[] = 'ViewGroup';
                break;
            case TblWorkSpace::VIEW_TYPE_CUSTODY:
                $viewGroup = new ViewGroup();
                $Parameter = ':Filter'.'Initial'.'Value'.'MetaTable';
                $Builder->from($viewGroup->getEntityFullName(),
                    $viewGroup->getViewObjectName() );
                $Builder->andWhere(
                    $Builder->expr()->eq('ViewGroup.TblGroup_MetaTable',
                        $Parameter)
                );
                $ParameterList[$Parameter] = TblGroup::META_TABLE_CUSTODY;
                $ViewList[] = 'ViewGroup';
                break;
            case TblWorkSpace::VIEW_TYPE_TEACHER:
                $viewGroup = new ViewGroup();
                $Parameter = ':Filter'.'Initial'.'Value'.'MetaTable';
                $Builder->from($viewGroup->getEntityFullName(),
                    $viewGroup->getViewObjectName());
                $Builder->andWhere(
                    $Builder->expr()->eq('ViewGroup.TblGroup_MetaTable',
                        $Parameter)
                );
                $ParameterList[$Parameter] = TblGroup::META_TABLE_TEACHER;
                $ViewList[] = 'ViewGroup';
                break;
            case TblWorkSpace::VIEW_TYPE_STAFF:
                $viewGroup = new ViewGroup();
                $Parameter = ':Filter'.'Initial'.'Value'.'MetaTable';
                $Builder->from($viewGroup->getEntityFullName(),
                    $viewGroup->getViewObjectName());
                $Builder->andWhere(
                    $Builder->expr()->eq('ViewGroup.TblGroup_MetaTable',
                        $Parameter)
                );
                $ParameterList[$Parameter] = TblGroup::META_TABLE_STAFF;
                $ViewList[] = 'ViewGroup';
                break;
            case TblWorkSpace::VIEW_TYPE_CLUB:
                $viewGroup = new ViewGroup();
                $Parameter = ':Filter'.'Initial'.'Value'.'MetaTable';
                $Builder->from($viewGroup->getEntityFullName(),
                    $viewGroup->getViewObjectName());
                $Builder->andWhere(
                    $Builder->expr()->eq('ViewGroup.TblGroup_MetaTable',
                        $Parameter)
                );
                $ParameterList[$Parameter] = TblGroup::META_TABLE_CLUB;
                $ViewList[] = 'ViewGroup';
                break;
        }
        return $Builder;
    }

    /**
     * @param QueryBuilder $Builder
     * @param string       $View
     * @param string       $Field
     * @param string       $Value
     * @param string       $Parameter
     * @param array        $ParameterList
     * @param int          $Behavior
     *
     * @return \Doctrine\ORM\Query\Expr\Comparison|Orx
     */
    private function chooseBehaviorMultiExpression(QueryBuilder &$Builder, $View, $Field, $Value, $Parameter, &$ParameterList, $Behavior)
    {

        switch ($Behavior){
            case 1:
                $ParameterList[$Parameter] = '%'.$Value.'%';
                return $Builder->expr()->like($View . '.' . $Field, $Parameter);
//                break;
            case 2:
                $ParameterList[$Parameter] = '%'.$Value.'%';
                return $Builder->expr()->notLike($View . '.' . $Field, $Parameter);
//                break;
            case 3:
                $ParameterList[$Parameter] = '';
                return $Builder->expr()->orX(
                    $Builder->expr()->isNull($View . '.' . $Field),
                    $Builder->expr()->eq($View . '.' . $Field,
                        $Parameter)
                );
//                break;
            case 4:
                $ParameterList[$Parameter] = '_%';
                return $Builder->expr()->like($View . '.' . $Field, $Parameter);
//                break;
        }
        //default
        $ParameterList[$Parameter] = '%'.$Value.'%';
        return $Builder->expr()->like($View . '.' . $Field, $Parameter);
    }

    /**
     * @param QueryBuilder $Builder
     * @param string       $View
     * @param string       $Field
     * @param string       $Value
     * @param string       $Parameter
     * @param array        $ParameterList
     * @param int       $Behavior
     *
     */
    private function chooseBehavior(QueryBuilder &$Builder, $View, $Field, $Value, $Parameter, &$ParameterList, $Behavior)
    {

        switch ($Behavior){
            case 1:
                $ParameterList[$Parameter] = '%'.$Value.'%';
                $Builder->andWhere($Builder->expr()->like($View . '.' . $Field, $Parameter));
                break;
            case 2:
                $ParameterList[$Parameter] = '%'.$Value.'%';
                if($Value){
                    $Builder->andWhere(
                        $Builder->expr()->orX(
                            $Builder->expr()->isNull($View . '.' . $Field),
                            $Builder->expr()->notLike($View . '.' . $Field,
                                $Parameter))
                    );
                } else {
                    $Builder->andWhere($Builder->expr()->notLike($View . '.' . $Field, $Parameter));
                }
                break;
            case 3:
                $ParameterList[$Parameter] = '';
                $Builder->andWhere($Builder->expr()->orX(
                    $Builder->expr()->isNull($View . '.' . $Field),
                    $Builder->expr()->eq($View . '.' . $Field,
                        $Parameter))
                );
                break;
            case 4:
                $ParameterList[$Parameter] = '_%';
                $Builder->andWhere($Builder->expr()->like($View . '.' . $Field, $Parameter));
                break;
        }
    }

    /**
     * @param string $ViewType
     *
     * @return array|bool|Info|Error|string
     */
    public function getSearchResult($ViewType = TblWorkSpace::VIEW_TYPE_ALL)
    {

        $ShowSQL = false;
        $Query = $this->buildSearchQuery($ViewType, true, $ShowSQL);

        if($ShowSQL){
            return $Query;
        }
        if( null === $Query ) {
            return 'Error';
        } else {

            try {
                $Result = $Query->getResult();
                $Error = false;
            } catch (\Exception $Exception) {
                $Result = array();
//                $Error = 'Abfrage fehlgeschlagen';
                $Error = $this->parseException( $Exception, 'Abfrage fehlgeschlagen');
            }

            if(!$Error && !empty($Result)) {
                $ColumnDTNames = array();
                $ColumnDBNames = array_keys(current($Result));
                $i = 0;
                $GermanStringOrder = array();
                $DateOrder = array();
                // Sortierung Type für TableData
                array_walk($ColumnDBNames, function ($Name) use (&$ColumnDTNames, &$i, &$DateOrder, &$GermanStringOrder) {
                    $ColumnDTNames[$Name] = $this->decodeField($Name);
//                    $ColumnDTNames[$Name] = preg_replace('!\_!is', ' ', $Name);
                    if(in_array($Name, $this->FieldNameSortByDate)) {
                        $DateOrder[] = array('type' => 'de_date', 'targets' => $i);
                    }
                    if(in_array($Name, $this->FieldNameSortByGermanString)) {
                        $GermanStringOrder[] = array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => $i);
                    }
                    if(in_array($Name, $this->FieldNameSortByNumeric)) {
                        $GermanStringOrder[] = array('type' => 'natural', 'targets' => $i);
                    }
                    $i++;
                });
                $ColumnDef = array_merge($DateOrder, $GermanStringOrder);

                // replace PersonId to Link
                foreach($Result as &$Row) {
                    foreach($Row as $Key => &$value) {
                        if($Key == 'Link'){
                            $tblPerson = Person::useService()->getPersonById($value);
                            if($tblPerson){
                                $PersonName = $tblPerson->getLastFirstName();
                                $Row[$Key] = '<span hidden>'.$PersonName.'</span>'.(new Link($PersonName, '/People/Person', new PersonIcon(),
                                        array('Id' => $value)))->setExternal();
                            }
                        }
                    }
                }

                $Result = (new TableData($Result, null, $ColumnDTNames,
//                        false
                        array(
                        'order'      => array(array(1, 'asc')),
                        'responsive' => false,
                        'fixedHeader'=> false,
                        'columnDefs' => $ColumnDef
                    )
                ))

                    .$this->getDownloadForm($ViewType);
//                    .'DEBUG'
//                    .new Listing( $this->getLogger(new QueryLogger())->getLog() );
            } elseif( $Error ) {
                $Result = $Error;
            } else {
                $Result = new Info( 'Keine Daten gefunden' );
//                .'DEBUG'
//                .new Listing( $this->getLogger(new QueryLogger())->getLog() );
            }
        }

        return $Result;
    }

    /**
     * @param \Exception $Exception
     * @param string $Name
     * @return Error
     */
    private function parseException( \Exception $Exception, $Name = '' ) {

        $TraceList = '';
        foreach ((array)$Exception->getTrace() as $Trace) {
            $TraceList .= nl2br('<samp class="text-info small">'
                .( isset( $Trace['type'] ) && isset( $Trace['function'] ) ? 'Method: '.$Trace['type'].$Trace['function'] : 'Method: ' )
                .( isset( $Trace['class'] ) ? '<br/>Class: '.$Trace['class'] : '<br/>Class: ' )
                .( isset( $Trace['file'] ) ? '<br/>File: '.$Trace['file'] : '<br/>File: ' )
                .( isset( $Trace['line'] ) ? '<br/>Line: '.$Trace['line'] : '<br/>Line: ' )
                .'</samp><br/>');
        }
        $Hit = '<hr/><samp class="text-danger"><div class="h6">'.get_class($Exception).'<br/><br/>'.nl2br($Exception->getMessage()).'</div>File: '.$Exception->getFile().'<br/>Line: '.$Exception->getLine().'</samp><hr/><div class="small">'.$TraceList.'</div>';
        return new Error(
            $Exception->getCode() == 0 ? $Name : $Exception->getCode(), $Hit
        );

    }

    /**
     * @param string $ViewType
     *
     * @return null|FilePointer
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    private function buildExcelFile($ViewType = TblWorkSpace::VIEW_TYPE_ALL)
    {
        $Result = array();
        if(($Query = $this->buildSearchQuery($ViewType))) {
            $Result = $Query->getResult();
        }
        if(!empty($Result)) {
            $ColumnDTNames = array();
            $ColumnDBNames = array_keys(current($Result));
            array_walk($ColumnDBNames, function ($Name, $Index) use (&$ColumnDTNames) {
//                $ColumnDTNames[$Index] = preg_replace('!\_!is', ' ', $Name);
                $ColumnDTNames[$Index] = $this->decodeField($Name);
            });

            $File = new FilePointer('xlsx','Auswertung');

            /** @var PhpExcel $Document */
            $Document = Document::getDocument( $File->getFileLocation() );
            $WorkSheetString = $this->getRealNameByViewType($ViewType);
            if($WorkSheetString === ''){
                $WorkSheetString = 'Tabelle1';
            }
            $Document->renameWorksheet($WorkSheetString);

            // Header
            foreach ( $ColumnDTNames as $Index => $Name ) {
                $Document->setValue( $Document->getCell( $Index, 0), $Name );
                $Document->setStyle( $Document->getCell( $Index, 0) )->setFontBold()->setColumnWidth();
            }

            // Body
            foreach( $Result as $RowIndex => $Row ) {
                $ColumnCount = 0;
                foreach( $Row as $Value ) {
                    $Document->setValue( $Document->getCell( $ColumnCount++, $RowIndex+1 ), $Value );
                }
            }

            $Document->saveFile( new FileParameter($File->getFileLocation()) );
            return $File;
        }
        return null;
    }

    /**
     * @param string $ViewType
     *
     * @return null|FilePointer
     * @throws \MOC\V\Component\Document\Component\Exception\Repository\TypeFileException
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    private function buildCsvFile($ViewType = TblWorkSpace::VIEW_TYPE_ALL)
    {
        $Result = array();
        if(($Query = $this->buildSearchQuery($ViewType))) {
            $Result = $Query->getResult();
        }
        if(!empty($Result)) {
            $ColumnDTNames = array();
            $ColumnDBNames = array_keys(current($Result));
            array_walk($ColumnDBNames, function ($Name, $Index) use (&$ColumnDTNames) {
                //                $ColumnDTNames[$Index] = preg_replace('!\_!is', ' ', $Name);
                $ColumnDTNames[$Index] = $this->decodeField($Name);
            });

            $File = new FilePointer('csv','Auswertung');

            /** @var PhpExcel $Document */
            $Document = Document::getDocument( $File->getFileLocation() );
            $Document->setDelimiter(';');

            // Header
            foreach ( $ColumnDTNames as $Index => $Name ) {
                $Document->setValue( $Document->getCell( $Index, 0), $Name );
                $Document->setStyle( $Document->getCell( $Index, 0) )->setFontBold()->setColumnWidth();
            }

            // Body
            foreach( $Result as $RowIndex => $Row ) {
                $ColumnCount = 0;
                foreach( $Row as $Value ) {
                    $Document->setValue( $Document->getCell( $ColumnCount++, $RowIndex+1 ), $Value );
                }
            }

            $Document->saveFile( new FileParameter($File->getFileLocation()) );
            return $File;
        }
        return null;
    }

    /**
     * @param string $ViewType
     *
     * @return string
     * @throws \MOC\V\Core\FileSystem\Exception\FileSystemException
     */
    public function downloadFile($ViewType = TblWorkSpace::VIEW_TYPE_ALL)
    {
        $File = $this->buildExcelFile($ViewType);
        if(!$File){
            return 'Download der Auswertung konnte nicht erstellt werden, prüfen Sie Ihre Filterung';
        }
        $FileName = 'Auswertung_';
        $FileName .= $this->getRealNameByViewType($ViewType);
        $FileName .= '_'.date('d-m-Y_H-i-s').'.xlsx';

        return FileSystem::getStream(
            $File->getRealPath(), $FileName
        )->__toString();
    }

    /**
     * @param string $ViewType
     *
     * @return string
     * @throws \MOC\V\Core\FileSystem\Exception\FileSystemException
     */
    public function downloadCsvFile($ViewType = TblWorkSpace::VIEW_TYPE_ALL)
    {
        $File = $this->buildCsvFile($ViewType);
        if(!$File){
            return 'Download der Auswertung konnte nicht erstellt werden, prüfen Sie Ihre Filterung';
        }
        $FileName = 'Auswertung_';
        $FileName .= $this->getRealNameByViewType($ViewType);
        $FileName .= '_'.date('d-m-Y_H-i-s').'.csv';

        return FileSystem::getDownload(
            $File->getRealPath(), $FileName
        )->__toString();
    }

    /**
     * @param string $ViewType
     *
     * @return string
     */
    private function getRealNameByViewType($ViewType = TblWorkSpace::VIEW_TYPE_ALL)
    {
        switch($ViewType){
            case TblWorkSpace::VIEW_TYPE_ALL :
                $Name = 'Allgemein';
                break;
            case TblWorkSpace::VIEW_TYPE_STUDENT :
                $Name = 'Schueler';
                break;
            case TblWorkSpace::VIEW_TYPE_PROSPECT :
                $Name = 'Interessent';
                break;
            case TblWorkSpace::VIEW_TYPE_CUSTODY :
                $Name = 'Sorgeberechtigten';
                break;
            case TblWorkSpace::VIEW_TYPE_TEACHER :
                $Name = 'Lehrer';
                break;
            case TblWorkSpace::VIEW_TYPE_CLUB :
                $Name = 'Vereinsmitglieder';
                break;
            default:
                $Name = '';
                break;
        }
        return $Name;
    }

}