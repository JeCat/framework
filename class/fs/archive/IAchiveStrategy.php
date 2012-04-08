<?php
namespace org\jecat\framework\fs\archive ;

use org\jecat\framework\lang\Object;
use org\jecat\framework\fs\Folder;
use org\jecat\framework\fs\File;

abstract class IAchiveStrategy extends Object
{
	/**
	 * @return org\jecat\framework\fs\IFile
	 */
	abstract public function makeFilePath(array $arrUploadedFile,Folder $aToDir) ;

	public function restoreOriginalFilename(File $aAchiveFile)
	{
		if( preg_match('/^hash[0-9A-Fa-f]{32}\.(.+)$/s',$aAchiveFile->name(),$arrRes) )
		{
			return $arrRes[1] ;
		}
		
		return $aAchiveFile->name() ;
	}
	
	public function makeFilename(array $arrUploadedFile)
	{
		return 'hash'.md5($arrUploadedFile['tmp_name']).'.'.$arrUploadedFile['name'];
		// return 'hash'.$aFile->hash().'.'.$aFile->name() ;
	}
}