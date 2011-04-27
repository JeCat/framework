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
		
		$this->set("#document",'jc\ui\Object') ;
		$this->set("if",__NAMESPACE__.'\\If') ;
		$this->set("include",__NAMESPACE__.'\\Include') ;
		
		$this->set("html",'jc\ui\xhtml\Node',self::TS_BLOCK) ;
		$this->set("body",'jc\ui\xhtml\Node',self::TS_BLOCK) ;
		$this->set("head",'jc\ui\xhtml\Node',self::TS_BLOCK) ;
		$this->set("div",'jc\ui\xhtml\Node',self::TS_BLOCK) ;
		$this->set("p",'jc\ui\xhtml\Node',self::TS_BLOCK) ;
		$this->set("ul",'jc\ui\xhtml\Node',self::TS_BLOCK) ;
		$this->set("ol",'jc\ui\xhtml\Node',self::TS_BLOCK) ;
		$this->set("li",'jc\ui\xhtml\Node',self::TS_BLOCK) ;
		$this->set("h1",'jc\ui\xhtml\Node',self::TS_PAIR) ;
		$this->set("h2",'jc\ui\xhtml\Node',self::TS_PAIR) ;
		$this->set("h3",'jc\ui\xhtml\Node',self::TS_PAIR) ;
		$this->set("h4",'jc\ui\xhtml\Node',self::TS_PAIR) ;
		$this->set("h5",'jc\ui\xhtml\Node',self::TS_PAIR) ;
		$this->set("br",'jc\ui\xhtml\Node',self::TS_INLINE) ;
	}
	
	public function set($sTagName,$sClassName,$nTagStyle=self::TS_BASE)
	{
		parent::set(strtolower($sTagName),array($sClassName,$nTagStyle)) ;
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
	
	public function tagStyle($sTagName,$nTagStyle)
	{
		$arrInfo = $this->get($sTagName) ;
		return (($arrInfo?$arrInfo[1]:self::TS_BASE)&$nTagStyle)==$nTagStyle ;
	}
	
	public function isSingle($sTagName)
	{
		return !$this->tagStyle($sTagName,self::TS_PAIR) ;
	}

	public function isInline($sTagName)
	{
		return $this->tagStyle($sTagName,self::TS_INLINE) ;
	}
	
	public function isMultiLine($sTagName)
	{
		return $this->tagStyle($sTagName,self::TS_MULTILINE) ;
	}
	
	private $arrTagClassMapping = array() ;
	
	private $sDefaultClassName = "jc\\ui\\xhtml\\Node" ;

}

?>