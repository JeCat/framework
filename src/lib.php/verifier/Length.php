<?php
namespace jc\mvc\view\widget ;

use jc\lang\Exception;
use jc\lang\Object;

class Length extends Object implements IVerifier
{
	public function __construct($nMinLen,$nMaxLen)
	{
		$this->nMinLen = $nMinLen ;
		$this->nMaxLen = $nMaxLen ;
	}

	public function verifier($data)
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
			throw new Exception("不能小于%d",array($this->nMinLen)) ;
		}
		if( $this->nMaxLen>=0 and $this->nMaxLen<$nLen )
		{
			throw new Exception("不能大于%d",array($this->nMaxLen)) ;
		}
		if( !$this->bAllowEmpty and $nLen<=0 )
		{
			throw new Exception("不能为空") ;
		}
		
		return true ;
	}

	private $bAllowEmpty = true ;
	private $nMaxLen = -1 ;
	private $nMinLen = -1 ;
}

?>