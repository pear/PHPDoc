<?
define("DR", $_SERVER['DOCUMENT_ROOT']);

/**
* KNIFE_dtFile - A file reading class used by KNIFE_dtDirectory
* 
* @access private
* @package KNIFE_directoryTools
* @author Tim Gallagher<timg@sunflowerroad.com>
* @version 0.5.0 3/1/2002
* @copyright This code is COPYRIGHT 2002 BY TIM GALLAGHER - 
* SEE LICENSE.TXT FOR FULL LICENSE
* This program is free software; you can redistribute it and/or modify it under the terms of
* the GNU General Public License as published by the Free Software Foundation; either
* version 2 of the License, or (at your option) any later version.
* This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
* without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
* PURPOSE. See the GNU General Public License for more details.
*/
class KNIFE_dtFile
{
	/**
	* The constructor checks for the existance of the file, and if valid, gets stat() info about the file.
	*	
	* @parameter string The full path to the file including filename.
	* @see setPath()
	*/
	function KNIFE_dtFile($full_path)
	{
		return $this->setPath($full_path);
	} // end function

	/**
	* Check the existance and validity of a file
	* Check the existance of a path, including a filename if provided. If the path is invalid, FALSE will be returned, if it is value, TRUE.
	* @parameter string The absolute path to a directory.
	* a non-absolute path can be used, but behavior is
	* unpredictable as the root of the class file could change,
	* thus making relative paths not recommended.
	* @returns boolean Returns TRUE if the path is valid.
	* @access private
	* @see setPath()
	*/
	function _checkPath($path)
	{
		if (! (filetype($path) == "dir")  || (filetype($path) == "file") )
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}; // end if
	} // end method

	/**
	* Set the filename of this particular file.
	* You must call this before calling get_info(). This is usually called from KNIFE_dtDirectory when setting up a directory of files, but can be used on an individual basis if so desired.
	* @param string An absolute location of the file including the filename.
	* @access public
	* @see _checkPath()
	*/
	function setPath($path)
	{
		if ($this->_checkPath($path))
		{
			$parts = pathinfo($path);
			$this->absoluteFileName = $path;
			$this->directory = $parts['dirname'];
			$this->filename = $parts['basename'];
			$this->extension = $parts['extension'];
			$this->type &= $this->extension;
		}
		else
		{
			// raise an error here
			die("ERROR setPath() " .  __FILE__  . " " . __LINE__);
		}; // end if
	} // end method
	
	/**
	* Retrieve info about the file.
	* This issues the PHP function stat(); on the current file.<br><br>
	* You MUST call set_path() before calling get_info()
	* @access public
	* see set_path
	*/
	function getInfo()
	{
		$this->stat_info = stat($this->absoluteFileName);
		if (! ($this->stat_info) )
		{
			// raise an error here
			die("ERROR getInfo() " .  __FILE__  . " " . __LINE__);
		}; // end if

		return $this->stat_info;
	} // end method
} // end class



/**
* KNIFE_dtDirectory - A directory reading class with recursion.
* 
* @access public
* @author Tim Gallagher<timg@sunflowerroad.com>
* @version 0.5.0 3/1/2002
* @package KNIFE_directoryTools
* @copyright This code is COPYRIGHT 2002 BY TIM GALLAGHER - 
* SEE LICENSE.TXT FOR FULL LICENSE
* This program is free software; you can redistribute it and/or modify it under the terms of
* the GNU General Public License as published by the Free Software Foundation; either
* version 2 of the License, or (at your option) any later version.
* This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
* without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
* PURPOSE. See the GNU General Public License for more details.
*/
class KNIFE_dtDirectory
{

	/**
	* An array of objects representing files in this directory.
	* Contains entries for files, sub directories, and links.  This is a private member of the class - 
	* Function calls will be used to access data in it.
	* @var array
	* @see $_filesByName
	* @access private
	*/
	var $_files;

	/**
	* An associative array (by filename) of objects of type KNIFE_dtFile.
	* Entries in this array are actually references to the entries in the _files array
	* @var array
	* @see $_files
	* @access private
	*/
	var $_filesByName;

	/**
	* An associative array (by file extension) of objects of type KNIFE_dtFile.
	* Entries in this array are actually references to the entries in the _files array
	* @var array
	* @see $_files
	* @access private
	*/
	var $_filesByExtension;

	/**
	* An array containing KNIFE_dtDirectory objects
	* 
	* @var array
	* @see $_directoriesByName
	* @access private
	*/
	var $_directories;


	/**
	* An associative array (by directory name) of type KNIFE_dtDirectory.
	* Entries in this array are actually references to the entries in the $_directories array
	* @var array
	* @see $_directories
	* @access private
	*/
	var $_directoriesByName;



	/**
	* Defaults to 1 which will only scan the single directory specified by the set_path() method.
	* 
	* @var integer Sets the limit on how many levels into directory structures scan() will go.
	* @see setLimit(), setPath()
	* @access private
	*/
	var $_scanLimit = 1;

	/**
	* Defaults to 0 - The lowest directory level in other words, the root directory.
	* 
	* @var integer Sets the limit on how many levels into directory structures scan() will go.
	* @see _addDirectoryEntry()
	* @access private
	*/
	var $_directoryLevel = 0;

	/**
	* No default, this is set through the public method setPath()
	*
	* @var string The root file system directory - used for scanning.
	* @see setPath(), scan()
	* @access private
	*/
	var $_rootDirectory = "";

	/**
	* Check the existance and validity of the given path.
	* Check the existance of the path, including a filename if provided. If the path is invalid, FALSE will be returned, if it is value, TRUE.
	* @parameter string The absolute path to a directory. A non-absolute path can be used, but behavior is
	* unpredictable as the root of the class file could change, thus making relative paths not recommended.
	* @parameter boolean If TRUE (NOT the default) checks for either files or directories when checking for validity.
	* If FALSE, only checks for directories (in other words, a valid path).
	* @returns boolean TRUE if the path is valid.
	* @access private
	* @see setPath(), scan()
	*/
	function _checkPath($path, $include_files = FALSE)
	{
		if ($include_files)
		{
			if (filetype($path) == "dir" || filetype($path) == "file")
			{
				return TRUE;
			}
			else
			{
				return FALSE;
			}; // end if
		}
		elseif (filetype($path) !== "dir")
		{
			return FALSE;
		}
		else
		{
			return TRUE;
		}; // end if
	} // end method


	/**
	* Set the directory to scan.
	*
	* @param string The absolute path to the directory the class will scan.
	* You must set the dir with this method before calling scan.
	* it is also advisable to call $object->set_limit before calling scan
	* to set a limit on how deep the scanning operation will go.
	* @access public
	* @see setLimit(), scan()
	* @throws Error message is displayed when an invalid path is passed - Returns False.
	* @returns boolean Returns TRUE if path is valid, or FALSE if invalid.
	*/
	function setPath($path)
	{
		// add a trailing slash on the path if it's not there already
		// by doing this, we know all our paths will always end with a trailing slash
		// and we won't have to compare at other times when we might need to perform an operation
		// one way if the slash is there, and another way if the slash is not there.
		if (! (substr($path,-1) == "/") )
		{
			$path .= "/";
		}; // end if


		if (! ($this->_checkPath($path)) )
		{
			echo "<br><b>Specified path does not point to a directory in cls_directory.php on line __LINE__</b><br>";
			return FALSE;
		}
		else
		{
			$this->_rootDirectory = $path;
			return TRUE;
		}; // end if
	} // end method
	
	/**
	* Set level limit for scanning.
	*
	* @param integer Sets the limit on how many levels into directory structures scan() will go.
	* Use this to keep from having an infinite loop (with sym links for instance)
	* The valid range of values is Zero and higher.  Zero is a special value which means unlimited.
	* @access public
	* @see scan(), $_scanLimit
	*/
	function setLimit($limit = 1)
	{
		if ($limit == 0)
		{
			// set limit to five million for what essentially is unlimited.
			// if you have a directory structure that is that deep, you've got other problems.
			$this->_scanLimit = 5000000;
		}
		elseif ($limit > 0)
		{
			$this->_scanLimit = $limit;
		}
		else
		{
			echo "<B><big>!WARNING!</big> $scan_limit out of range, setting $this->_scanLimit=1 in cls_directory on line __LINE__";
			$this->scanLimit = 1;
		}; // end if
	} // end method

	/**
	* Add a entries to the $this->_files and $this->_directories arrays.
	* 
	* @parameter string The full path (including filename for files) of the entry.
	* both FILE and DIR types create entries in the entries[] array
	* But DIR types create object entries of the directory class type, while FILE creates object entries of the file class
	* as defined by the new_file_object method to contain attributes, and misc info about the entry
	* @access private
	* @see _newFileObject(), $_files, $_directories, $_filesByName, $_directoriesByName
	*/
	function _addDirectoryEntry($full_path)
	{
		// we use filetype($filename) to determine the type of the entry we're trying to add
		
		if (! $type = filetype($full_path) )
		{
			// raise an error here
			die("files does not exist in: " . __FILE__ . __LINE__);
		}; // end if
		switch (strtolower($type))
		{
			case "file":
				$entry = $this->_newFileObject($full_path);
					// if you don't want to download KNIFE_devArrayAdd simply use the count of the array as the key.
					// I deemed this to be almost unsafe, although in practice, in this implementation, it's probably just fine.
					$key = KNIFE_devArrayAdd($this->_files, $entry);
					$this->_filesByName[$entry->filename] =& $this->_files[$key];
					$this->_filesByExtension[$entry->extension] =& $this->_files[$key];
				if ($entry->extension = "lnk")
				{
					// implement windows shortcut code here
				}
				else
				{
					
				}; // end if

				
				break;
		
			case "dir":
				// check for maximum recursion into structure by checking _directoryLevel against _scanLimit
				if (! ($this->_directoryLevel > $this->_scanLimit-1) )
				{
					$entry = $this->_newDirectoryObject();
					$entry->setPath($full_path . "/");
					$entry->setLimit($this->_scanLimit);

					// since we're adding a subdirectory, we add one to the level to indicate it's one deeper.
					$entry->_directoryLevel = $this->_directoryLevel + 1;
					$entry->scan();

					// if you don't want to download KNIFE_devArrayAdd simply use the count of the array as the key.
					// I deemed this to be almost unsafe, although in practice, in this implementation, it's probably just fine.
					$key = KNIFE_devArrayAdd($this->_directories, $entry);
					$this->_directoriesByName[$entry->_rootDirectory] =& $this->_directories[$key];
				}; // end if				
				break;

			case "link":
				// we must check here for operating system support for sym links
				// so we don't raise errors on unsupported platforms
				// and so we can create links for windows platform shortcut files .lnk files
				break;

			default:
				
				break;
		}; // end switch
		$next_file = count($this->files) + 1;
		//$this->entries[$next_file] = $this->_new_file_object();
		//$this->entries[
	} // end method

	/**
	* Creates a new instance of the KNIFE_dtFile object.
	* By having a layer for this, we create an easy way to extend the classes associated with this module if the files class is extended, simply extend the directory class and over-ride this method.
	* @access private
	* @returns object KNIFE_dtFile A new file object.
	* @parameter string The full path to the file including filename.
	*/
	function _newFileObject($full_path)
	{
		$new_object = new KNIFE_dtFile($full_path);
		return $new_object;
	}

	/**
	* Creates a new instance of the KNIFE_dtDirectory object.
	* By having a layer for this, we create an easy way to extend the classes associated with this module if the directory class is extended, simply extend the directory class and over-ride this method.
	* @access private
	* @returns object KNIFE_dtDirectory A new directory object.
	* 
	*/
	function _newDirectoryObject()
	{
		$new_object = new KNIFE_dtDirectory();
		return $new_object;
	}

	/**
	* Scans the directory that 
	* 
	* @access public
	* @see setPath(), setLimit()
	*/
	function scan()
	{
		if (! ($this->_checkPath($this->_rootDirectory)) )
		{
			
		}; // end if
		$handle = @opendir($this->_rootDirectory); 
		while (($file = @readdir($handle)) !== FALSE)
		{
			
			if ($file != "." && $file != "..")
			{
				$this->_addDirectoryEntry($this->_rootDirectory .  $file);
			}; // end if
		}; // end while loop
		closedir($handle);
	} // end method

	/**
	* Returns an array of file names including the entire path (as strings) recursively into the directory structure.
	* An array of acceptable extensions may be supplied.
	* @access public
	* @parameter mixed Either a string or an array of strings of acceptable extensions.  A straight compare is done so no wildcards are allowed. A single "*" means all files.
	* @parameter boolean TRUE makes the function return all the filenames that have been scanned.  FALSE only returns filenames in the current directory.
	*/
	function fileList($extMask = "*", $recurse = TRUE)
	{
		// we want to treat the mask like an array so we can use the in_array php function to compare.
		if (! (is_array($extMask)) )
		{
			$extensionMask[] = $extMask;
			$extMask = $extensionMask;
			unset($extensionMask);
		}; // end if

		$files =& $this->_files;
		$directories =& $this->_directories;

		for ($a = 0; $a < count($files) ; $a++)
		{
			 
			if ($extMask[0] == "*")
			{
				$out[] = $this->_rootDirectory . $files[$a]->filename;
			}
			elseif (in_array($files[$a]->extension,$extMask))
			{
				$out[] = $this->_rootDirectory . $files[$a]->filename;
			}; // end elseif
		}; // end for loop
		
		// this for loop is for the sub directories, we'll check for recursion now, and if it's
		// false we don't add sub directories to the output
		if ($recurse)
		{
			for ($a = 0; $a < count($directories) ; $a++)
			{
				$subs = array_merge($subs,$directories[$a]->fileList($extMask, $recurse));

			}; // end for loop
			$out = array_merge($out,$subs);	
		}; // end if		
		return $out;
	} // end method

	/**
	* Returns an array of directory names including the entire path (as strings) recursively into the directory structure.
	* 
	* @access public
	* @parameter boolean TRUE makes the function return all the filenames that have been scanned.  FALSE only returns filenames in the current directory.
	*/
	function directoryList($recurse = TRUE)
	{
		$directories =& $this->_directories;
		

		for ($a = 0; $a < count($directories) ; $a++)
		{
			$out[] = $directories[$a]->_rootDirectory;
		}; // end for loop
		
		// this for loop is for the sub directories, we'll check for recursion now, and if it's
		// false we don't add sub directories to the output
		if ($recurse)
		{
			for ($a = 0; $a < count($directories) ; $a++)
			{
				$subs = array_merge($subs,$directories[$a]->directoryList($recurse) );

			}; // end for loop
			$out = array_merge($out,$subs);	
		}; // end if
		return $out;
	} // end method

	/**
	* Returns an array of arrays containing several entries.
	* One Entry for each directory, an array containing the directory name, number of files and total size of files.
	* 
	* @access public
	* @parameter boolean TRUE makes the function return info for all the files that have been scanned.  FALSE only returns info for files in the current directory.
	*/
	function getDirectoryInfo($recurse = TRUE)
	{
		if ($recurse)
		{
			
		}; // end if
	} // end function

} // end class

$directory = new KNIFE_dtDirectory();
$directory->setPath($_SERVER['DOCUMENT_ROOT'] . $_REQUEST['PHPDOC_targetdir']);
$directory->setLimit(1);
$directory->scan();
$exts = array ("html","htm","xml","css","dtd");
$filesToDelete = $directory->fileList($exts);

for ($a=0;$a < count($filesToDelete) ;$a++)
{
	unlink ($filesToDelete[$a]);
}; // end for loop
?>