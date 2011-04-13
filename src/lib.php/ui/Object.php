<?php

namespace jc\ui ;

use jc\pattern\composite\CompositeObject;

class Object extends CompositeObject implements IObject
{
	public function __construct()
	{
		$this->addChildTypes(__CLASS__) ;
	}

	// implement for IContainedable //////////////////
	static public function type()
	{
		return __CLASS__ ;
	}
}

?>