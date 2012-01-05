<?php
namespace org\jecat\framework\auth ;

use org\jecat\framework\bean\BeanFactory;
use org\jecat\framework\bean\IBean;

class Authorizer implements IBean
{
	public function check(IdManager $aIdManager) 
	{
		foreach($this->arrPermissions as $aPermission)
		{
			if( !$aPermission->check($aIdManager) )
			{
				return false ;
			}
		}
		
		return true ;
	}
	
	public function requirePermission(IPermission $aPermission)
	{
		$this->arrPermissions[] = $aPermission ;		
		return $this ;
	}
	
	public function removePermission(IPermission $aPermission,$bRestrict=false)
	{
		$pos = array_search($aPermission,$this->arrPermissions,$bRestrict) ;
		if($pos!==false)
		{
			unset($this->arrPermissions[$pos]) ;
		}
		return $this ;
	}
	
	public function clearPermissions()
	{
		$this->arrPermissions = array() ;
		return $this ;
	}
	
	public function hasPermission(IPermission $aPermission,$bRestrict=false)
	{
		return array_search($aPermission,$this->arrPermissions,$bRestrict)!==false ;
	}
	
	public function permissionIterator()
	{
		return new \ArrayIterator($this->arrPermissions) ;
	}
	
	
	static public function createBean(array & $arrConfig,$sNamespace='*',$bBuildAtOnce,BeanFactory $aBeanFactory=null)
	{
		$aBean = new self() ;
		$aBean->arrBeanConfig = $arrConfig ;
		
		if($bBuildAtOnce)
		{
			if(!$aBeanFactory)
			{
				$aBeanFactory = BeanFactory::singleton() ;
			}
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
	
	private $arrPermissions = array() ;
	
	private $arrBeanConfig ;
}
