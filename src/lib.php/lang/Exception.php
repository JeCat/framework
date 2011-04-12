<?php
namespace jc\lang ;

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
		parent::__construct($sMessage, 0, $aCause) ;
	}
	
	public function message(ILocale $aLocale=null)
	{
		if( !$aLocale )
		{
			$aLocale = $this->application(true)->localeManager()->locale() ;
		}
		
		return $aLocale->trans($this->getMessage(),$this->arrArgvs) ;
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
	
	
	// for IJeCatObject //////////////////////////////////
	public function create($sClassName,$sNamespace='\\',array $arrArgvs=array())
	{
		$aObject = Factory::createNewObject($sClassName,$sNamespace,$arrArgvs) ;
		
		if( $aObject instanceof IObject )
		{
			$aObject->setApplication($this->application(true)) ;
		}
		
		return $aObject ;
	}
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
	
	private $arrArgvs = array() ;
}

?>