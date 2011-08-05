<?php
namespace jc\io ;

use jc\util\FilterMangeger;

/**
 * 注意：直接使用 ob_end_flush() 或 ob_end_clean() 等php原生函数，可能会导致 StdOutputFilterMgr 失效
 */
class StdOutputFilterMgr extends FilterMangeger 
{
	static public function singleton ($bCreateNew=true,$createArgvs=null,$sClass=null)
	{
		if( !$aInstance=self::singleton(false) )
		{
			$aInstance = new self() ;
			self::setSingleton($aInstance) ;
		}
		
		return $aInstance ;
	}
	
	protected function StdOutputFilterMgr()
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