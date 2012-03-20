<?php
namespace org\jecat\framework\fs\archive ;

use org\jecat\framework\fs\Folder;

use org\jecat\framework\fs\IFolder;
use org\jecat\framework\fs\IFile;

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
	 * @return org\jecat\framework\fs\IFile
	 */
	public function makeFilePath(IFile $aOriginalFile,IFolder $aToDir) 
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
		
		return  $sToPath.'/';
	}
}

?>