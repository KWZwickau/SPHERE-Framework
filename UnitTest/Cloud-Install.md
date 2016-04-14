Everything with --no-install-recommends

- SSH for Putty (Copy/Paste)
- Nano for Editing

Balancer
========
Install
-------
- apache2
- ufw

Config
------
- Console:


    a2dismod mpm_event mpm_prefork
    a2enmod mpm_worker
    a2enmod rewrite deflate headers
    a2enmod proxy proxy_http proxy_balancer lbmethod_byrequests
    a2enmod ssl
- Console:


    ufw default deny
    ufw allow 80
    ufw allow 443
    ufw enable

Node
====
Install
-------
- apache2
- php5
- php5-gmp
- php5-mysql
- php5-curl
- php5-apcu
- php5-memcached
- proftpd

Config
------
- a2enmod rewrite headers

- File: /etc/apache2/apache2.conf


    <Directory /var/www/>
            Options Indexes FollowSymLinks
            AllowOverride All
            Require all granted
    </Directory>

- File: /etc/apache2/sites-available/000-default.conf


    DocumentRoot /var/www

- File: /etc/php5/mods-available/apcu.ini


    apc.shm_segments=1
    apc.shm_size=256M
    apc.optimization=0
    apc.num_files_hint=2048
    apc.ttl=3600
    apc.user_ttl=3600
    apc.enable_cli=1
    apc.max_file_size=1M

- File: /etc/php5/apache2/php.ini


    [Session]
    session.save_handler = memcached
    session.save_path = "<server>:11211"

- File /etc/proftpd/proftpd.conf


    ServerType standalone
    DefaultServer on
    Umask 002
    ServerName "0.0.0.0"
    ServerIdent on "Sphere-Development-Server"
    ServerAdmin gerdchristian.kunze@haus-der-edv.de
    IdentLookups off
    UseReverseDNS off
    Port 21
    PassivePorts 49152 65534
    TimesGMT off
    MaxInstances 30
    MaxLoginAttempts 3
    TimeoutLogin 300
    TimeoutNoTransfer 120
    TimeoutIdle 120
    DisplayLogin welcome.msg
    DisplayChdir .message
    User nobody
    Group www-data
    DirFakeUser off nobody
    DirFakeGroup off nobody
    DefaultTransferMode binary
    AllowForeignAddress off
    AllowRetrieveRestart on
    AllowStoreRestart on
    DeleteAbortedStores off
    TransferRate RETR 0
    TransferRate STOR 0
    TransferRate STOU 0
    TransferRate APPE 0
    SystemLog /var/log/secure
    RequireValidShell off
    <IfModule mod_tls.c>
    TLSEngine off
    TLSRequired off
    TLSVerifyClient off
    TLSProtocol SSLv23
    TLSLog /var/log/proftpd_tls.log
    TLSRSACertificateFile /etc/gadmin-proftpd/certs/cert.pem
    TLSRSACertificateKeyFile /etc/gadmin-proftpd/certs/key.pem
    TLSCACertificateFile /etc/gadmin-proftpd/certs/cacert.pem
    TLSRenegotiate required off
    TLSOptions AllowClientRenegotiation
    </IfModule>
    <IfModule mod_ratio.c>
    Ratios off
    SaveRatios off
    RatioFile "/restricted/proftpd_ratios"
    RatioTempFile "/restricted/proftpd_ratios_temp"
    CwdRatioMsg "Please upload first!"
    FileRatioErrMsg "FileRatio limit exceeded, upload something first..."
    ByteRatioErrMsg "ByteRatio limit exceeded, upload something first..."
    LeechRatioMsg "Your ratio is unlimited."
    </IfModule>
    <Limit LOGIN>
      AllowUser administrator
      DenyALL
    </Limit>
    
    <Anonymous /var/www>
    User administrator
    Group www-data
    AnonRequirePassword on
    MaxClients 10 "The server is full, hosting %m users"
    DisplayLogin welcome.msg
    DisplayChdir .msg
    <Limit LOGIN>
    Allow from All
    Deny from all
    </Limit>
    AllowOverwrite on
    <Limit LIST NLST  STOR STOU  APPE  RETR  RNFR RNTO  DELE  MKD XMKD SITE_MKDIR  RMD XRMD SITE_RMDIR  SITE  SITE_CHMOD  SITE_CHGRP  MTDM  PWD XPWD  SIZE  STAT  CWD XCWD  CDUP XCUP >
     AllowAll
    </Limit>
    <Limit NOTHING >
     DenyAll
    </Limit>
    </Anonymous>

- Access


    chown -R administrator:www-data /var/www
    chmod -R 0775 /var/www

Data
====
Install
-------
- mysql-server-5.6

Config
------
- File: /etc/mysql/my.cnf


    # bind-address = 127.0.0.1
    lower_case_table_names = 0

- Console:


    mysql -u route -p
    use mysql;
    update user set host='%' where host='localhost';

Cache
=====
Install
-------
- memcached

Config
------
- File: /etc/memcached.conf


    # -l 127.0.0.1
