<?php

namespace jc\ui\xhtml\nodes ;

use jc\util\HashTable;
use jc\lang\Object;

class TagLibrary extends HashTable 
{
	public function __construct()
	{
		parent::__construct() ;
		
		$this->set("if",__NAMESPACE__.'\\If') ;
		$this->set("include",__NAMESPACE__.'\\Include') ;
	}
		
	private $arrTagClassMapping = array() ;

}

?>