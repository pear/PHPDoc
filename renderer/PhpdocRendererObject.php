<?php
/**
* Superclass of all Renderer. 
*
* Derive all custom renderer from this class.
*
* @version $Id$
*/
class PhpdocRendererObject extends PhpdocObject {

    var $warn;

    var $accessor;

    /**
    * Extension for generated files.
    * @var  string  
    */
    var $file_extension = ".html";

} // end class PhpdocRendererObject
?>