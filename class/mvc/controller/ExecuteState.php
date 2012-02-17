<?php
namespace org\jecat\framework\mvc\controller ;

use org\jecat\framework\lang\Object;

class ExecuteState extends Object 
{
	public function __construct($bSystemClass)
	{
		$this->bSystemClass = $bSystemClass ;
	}
	
	public function isSystemClass()
	{
		return $this->bSystemClass ;
	}
	
	private $bSystemClass = true ;
}

?>