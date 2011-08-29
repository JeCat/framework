<?php
namespace jc\lang\compile ;

use jc\lang\Object;

use jc\pattern\iterate\ArrayIterator;
use jc\util\match\RegExp;

/**
 * 用于分析DocComment格式的类
 */
class DocComment extends Object
{
	public function __construct($sComment)
	{
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

	public function itemIterator()
	{
		return new ArrayIterator(array_keys($this->arrItems)) ;
	}
		
	private $sDescription = '' ;
	private $arrItems = array() ;
}

?>