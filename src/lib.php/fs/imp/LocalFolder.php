<?php
namespace jc\fs ;


class LocalFolder extends LocalFSO implements IFolder
{
	/**
	 * @return \IFSO
	 */
	public function find($sPath)
	{
		return $this->fileSystem()->find(
				(substr($sPath,0,1)=='/')? $sPath: ($this->path().'/'.$sPath)
		) ;	
	}
	
	public function createFile()
	{
		return $this->fileSystem()->createFile(
				(substr($sPath,0,1)=='/')? $sPath: ($this->path().'/'.$sPath)
		) ;
	}
	
	public function createFolder()
	{
		return $this->fileSystem()->createFolder(
				(substr($sPath,0,1)=='/')? $sPath: ($this->path().'/'.$sPath)
		) ;
	}
	
	/**
	 * @return \Iterator
	 */
	public function iterator()
	{
		
	}
} 



?>