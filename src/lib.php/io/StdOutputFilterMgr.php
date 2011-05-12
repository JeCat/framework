<?php
namespace jc\io ;

use jc\util\FilterMangeger;

/**
 * 注意：直接使用 ob_end_flush() 或 ob_end_clean() 等php原生函数，可能会导致 StdOutputFilterMgr 失效
 */
class StdOutputFilterMgr extends FilterMangeger 
{
	public function __construct()
	{
		ob_start(array($this,'handleForStdOutput')) ;
		$this->start() ;
	}
		
	public function handleForStdOutput($sData)
	{
		$Ret = $this->handle($sData) ;
		
		return (is_array($Ret) and isset($Ret[0]))? $Ret[0]: null ;
	}
}

?>