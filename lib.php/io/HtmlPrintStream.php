<?php
namespace jc\io ;

use jc\lang\Object;

class HtmlPrintStream extends PrintStream implements IOutputStream
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