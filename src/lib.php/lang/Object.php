<?php
namespace jc\lang ;

use jc\system\Application ;

class Object implements IObject
{
	public function __construct()
	{
		// 从调用堆栈上设置 application
		$arrStrace = debug_backtrace() ;
		for (end($arrStrace);$arrStack=current($arrStrace);prev($arrStrace))
		{
			if( !empty($arrStack['object']) and ($arrStack['object'] instanceof self) and $aApp=$arrStack['object']->application() )
			{
				$this->setApplication($aApp) ;
				break ;
			}
		}
	}
		
	/**
	 * Enter description here ...
	 * 
	 * @return stdClass
	 */
	public function create($sClassName,$sNamespace='\\',array $arrArgvs=array())
	{		
		$aObject = Factory::createNewObject($sClassName,$sNamespace,$arrArgvs) ;
		
		if( $aObject instanceof IObject )
		{
			$aObject->setApplication($this->application(true)) ;
		}
		
		return $aObject ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return jc\system\Application
	 */
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
	
	/**
	 * Enter description here ...
	 * 
	 * @return jc\system\Application
	 */
	public function setApplication(Application $aApp)
	{
		$this->aApplication = $aApp ;
	}

	static public function singleton ($bCreateNew=true)
	{
		$sClass = get_called_class() ;

		if( empty(self::$arrGlobalInstancs[$sClass]) ) 
		{
			if($bCreateNew)
			{
				self::$arrGlobalInstancs[$sClass] = new static() ;
			}
			else 
			{
				return null ;
			}
		}
		
		return self::$arrGlobalInstancs[$sClass] ;
	}
	static public function setSingleton (self $aInstance)
	{
		$sClass = get_called_class() ;
		
		if( !($aInstance instanceof static) )
		{
			throw new Exception('%s::setSingleton() �Ĳ������Ϊ%s����',array($sClass,$sClass)) ;
		}
		self::$arrGlobalInstancs[$sClass] = $aInstance ;
	}
	
	static private $arrGlobalInstancs = array() ;
	
	private $aApplication ;
}
?>