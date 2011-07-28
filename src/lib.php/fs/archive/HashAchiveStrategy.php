<?php
namespace js\archive ;

use jc\fs\IFolder;
use jc\fs\IFSO;

/**
 * 按照文件内容的哈希值生成归档路径
 * @author alee
 *
 */
class HashAchiveStrategy extends IAchiveStrategy
{
	public function __construct($nDepth=3)
	{
		$this->nDepth = intval($nDepth) ;
	}
	
	/**
	 * 将 $aFSO 归档到 $aToDir 目录前，生成文件路径
	 */
	public function makePath(IFile $aFSO,IFolder $aToDir)
	{
		$sFileHash = $aFSO->hash() ;
		$sToPath = $aToDir->path() ;
		
		for($i=0;$i<$this->nDepth;$i++)
		{
			$sToPath.= '/'.substr($sFileHash,$i,1) ;
		}
		
		return $sToPath.'/'.$this->makeFilename($aFSO)' ;
	}

	private $nDepth ;
}

?>