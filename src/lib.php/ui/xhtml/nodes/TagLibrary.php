<?php

namespace jc\ui\xhtml\nodes ;

use jc\util\HashTable;
use jc\lang\Object;

class TagLibrary extends HashTable 
{
	const TS_PAIR = 1 ;			// 成对
	const TS_MULTILINE = 2 ;	// 多行
	const TS_INLINE = 4 ;		// 内联
		
	const TS_BLOCK = 3 ;		// TS_PAIR|TS_MULTILINE
	const TS_BASE = 5 ;			// TS_PAIR|TS_INLINE
	
	
	public function __construct()
	{
		parent::__construct() ;
		
		$this->set("if",__NAMESPACE__.'\\NodeIf') ;
		$this->set("include",__NAMESPACE__.'\\NodeInclude') ;
	}
	
	public function set($sTagName,$sClassName)
	{
		parent::set(strtolower($sTagName),array($sClassName)) ;
	}

	public function get($sTagName)
	{
		return parent::get(strtolower($sTagName))?:null ;
	}
	
	public function getClassName($sTagName)
	{		
		$arrInfo = $this->get($sTagName) ;
		return $arrInfo?$arrInfo[0]:$this->sDefaultClassName ;
	}
		
	private $sDefaultClassName = "jc\\ui\\xhtml\\Node" ;

}

?>