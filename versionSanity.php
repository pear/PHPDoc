<?php

// 3/18/2002 - Tim Gallagher<timg@sunflowerroad.com>
// this allows you to change the document root and not have to rely on it to
// be set correctly every place it get used throughout the code.
// you could even set it manually without using the $_SERVER, OR $DOCUMENT_ROOT variables.
if (! isset($_SERVER)) {
    define("SERVER_DOC_ROOT", $DOCUMENT_ROOT);    
} else {
    define("SERVER_DOC_ROOT", $_SERVER['DOCUMENT_ROOT']);    
}; // end if

// 3/18/2002 - Tim Gallagher<timg@sunflowerroad.com>
// if $_REQUEST isn't set, we will set it based on $HTTP_GET_VARS AND $HTTP_POST_VARS
// however, we should still global these variables in the functions to keep backward
// compatability from breaking.
if ( (! isset($_REQUEST)) && (! isset($_GET)) ) {
    // swap the foreach loops to change the order of variable registration
    // in other words you can change GET then POST to POST then GET
    // where the second set of variables overrides the first.

    foreach ($HTTP_GET_VARS as $key => $value)
    {
        $_GET[$key] = $value;
        $_REQUEST[$key] =& $_GET[$key];
    }; // end foreach loop

    foreach ($HTTP_POST_VARS as $key => $value)
    {
        $_POST[$key] = $value;
        $_REQUEST[$key] =& $_POST[$key];
    }; // end foreach loop

}; // end if

?>