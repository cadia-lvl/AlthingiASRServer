#! /bin/bash
# Copyright 2018 Judy Fong (Reykjavik University lirfa@judyyfong.xyz)
# Apache 2.0
# Description: Runs the full existing test suite from the project root directory and outputs it all to stdout

curl -k http://localhost:8000/testphpini.php

echo -e "\nVerify php version is at least 5"
curl -k "http://localhost:8000/test/test_scripts/test_php_version.php"

echo -e "\nTest confirm words endpoint of words which are in the database"
curl -k http://localhost:8000/test/test_suite/test_confirmWords_Endpoint.html
echo -e "Test suite finished running"
