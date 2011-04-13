<?php

namespace jc\ui ;

use jc\pattern\composite\CompositeObject;

class UIObject extends CompositeObject
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