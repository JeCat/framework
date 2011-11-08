<?php
namespace jc\verifier ;

use jc\bean\IBean;
use jc\message\Message;
use jc\lang\Exception;
use jc\lang\Object;

class Length extends Object implements IVerifier, IBean
{
	public function __construct($nMinLen=-1,$nMaxLen=-1)
	{
		$this->nMinLen = $nMinLen ;
		$this->nMaxLen = $nMaxLen ;
	}

	public function build(array & $arrConfig)
	{
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