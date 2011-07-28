<?php
namespace js\archive ;

use jc\lang\Object;
use jc\fs\IFolder;
use jc\fs\IFSO;

abstract class IAchiveStrategy extends Object
{
	/**
	 * 将 $aFSO 归档到 $aToDir 目录前，生成文件路径
	 */
	abstract public function makePath(IFile $aFSO,IFolder $aToDir) ;

	public function restoreOriginalFilename(IFile $aAchiveFile)
	{
		if( preg_match('/^hash[0-9A-Fa-f]{32}\.(.+)$/s',$aAchiveFile->name(),$arrRes) )
		{
			return $arrRes[1] ;
		}
		
		return $aAchiveFile->name() ;
	}
	
	public function makeFilename(IFile $aFile)
	{
		return 'hash'.$aFile->hash().'.'.$aFile->name() ;
	}
}

?>