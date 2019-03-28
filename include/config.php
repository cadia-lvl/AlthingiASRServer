<?php
//Author: Judy Fong, Háskolinn í Reykjavík
//Description: Configuration file so all the variables which will change depending on installation will all be in one place

$rootdir = '/home/USER-NAME/public_html/lirfa/';
//Directories of all the files that Lirfa creates and manipulates
$datadir = 'data/';
$newvocabdir = $rootdir . $datadir . 'NewVocab/';
$concordancedir = $newvocabdir . 'concordance/';
//logging file location
$loggingfile = '../logs/errors.log';

//prod - supply production server name
$prodhostname = '';
//dev
$devhostname = '';

error_reporting(E_ALL); // Error engine - always ON!
if ($prodhostname == gethostname()) //production server
{
  ini_set('display_errors', 'OFF'); // Error display - OFF in production env or real server
}
else //presume dev server
{
    ini_set('display_errors', ON); 
}
ini_set('log_errors', 'TRUE'); // Error logging
ini_set('error_log', $loggingfile); // Logging file
ini_set('log_errors_max_len', 1024); // Logging file size

?>
