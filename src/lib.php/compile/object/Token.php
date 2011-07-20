<?php
namespace jc\compile\object ;

class Token extends AbstractObject
{
	public function __construct($nType,$sSource,$nPostion)
	{
		parent::__construct($sSource,$nPostion) ;
		
		$this->nType = $nType ;
	}

	public function type()
	{
		return $this->nType ;
	}
	
	private $nType ; 
}

?>