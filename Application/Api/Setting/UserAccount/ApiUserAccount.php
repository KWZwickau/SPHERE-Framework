<?php
namespace SPHERE\Application\Api\Setting\UserAccount;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\Setting\User\Account\Account;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\InlineReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\HiddenField;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Repository\Title;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\TileBig;
use SPHERE\Common\Frontend\Icon\Repository\Warning as WarningIcon;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\ProgressBar;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger as DangerMessage;
use SPHERE\Common\Frontend\Message\Repository\Info as InfoMessage;
use SPHERE\Common\Frontend\Message\Repository\Success as SuccessMessage;
use SPHERE\Common\Frontend\Message\Repository\Warning as WarningMessage;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\System\Extension\Extension;

/**
 * Class SerialLetter
 * @package SPHERE\Application\Api\Setting\UserAccount
 */
class ApiUserAccount extends Extension implements IApiInterface
{

    use ApiTrait;

    const SERVICE_CLASS = 'ServiceClass';
    const SERVICE_METHOD = 'ServiceMethod';

    /**
     * @param string $Method
     *
     * @return string
     */
    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);
        $Dispatcher->registerMethod('openAccountModal');
        $Dispatcher->registerMethod('serviceAccount');
        $Dispatcher->registerMethod('openAccountModalResult');

        $Dispatcher->registerMethod('loadingScreen');
        $Dispatcher->registerMethod('openFilter');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @return ModalReceiver
     */
    public static function receiverAccountModal()
    {

        return (new ModalReceiver('Erstellen der '.new Bold('Benutzer'), null, false))
            ->setIdentifier('Loadingscreen');
    }

    /**
     * @return InlineReceiver
     */
    public static function receiverAccountService()
    {

        return (new InlineReceiver(''))
            ->setIdentifier('AccountService');
    }

    /**
     * @param string $Content
     *
     * @return BlockReceiver
     */
    public static function receiverFilter($Content = '')
    {

        return (new BlockReceiver($Content))
            ->setIdentifier('Filter');
    }

    /**
     * @param string $Type (S = Student; C = Custody)
     *
     * @return Pipeline
     */
    public static function pipelineSaveAccount($Type = 'S')
    {
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverAccountModal(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'openAccountModal'
        ));
        $Pipeline->appendEmitter($Emitter);

        $Emitter = new ServerEmitter(self::receiverAccountService(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'serviceAccount'
        ));
        $Emitter->setPostPayload(array(
            'Type' => $Type
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     *
     * @param string $GroupByTime
     *
     * @return Pipeline
     */
    public static function pipelineShowLoad($GroupByTime = '')
    {

        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverFilter(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'loadingScreen'
        ));
        $Emitter->setPostPayload(array(
            'GroupByTime' => $GroupByTime
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     *
     * @param string $GroupByTime
     *
     * @return Pipeline
     */
    public static function pipelineShowFilter($GroupByTime = '')
    {

        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverFilter(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'openFilter'
        ));
        $Emitter->setPostPayload(array(
            'GroupByTime' => $GroupByTime
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }


    /**
     * @param string $GroupByTime
     *
     * @return Layout
     */
    public function loadingScreen($GroupByTime = '')
    {

        return new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(
                        new InfoMessage('Lädt'
                            .new Container((new ProgressBar(0, 100, 0, 10))
                                ->setColor(ProgressBar::BAR_COLOR_SUCCESS, ProgressBar::BAR_COLOR_SUCCESS))
                        )
                    ),
                    new LayoutColumn(
                        self::pipelineShowFilter($GroupByTime)
                    ),
                ))
            )
        );
    }

    /**
     * @param string $GroupByTime
     *
     * @return Layout|string
     */
    public function openFilter($GroupByTime = '')
    {

        $form = $this->formFilterPdf($GroupByTime);

        return new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(
                        new Well($form)
                    ),
//                    new LayoutColumn(
//                        $GroupByTime.'<br/>'.
//                        new InfoMessage('Dieser Vorgang kann einige Zeit in Anspruch nehmen'
//                            .new Container((new ProgressBar(0, 100, 0, 10))
//                                ->setColor(ProgressBar::BAR_COLOR_SUCCESS, ProgressBar::BAR_COLOR_SUCCESS))
//                        )
//                    ),
                ))
            )
        );
    }

    /**
     * @param string $GroupByTime
     * @param bool   $IsParent
     * @param null   $Data
     *
     * @return Form|string
     */
    private function formFilterPdf($GroupByTime, $IsParent = false, $Data = null)
    {

        $SelectBoxContent = array();
        if($GroupByTime){
            $tblUserAccountGroup = Account::useService()->getUserAccountByTimeGroupLimitList(new \DateTime($GroupByTime));
            foreach ($tblUserAccountGroup as $GroupIdentifier =>$tblUserAccountList){
                $SelectBoxContent[$GroupIdentifier] = $GroupIdentifier.'.te Liste aus maximal 30 Personen';
            }
        } else {
            return new WarningMessage(new WarningIcon().' Form konnte nicht geladen werden.');
        }


//        $tblSchoolAll = School::useService()->getSchoolAll();
//        if(!$tblSchoolAll){
//            $Warning = new WarningMessage('Es sind keine Schulen in den Mandanteneinstellungen hinterlegt.
//            Um diese Funktionalität nutzen zu können ist dies zwingend erforderlich.');
//        }

        $CompanyPlace = '';

        if(!isset($Data)){
            $Global = $this->getGlobal();
            $Global->POST['Data']['GroupByTime'] = $GroupByTime;
            $Global->POST['Data']['IsParent'] = $IsParent;
//
            $Global->POST['Data']['SignerType'] = 'Geschäftsführer';
            $Global->POST['Data']['Date'] = (new \DateTime())->format('d.m.Y');
            $Global->POST['Data']['Place'] = $CompanyPlace;
            $Global->savePost();
        }

//        $SelectBoxList = array();
//        $SelectBoxList[] = new FormColumn(
//            new Title(new TileBig().' Auswahl Schule')
//        );
//        if(isset($Warning)){
//            $SelectBoxList[] = new FormColumn($Warning);
//        } else {
//            foreach($tblSchoolAll as $tblSchool){
//                $tblCompany = $tblSchool->getServiceTblCompany();
//                if($tblCompany){
//
//                    $tblCompanyAddress = Address::useService()->getAddressByCompany($tblCompany);
//                    if(count($SelectBoxList) == 1){
//                        $Global->POST['Data']['Company'] = $tblCompany->getId();
//                        $Global->savePost();
//                        $SelectBoxList[] = new FormColumn(
//                            new Panel('Schule',
//                                (new RadioBox('Data[Company]',
//                                    $tblCompany->getName()
//                                    .( $tblCompany->getExtendedName() != '' ?
//                                        new Container($tblCompany->getExtendedName()) : null )
//                                    .( $tblCompanyAddress ?
//                                        new Container($tblCompanyAddress->getGuiTwoRowString()) : null )
//                                    , $tblCompany->getId()))
//                                , Panel::PANEL_TYPE_INFO)
//                            , 4);
//                    } else {
//                        $SelectBoxList[] = new FormColumn(
//                            new Panel('Schule',
//                                new RadioBox('Data[Company]',
//                                    $tblCompany->getName()
//                                    .( $tblCompany->getExtendedName() != '' ?
//                                        new Container($tblCompany->getExtendedName()) : null )
//                                    .( $tblCompanyAddress ?
//                                        new Container($tblCompanyAddress->getGuiTwoRowString()) : null )
//                                    , $tblCompany->getId())
//                                , Panel::PANEL_TYPE_INFO)
//                            , 4);
//                    }
//                }
//            }
//        }

        return new Form(
            new FormGroup(array(
                new FormRow(
                    new FormColumn(
                        new SelectBox('Data[GroupByCount]', 'Listenauswahl für den Download', $SelectBoxContent)
                    )
                ),
//                new FormRow(
//                    $SelectBoxList
//                ),
                new FormRow(array(
                    new FormColumn(
                        new HiddenField('Data[GroupByTime]')
                        , 4),
                    new FormColumn(
                        new HiddenField('Data[IsParent]')
                        , 1),
                )),
                new FormRow(array(
                    new FormColumn(
                        new Title(new TileBig().' Informationen Ansprechpartner')
                    ),
                    new FormColumn(
                        new Panel('Person',
                            new TextField('Data[ContactPerson]', '', 'Name')
                            ,Panel::PANEL_TYPE_INFO)
                        , 4
                    ),
                    new FormColumn(
                        new Panel('Kontaktinformation',array(
                            new TextField('Data[Phone]', '', 'Telefon'),
                            new TextField('Data[Fax]', '', 'Fax'),
                        ),Panel::PANEL_TYPE_INFO)
                        , 4
                    ),
                    new FormColumn(
                        new Panel('Internet Präsenz',array(
                            new TextField('Data[Mail]', '', 'E-Mail'),
                            new TextField('Data[Web]', '', 'Internet')
                        ), Panel::PANEL_TYPE_INFO)
                        , 4
                    ),
                )),
                new FormRow(array(
                    new FormColumn(
                        new Title(new TileBig().' Informationen Unterzeichner')
                    ),
                    new FormColumn(
                        new Panel('Unterzeichner', array(
                            new TextField('Data[SignerName]', '', 'Name'),
                            new TextField('Data[SignerType]', '', 'Funktion'),
                        ), Panel::PANEL_TYPE_INFO)
                        , 4
                    ),
                    new FormColumn(
                        new Panel('Ort, Datum', array(
                            new TextField('Data[Place]', '', 'Ort'),
                            new TextField('Data[Date]', '', 'Datum')
                        ), Panel::PANEL_TYPE_INFO)
                        , 4
                    ),
                )),
            )), new Primary('Überprüfen & Weiter', null, true) , '\Api\Document\Standard\MultiPassword\Create'
        );
    }

    /**
     * @return Layout
     */
    public function openAccountModal()
    {

        return new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(
                        new InfoMessage('Dieser Vorgang kann einige Zeit in Anspruch nehmen'
                            .new Container((new ProgressBar(0, 100, 0, 10))
                                ->setColor(ProgressBar::BAR_COLOR_SUCCESS, ProgressBar::BAR_COLOR_SUCCESS))
                        )
                    ),
                    new LayoutColumn(self::receiverAccountService())
                ))
            )
        );
    }

    /**
     * @param array $PersonIdArray
     * @param       $Type
     *
     * @return Pipeline
     */
    public function serviceAccount($PersonIdArray = array(), $Type)
    {

        $result = Account::useService()->createAccount($PersonIdArray, $Type);
        return self::pipelineSaveAccountResult($result, $Type);
    }

    /**
     *
     * @var array    $result
     *
     * @param string $Type
     *
     * @return Pipeline
     */
    public static function pipelineSaveAccountResult($result = array(), $Type = 'S')
    {
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverAccountModal(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'openAccountModalResult'
        ));
        $Emitter->setPostPayload(array(
            'result' => $result,
            'Type'   => $Type
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param array  $result
     * @param string $Type
     *
     * @return string
     */
    public function openAccountModalResult($result = array(), $Type = 'S')
    {

        $Content = '';
        $Time = false;
        if (isset($result['Time']) && $result['Time']) {
            $Time = $result['Time'];
        }

        if (isset($result['AccountExistCount']) && $result['AccountExistCount'] > 0) {
            $Content .= new WarningMessage($result['AccountExistCount'].' Personen haben bereits einen Account (ignoriert)');
        }
        if (isset($result['AddressMissCount']) && $result['AddressMissCount'] > 0) {
            $Content .= new WarningMessage($result['AddressMissCount'].' Personen ohne Hauptadresse (ignoriert)');
        }
        if (isset($result['SuccessCount']) && $result['SuccessCount'] > 0) {
            $Content .= new SuccessMessage($result['SuccessCount'].' Benutzer wurden erfolgreich angelegt.');
        }

        $BackwardRoute = '/Setting/User/Account/Student/Add';
        if ($Type == 'C') {
            $BackwardRoute = '/Setting/User/Account/Custody/Add';
        }

        if ($Content == '') {
            $Content = new DangerMessage('Es wurden keine Benutzer angelegt')
                .new Container(new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(new Center(new Standard('Zurück', $BackwardRoute)))
                        )
                    )
                ));
        } else {
            $Content .= new Container(new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(new Center(new Standard('Zurück', $BackwardRoute)
                            .new Standard('Export', '/Setting/User/Account/Export', null, array('Time' => $Time))))
                    )
                )
            ));
        }

        return $Content;
//            .new Code(print_r($result, true));
    }
}