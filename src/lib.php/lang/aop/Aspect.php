<?php
namespace jc\lang\aop ;

use jc\pattern\composite\Container;
use jc\pattern\composite\NamedObject;

class Aspect extends NamedObject
{
	/**
	 * @return jc\pattern\IContainer
	 */
	public function pointcuts()
	{
		if( !$this->aPointcuts )
		{
			$this->aPointcuts = new Container('jc\\aop\\Pointcut') ;
		}
		
		return $this->aPointcuts ;
	}
	
	private $aPointcuts ;
	
}

?>