<?php

namespace jc\ui\xhtml ;

use jc\util\CombinedIterator;

use jc\lang\Exception;
use jc\lang\Type;
use jc\io\IOutputStream;
use jc\util\HashTable;
use jc\ui\xhtml\compiler\ExpressionCompiler ;

class Attributes extends HashTable
{
	public function count() 
	{
		return parent::count() + $this->anonymousCount() ;
	}
	
	public function anonymousCount() 
	{
		return count($this->arrAnonymous) ;
	}
	
	public function add(AttributeValue $aVal)
	{
		if( $aVal->name() )
		{
			$this->set($aVal->name(),$aVal) ;
		}
		else 
		{
			parent::add($aVal) ;
			$this->arrAnonymous[] = $aVal ;
		}
	}

	public function anonymous()
	{
		return reset($this->arrAnonymous) ;
	}
	
	public function anonymousIterator()
	{
		return new \ArrayIterator($this->arrAnonymous) ;
	}
	
	public function remove($req)
	{
		if( is_string($req) )
		{
			parent::remove($req) ;
		}
		
		else if( $req instanceof \jc\ui\xhtml\AttributeValue )
		{
			$this->removeByValue($req) ;
			
			// anonymous attribute
			$key = array_search($req, $this->arrAnonymous) ;
			if($key!==false)
			{
				unset($this->arrAnonymous[$key]) ;
			}
		}
		
		else 
		{
			Type::check(array('string',__NAMESPACE__.'\\AttributeValue'),$req) ;
		}
	}
	
	public function get($sName)
	{
		$sType = $this->has($sName.'.type')? $this->string($sName.'.type'): 'string' ;
		if( !in_array($sType,self::$arrAttributeTypes) )
		{
			throw new Exception(
					"ui node 属性 %s.type 值无效：%s; 必须为以下值："
					, array($sName,$sType,implode(',', self::$arrAttributeTypes))
			) ; 
		}
		
		switch($sType)
		{
			case 'string' :
				return '"'.$this->string($sName).'"' ;
				
			case 'expression' :
				return $this->expression($sName) ;
				
			default :
				return $this->$sType($sName) ;
		}
	}
	
	public function string($sName)
	{
		return ($aText=parent::get($sName))? $aText->source() :null ;
	}
	public function bool($sName)
	{
		return !in_array( 
				strtolower( ($aText=parent::get($sName))?$aText->source():'' )
				, self::$arrFalseValues
				, true ) ;
	}
	public function int($sName)
	{
		return ($aText=parent::get($sName))?intval($aText->source()):0 ;
	}
	public function float($sName)
	{
		return ($aText=parent::get($sName))?floatval($aText->source()):0 ;
	}
	public function expression($sName)
	{
		return ($aText=parent::get($sName))? ExpressionCompiler::compileExpression($aText->source()): null ;
	}
	public function object($sName)
	{
		return parent::get($sName) ;
	}
	
	public function source()
	{
		return $this->sSource ;
	}
	public function setSource($sSource)
	{
		$this->sSource = $sSource ;
	}
	
	static public $arrFalseValues = array(
		'false', '0', 'off', '',
	) ;
	
	static private $arrAttributeTypes = array('string','int','float','bool','expression') ; 
	
	private $sSource ;
	private $arrAnonymous = array() ;
}

?>