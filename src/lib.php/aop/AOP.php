<?php
namespace jc\aop ;

use jc\pattern\composite\Container;
use jc\lang\Object;

class AOP extends Object
{	
	/**
	 * @return jc\pattern\IContainer
	 */
	public function aspects()
	{
		if( !$this->aAspects )
		{
			$this->aAspects = new Container('jc\\aop\\Aspect') ;
		}
		
		return $this->aAspects ;
	}
	
	private $aAspects ;
}

?>