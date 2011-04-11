<?php
namespace jc\lang ;

use jc\locale\ILocale ;

class Exception extends \Exception implements IException
{
	/**
	 * Enter description here ...
	 * 
	 * @return void
	 */
	public function __construct($sMessage,$arrArgvs=array(),\Exception $aCause=null)
	{
		$this->arrArgvs = $arrArgvs ;
		parent::__construct($sMessage, 0, $aCause) ;
	}
	
	public function message(ILocale $aLocale=null)
	{
		return $this->getMessage() ;
	}
	
	public function code() 
	{
		return $this->getMessage() ;
	}
	
	public function file()
	{
		return $this->getFile() ;
	}
	
	public function line()
	{
		return $this->getLine() ;
	}
	
	public function trace()
	{
		return $this->getTrace() ;
	}
	
	private $arrArgvs = array() ;
}

?>