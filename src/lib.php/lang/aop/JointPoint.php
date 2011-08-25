<?php
namespace jc\aop ;

use jc\lang\Exception;

class JointPoint
{
	const ACCESS_SET = 'set' ;
	const ACCESS_GET = 'get' ;
	const ACCESS_ANY = '*' ;
	
	/**
	 * @return JointPoint
	 */
	static public function createCallFunction($sFuncName='*',$sClassName='')
	{
		$sClass = get_called_class() ;
		
		$aJointPoint = new $sClass() ;
		$aJointPoint->setExecutionPattern("{$sClassName}::{$sFuncName}()") ;
		return $aJointPoint ;
	}
	
	/**
	 * @return JointPoint
	 */
	static public function createAccessProperty($sClassName='*',$sPropertyName='*',$sAccess=self::ACCESS_ANY)
	{
		if( !in_array($sAccess, array(self::ACCESS_SET,self::ACCESS_GET,self::ACCESS_ANY)) )
		{
			throw new Exception('参数$sAccess值不合法，必须为：%s，输入值为“%s”',array(implode(',', array(self::ACCESS_SET,self::ACCESS_GET,self::ACCESS_ANY)),$sAccess)) ;
		}
		
		$sClass = get_called_class() ;
		
		$aJointPoint = new $sClass() ;
		$aJointPoint->setExecutionPattern("{$sClassName}::\${$sPropertyName} {$sAccess}") ;
		return $aJointPoint ;
	}
	
	/**
	 * @return JointPoint
	 */
	static public function createThrowException($sClassName='*')
	{
		$sClass = get_called_class() ;
		
		$aJointPoint = new $sClass() ;
		$aJointPoint->setExecutionPattern("throw {$sClassName}") ;
		return $aJointPoint ;
	}
	
	/**
	 * @return JointPoint
	 */
	static public function createNewObject($sClassName='*')
	{
		$sClass = get_called_class() ;
		
		$aJointPoint = new self() ;
		$aJointPoint->setExecutionPattern("new {$sClassName}") ;
		return $aJointPoint ;
	}
	
	
	//////////////////////////////////////////////////////////////////
	
	public function setExecutionPattern($sPartten)
	{
		$this->setExecutionRegexp(self::transRegexp($sPartten)) ;
	}
	
	public function setExecutionRegexp($sRegexp)
	{
		$this->sExecutionRegexp = $sRegexp ;
	}
	
	public function executionRegexp()
	{
		return $this->sExecutionRegexp ;
	}
	
	public function setCallTrac($sPartten=null)
	{
		if($sPartten)
		{
			$this->sCallTracRegexp = self::transRegexp($sPartten) ;
		}
		else 
		{
			$this->sCallTracRegexp = null ;
		}
	}
	
	public function callTracRegexp()
	{
		return $this->sCallTracRegexp ;
	}
	
	static public function transRegexp($sPartten)
	{
		$sPartten = preg_quote($sPartten) ;
		$sPartten = str_replace('\\*', '.*', $sPartten) ;
		
		return '`' . $sPartten . '`is' ;
	} 
	
	private $sExecutionRegexp ;
	
	private $sCallTracRegexp ;
	
}

?>