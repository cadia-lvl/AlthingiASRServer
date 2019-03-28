#! /bin/bash
# Description: makes sure that directories in Lirfa are not easily accessible by just anyone
# needs to be run from the project directory

chmod 711 test
chmod 711 setup
chmod 711 test/test_scripts/
chmod 711 include/
chmod 710 .git
chmod 711 api
mkdir data/confirmWords/
