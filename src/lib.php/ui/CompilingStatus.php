<?php
namespace jc\ui ;

class CompilingStatus
{
	public function __construct(array $arrStatuses)
	{
		$this->arrStatuses = $arrStatuses ;
	}
	
	public function sourceNamespace()
	{
		return isset($this->arrStatuses[__FUNCTION__])? $this->arrStatuses[__FUNCTION__]: null ;
	}
	public function sourceFilename()
	{
		return isset($this->arrStatuses[__FUNCTION__])? $this->arrStatuses[__FUNCTION__]: null ;
	}
	public function sourceFilepath()
	{
		return isset($this->arrStatuses[__FUNCTION__])? $this->arrStatuses[__FUNCTION__]: null ;
	}
	public function compiledFilepath()
	{
		return isset($this->arrStatuses[__FUNCTION__])? $this->arrStatuses[__FUNCTION__]: null ;
	}
	
	private $arrStatuses ;
}

?>