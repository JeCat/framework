<?php
namespace jc\lang\aop ;

use jc\pattern\composite\Container;
use jc\pattern\composite\NamedObject;
use jc\pattern\iterate\ArrayIterator;
use jc\lang\Object;

class Pointcut extends NamedObject
{
	/**
	 * @return jc\pattern\IContainer
	 */
	public function jointPoints()
	{
		if( !$this->aJointPoints )
		{
			$this->aJointPoints = new Container('jc\\aop\\JointPoint') ;
		}
		
		return $this->aJointPoints ;
	}
	
	/**
	 * @return jc\pattern\IContainer
	 */
	public function advices()
	{
		if( !$this->aAdvices )
		{
			$this->aAdvices = new Container('jc\\aop\\Advice') ;
		}
		
		return $this->aAdvices ;
	}
	
	private $aAdvices ;
	
	private $aJointPoints ;
}

?>