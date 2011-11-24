<?php
namespace jc\lang ;

use jc\locale\LocaleManager;
use jc\locale\ILocale ;
use jc\System\Application ;

class Exception extends \Exception implements IException, IObject
{
	/**
	 * Enter description here ...
	 * 
	 * @return void
	 */
	public function __construct($sMessage,$Argvs=array(),\Exception $aCause=null)
	{
		$this->arrArgvs = (array)$Argvs ;
		$this->sMessage = $sMessage ;
		
		parent::__construct($this->message(), 0, $aCause) ;
	}
	
	public function message(ILocale $aLocale=null)
	{
		if( !$aLocale and class_exists('LocaleManager'))
		{
			$aLocale = LocaleManager::singleton()->locale() ;
		}
		
		return $aLocale?
				$aLocale->trans($this->sMessage,$this->arrArgvs) :
				call_user_func_array('sprintf', array_merge(array($this->sMessage),$this->arrArgvs)) ;
	}
	
	public function code() 
	{
		return $this->getCode() ;
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
	
	public function messageArgvs()
	{
		return $this->arrArgvs ;
	}
	
	// for IJeCatObject //////////////////////////////////
	public function application($bDefaultGlobal=true)
	{
		if($this->aApplication)
		{
			return $this->aApplication ;
		}
		else 
		{
			return $bDefaultGlobal? Application::singleton(): null ;
		}
	}
	public function setApplication(Application $aApp)
	{
		$this->aApplication = $aApp ;
	}

	private $aApplication ;
	
	private $sMessage ;
	
	private $arrArgvs = array() ;
}

?>