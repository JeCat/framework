<?php
namespace org\jecat\framework\lang\compile ;

use org\jecat\framework\fs\File ;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\compile\object\Token;

class ClassCompileException extends Exception
{
	public function __construct(File $aClassSouce=null,Token $aCauseToken,$sMessage,$messageArgvs=array(),\Exception $aCause=null)
	{
		$this->aClassSouce = $aClassSouce ;
		$this->aCauseToken = $aCauseToken ;
		
		parent::__construct($sMessage,$messageArgvs,$aCause) ;
	}
	
	/**
	 * @return org\jecat\framework\lang\compile\object\Token
	 */
	public function causeToken()
	{
		return $this->aCauseToken ;
	}
	
	public function setClassSouce(File $aClassSouce)
	{
		$this->aClassSouce = $aClassSouce ;
	}
	
	/**
	 * @return org\jecat\framework\fs\File
	 */
	public function classSouce()
	{
		return $this->aClassSouce ;
	}
	
	/**
	 * @var org\jecat\framework\lang\compile\object\Token
	 */
	private $aCauseToken ;
	
	/**
	 * @var org\jecat\framework\fs\File
	 */
	private $aClassSouce ;
}

?>