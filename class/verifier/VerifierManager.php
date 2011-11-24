<?php
namespace org\jecat\framework\verifier ;


use org\jecat\framework\lang\Object;

class VerifierManager extends Object
{
	
	public function __construct($bLogic = true){
		$this->setLogic($bLogic);
	}
	
	public function add(IVerifier $aVerifier, $sExceptionWords=null, $callback=null, $arrCallbackArgvs=array()) 
	{
		if( in_array($aVerifier,$this->arrVerifiers) )
		{
			return $this ;
		}
		
		$this->arrVerifiers[] = $aVerifier ;
		
		$nIdx = array_search($aVerifier, $this->arrVerifiers) ;
		$this->arrVerifierOthers[$nIdx] = array(
					$sExceptionWords, $callback, $arrCallbackArgvs
		) ;
		
		// 连续操作
		return $this ;
	}
	
	public function setLogic($bLogic){
		$this->bLogic = (bool)$bLogic;
		return $this ;
	}
	
	public function logic(){
		return $this->bLogic;
	}
	
	public function remove(IVerifier $aVerifier)
	{
		$nIdx = array_search($aVerifier, $this->arrVerifiers) ;
		if( $nIdx===false )
		{
			return ;
		}
		
		unset($this->arrVerifiers[$nIdx]) ;
		unset($this->arrVerifierOthers[$nIdx]) ;
	}
	
	public function clear()
	{
		$this->arrVerifiers = array() ;
		$this->arrVerifierOthers = array() ;
	}
	
	public function count()
	{
		return count($this->arrVerifiers) ;
	}
	
	public function iterator()
	{
		return new \org\jecat\framework\pattern\iterate\ArrayIterator($this->arrVerifiers) ;
	}
	
	public function verifyData($value,$bThrowExcetion=false)
	{		
		$aVerifyFailed = new VerifyFailed(''); 
		foreach($this->arrVerifiers as $nIdx=>$aVerifier)
		{
			try{
				
				$aVerifier->verify( $value, true ) ;
				
			} catch (VerifyFailed $e) {
				
				// 通过回调函数报告错误
				if( $this->arrVerifierOthers[$nIdx][1] )
				{
					call_user_func_array(
							$this->arrVerifierOthers[$nIdx][1]
							, array_merge(
									array( $value, $aVerifier, $e, $this->arrVerifierOthers[$nIdx][0] )
									, (array)$this->arrVerifierOthers[$nIdx][2]
							)
					) ;
				}
				
				// 抛出异常
				else if($bThrowExcetion)
				{
					if( $this->arrVerifierOthers[$nIdx][0] )
					{
						throw new VerifyFailed($this->arrVerifierOthers[$nIdx][0],null,$e) ;
					}
					else 
					{
						throw $e ;
					}
				}
				return false ;
			}
		}
		
		return true;
	}
	
	private $arrVerifiers = array() ; 
	private $arrVerifierOthers = array() ; 
	private $bLogic = true; //true校验器之间为and关系, false校验器之间为or关系
}

?>