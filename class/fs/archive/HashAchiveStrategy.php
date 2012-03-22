<?php
namespace org\jecat\framework\fs\archive ;

use org\jecat\framework\fs\IFolder;
use org\jecat\framework\fs\IFile;

/**
 * 按照文件内容的哈希值生成归档路径
 * 使用享元模式创建对象, 按照文件哈希值字符串的前3位字符创建分类目录：
 * 	$aAchiveStrategy = DateAchiveStrategy::flyweight( 3 ) ;
 * 	$sPath = $aAchiveStrategy->makePath($aFile,$aFolder) ;
 *
 */
class HashAchiveStrategy extends IAchiveStrategy
{
	public function __construct($nDepth=3)
	{
		$this->nDepth = intval($nDepth) ;
	}
	
	/**
	 * @return org\jecat\framework\fs\IFile
	 */
	public function makeFilePath(array $arrUploadedFile,IFolder $aToDir) 
	{
		$sFileHash = md5($arrUploadedFile['tmp_name']) ;
		$sToPath = $aToDir->path() ;
		
		for($i=0;$i<$this->nDepth;$i++)
		{
			$sToPath.= '/'.substr($sFileHash,$i,1) ;
		}
		
		return $sToPath.'/';
	}

	private $nDepth ;
}

?>
