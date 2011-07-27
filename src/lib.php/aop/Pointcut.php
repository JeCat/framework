<?php
namespace jc\aop ;

use jc\pattern\iterate\ArrayIterator;
use jc\lang\Object;

class Pointcut extends Object
{
	const around = 'around' ;
	const before = 'before' ;
	const after = 'after' ;
	
	static private $arrTypes = array(
		self::around, self::before, self::after
	) ;
	
	public function __construct($fnAdvice,$type=self::around,$jointPoints=array())
	{
		$this->fnAdvice = $fnAdvice ;
	}

	public function addJointPoint(JointPoint $aJointPoint)
	{
		
	}
	
	public function iteratorJointPoints()
	{
		return new ArrayIterator($this->arrJointPoints) ;
	}
	
	private $fnAdvice ;
	private $arrJointPoints ;
}

?>