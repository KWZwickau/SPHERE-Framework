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
- File: /etc/php5/mods-available/apcu.conf


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
- couchbase

Config
------
- File: /etc/memcached.conf


    # -l 127.0.0.1


- Couchbase
- php5-dev
- 
