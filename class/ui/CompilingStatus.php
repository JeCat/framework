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
		$aSourceFile = $this->sourceFile() ;
		return $aSourceFile? $aSourceFile->path(): null ;
	}
	public function compiledFilepath()
	{
		$aCompiledFile = $this->compiledFile() ;
		return $aCompiledFile? $aCompiledFile->path(): null ;
	}
	
	/**
	 * @return IFile
	 */
	public function sourceFile()
	{
		return isset($this->arrStatuses[__FUNCTION__])? $this->arrStatuses[__FUNCTION__]: null ;
	}
	
	/**
	 * @return IFile
	 */
	public function compiledFile()
	{
		return isset($this->arrStatuses[__FUNCTION__])? $this->arrStatuses[__FUNCTION__]: null ;
	}
	
	private $arrStatuses ;
}

?>