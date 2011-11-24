<?php
namespace org\jecat\framework\verifier ;

use org\jecat\framework\bean\IBean;
use org\jecat\framework\message\Message;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\Object;

class Length extends Object implements IVerifier, IBean
{
	public function __construct($nMinLen=-1,$nMaxLen=-1)
	{
		$this->nMinLen = $nMinLen ;
		$this->nMaxLen = $nMaxLen ;
	}

	public function build(array & $arrConfig,$sNamespace='*')
	{
		if( !empty($arrConfig['min']) )
		{
			$this->nMinLen = (int)$arrConfig['min'] ;
		}
		if( !empty($arrConfig['max']) )
		{
			$this->nMaxLen = (int)$arrConfig['max'] ;
		}
		$this->arrConfig = $arrConfig;
	}
	
	public function beanConfig()
	{
		return $this->arrConfig;
	}
	
	public function verify($data,$bThrowException)
	{
		if( is_array($data) )
		{
			$nLen = count($data) ;
		}
		
		else 
		{
			$nLen = strlen($data) ;
		}
		
		if( $this->nMinLen>=0 and $this->nMinLen>$nLen )
		{
			if($bThrowException)
			{
				throw new VerifyFailed("不能小于%d",array($this->nMinLen)) ;
			}
			return false ;
		}
		if( $this->nMaxLen>=0 and $this->nMaxLen<$nLen )
		{
			if($bThrowException)
			{
				throw new VerifyFailed("不能大于%d",array($this->nMaxLen)) ;
			}
			return false ;
		}
		if( !$this->bAllowEmpty and $nLen<=0 )
		{
			if($bThrowException)
			{
				throw new VerifyFailed("不能为空") ;
			}
			return false ;
		}
		
		return true ;
	}

	private $arrConfig = array();
	private $bAllowEmpty = true ;
	private $nMaxLen = -1 ;
	private $nMinLen = -1 ;
}

?>