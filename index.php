<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
/*
  +----------------------------------------------------------------------+
  | PHP Version 4                                                        |
  +----------------------------------------------------------------------+
  | Copyright (c) 1997-2002 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 2.02 of the PHP license,      |
  | that is bundled with this package in the file LICENSE, and is        |
  | available at through the world-wide-web at                           |
  | http://www.php.net/license/2_02.txt.                                 |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Author: Antônio Carlos Venâncio Júnior <floripa@php.net>             |
  +----------------------------------------------------------------------+

  $Id$:
*/

/*
 * Where is PHPDoc located
 *
*/
$sourcedir = '';

/*
 * Where is PHPDoc Template located
 *
*/
$templatedir = '';


$start = time();
include ("versionSanity.php");

if (empty($sourcedir)) {
    $sourcedir = './';
}

if (empty($templatedir)) {
    $templatedir = './';
}

define('PHPDOC_INCLUDE_DIR', $sourcedir);
define('PHPDOC_TEMPLATE_DIR', $templatedir);

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
$doc->setApplication($_POST['PHPDOC_appname']);

// directory where your source files reside:
$doc->setSourceDirectory(SERVER_DOC_ROOT . $_POST['PHPDOC_sourcedir']);

// save the generated docs here:
$doc->setTarget(SERVER_DOC_ROOT . $_POST['PHPDOC_targetdir']);

$templateName = $_POST['PHPDOC_selectedTemplate'];

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
if ($_POST['PHPDOC_deleteFilesOption'] =="ON") {
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
$fileToCopyDst = str_replace("\\","/", realpath(SERVER_DOC_ROOT. $_POST['PHPDOC_targetdir']) . "/");

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
echo '<a href="' . $_POST['PHPDOC_targetdir'] . '/">View your generated documentation here.</a><br>';

echo nl2br($out);

// look at the bottom of prepend.php for this function
mainPageFooter();

?>