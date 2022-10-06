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

        $PersonContent = array(
            'name' => $group,
            'school' => $school,
            'users' => $UserList,
        );
        $PersonContent = json_encode($PersonContent);
//        return $PersonContent;
        //  ToDO Refactor to correct API
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
        if($Json == 'Bad Gateway'){
            return $group.' '.new Bold('UCS: Bad Gateway');
        }

        // Object to Array
        $StdClassArray = json_decode($Json, true);
        $Error = null;
        if(isset($StdClassArray['detail'])){
            if(is_string($StdClassArray['detail'])){
                $Error = new Bold($group.': ').$StdClassArray['detail'];
            }elseif(is_array($StdClassArray['detail'])){
                $Error = '';
                foreach($StdClassArray['detail'] as $Detail){
                    if($Detail['msg']){
                        $Error .= new Bold($group.': ').$Detail['msg'];
                    }
                }
            }
        }

        return $Error;
    }

    /**
     * @param string $name
     * @param string $email
     * @param string $firstname
     * @param string $lastname
     * @param string $record_uid
     * @param array  $roles
     * @param array  $schools
     * @param array  $school_classes
     * @param string $recoveryMail
     *
     * @return string|null
     */
    public function updateUserWorkgroup($group = '', $Acronym = '', $UserList = array())
    {

        curl_reset($this->curlhandle);

        foreach($UserList as &$Name){
            $Name = 'https://'.$this->server.'/v1/users/'.$Name;
        }
//        $NameList = array(current($NameList));

        $PostFields = array(
            'users' => $UserList
        );
        $PostFields = json_encode($PostFields);

//        return $PersonContent;
        //  ToDO Refactor to correct API
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
        if($Json == 'Internal Server Error'){
            return $group.' '.new Bold('UCS: Internal Server Error');
        }
        if($Json == 'Bad Gateway'){
            return $group.' '.new Bold('UCS: Bad Gateway');
        }
        // Object to Array
        $StdClassArray = json_decode($Json, true);
        $Error = null;
        if(isset($StdClassArray['detail'])){
            if(is_string($StdClassArray['detail'])){
                $Error = new Bold($group.': ').$StdClassArray['detail'];
            }elseif(is_array($StdClassArray['detail'])){
                $Error = '';
                foreach($StdClassArray['detail'] as $Detail){
                    if($Detail['msg']){
                        $Error .= new Bold($group.'-> ').$Detail['loc'].':'.$Detail['msg'];
                    }
                }
            }
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