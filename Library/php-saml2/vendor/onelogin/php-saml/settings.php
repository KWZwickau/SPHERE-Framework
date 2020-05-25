<?php

$settings = array(
    // If 'strict' is True, then the PHP Toolkit will reject unsigned
    // or unencrypted messages if it expects them signed or encrypted
    // Also will reject the messages if not strictly follow the SAML
    // standard: Destination, NameId, Conditions ... are validated too.
    'strict' => true,

    // Enable debug mode (to print errors)
    'debug' => true,

    // Set a BaseURL to be used instead of try to guess
    // the BaseURL of the view that process the SAML Message.
    // Ex. http://sp.example.com/
    //     http://example.com/sp/
    'baseurl' => null,

    // Service Provider Data that we are deploying
    'sp' => array(
        // Identifier of the SP entity  (must be a URI)
        'entityId' => 'https://www.schulsoftware.schule/Platform/Gatekeeper/Saml/MetaData',
        // Specifies info about where and how the <AuthnResponse> message MUST be
        // returned to the requester, in this case our SP.
        'assertionConsumerService' => array(
            // URL Location where the <Response> from the IdP will be returned
            'url' => 'https://www.schulsoftware.schule/Platform/Gatekeeper/Authentication/Saml',
            // SAML protocol binding to be used when returning the <Response>
            // message.  Onelogin Toolkit supports for this endpoint the
            // HTTP-POST binding only
            'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
        ),
//        // If you need to specify requested attributes, set a
//        // attributeConsumingService. nameFormat, attributeValue and
//        // friendlyName can be omitted. Otherwise remove this section.
//        "attributeConsumingService"=> array(
//                "serviceName" => "Schulsoftware",
//                "serviceDescription" => "SSO Schulsoftware",
//                "requestedAttributes" => array(
//                    array(
//                        "name" => "record_uid",
//                        "isRequired" => true,
//                        "nameFormat" => "",
//                        "friendlyName" => "",
//                        "attributeValue" => ""
//                    )
//                )
//        ),
        // Specifies info about where and how the <Logout Response> message MUST be
        // returned to the requester, in this case our SP.
        'singleLogoutService' => array(
            // URL Location where the <Response> from the IdP will be returned
            'url' => 'https://schulsoftware.schule/Platform/Gatekeeper/Logout', //ToDO Logout für Univention erstellen
            // SAML protocol binding to be used when returning the <Response>
            // message.  Onelogin Toolkit supports for this endpoint the
            // HTTP-Redirect binding only
            'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
        ),
        // Specifies constraints on the name identifier to be used to
        // represent the requested subject.
        // Take a look on lib/Saml2/Constants.php to see the NameIdFormat supported
        'NameIDFormat' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',

        // Usually x509cert and privateKey of the SP are provided by files placed at
        // the certs folder. But we can also provide them with the following parameters
        'x509cert' => '',
        'privateKey' => '',

        /*
         * Key rollover
         * If you plan to update the SP x509cert and privateKey
         * you can define here the new x509cert and it will be 
         * published on the SP metadata so Identity Providers can
         * read them and get ready for rollover.
         */
        // 'x509certNew' => '',
    ),

    // Identity Provider Data that we want connect with our SP
    'idp' => array(
        // Identifier of the IdP entity  (must be a URI)
        'entityId' => 'https://sso.evssn.de/simplesamlphp/saml2/idp/metadata.php',
        // SSO endpoint info of the IdP. (Authentication Request protocol)
        'singleSignOnService' => array(
            // URL Target of the IdP where the SP will send the Authentication Request Message
            'url' => 'https://sso.evssn.de/simplesamlphp/saml2/idp/SSOService.php',
            // SAML protocol binding to be used when returning the <Response>
            // message.  Onelogin Toolkit supports for this endpoint the
            // HTTP-Redirect binding only
            'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
        ),
        // SLO endpoint info of the IdP.
        'singleLogoutService' => array(
            // URL Location of the IdP where the SP will send the SLO Request
            'url' => 'https://sso.evssn.de/simplesamlphp/saml2/idp/SingleLogoutService.php',
            // URL location of the IdP where the SP will send the SLO Response (ResponseLocation)
            // if not set, url for the SLO Request will be used
            'responseUrl' => '',
            // SAML protocol binding to be used when returning the <Response>
            // message.  Onelogin Toolkit supports for this endpoint the
            // HTTP-Redirect binding only
            'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
        ),
        // Public x509 certificate of the IdP
        'x509cert' => 'MIIFIjCCBAqgAwIBAgIBEDANBgkqhkiG9w0BAQsFADCBvTELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAlVTMQswCQYDVQQHEwJVUzELMAkGA1UEChMCVVMxJDAiBgNVBAsTG1VuaXZlbnRpb24gQ29ycG9yYXRlIFNlcnZlcjE6MDgGA1UEAxMxVW5pdmVudGlvbiBDb3Jwb3JhdGUgU2VydmVyIFJvb3QgQ0EgKElEPWFEc1JjdWVkKTElMCMGCSqGSIb3DQEJARYWc3NsQGNvbm5leGlvbi5ldnNzbi5kZTAeFw0yMDA0MTcxMzAwMjFaFw0yNTA0MTYxMzAwMjFaMIGYMQswCQYDVQQGEwJVUzELMAkGA1UECBMCVVMxCzAJBgNVBAcTAlVTMQswCQYDVQQKEwJVUzEkMCIGA1UECxMbVW5pdmVudGlvbiBDb3Jwb3JhdGUgU2VydmVyMRUwEwYDVQQDEwxzc28uZXZzc24uZGUxJTAjBgkqhkiG9w0BCQEWFnNzbEBjb25uZXhpb24uZXZzc24uZGUwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQCpKw0UquLIroqnlTycIshp8DOP4Adxtt1i0rPBVUNQRMz/u3+YTEhKtkcbgRX6yoN4SnAILaI7vecHvUYTVprl4hn7yYSO/8b/a2sqiYn0r9vjhWjAVL/rA/o4WIjG3P1mIzooItaGocSwIHNtyddh3atP3cmfB1oSGkfP+ndNAlPWGZH9LFpQJKDnVsPM1J8FAo2DJMKEheTHN898DhS/DY/6zgeiK0+e4TMtz5fP2lg/lf6+qnYyTEJrvi44tuW/rPnGbtPX6lX9tsZBGpPa3PRmzK5rmN1lJhbf/qP1PTyj9zc/NokDVFrkwgp4s5Ltj4yANNVudisYraTTfKFZAgMBAAGjggFOMIIBSjAJBgNVHRMEAjAAMB0GA1UdDgQWBBRzAqOQi8hL3Swj0mhVoEOhH937WTCB8gYDVR0jBIHqMIHngBTuXFmcAnv5NQU3oD926U76si8GJ6GBw6SBwDCBvTELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAlVTMQswCQYDVQQHEwJVUzELMAkGA1UEChMCVVMxJDAiBgNVBAsTG1VuaXZlbnRpb24gQ29ycG9yYXRlIFNlcnZlcjE6MDgGA1UEAxMxVW5pdmVudGlvbiBDb3Jwb3JhdGUgU2VydmVyIFJvb3QgQ0EgKElEPWFEc1JjdWVkKTElMCMGCSqGSIb3DQEJARYWc3NsQGNvbm5leGlvbi5ldnNzbi5kZYIJAOw3DOYtSVKKMAsGA1UdDwQEAwIF4DAcBgNVHREEFTATggxzc28uZXZzc24uZGWCA3NzbzANBgkqhkiG9w0BAQsFAAOCAQEAk+WeK6WbWdrzITEmNWIzy62YDOGPyOxeyZjUSkOx/NhOLPx/zd57lzrIFGEL31WSzRJz1fJ/Xlnt41zYI1FlGDGzgYs0slol+0j44hVtcZrfnoPkySTC7hU+68HJ3lz/MApFh1PYaaCRQMGpjdqyUdXR+qVQzuIKV+hnYCjZMTPSyNsgSjsgBEN6ojFrih+J/AjiR2korskaMnH9aufZOmI8C9mTOti8lSlQsMQdD6W7uEPivhP0GkmC8em0IgrwjTAKhne2hoCyZeiGtXAJeAZe2V6Uf63HGk4XPu5gG/99bsqba2WPUzBrFdfuvqhRXvlB0yds+/g5vuN//ngplw==',
        /*
         *  Instead of use the whole x509cert you can use a fingerprint in
         *  order to validate the SAMLResponse, but we don't recommend to use
         *  that method on production since is exploitable by a collision
         *  attack.
         *  (openssl x509 -noout -fingerprint -in "idp.crt" to generate it,
         *   or add for example the -sha256 , -sha384 or -sha512 parameter)
         *
         *  If a fingerprint is provided, then the certFingerprintAlgorithm is required in order to
         *  let the toolkit know which Algorithm was used. Possible values: sha1, sha256, sha384 or sha512
         *  'sha1' is the default value.
         */
        // 'certFingerprint' => '',
        // 'certFingerprintAlgorithm' => 'sha1',

        /* In some scenarios the IdP uses different certificates for
         * signing/encryption, or is under key rollover phase and more 
         * than one certificate is published on IdP metadata.
         * In order to handle that the toolkit offers that parameter.
         * (when used, 'x509cert' and 'certFingerprint' values are
         * ignored).
         */
        // 'x509certMulti' => array(
        //      'signing' => array(
        //          0 => '<cert1-string>',
        //      ),
        //      'encryption' => array(
        //          0 => '<cert2-string>',
        //      )
        // ),
    ),
);
