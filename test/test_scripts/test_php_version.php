<?php
$matches = array();
$minimal_version = 5.6; //because it was tested on 5.6.33
preg_match("([0-9\.]+)", PHP_VERSION, $matches);
assert('$matches[0] >= $minimal_version;', 'PHP version doesn\'t meet minimum specifications.');

?>