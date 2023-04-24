<?php
namespace SPHERE\Application\Setting\Univention;

use SPHERE\Application\Setting\Univention\Service\Entity\TblUnivention;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Text\Repository\Bold;

class UniventionUser
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
     * @param string $property // name, firstname, lastname, birthday, record_uid (alle Properties "Resource Users")
     * @param string $value // Suche nach Mandanten Beispiel: "ref-"
     * @param bool   $fromFirstChar
     *
     * @return array|bool
     */
    public function getUserListByProperty($property = 'name', $value = '', $fromFirstChar = true)
    {

        curl_reset($this->curlhandle);

        if($fromFirstChar){
            $Url = 'https://'.$this->server.'/v1/users/?'.$property.'='.$value.'%2A';
        } else {
            $Url = 'https://'.$this->server.'/v1/users/?'.$property.'=%2A'.$value.'%2A';
        }

        curl_setopt_array($this->curlhandle, array(
            CURLOPT_URL => $Url,
            CURLOPT_HTTPGET => TRUE,
            CURLOPT_SSL_VERIFYHOST => FALSE,
            CURLOPT_HTTPHEADER => array('accept: application/json',
                'Authorization: bearer '.$this->token),
            //return the transfer as a string
            CURLOPT_RETURNTRANSFER => TRUE
//            CURLOPT_POSTFIELDS => $Content
        ));

        $Json = curl_exec($this->curlhandle);
        // Object to Array
        $StdClassAsArray = json_decode($Json, true);

        $UserList = array();
        if(is_array($StdClassAsArray) && !empty($StdClassAsArray)){
            foreach($StdClassAsArray as $User){
                $UserList[] = $User;
            }
        }
        return (is_array($UserList) && !empty($UserList) ? $UserList : false);
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
    public function createUser($name = '', $email = '', $firstname = '', $lastname = '', $record_uid = '', $roles = array(),
        $schools = array(), $school_classes = array(), $recoveryMail = '')
    {
        curl_reset($this->curlhandle);

        $PersonContent = array(
            'name' => $name,
//            'mailPrimaryAddress' => $email,
            'email' => $email,
            'firstname' => $firstname,
            'lastname' => $lastname,
            // AccountId
            'record_uid' => $record_uid,
            'roles' => $roles,
            'schools' => $schools,
            'school_classes' => $school_classes,
            'udm_properties' => array("PasswordRecoveryEmail" => $recoveryMail)
//            'udm_properties' => array('pwdChangeNextLogin' => true),
//            'kelvin_password_hashes' => array(
//                'user_password' => array($password),
//                'samba_nt_password' => 'string',
//                'krb_5_key' => array(),
//                'krb5_key_version_number' => 0,
//                'samba_pwd_last_set' => 0,
//            ),
            // Mandant + AccountId
//            'source_uid' => $source_uid // kann raus, ist nur für den CSV Import wichtig
        );
        $PersonContent = json_encode($PersonContent);
//        echo'<pre>';
//        echo 'Gesendete Daten:';
//        var_dump($PersonContent);
//        echo'</pre>';
//        $PersonContent = http_build_query($PersonContent);

        curl_setopt_array($this->curlhandle, array(
            CURLOPT_URL => 'https://'.$this->server.'/v1/users/',
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
        - dn
        - url
        - ucsschool_roles
        - name
        - school
        - firstname
        - lastname
        - birthday
        - disabled
        - email
        - record_uid
        - roles
        - schools
        - school_classes
        - source_uid
        - udm_properties { description, gidNumber, employeeType, organisation, phone, title, uidNumber }
         **/
        $Json = $this->execute($this->curlhandle);
//        echo'<pre>';
//        var_dump('Zeitstempel: '.(new \DateTime())->format('d.m.Y H:i:s'));
//        var_dump('Antwort:');
//        var_dump($Json);
//        echo'</pre>';

        // return Server error as an Error
        if($Json == 'Internal Server Error'){
            return $name.' '.new Bold('UCS: Internal Server Error');
        }
        if($Json == 'Bad Gateway'){
            return $name.' '.new Bold('UCS: Bad Gateway');
        }

        // Object to Array
        $StdClassArray = json_decode($Json, true);
        $Error = null;
        try{
            if(isset($StdClassArray['detail'])){
                if(is_string($StdClassArray['detail'])){
                    $Error = new Bold($name.': ').$StdClassArray['detail'];
                }elseif(is_array($StdClassArray['detail'])){
                    $Error = '';
                    foreach($StdClassArray['detail'] as $Detail){
                        if($Detail['msg']){
                            $Error .= new Bold($name.'-> ').$Detail['loc'].':'.$Detail['msg'];
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $Error = $e;
            $Error .= new Container($Json);
            return $Error;
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
    public function updateUser($name = '', $email = '', $firstname = '', $lastname = '', $record_uid = '', $roles = array(),
        $schools = array(), $school_classes = array(), $recoveryMail = '')
    {
        curl_reset($this->curlhandle);

        // Verwendet für die Api "school" das erste element aus der Liste, da keine genauere Auswahl getroffen werden kann
        $school = current($schools);

        $PersonContent = array(
            'name' => $name,
        // keine reaktion der API auf dieses Feld
//            'mailPrimaryAddress' => $email,
        // letze Info email = mailPrimaryAddress,
            'email' => $email,
            // Weiteres E-Mail feld, welches als UDM Propertie zurück kommt ("e-mail") ist aber ein Array und für unsere Zwecke nicht zu verwenden
//            'mail' => $email,
            'firstname' => $firstname,
            'lastname' => $lastname,
            // AccountId
            'record_uid' => $record_uid,
            // Orientierung an connexion-ssw
            'source_uid' => 'connexion-ssw',
            'roles' => $roles,
            'school' => $school, // one school
            'schools' => $schools, // array school
            'school_classes' => $school_classes,
            'udm_properties' => array("PasswordRecoveryEmail" => $recoveryMail)
            // Mandant + AccountId to human resolve problems?
//            'source_uid' => $source_uid
        );

//        Debugger::devDump($PersonContent);

        $PersonContent = json_encode($PersonContent);
//        $PersonContent = http_build_query($PersonContent);

//        Debugger::devDump($PersonContent);

        curl_setopt_array($this->curlhandle, array(
            CURLOPT_URL => 'https://'.$this->server.'/v1/users/'.$name,
            CURLOPT_CUSTOMREQUEST => 'PUT',
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
        - dn
        - url
        - ucsschool_roles
        - name
        - school
        - firstname
        - lastname
        - birthday
        - disabled
        - email
        - record_uid
        - roles
        - schools
        - school_classes
        - source_uid
        - udm_properties { description, gidNumber, employeeType, organisation, phone, title, uidNumber }
         **/
        $Json = $this->execute($this->curlhandle);
//        echo '<pre>';
//        var_dump($Json);
//        var_dump((new \DateTime('now'))->format('H:i:s d.m.Y'));
//        echo '</pre>';

        // return Server error as an Error
        if($Json == 'Internal Server Error'){
            return $name.' '.new Bold('UCS: Internal Server Error');
        }
        if($Json == 'Bad Gateway'){
            return $name.' '.new Bold('UCS: Bad Gateway');
        }
        // Object to Array
        $StdClassArray = json_decode($Json, true);
        $Error = null;
        try{
            if(isset($StdClassArray['detail'])){
                if(is_string($StdClassArray['detail'])){
                    $Error = new Bold($name.': ').$StdClassArray['detail'];
                }elseif(is_array($StdClassArray['detail'])){
                    $Error = '';
                    foreach($StdClassArray['detail'] as $Detail){
                        if($Detail['msg'] && is_string($Detail['loc'])){
                            $Error .= new Bold($name.' → ').$Detail['loc'].':'.$Detail['msg'];
                        } elseif($Detail['msg'] && !empty($Detail['loc'])) {
                            $Error .= new Bold($name.' → ').print_r($Detail['loc'], true).':'.$Detail['msg'];
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $Error = $e;
            $Error .= new Container($Json);
            return $Error;
        }

        return $Error;
    }

    /**
     * @param string $AccountName
     *
     * @return string|null
     */
    public function deleteUser(string $AccountName = '')
    {

        curl_reset($this->curlhandle);
        // löschen durch Nutnername
        if($AccountName == ''){
            return 'Benutzername nicht gefunden';
        }

        curl_setopt_array($this->curlhandle, array(
            CURLOPT_URL => 'https://'.$this->server.'/v1/users/'.$AccountName,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_SSL_VERIFYHOST => FALSE,
            CURLOPT_HTTPHEADER => array('Authorization: bearer '.$this->token),
            //return the transfer as a string
            CURLOPT_RETURNTRANSFER => TRUE,
        ));
        // prevent "Bad Gateway" for every second try
        sleep(1);
        $Json = $this->execute($this->curlhandle);
//        echo'<pre>';
//        var_dump('Zeitstempel: '.(new \DateTime())->format('d.m.Y H:i:s'));
//        var_dump('Antwort:');
//        var_dump($Json);
//        echo'</pre>';
        // return Server error as an Error
        if($Json == 'Internal Server Error'){
            return $AccountName.' '.new Bold('UCS: Internal Server Error');
        }
        if($Json == 'Bad Gateway'){
            return $AccountName.' '.new Bold('UCS: Bad Gateway');
        }
        // Object to Array
        $StdClassArray = json_decode($Json, true);
        $Error = null;
        try{
            if(isset($StdClassArray['detail'])){
                if(is_string($StdClassArray['detail'])){
                    $Error = new Bold($AccountName.': ').$StdClassArray['detail'];
                }elseif(is_array($StdClassArray['detail'])){
                    $Error = '';
                    foreach($StdClassArray['detail'] as $Detail){
                        if($Detail['msg']){
                            $Error .= new Bold($AccountName.'-> ').$Detail['loc'].':'.$Detail['msg'];
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $Error = $e;
            $Error .= new Container($Json);
            return $Error;
        }

        return $Error;
    }

    /**
     * @param array $AccountArray
     *
     * @return string|null
     */
    public function deleteUserByName($name)
    {

        curl_reset($this->curlhandle);

        curl_setopt_array($this->curlhandle, array(
            CURLOPT_URL => 'https://'.$this->server.'/v1/users/'.$name,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_SSL_VERIFYHOST => FALSE,
            CURLOPT_HTTPHEADER => array('Authorization: bearer '.$this->token),
            //return the transfer as a string
            CURLOPT_RETURNTRANSFER => TRUE,
        ));

        $Json = $this->execute($this->curlhandle);

        // Object to Array
        $StdClassArray = json_decode($Json, true);
        $Error = null;
        try{
            if(isset($StdClassArray['detail'])){
                if(is_string($StdClassArray['detail'])){
                    $Error = new Bold($name.': ').$StdClassArray['detail'];
                }elseif(is_array($StdClassArray['detail'])){
                    $Error = '';
                    foreach($StdClassArray['detail'] as $Detail){
                        if($Detail['msg']){
                            $Error .= new Bold($name.'-> ').$Detail['loc'].':'.$Detail['msg'];
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $Error = $e;
            $Error .= new Container($Json);
            return $Error;
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