<?php
namespace jc\fs\archive ;

use jc\fs\IFolder;
use jc\fs\IFSO;

/**
 * 按照当前日期生成归档路径
 * 使用享元模式创建对象, 按照年、月、日分类目录归档文件：
 * 	$aAchiveStrategy = DateAchiveStrategy::flyweight( array(true,true,true) ) ;
 * 	$sPath = $aAchiveStrategy->makePath($aFile,$aFolder) ;
 *
 */
class DateAchiveStrategy extends IAchiveStrategy
{
	public function __construct($bYearly=true,$bMonthly=true,$bDaily=false,$bHourly=false)
	{
		$this->bYearly = $bYearly? true: false ;
		$this->bMonthly = $bMonthly? true: false ;
		$this->bDaily = $bDaily? true: false ;
		$this->bHourly = $bHourly? true: false ;
	}
	
	/**
	 * 将 $aFSO 归档到 $aToDir 目录前，生成文件路径
	 */
	public function makePath(IFile $aFSO,IFolder $aToDir)
	{
		$sToPath = $aToDir->path() ;
		
		if($this->bYearly)
		{
			$sToPath.= '/'.date('y') ;
		}
	
		if($this->bMonthly)
		{
			$sToPath.= '/'.date('n') ;
		}
	
		if($this->bDaily)
		{
			$sToPath.= '/'.date('j') ;
		}
	
		if($this->bHourly)
		{
			$sToPath.= '/'.date('H') ;
		}
		
		return $sToPath.'/'.$this->makeFilename($aFSO) ;
	}
}

?>