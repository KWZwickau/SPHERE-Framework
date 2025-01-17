<?php
namespace SPHERE\Application\Setting\Univention;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Setting\Univention\Service\Entity\TblUnivention;
use SPHERE\Common\Frontend\Text\Repository\Bold;

class UniventionWorkGroup
{
    private $curlhandle;
    private $server;
    private $token;

    public function __construct() {

        if(($tblUnivention = Univention::useService()->getUnivention(TblUnivention::TYPE_VALUE_SERVER))){
            $this->server = $tblUnivention->getValue();
        }
        if(($tblUnivention = Univention::useService()->getUnivention(TblUnivention::TYPE_VALUE_TOKEN))){
            $this->token = $tblUnivention->getValue();
        }
        $this->curlhandle = curl_init();
    }

    /**
     * @return array|false
     */
    public function getWorkGroupListAll()
    {

        curl_reset($this->curlhandle);
        $tblConsumer = Consumer::useService()->getConsumerBySession();
        $Url = 'https://'.$this->server.'/v1/workgroups/?school='.$tblConsumer->getAcronym();

//        Debugger::devDump(array(
//            CURLOPT_URL => $Url,
//            CURLOPT_HTTPGET => TRUE,
//            CURLOPT_SSL_VERIFYHOST => FALSE,
//            CURLOPT_HTTPHEADER => array('accept: application/json', 'Authorization: bearer '.$this->token),
//            CURLOPT_RETURNTRANSFER => TRUE
//        ));

        curl_setopt_array($this->curlhandle, array(
            CURLOPT_URL => $Url,
            CURLOPT_HTTPGET => TRUE,
            CURLOPT_SSL_VERIFYHOST => FALSE,
            CURLOPT_HTTPHEADER => array('accept: application/json', 'Authorization: bearer '.$this->token),
            CURLOPT_RETURNTRANSFER => TRUE
        ));
        $Json = curl_exec($this->curlhandle);
//        Debugger::devDump($Json);

        // Object to Array
        $StdClassAsArray = json_decode($Json, true);
//        Debugger::devDump($StdClassAsArray);

        $WorkGroupList = array();
        if(is_array($StdClassAsArray) && !empty($StdClassAsArray)){
            foreach($StdClassAsArray as $WorkGroup){
                $WorkGroupList[] = $WorkGroup;
            }
        }
        return (is_array($WorkGroupList) && !empty($WorkGroupList) ? $WorkGroupList : false);
    }

    /**
     * @param string $group
     * @param string $school
     *
     * @return string|null
     */
    public function createUserWorkgroup($group = '', $school = '',$UserList = array())
    {

        curl_reset($this->curlhandle);

        foreach($UserList as &$Name){
            $Name = 'https://'.$this->server.'/v1/users/'.$Name;
        }
        $UserNameList = array();
        foreach($UserList as $User){
            $UserNameList[] = $User;
        }

        // URL Fähiger Gruppenname -> Create braucht den normal!
//        $group = urlencode($group);
        $PersonContent = array(
            'name' => $group,
            'school' => $school,
            'users' => $UserNameList,
        );
        $PersonContent = json_encode($PersonContent);
//        return $PersonContent;
        curl_setopt_array($this->curlhandle, array(
            CURLOPT_URL => 'https://'.$this->server.'/v1/workgroups/',
            CURLOPT_POST => TRUE,
            CURLOPT_SSL_VERIFYHOST => FALSE,
            CURLOPT_HTTPHEADER => array('accept: application/json',
                'Content-Type: application/json',
                'Authorization: bearer '.$this->token),
            //return the transfer as a string
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_POSTFIELDS => $PersonContent
        ));

        /**
         * possible field's
         * User
         * Group
         **/
        $Json = $this->execute($this->curlhandle);
        // return Server error as an Error
        if($Json == 'Internal Server Error'){
            return $group.' '.new Bold('UCS: Internal Server Error');
        }
        if(false !== strpos($Json, 'Bad Gateway')){
            return $group.' '.new Bold('UCS: Bad Gateway');
        }
        if(false !== strpos($Json, 'Bad Request')){
            return $group.' '.new Bold('UCS: Bad Request');
        }
        if(false !== strpos($Json, 'Forbidden')){
            return $group.' '.new Bold('UCS: You don\'t have permission to access this resource.');
        }
        if(false !== ( $msPos = strpos($Json, '"msg":"'))){
            return $group.' '.new Bold(substr($Json, $msPos - 17));
        }
        // Object to Array
        $StdClassArray = json_decode($Json, true);
        $Error = null;
        if(isset($StdClassArray['message']) && $StdClassArray['message']){
            $Error .= new Bold($group.': ').$StdClassArray['message'];
        }

        return $Error;
    }

    /**
     * @param $group
     * @param $Acronym
     * @param $UserList
     *
     * @return string|null
     */
    public function updateUserWorkgroup($group = '', $Acronym = '', $UserList = array())
    {

        curl_reset($this->curlhandle);

        // URL Fähiger Gruppenname
        $groupName = $group;
         $group = str_replace(' ', '%20', $group);
         // urlencode macht aus ' ' -> '+'
//         $group = urlencode($group);

        foreach($UserList as &$Name){
            $Name = 'https://'.$this->server.'/v1/users/'.$Name;
        }
//        $UserList = array(current($UserList));
        // Id Problem, dies verhindert ein korrektes Update mit der KelvinAPI
        // Workaround umspeichern in ein neues Array
        $UserNameList = array();
        foreach($UserList as $User){
            $UserNameList[] = $User;
        }

        $PostFields = array(
            'users' => $UserNameList
        );
        $PostFields = json_encode($PostFields);

//        return $PersonContent;
        curl_setopt_array($this->curlhandle, array(
            CURLOPT_URL => 'https://'.$this->server.'/v1/workgroups/'.$Acronym.'/'.$group,
            CURLOPT_CUSTOMREQUEST => 'PATCH',
            CURLOPT_SSL_VERIFYHOST => FALSE,
            CURLOPT_HTTPHEADER => array('accept: application/json',
                'Content-Type: application/json',
                'Authorization: bearer '.$this->token),
            //return the transfer as a string
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_POSTFIELDS => $PostFields
        ));

        /**
         * possible field's
         * User
         * Group
         **/
        $Json = $this->execute($this->curlhandle);
//        Debugger::devDump(
//            'URL: '.'https://'.$this->server.'/v1/workgroups/'.$Acronym.'/'.$group.'</br>'.
//            'Request: PATCH'.'</br>'.
//            'header: '.'accept: application/json'.'</br>'.
//            'header: '.'Content-Type: application/json'.'</br>'.
//            'header: '.'Authorization: bearer '.$this->token.'</br>'.
//            'postFields: '.print_r($PostFields, true)
//        );
//        Debugger::devDump($Json);
//        if($groupName == '12Gy G-GK BIO'){
//            return $Json;
//        }
        if($Json == 'Internal Server Error'){
            return $groupName.' '.new Bold('UCS: Internal Server Error');
        }
        if(false !== strpos($Json, 'Bad Gateway')){
            return $groupName.' '.new Bold('UCS: Bad Gateway');
        }
        if(false !== strpos($Json, 'Bad Request')){
            return $groupName.' '.new Bold('UCS: Bad Request');
        }
        if(false !== ( $msPos = strpos($Json, '"msg":"'))){
//            return $Json;
            return $groupName.' '.new Bold(substr($Json, $msPos - 17));
        }
        // Object to Array
        $StdClassArray = json_decode($Json, true);
        $Error = null;
        if(isset($StdClassArray['message']) && $StdClassArray['message']){
            $Error .= new Bold($group.': ').$StdClassArray['message'];
        }

        return $Error;
    }

    private $retriableErrorCodes = [
        CURLE_COULDNT_RESOLVE_HOST,
        CURLE_COULDNT_CONNECT,
        CURLE_HTTP_NOT_FOUND,
        CURLE_READ_ERROR,
        CURLE_OPERATION_TIMEOUTED,
        CURLE_HTTP_POST_ERROR,
        CURLE_SSL_CONNECT_ERROR,
    ];

    /**
     * Executes a CURL request with optional retries and exception on failure
     *
     * @param  resource    $ch             curl handler
     * @param  int         $retries
     *
     * @return bool|string @see curl_exec
     */
    public function execute($ch, $retries = 5)
    {
        while ($retries--) {
            $curlResponse = curl_exec($ch);
            if ($curlResponse === false) {
                $curlErrno = curl_errno($ch);
                if (false === in_array($curlErrno, $this->retriableErrorCodes, true) || !$retries) {
                    echo curl_error($ch);
//                    if ($closeAfterDone) {
                    curl_close($ch);
//                    }
                    return null; //throw new \RuntimeException(sprintf('Curl error (code %d): %s', $curlErrno, $curlError));
                }
                continue;
            }
            // Verbindung wird nur für eine Verbindung benötigt
            // jede weitere Anfrage initialisiert eigene Verbindung
//            if ($closeAfterDone) {
            curl_close($ch);
//            }
            return $curlResponse;
        }
        return false;
    }
}