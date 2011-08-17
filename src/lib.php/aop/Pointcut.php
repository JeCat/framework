<?php
namespace jc\aop ;

use jc\pattern\iterate\ArrayIterator;
use jc\lang\Object;

class Pointcut extends Object
{
	public function addJointPoint(JointPoint $aJointPoint)
	{
		
	}
	
	public function removeJointPoint(JointPoint $aJointPoint)
	{
		
	}
	
	/**
	 * @return \Iterator
	 */
	public function jointPointIterator()
	{
		return new ArrayIterator($this->arrJointPoints) ;
	}
	
	public function clearJointPoints()
	{
		
	}
	
	public function addAdvice(Advice $aAdvice)
	{
		
	}
	
	public function hasAdvice($sFnName)
	{
		
	}
	
	public function removeAdvice($sFnName)
	{
		
	}
	
	/**
	 * @return \Iterator
	 */
	public function adviceIterator()
	{
		return new ArrayIterator($this->arrAdvices) ;
	}
	
	public function clearAdvices()
	{
		
	}
	
	private $arrJointPoints = array() ;
	
	private $arrAdvices = array() ;
}

?>