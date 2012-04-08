<?php
namespace org\jecat\framework\lang\compile ;

use org\jecat\framework\lang\Object;

use org\jecat\framework\pattern\iterate\ArrayIterator;
use org\jecat\framework\util\match\RegExp;

/**
 * 用于分析DocComment格式的类
 */
class DocComment extends Object
{
	public function __construct($sComment)
	{
		$this->sSource = $sComment ;
		
		$sComment = trim($sComment) ;
		
		// 统一换行符
		$sComment = str_replace("\r\n","\n", $sComment) ;
		$sComment = str_replace("\r","\n", $sComment) ;
		
		$arrLines = explode("\n", $sComment) ;
		
		// 第一行
		$sTopLine = array_shift($arrLines) ;
		if( !preg_match("|^\s*/\\*\\*\s*$|", $sTopLine) )
		{
			array_unshift($arrLines,$sTopLine) ;
		}
	
		// 最后一行
		$sEndLine = array_pop($arrLines) ;
		if( !preg_match("|^\s*\\*/\s*$|", $sEndLine) )
		{
			array_push($arrLines,$sEndLine) ;
		}
		
		$aRegexpItem = new RegExp("|^\\s*\\*\s*@([^\\s]+)(.*)?|") ;
		$aRegexpDesc = new RegExp("|^\\s*\\* ?(.*)$|") ;
		
		foreach($arrLines as $sLine)
		{
			// item
			if( $aResSet=$aRegexpItem->match($sLine) )
			{
				$sItemName = $aResSet->content(1) ;
				
				if( !isset($this->arrItems[$sItemName]) )
				{
					$this->arrItems[$sItemName] = array() ;
				}
				$this->arrItems[$sItemName][] = trim($aResSet->content(2)) ;
			}
			
			else if( $aResSet=$aRegexpDesc->match($sLine) )
			{
				if($this->sDescription)
				{
					$this->sDescription.= "\r\n" ;
				}
				
				$this->sDescription.= $aResSet->content(1) ;
			}
		} 
	}
	
	public function description()
	{
		return $this->sDescription ;
	}

	public function item($sName)
	{
		return isset($this->arrItems[$sName])? reset($this->arrItems[$sName]): null ;
	}
	
	public function items($sName)
	{
		return isset($this->arrItems[$sName])? $this->arrItems[$sName]: null ;
	}

	public function hasItem($sName)
	{
		return array_key_exists($sName,$this->arrItems) ;
	}

	public function itemNameIterator()
	{
		return new ArrayIterator(array_keys($this->arrItems)) ;
	}
	
	public function itemIterator($sName)
	{
		return isset($this->arrItems[$sName])?
				new ArrayIterator($this->arrItems[$sName]):
				new ArrayIterator() ;
	}
	
	public function source()
	{
		return $this->sSource ;
	}
		
	private $sSource = '' ;
	private $sDescription = '' ;
	private $arrItems = array() ;
}

?>