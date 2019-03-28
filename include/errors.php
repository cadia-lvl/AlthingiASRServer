<?php
// if (empty($_SESSION['errors']) == false)
//     print_r(array_values($_SESSION['errors'];
function errors_on_two_fronts($error_msg_string) {
    trigger_error($error_msg_string);
    echo "$error_msg_string \n";
}

$lirfa_errors = [
        0 => "No error",
        //200 errors non api specific
        204 => "Operation completed without any cURL errors",
        205 => "Curl error ",
	//800 new words
	800 => "A transcript ID must be given to proceed",
	801 => "No new words found."
	//900 confirmed words
    ];
?>
