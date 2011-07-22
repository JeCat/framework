<?php
namespace jc\compile\object ;

class Expression extends AbstractObject
{
	public function __construct(AbstractObject $aBegin)
	{
		$this->aBegin = $aBegin ;
	}
	
	public function begin()
	{
		return $this->aBegin ;
	}
	
	public function end()
	{
		return $this->aEnd ;
	}
	
	private $aBegin ;
	private $aEnd ;
}

?>