<?php
namespace org\jecat\framework\auth ;

use org\jecat\framework\bean\BeanFactory;
use org\jecat\framework\bean\IBean;
use org\jecat\framework\lang\Object;

abstract class PermissionBase extends Object implements IPermission,IBean
{
	public function __construct($bNecessary=false)
	{
		$this->setNecessary($bNecessary) ;
	}
	
	public function isNecessary()
	{
		return $this->bNecessary ; 
	}
	
	public function setNecessary($bNecessary=true)
	{
		$this->bNecessary = $bNecessary ;
		return $this ;
	}
	
	// implements IBean 
	static public function createBean(array & $arrConfig,$sNamespace='*',$bBuildAtOnce,BeanFactory $aBeanFactory=null)
	{
		$sClass = get_called_class() ;
		$aBean = new $sClass() ;
    	if($bBuildAtOnce)
    	{
    		$aBean->buildBean($arrConfig,$sNamespace,$aBeanFactory) ;
    	}
    	return $aBean ;		
	}
	
	public function buildBean(array & $arrConfig,$sNamespace='*',BeanFactory $aBeanFactory=null)
	{
		$this->arrBeanConfig = $arrConfig ;
	}
	
	public function beanConfig()
	{
		return $this->arrBeanConfig ;
	}
	
	
	private $bNecessary = false ;
	
	protected $arrBeanConfig ;
}

