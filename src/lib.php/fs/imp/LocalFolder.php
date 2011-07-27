<?php
namespace jc\fs\imp ;

use jc\fs\IFolder;

class LocalFolder extends LocalFSO implements IFolder
{
	/**
	 * @return \IFSO
	 */
	public function findFile($sPath)
	{
		return $this->fileSystem()->findFile(
				(substr($sPath,0,1)=='/')? $sPath: ($this->path().'/'.$sPath)
		) ;	
	}

	/**
	 * @return \IFSO
	 */
	public function findFolder($sPath)
	{
		return $this->fileSystem()->findFolder(
				(substr($sPath,0,1)=='/')? $sPath: ($this->path().'/'.$sPath)
		) ;	
	}
	
	public function createFile($sPath)
	{
		return $this->fileSystem()->createFile(
				(substr($sPath,0,1)=='/')? $sPath: ($this->path().'/'.$sPath)
		) ;
	}
	
	public function createFolder($sPath)
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