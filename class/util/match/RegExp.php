<?php

namespace org\jecat\framework\util\match ;

use org\jecat\framework\util\String;

use org\jecat\framework\lang\Object;

class RegExp extends Object
{
	public function __construct($sFullRegExp)
	{
		$this->sFullRegExp = $sFullRegExp ;
	}
	
	public function fullRegExp() 
	{
		return $this->sFullRegExp ;
	}
	
	function setFullRegExp($sFullRegExp) 
	{
		$this->sFullRegExp = $sFullRegExp ;
	}
	
	/**
	 * @return ResultSet
	 */
	function match($sSource,$nLimit=-1)
	{
		$arrResult = array() ;
		
		if($nLimit==1)
		{
			if(!preg_match($this->fullRegExp(),$sSource,$arrResult,PREG_OFFSET_CAPTURE))
			{
				return null ;
			}
			$arrResult = array( $arrResult ) ;
		}
		
		else
		{
			if(!preg_match_all($this->fullRegExp(),$sSource,$arrResult,PREG_SET_ORDER|PREG_OFFSET_CAPTURE))
			{
				return null ;
			}
			
			if($nLimit>0)
			{
				$arrResult = array_slice($arrResult,0,$nLimit) ;
			}
		}
		
		$aResSet = new ResultSet() ;
		
		foreach($arrResult as $arrOneResult)
		{
			$aResSet->add( new Result($arrOneResult) ) ;
		}
		
		return $aResSet ;
	}
	
	public function callbackReplace($Source,$callback,$nLimit=-1)
	{
		$sSource = strval($Source) ;
		
		$aResSet = $this->match($sSource,$nLimit) ;
		$aResSet->reverse() ;
		
		foreach($aResSet as $aRes)
		{
			$sTo = call_user_func_array($callback, array($aRes)) ;
			$sSource = substr_replace($sSource,$sTo,$aRes->position(),$aRes->length()) ;
		}
		
		if($Source instanceof String)
		{
			$Source->set($sSource) ;
			return $Source ;
		}
		else 
		{
			return $sSource ;
		}
	}
	public function replace($sSource,$sTo,$nLimit=-1)
	{
		
	}
	
	public function split($sSource,$nLimit=-1)
	{
		
	}
	
	private $sFullRegExp ;
}

?>