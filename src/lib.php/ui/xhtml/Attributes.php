<?php

namespace jc\ui\xhtml ;

use jc\io\IOutputStream;
use jc\util\HashTable;

class Attributes extends HashTable
{
	public function compile(IOutputStream $aDev) 
	{
		foreach ($this->nameIterator() as $sName)
		{
			$aDev->write(" ") ;
			$aDev->write($sName) ;
			$aDev->write('="') ;
			$aDev->write(addslashes($this->get($sName))) ;
			$aDev->write('"') ;
		}
	}
	
	public function get($sName)
	{
		return $this->bool($sName.'.express')?
			$this->expression($sName) :
			(($aText=parent::get($sName))?$aText->source():null) ;
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
}

?>