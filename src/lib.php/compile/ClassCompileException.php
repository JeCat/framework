<?php
namespace jc\compile ;

use jc\lang\Exception;
use jc\compile\object\Token;

class ClassCompileException extends Exception
{
	public function __construct(Token $aCauseToken,$sMessage,$messageArgvs=array(),\Exception $aCause=null)
	{
		$this->aCauseToken = $aCauseToken ;
		
		parent::__construct($sMessage,$messageArgvs,$aCause) ;
	}
	
	/**
	 * @return jc\compile\object\Token
	 */
	public function causeToken()
	{
		return $this->aCauseToken ;
	}
	
	/**
	 * @var jc\compile\object\Token
	 */
	private $aCauseToken ;
}

?>