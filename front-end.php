<?
// phpdoc front end
// written by tim gallagher
// who got tired of manually modifying the index.php file every time he wanted to use phpdoc.
// future enhancements will include automatically scanning the document root for directories
// and giving a tree view of directories to use.
// but for now, we're just going to use a simple form.

// in case we're running less than 4.1.0
global $_REQUEST;
global $_SERVER;

?>
<form method="POST" action="">
    <input type="hidden" name="PHPDOCFE" value="go">
    <p><font face="Arial" size="4"><b>PHPDoc Front-End</b></font><br>
    Application Name:<br><input type="text" name="PHPDOC_appname" size="20" value="<?= $_REQUEST['PHPDOC_appname']; ?>"><br>
    Directory to scan (Document root is pre-pended to this.)<br><input type="text" name="PHPDOC_sourcedir" size="20" value="<?= $_REQUEST['PHPDOC_sourcedir']; ?>"><br>
    Directory to store generated documentation.<br><input type="text" name="PHPDOC_targetdir" size="20" value="<?= $_REQUEST['PHPDOC_targetdir']; ?>"><br>
    Delete Existing Files in Doc Location?<br><INPUT TYPE="checkbox" VALUE="ON" NAME="PHPDOC_deleteFilesOption" CHECKED><br>
    Template to Use<br><select size="1" name="PHPDOC_selectedTemplate">
    <?PHP
    // the list of templates
    // see PHPDOC/renderer/templates.php
    foreach ($PHPDOC_templates as $key => $value)
    {
		echo '<option value="' . $key . '">' . $value['display_name'] . "</option>\n";
    }; // end foreach loop
    ?>
    </select>
    <input type="submit" value="Submit" name="B1"></p>
</form>

<?

// exit if we've not gone through this before.
if ($_REQUEST['PHPDOCFE'] != "go" ) {
    echo "</body>\n";
    echo "</html>";
    exit();
}; // end if

?>