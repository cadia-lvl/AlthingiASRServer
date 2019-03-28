<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header("Access-Control-Allow-Headers: content-type, X-Requested-With");
//Author: Judy Fong, Háskólinn í Reykjavík
//Description: This script receives a speech ID as a get request. If new_vocab,
// files exist then serve them as JSON. Else, return an empty json object or null, hmmm. 
//Must enable read permissions for php/curl in the appropriate folder to 
//  read the new_words files
//TODO: how does it know which althingi sessions a new word is in?
//TODO: get all new words added within the past 24 hrs, get all new words which haven't been confirmed

// API parameters
// GET parameter: speechId
//          i.e.: rad20180222T114624
//      required: Yes
//   description: the letters rad and timestamp of when the speech ends
//      response: JSON of the new words, phonetic transcription, and times it appears in context

// OR

// GET parameter: startDate
//          i.e.: 2019-01-02
//      required: No
//   description: YYYY-MM-DD, the day from which to start gathering words
// GET parameter: endDate
//          i.e.: 2019-12-21
//      required: No
//   description: YYYY-MM-DD, the day from which to stop gathering words
//      response: JSON of the new words, phonetic transcription, and times it appears in context

// OR

// GET parameter: speechId
//          i.e.: rad20180222T114624
//      required: Yes
//   description: the letters rad and timestamp of when the speech ends
// GET parameter: speakerAbbr
//          i.e.: GBS
//      required: Yes
//   description: the speaker's abbreviation as used in the metadata
//      response: JSON of the new words, phonetic transcription, and times it appears in context

include('../include/config.php');
include_once('../include/errors.php');

//Make sure the message is parsed properly

function validateSpeechID($speechID)
{
    $validSpeechID = false;
    if (strpos($speechID, 'rad') !== false) {
        $validSpeechID = true;
    }
    return $validSpeechID;
}

if (isset($_GET['speechID']) || 
    (isset($_GET['startDate']) && isset($_GET['endDate']))) {
    include_once('../include/errors.php');
    include_once('../include/config.php');
    include_once('../include/mysql_config.php');
    //TODO: save the words, context, and the phonetic transcription to mysql 
    $speechID = '';

    $startingSpeech = "";
    $endingSpeech = "";
    // Hardcoded SQL Connection Example:
    // $con=mysqli_connect("localhost", "root", "", "althingi_words", "3306");
    $con=mysqli_connect($tdbaddr, $tdbuser, $tdbpass, $tdbname);
    if(mysqli_error($con)) {
        die(mysqli_error($con));
    } 
    if(!mysqli_set_charset($con, $tdbcharset)){
        die ("failed to set character set");
    }
    //$wordsQuery = "SELECT newWord, pronunciation, 
    //       count(context) as num_contexts, 
    //       group_concat(distinct context separator 'hello' ) as context_array
    //      FROM contexts
    //      INNER JOIN words ON contexts.wordId=words.id 
    //      WHERE speechId='$speechID' and reject='0'
    //      GROUP BY newWord 
    //      ORDER BY num_contexts DESC";
    $wordsQuery = "SELECT `words`.`newWord`, `pronunciation`, 
          count(context) as num_contexts, 
          group_concat(context separator '\n' ) as context_array
          FROM `contexts`
          INNER JOIN `words` ON contexts.newWord=words.newWord
	  WHERE reject='0' AND confirmedWord IS NULL ";
    if (isset($_GET['startDate']) && isset($_GET['endDate'])) { 
        $startDate = htmlspecialchars($_GET['startDate']);
        $endDate = htmlspecialchars($_GET['endDate']);
        $wordsQuery .= "AND creation_date >='$startDate' AND creation_date <='$endDate' ";
    } elseif (validateSpeechID(htmlspecialchars($_GET['speechID']))) {  
        $speechID = htmlspecialchars($_GET['speechID']);
        $wordsQuery .= "AND speechId='$speechID' ";
    } else  {
        $wordsQuery .= "AND speechId LIKE '%$startingSpeech%' OR speechId LIKE '%$endingSpeech%' ";
    }
        $wordsQuery .=	"GROUP BY newWord 
        		ORDER BY num_contexts DESC";
    $queryResult = mysqli_query($con, $wordsQuery) or die (mysqli_error($con));
    $numResults = mysqli_num_rows($queryResult);
    $counter = 0;

    header('Content-Type: application/json;charset=UTF-8');
    //if the database doesn't have the words, then pull from the files directly
    //XXX: this doesn't work if the directory isn't local to the server
    if ($numResults == 0 && $speechID!=='' && isset($_GET['speakerAbbr'])) {
        //e.g.: GBS
        $speaker = htmlspecialchars($_GET['speakerAbbr']);
        $new_vocab = $newvocabdir  . $speaker . '-' . $speechID . '.txt';
        $new_vocab_concordance = $concordancedir . $speaker . '-' . $speechID . '.txt';
        //echo $new_vocab . '<br/>';
        //echo $new_vocab_concordance;
        $new_word_reader = fopen($new_vocab, "r") or die($lirfa_errors[801]);
        $context_reader = fopen($new_vocab_concordance, "r") or die($lirfa_errors[801]);
        $all_words= array();
        if (file_exists($new_vocab) && filesize($new_vocab) > 0) {
            // echo "<br/>";
            while(!feof($new_word_reader)) {
                $parts = preg_split('/\t|\n/', fgets($new_word_reader));
                if (array_filter($parts) != []) {
                    //echo $parts[0] . "<br/>";
                    //echo $parts[1] . "<br/>";
                    if (end($parts) == ""){
                        array_pop($parts);
                    }
                    $data = array();
                    $data['word'] = $parts[0];
                    $data['pronunciation'] = $parts[1];
                    $data['context'] = array();
                    array_push($all_words, $data);
                }
            }
        }
        fclose($new_word_reader);
        if (file_exists($new_vocab_concordance) && filesize($new_vocab_concordance) > 0) {
            while(!feof($context_reader)) {
                //trim to remove any new lines or zero terminated strings
                $parts = preg_split('/\t|\n/', fgets($context_reader));
                if (array_filter($parts) != []) {
                    //echo $parts[0] . "<br/>";
                    //echo $parts[1] . "<br/>";
                    if (end($parts) == ""){
                        array_pop($parts);
                    }
                    foreach ($all_words as &$word_array) {
                        if (strpos(strtolower($parts[0]), strtolower($word_array['word'])) == false &&
                            strcmp(strtolower($word_array['word']), strtolower($parts[0])) == 0) {
                                array_push($word_array['context'], $parts[1]);
                                break;
                        }
                    }
                }
            }
        }
        fclose($context_reader);
        //straight from the files themselves
        echo json_encode($all_words, JSON_UNESCAPED_UNICODE );

    } else {
        echo "[";
        while ($row = mysqli_fetch_array($queryResult)) {
        //TODO: have the option to also show the speech ID per context while maintaining
        // backwards compatibility
            echo '{ "word": "' . $row{'newWord'} . '", ' . 
            '"pronunciation": "' .  $row{'pronunciation'} . '", ' . 
            '"context": ' . json_encode(explode("\n", $row{'context_array'}), JSON_UNESCAPED_UNICODE) . 
             '}';
            if (++$counter != $numResults) { 
                // all but the last row
            echo ",";
            }
        } 
        echo "]";
    }
    mysqli_close($con);
}
else
{
  trigger_error($lirfa_errors[800]);
  echo $lirfa_errors[800];
}
?>
