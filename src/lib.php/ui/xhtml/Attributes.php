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
			if(!$req->name())
			{
				$key = array_search($req, $this->arrAnonymous) ;
				if($key!==false)
				{
					unset($this->arrAnonymous[$key]) ;
				}
			}
		}
		
		else 
		{
			Type::check(array('string',__NAMESPACE__.'\\AttributeValue'),$req) ;
		}
	}
	
	public function get($sName)
	{
		return $this->bool($sName.'.express')?
			$this->expression($sName) :
			(($aText=parent::get($sName))?'"'.$aText->source().'"':null) ;
	}
	
	public function string($sName)
	{
		return $aText=parent::get($sName)? $aText->source() :null ;
	}
	public function bool($sName)
	{
		return !in_array( strtolower(
				$aText=parent::get($sName))?$aText->source():''
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
	
	private $sSource ;
	private $arrAnonymous = array() ;
}

?>