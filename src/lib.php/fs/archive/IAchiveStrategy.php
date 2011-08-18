<?php
namespace jc\fs\archive ;

use jc\lang\Object;
use jc\fs\IFolder;
use jc\fs\IFile;

abstract class IAchiveStrategy extends Object
{
	/**
	 * @return jc\fs\IFile
	 */
	abstract public function makeFilePath(IFile $aOriginalFile,IFolder $aToDir) ;

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