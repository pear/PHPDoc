<?PHP
$start = time();

// 3/12/2002 - Tim Gallagher<timg@sunflowerroad.com> added the version checking
// included below to deal with the different ways php sends variables into scripts.
// see the file for more information.
include ("versionSanity.php");

// WARNING: long runtimes! Make modifications 
// to the php[3].ini if neccessary. A P3-500 
// needs slightly more than 30 seconds to 
// document phpdoc itself.

// 3/8/2002 Tim Gallagher - cleaned up the code on this page
// by making the html on this page a function call.
// this makes the indenting much cleaner.
// new functions can be found at the bottom of prepend.php

//SERVER_DOC_ROOT is defined in versionSanity.php

// Directory with include files
define("PHPDOC_INCLUDE_DIR", SERVER_DOC_ROOT . "/apps/PHPDoc/");

// Html tags allowed to be used in doc tags
define("PHPDOC_ALLOWEDHTMLTAGS", "<a>,<i>,<b>,<pre>,<ul>,<li>,<br>,<code>");

// main PHPDoc Include File
include("./prepend.php");		

// look at the bottom of prepend.php for this function
// this function also includes front-end.php
mainPageHeader();

// output buffering used to format the previously <pre>ed text on this page prettier.
ob_start();
$doc = new Phpdoc;

// Sets the name of your application.
// The name of the application gets used in many default templates.
$doc->setApplication($_REQUEST['PHPDOC_appname']);

// directory where your source files reside:
$doc->setSourceDirectory(SERVER_DOC_ROOT . $_REQUEST['PHPDOC_sourcedir']);

// save the generated docs here:
$doc->setTarget(SERVER_DOC_ROOT . $_REQUEST['PHPDOC_targetdir']);

$templateName = $_REQUEST['PHPDOC_selectedTemplate'];

// use these templates:
// this is the data sent in from the html form.
$doc->setTemplateDirectory($PHPDOC_templates[$templateName]['path']);

// source files have one of these suffixes:
$doc->setSourceFileSuffix( array ("php", "inc") );

/* ------------------------------------------------------------------
// 3/2/2002 - Tim Gallagher<timg@sunflowerroad.com> changed this section to allow
// for deleting files in the existing targetdir location
// this should be considered a HUGE security risk... we're not
// checking any file permissions to see what we're deleting.
*/
if ($_REQUEST['PHPDOC_deleteFilesOption'] =="ON") {
    // uncomment the line below to turn file deleting on.
    // if you do, you MUST be sure that only trusted people have access to the PHPDoc directory
    // because a malicious user could send bogus data to the script and delete EVERYTHING on your webserver.
    //include ("delete_files.php");
}; // end if

// parse and generate the xml files
$doc->parse();

/* ------------------------------------------------------------------
3/1/2002 - Tim Gallagher<timg@sunflowerroad.com> changed this section (added it really) to
automatically copy the phpdoc.css file (and others) to the destination
directory automatically so it's always there.
also added an auto copy for the frameset, dtd, and empty.html files.
*/

/* ------------------------------------------------------------------
3/11/2002 - Tim Gallagher<timg@sunflowerroad.com> changed this section
to use the template array as defined in /renderer/templates.php
for the path in the following section
*/ 

// 3/11/2002 - Tim Gallagher<timg@sunflowerroad.com> added this note:
// we do a str_replace here to keep all paths using the forward slash (which is compatible under
// linux, unix, and windows - make sure apache is set up correctly under win32 - in other words
// make sure apache is using forward slashes in the virtual directive (or in the document root directive)
$fileToCopySrc = str_replace("\\","/", realpath($PHPDOC_templates[$templateName]['static_files_path']) . "/");
$fileToCopyDst = str_replace("\\","/", realpath(SERVER_DOC_ROOT. $_REQUEST['PHPDOC_targetdir']) . "/");

// 3/11/2002 - Tim Gallagher<timg@sunflowerroad.com> added this note:
// we've already scanned the static file path in templates.php located in PHPDOC/renderer/
// so we can simply set our $filesToCopy to the array defined in the $PHPDOC_templates variable
$filesToCopy = $PHPDOC_templates[$templateName]['files'];
echo "Copying files now...\n";
for ($a = 0; $a < count($filesToCopy) ; $a++) {
    //echo "Copying $fileToCopySrc$filesToCopy[$a].\n";
    if (! (copy ($fileToCopySrc . $filesToCopy[$a], $fileToCopyDst . $filesToCopy[$a])) ) {
        echo "An error occured trying to copy $fileToCopySrc$filesToCopy[$a].\n\n";
    }; // end if
}; // end for loop

// ------------------------------------------------------------------

// 3/11/2002 - Tim Gallagher<timg@sunflowerroad.com> moved the render method call 
// and put it below the file copy section
// so we can copy a dummy warnings html file to the destination, but have it overwritten
// by the render method if warnings do exist.  I got really tired of having a 404 error on all my pages
// when clicking on the "Report" link.

// turn xml into html using templates
$doc->render();

echo time() - $start . " seconds needed.";
$out = ob_get_contents();
ob_end_clean();

// display a link to view the documentation with.
echo '<a href="' . $_REQUEST['PHPDOC_targetdir'] . '/">View your generated documentation here.</a><br>';

echo nl2br($out);

// look at the bottom of prepend.php for this function
mainPageFooter();

?>