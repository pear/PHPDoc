<?php
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

if (isset($_POST) && !empty($_POST)) {
    $PHPDOC_appname = $_POST['PHPDOC_appname'];
    $PHPDOC_sourcedir = $_POST['PHPDOC_sourcedir'];
    $PHPDOC_targetdir = $_POST['PHPDOC_targetdir'];

    // check aplication name
    if (empty($PHPDOC_appname)) {
        $errormsg['appname'] = 'Please type something here';
    }

    // check the source dir
    $sourcedir = SERVER_DOC_ROOT . $PHPDOC_sourcedir;
    if (!is_dir($sourcedir)) {
        $errormsg['sourcedir'] = "This directory doesn't exists";
    }

    // check the target dir
    $targetdir = SERVER_DOC_ROOT . $PHPDOC_targetdir;
    if (!is_dir($targetdir)) {
        $errormsg['targetdir'] = "This directory doesn't exists";
    } else {
        $tmp = $targetdir . '/tmp';
        $ok = mkdir($tmp, 0777);
        if (!$ok) {
            $errormsg['targetdir'] = 'Insufficient permissions on this dir';
        } else {
            rmdir($tmp);
        }
    }
} else {
    $PHPDOC_appname = '';
    $PHPDOC_sourcedir = '';
    $PHPDOC_targetdir = '';
}

?>
<form method="POST" action="index.php">
    <input type="hidden" name="PHPDOC_FRONT_END" value="1">
    <p><font face="Arial" size="4"><b>PHPDoc Front-End</b></font></p>
    <p>Application Name:<br><input type="text" name="PHPDOC_appname" size="20" value="<?= $PHPDOC_appname; ?>">
<?php
if (isset($errormsg['appname'])) 
     printf ('<font color="red">%s</font>', $errormsg['appname']);
?>
<br>
    Directory to scan (Document root is pre-pended to this)<br><input type="text" name="PHPDOC_sourcedir" size="20" value="<?= $PHPDOC_sourcedir; ?>">
<?php
if (isset($errormsg['sourcedir'])) 
     printf ('<font color="red">%s</font>', $errormsg['sourcedir']);
?>
<br>
    Directory to store generated documentation (Document root is pre-pended to this)<br><input type="text" name="PHPDOC_targetdir" size="20" value="<?= $PHPDOC_targetdir; ?>">
<?php
if (isset($errormsg['targetdir'])) 
     printf ('<font color="red">%s</font>', $errormsg['targetdir']);
?>
<br>
    Delete Existing Files in Doc Location? <input type="checkbox" value="ON" name="PHPDOC_deleteFilesOption" checked><br>
    Template to Use<br><select size="1" name="PHPDOC_selectedTemplate">
<?php

foreach ($PHPDOC_templates as $key => $value) {
    echo '<option value="' . $key . '">' . $value['display_name'] . "</option>\n";
};

?>
    </select>
    <input type="submit" value="Submit" name="B1"></p>
</form>

<?

if (!isset($_POST['PHPDOC_FRONT_END']) || isset($errormsg)) {
    echo "</body>\n";
    echo "</html>";
    exit();
};

?>