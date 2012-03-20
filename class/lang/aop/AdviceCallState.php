<?php
namespace org\jecat\framework\lang\aop ;

use org\jecat\framework\lang\Exception;

class AdviceCallState
{
	public function setReturn($value)
	{
		$this->returnValue =& $value ;
	}
	
	public function setReturnRef(&$value) 
	{
		$this->returnValue =& $value ;
	}
	
	public function originMethod()
	{
		return $this->sOriginClass . ($this->aOriginObject? '->': '::') . $this->sOriginMethod . '()' ;
	}

	/*
	public function __construct(array & $arrAdvices,$sOriginMethod)
	{
		$this->arrAdvices =& $arrAdvices ;
		$this->sOriginMethod =& $sOriginMethod ;
	}
	
	public function callOrigin()
	{
		if(!$this->bExecutingAroundAdvice)
		{
			throw new Exception("错误的 AOP Advice 调用：无法在 before 或 after advice 中调用原始函数") ;
		}
		
		// 调用 advice 
		if( is_object($this->arrAdvices['around']) and $arrAdvice=&current($this->arrAdvices['around']) )
		{
			next($this->arrAdvices['around']) ;
			$this->callAdvice($arrAdvice,$bReturn) ;
		}
		
		// 调用原始函数
		else
		{
			$this->returnValue =& call_user_func_array(
					array($this->aOriginObject,$this->sOriginMethod)
					, $this->arrAvgvs
			) ;
		}
	}
	
	public function execute($aOriginObject,array & $arrArgvs=array())
	{
		$this->aOriginObject =& $aOriginObject ;
		$this->arrArgvs =& $arrArgvs ;
		
		// 陆续调用 before advice
		if(is_object($this->arrAdvices['before']))
		{
			$this->bExecutingBeforeAdvice = true ;
			foreach($this->arrAdvices['before'] as &$arrAdvice)
			{
				$this->callAdvice($arrAdvice) ;
			}
			$this->bExecutingBeforeAdvice = false ;
		}
		
		// 调用最外层的 around advice
		if(is_object($this->arrAdvices['around']))
		{
			reset($this->arrAdvices['around']) ;
		}
		$this->bExecutingAroundAdvice = true ;
		$this->callOrigin() ;
		$this->bExecutingAroundAdvice = false ;
		
		
		// 陆续调用 after advice
		if(is_object($this->arrAdvices['after']))
		{
			$this->bExecutingAfterAdvice = true ;
			foreach($this->arrAdvices['after'] as &$arrAdvice)
			{
				$this->callAdvice($arrAdvice) ;
			}
			$this->bExecutingAfterAdvice = true ;
		}
		
		$this->bExecuting = false ;
	}
	
	private function callAdvice(&$arrAdvice)
	{
		call_user_func(
				(is_array($arrAdvice) and $arrAdvice[0]===null)?
						array($this->aOriginObject,$arrAdvice[1]):$arrAdvice
				, $this)  ;
	}
	
	*/
	
	//private $arrAdvices ;
	
	public $aOriginObject ;
	public $sOriginClass ;
	public $sOriginMethod ;
	
	public $arrAvgvs = array() ;
	public $returnValue ;
	
	/*private $bExecutingBeforeAdvice = false ;
	private $bExecutingAroundAdvice = false ;
	private $bExecutingAfterAdvice = false ;*/
	
}

?>