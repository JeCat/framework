<?php
namespace jc\io ;

use jc\lang\Object;

class HtmlPrintSteam extends PrintSteam implements IOutputStream
{
	/**
	 * Enter description here ...
	 * 
	 * @return void
	 */
	public function write($sBytes)
	{
		parent::write('<pre>'.$sBytes.'</pre>') ;
	}
}
?>