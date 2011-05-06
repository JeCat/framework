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
			parent::get($sName) ;
	}
	
	public function bool($sName)
	{
		return in_array( strtolower(parent::get(sName)),self::$arrFalseValues,true) ;
	}
	public function int($sName)
	{
		return intval(parent::get(sName)) ;
	}
	public function float($sName)
	{
		return floatval(parent::get(sName)) ;
	}
	public function expression($sName)
	{
		return ExpressionCompiler::compileExpression(parent::get(sName)) ;
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