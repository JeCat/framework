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
		return $this->fileSystem()->rootFileSystem()->findFolder(
				(substr($sPath,0,1)=='/')? $sPath: ($this->path().'/'.$sPath)
		) ;	
	}

	public function create($nMode=0755,$bRecursive=true)
	{
		return mkdir($this->localPath(),$nMode,$bRecursive) ;
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
	public function iterator($nFlag=\jc\fs\FSIterator::FLAG_DEFAULT)
	{
		return new LocalFolderIterator($this,$nFlag);
	}
	
	public function exists()
	{
		return is_dir($this->localPath());
	}
} 



?>
