<?php

namespace jc\ui\xhtml ;

use jc\io\IOutputStream;
use jc\util\HashTable;

class Attributes extends HashTable
{
	public function compile(IOutputStream $aDev) 
	{		
		foreach ($this as $sName=>$sValue)
		{
			$aDev->write(' ') ;
			$aDev->write($sName) ;
			$aDev->write('="') ;
			$aDev->write(addslashes($sValue)) ;
			$aDev->write('"') ;
		}
	}
}

?>