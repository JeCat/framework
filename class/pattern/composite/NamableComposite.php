<?php

namespace org\jecat\framework\pattern\composite ;


class NamableComposite extends Composite implements INamable
{
	public function name()
	{
		return $this->sName ;
	}
	
	public function setName($sName)
	{
		$this->sName = $sName ;
	}
	
	private $sName ;
	
}

?>