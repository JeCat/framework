<?php
namespace jc\lang\compile ;

use jc\fs\IFile ;
use jc\lang\Exception;
use jc\lang\compile\object\Token;

class ClassCompileException extends Exception
{
	public function __construct(IFile $aClassSouce=null,Token $aCauseToken,$sMessage,$messageArgvs=array(),\Exception $aCause=null)
	{
		$this->aClassSouce = $aClassSouce ;
		$this->aCauseToken = $aCauseToken ;
		
		parent::__construct($sMessage,$messageArgvs,$aCause) ;
	}
	
	/**
	 * @return jc\lang\compile\object\Token
	 */
	public function causeToken()
	{
		return $this->aCauseToken ;
	}
	
	public function setClassSouce(IFile $aClassSouce)
	{
		$this->aClassSouce = $aClassSouce ;
	}
	
	/**
	 * @return jc\fs\IFile
	 */
	public function classSouce()
	{
		return $this->aClassSouce ;
	}
	
	/**
	 * @var jc\lang\compile\object\Token
	 */
	private $aCauseToken ;
	
	/**
	 * @var jc\fs\IFile
	 */
	private $aClassSouce ;
}

?>