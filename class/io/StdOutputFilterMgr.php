<?php
namespace org\jecat\framework\io ;

use org\jecat\framework\util\FilterMangeger;

/**
 * 注意：直接使用 ob_end_flush() 或 ob_end_clear() 等php原生函数，可能会导致 StdOutputFilterMgr 失效
 */
class StdOutputFilterMgr extends FilterMangeger 
{
	public function __construct()
	{
		$this->start() ;
	}
		
	public function handleForStdOutput($sData)
	{
		$Ret = $this->handle($sData) ;
		
		return (is_array($Ret) and isset($Ret[0]))? $Ret[0]: null ;
	}
	
	public function start()
	{
		ob_start(array($this,'handleForStdOutput')) ;
		parent::start() ;
	}

	public function stop()
	{
		ob_end_flush() ;		
		parent::stop() ;
	}
}

?>