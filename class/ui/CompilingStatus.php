<?php
namespace org\jecat\framework\ui ;

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
	public function template()
	{
		return isset($this->arrStatuses[__FUNCTION__])? $this->arrStatuses[__FUNCTION__]: null ;
	}
	public function sourceFilepath()
	{
		$aSourceFile = $this->sourceFile() ;
		return $aSourceFile? $aSourceFile->path(): null ;
	}
	public function compiledFilepath()
	{
		$aCompiledFile = $this->compiledFile() ;
		return $aCompiledFile? $aCompiledFile->path(): null ;
	}
	
	/**
	 * @return File
	 */
	public function sourceFile()
	{
		return isset($this->arrStatuses[__FUNCTION__])? $this->arrStatuses[__FUNCTION__]: null ;
	}
	
	/**
	 * @return File
	 */
	public function compiledFile()
	{
		return isset($this->arrStatuses[__FUNCTION__])? $this->arrStatuses[__FUNCTION__]: null ;
	}
	
	private $arrStatuses ;
}

?>