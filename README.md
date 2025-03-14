# remove old services
yum remove -y httpd
yum remove -y libopendkim*
yum remove -y opendkim
yum remove -y postfix
yum remove -y php*
rm -rf /etc/httpd
rm -rf /etc/opendkim*

# install new services 
yum install -y openssh-clients
yum install -y glibc.i686
yum install -y pam.i686 pam
yum install -y nano
yum install -y rsync
yum install -y wget
yum install -y xinetd
yum install -y gcc
yum install -y make
yum install -y httpd      
yum install -y perl
yum install -y mod_ssl
yum install -y zip
yum install -y unzip
yum update -y

# disable selinux
sudo setenforce 0
sudo setsebool -P httpd_can_network_connect on
sudo setsebool -P httpd_can_network_connect_db on

# check if selinux is disabled or not :

sestatus

#if not disabled :
nano /etc/selinux/config

change SELINUX=enforcing to SELINUX=disabled

sudo shutdown -r now

---------------------------------------------------------

# install php 
wget https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm
wget http://rpms.remirepo.net/enterprise/remi-release-7.rpm

rpm -Uvh epel-release-latest-7.noarch.rpm
rpm -Uvh remi-release-7*.rpm

------------------------------------

nano /etc/yum.repos.d/remi.repo

change these values :
#	[remi]
#		enabled=1
#	[remi-php56]
#		enabled=1

yum install -y php
yum install -y php-pgsql
yum install -y php-mysql
yum install -y php-common
yum install -y php-pdo
yum install -y php-opcache 
yum install -y php-mcrypt
yum install -y php-imap
yum install -y php-mbstring
yum install -y php-xmlrpc
yum install -y cronie
yum --enablerepo=remi install -y php-pecl-ssh2
yum --disablerepo=epel -y update  ca-certificates
rm -rf /home/epel-release-6-8.noarch.rpm /home/remi-release-6*.rpm

-----------------------------------

nano /etc/httpd/conf.d/bluemail.conf

#put this content in it and CHANGE domain.com with your main domain or with your ip ( don't forget to create subdomain app in your domain provider )

<VirtualHost *:80>
        ServerName 91.213.245.166
        DocumentRoot "/var/bluemail/"
        <Directory /var/bluemail/>
                AllowOverride all
                Options Indexes FollowSymLinks ExecCGI
                AddHandler cgi-script .cgi .pl
                Order Deny,Allow
                Allow from all
        </Directory>
</VirtualHost>

-------------------------------	
nano /etc/httpd/conf/httpd.conf

1- search for : <Directory />
then replace : Require all Denied
to : Require all granted

2- search for : conf.d/*.conf
then add this
NameVirtualHost *:80

nano /etc/sysconfig/network

# CHANGE domain.com with your main domain or with your ip

NETWORKING=yes
HOSTNAME=91.213.245.166
#GATEWAY=0.0.0.0
------------------------------

# unzip the app :

cd /var/
unzip bluemail.zip

systemctl restart httpd.service
sudo systemctl start httpd.service
sudo systemctl enable httpd.service

--------------------------------------------------------------

# Step 3: Configure the firewall
# You need to modify the default firewall configuration before you can access phpPgAdmin from a web browser:

sudo yum install firewalld

sudo systemctl start firewalld
sudo systemctl enable firewalld
sudo systemctl status firewalld

sudo firewall-cmd --zone=public --permanent --add-service=http
sudo firewall-cmd --zone=public --permanent --add-port=5432/tcp
sudo firewall-cmd --reload


# Step 4: Install PHP 7 and the necessary extensions
# phpPgAdmin is written in PHP, you need to install PHP 7 and some extensions to serve phpPgAdmin.

# Install PHP 7 on CentOS 7

sudo yum install http://rpms.remirepo.net/enterprise/remi-release-7.rpm

sudo yum install epel-release yum-utils

# Start by enabling the PHP 7.3 Remi repository:

sudo yum-config-manager --enable remi-php73

# Install PHP 7.3 and some of the most common PHP modules:

sudo yum install php php-common php-opcache php-mcrypt php-cli php-gd php-curl php-mysqlnd

# Verify the PHP installation, by typing the following command which will print the PHP version:

php -v

# Good Result exp :
# PHP 7.3.25 (cli) (built: Jul  7 2020 07:53:49) ( NTS )
# Copyright (c) 1997-2018 The PHP Group
# Zend Engine v3.3.20, Copyright (c) 1998-2018 Zend Technologies
#    with Zend OPcache v7.3.20, Copyright (c) 1999-2018, by Zend Technologies

--------------------------------------

# Step 5: Install and configure PostgreSQL

# 5.1) Use the following commands to install PostgreSQL 11 on your CentOS 7 server:

******************************************************************************************************************************
sudo yum install -y https://download.postgresql.org/pub/repos/yum/reporpms/EL-7-x86_64/pgdg-redhat-repo-latest.noarch.rpm
sudo yum install -y postgresql11-server
sudo /usr/pgsql-11/bin/postgresql-11-setup initdb
sudo systemctl enable postgresql-11
sudo systemctl start postgresql-11

yum install postgresql-server
******************************************************************************************************************************

# 5.2) Initiate the database:
service postgresql initdb

# 5.3) Setup PostgreSQL listening addresses:

nano /var/lib/pgsql/11/data/postgresql.conf

Find:

#listen_addresses = 'localhost'
modify it to:

listen_addresses = '*'

Find:

#port = 5432
modify it to:

port = 5432

Save and quit

# 5.5) Start the PostgreSQL service:

sudo systemctl start postgresql-11.service
sudo systemctl enable postgresql-11.service

# 5.6) Setup database user credentials:

sudo -u postgres psql

# In the psql shell: replace db_password with your own password

CREATE ROLE admin PASSWORD '53344Yd34e35' SUPERUSER CREATEDB CREATEROLE INHERIT LOGIN;

CREATE DATABASE bluemail_system;
CREATE DATABASE bluemail_lists;

# for exit enter :
\q

nano /var/lib/pgsql/11/data/pg_hba.conf

change this values :

# "local" is for Unix domain socket connections only
loca	all		all						peer
# IPv4 local connections:
host	all		all		127.0.0.1/32	ident
# IPv6 local connections:
host	all		all		::1/128			ident

to :

# "local" is for Unix domain socket connections only
loca	all		all						md5
# IPv4 local connections:
host	all		all		0.0.0.0/0		md5
# IPv6 local connections:
host	all		all		::1/128			md5

service postgresql-11 restart
sudo systemctl start postgresql-11.service
sudo systemctl enable postgresql-11.service

--------------------------------

# Step 6: Install and Use phpPgAdmin
# Install phpPgAdmin with the following command:

sudo yum install phpPgAdmin

# Then configure phpPgAdmin as accessible from outside:

> /etc/httpd/conf.d/phpPgAdmin.conf

nano /etc/httpd/conf.d/phpPgAdmin.conf

# empty the file and write this content in it 

Alias /phpPgAdmin /usr/share/phpPgAdmin

<Location /phpPgAdmin>
	Order deny,allow
	Allow from all
</Location>

# configure pgAdmin

nano /etc/phpPgAdmin/config.inc.php

# change $conf['servers'][0]['host'] = '' to $conf['servers'][0]['host'] = 'localhost'
# change $conf['extra_login_security'] = true to $conf['extra_login_security'] = false
# change $conf['owned_only'] = false to $conf['owned_only'] = true

sudo systemctl start postgresql-11.service
sudo systemctl reload httpd.service

# go to browser and try phpPgAdmin :

http://91.213.245.166/phpPgAdmin/

systemctl restart httpd.service
sudo systemctl start httpd.service
sudo systemctl enable httpd.service

------------------------------

psql -U admin -d bluemail_system -a -f /var/bluemail_system.sql
53344Yd34e35
psql -U admin -d bluemail_lists -a -f /var/bluemail_lists.sql
53344Yd34e35
# if password required : put your db_password

-------------------------------------

Editing servers info :

sudo sed -i 's/upload_max_filesize = 2000M/upload_max_filesize = 2000M/g' /etc/php.ini
sudo sed -i 's/max_file_uploads = 2/max_file_uploads = 200/g' /etc/php.ini
sudo sed -i 's/post_max_size = 800M/post_max_size = 2000M/g' /etc/php.ini
sudo sed -i 's/memory_limit = 11000M/memory_limit = 2000M/g' /etc/php.ini
sudo sed -i 's/short_open_tag = Off/short_open_tag = On/g' /etc/php.ini

-----------------Edit by FTP --------------------
nano /var/bluemail/applications/bluemail/configs/databases.ini

# for edit :

master.user = admin
master.password = 53344Yd34e35
hash md5:  22da77375fdfd79161693b07954e4174
-------------------------------------
# Edit Password Master

	for create a password for md5 enter the website

	http://www.cryptage-md5.com/
	
	put your password in the website above and copy the md5 password then :
	# Go phpPgAdmin, login and follow this :

	bluemail_system => Schema => admin => Tables => ueser 
	
	after click on Browse => Edit => password

	put your md5 password and save.

-------------------------------------

yum -y update
service iptables stop
service network restart
service httpd restart
service postgresql-11 restart

-------------------------------------

cd /opt/
wget --no-cookies --no-check-certificate --header "Cookie: gpw_e24=http%3A%2F%2Fwww.oracle.com%2F; oraclelicense=accept-securebackup-cookie" "http://download.oracle.com/otn-pub/java/jdk/8u131-b11/d54c1d3a095b4ff2b6607d096fa80163/jdk-8u131-linux-x64.tar.gz"

tar xzf jdk-8u131-linux-x64.tar.gz

cd /opt/jdk1.8.0_131/
alternatives --install /usr/bin/java java /opt/jdk1.8.0_131/bin/java 2
alternatives --config java

# Enter to keep the current selection[+], or type selection number: 1

java -version

-------------------------------------

alternatives --install /usr/bin/jar jar /opt/jdk1.8.0_131/bin/jar 2
alternatives --install /usr/bin/javac javac /opt/jdk1.8.0_131/bin/javac 2
alternatives --set jar /opt/jdk1.8.0_131/bin/jar
alternatives --set javac /opt/jdk1.8.0_131/bin/javac

-------------------------------------


chown -R apache:apache /var/bluemail
chown -R apache:apache /var/bluemail/tmp/*
chown -R apache:apache /var/bluemail/tmp/logs/

-------------------------------------
----- End - or After reboot ---------
-------------------------------------

chown -R apache:apache /var/bluemail
chown -R apache:apache /var/bluemail/tmp/*
chown -R apache:apache /var/bluemail/tmp/logs/

export JAVA_HOME=/opt/jdk1.8.0_131
export JRE_HOME=/opt/jdk1.8.0_131/jre
export PATH=$PATH:/opt/jdk1.8.0_131/bin:/opt/jdk1.8.0_131/jre/bin


echo $JAVA_HOME
/opt/jdk1.8.0_131    

yum -y update
service iptables stop
service network restart
service httpd restart
service postgresql-11 restart

# DONE.
