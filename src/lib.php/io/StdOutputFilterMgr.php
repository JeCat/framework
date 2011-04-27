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
		ob_start(array($this,'handle')) ;
		$this->start() ;
	}
}

?>