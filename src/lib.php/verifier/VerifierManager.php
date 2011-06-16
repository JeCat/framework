<?php
namespace jc\verifier ;


use jc\lang\Object;

class VerifierManager extends Object
{
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
		return new \ArrayIterator($this->arrVerifiers) ;
	}
	
	public function verifyData(&$value,$bThrowExcetion=false)
	{		
		foreach($this->arrVerifiers as $nIdx=>$aVerifier)
		{
			try{
				
				$aVerifier->verify( $value, true ) ;
				
			} catch (VerifyFailed $e) {
				
				// 通过回调函数报告错误
				if( $this->arrVerifierOthers[$value][1] )
				{
					call_user_func_array(
							$this->arrVerifierOthers[$value][1]
							, array_merge(
									array( $value, $aVerifier, $e, $this->arrVerifierOthers[$value][0] )
									, (array)$this->arrVerifierOthers[$value][2]
							)
					) ;
				}
				
				// 抛出异常
				else if($bThrowExcetion)
				{
					if( $this->arrVerifierOthers[$value][0] )
					{
						throw new VerifyFailed($this->arrVerifierOthers[$value][0],null,$e) ;
					}
					else 
					{
						throw new $e ;
					}
				}
				
				return false ;
				
			}
		}
		
		return true;
	}
	
	private $arrVerifiers = array() ; 
	private $arrVerifierOthers = array() ; 
}

?>