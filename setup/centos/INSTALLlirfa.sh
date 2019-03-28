#!/bin/bash -eu
# Instructions for install lirfa on centos 7
# Follow the Ubuntu install instructions for the most part
# didn´t enable userdirs in apache
#NOTE! assume this is run from the lirfa root directory

. ./path.sh

sudo yum update 
sudo yum install git httpd -y
sudo yum install mariadb-server mariadb
sudo yum install -y curl
sudo yum install -y php
sudo yum install -y php-mysql php-pdo php-gd php-mbstring php-mcrypt php-curl php-common
git clone https://github.com/cadia-lvl/lirfa.git lirfa

#modify the /etc/httpd/conf.d/userdir.conf 
#add the following line:
#UserDir enabled lirfa
#uncomment UserDir public_html
sudo setsebool -P httpd_enable_homedirs true
#restart apache
sudo systemctl enable httpd.service
sudo systemctl restart httpd.service

sudo systemctl start mariadb
sudo systemctl enable mariadb
sudo systemctl status mariadb
sudo mysql_secure_installation
#choose Y when asked if you want to reset the root password (N if on debian), 
#then in the root home directory, create ~/.my.cnf
touch ~/.my.cnf
#in that file create the following files

# [client]
# user=root
# password=YOURROOTPASSWORD

# This will allow root to log in to mysql without a password
#Choose Y the rest of the prompts such as removing test users and databases, as well as accessing root remotely
#Create an admin user account if you'd like an all access account with all privileges
sudo mysql -u root < $LIRFA_ROOT/db/schema_setup.sql
sudo mysql -u root < $LIRFA_ROOT/db/permissions.sql

#Make the Lirfa API web accessible if using a user dir
chmod 711 ~
chmod 755 ~/public_html/Lirfa/
#the api files themselves should be readable by everyone

#Allow apache through the firewall
#restrict the permissions of everything else
sudo firewall-cmd --add-service=http --permanent && sudo firewall-cmd --add-service=https --permanent
sudo systemctl restart firewalld
