<?php
    //Author: Judy Fong, Háskolinn í Reykjavík
    function validateSpeechID($speechID)
    {
        $validSpeechID = false;
        if (strpos($speechID, 'rad') !== false && preg_match('/^rad\d{8}T\d{6}\z/', $speechID)) {
            $validSpeechID = true;
        }
        return $validSpeechID;
    }
    
    function validateDate($date)
    {
        $formats = array('Y-m-d', 'Y-m-d H:i:s', 'Y-m-d\TH:i:s');
        foreach ($formats as $format) {
            $d = DateTime::createFromFormat($format, $date);
            if ($d && $d->format($format) == $date) {
                return true;
            }
        }
    }

?>
