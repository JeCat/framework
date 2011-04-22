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
	
	public function set($sTagName,$sClassName,$bSingle=false)
	{
		parent::set(strtolower($sTagName),array($sClassName,$bSingle)) ;
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
	
	public function isSingle($sTagName)
	{
		$arrInfo = $this->get($sTagName) ;
		return $arrInfo?$arrInfo[1]:false ;
	}
	
	
	private $arrTagClassMapping = array() ;
	
	private $sDefaultClassName = "jc\\ui\\xhtml\\Node" ;

}

?>