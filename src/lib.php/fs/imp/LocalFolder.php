<?php
namespace jc\fs\imp ;

use jc\fs\IFolder;
use jc\fs\FileSystem;

class LocalFolder extends LocalFSO implements IFolder
{
	/**
	 * @return \IFSO
	 */
	public function findFile($sPath)
	{
		return $this->fileSystem()->rootFileSystem()->findFile(
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

	public function create($nMode=FileSystem::CREATE_FOLDER_DEFAULT)
	{
		return mkdir(
			$this->localPath()
			, ($nMode&FileSystem::CREATE_PERM_BITS)
			, $nMode&FileSystem::CREATE_RECURSE_DIR
		) ;
	}
	
	public function createFile($sPath,$nMode=FileSystem::CREATE_FILE_DEFAULT)
	{
		return $this->fileSystem()->rootFileSystem()->createFile(
				(substr($sPath,0,1)=='/')? $sPath: ($this->path().'/'.$sPath)
				, $nMode
		) ;
	}
	
	public function createFolder($sPath,$nMode=FileSystem::CREATE_FOLDER_DEFAULT)
	{
		return $this->fileSystem()->rootFileSystem()->createFolder(
				(substr($sPath,0,1)=='/')? $sPath: ($this->path().'/'.$sPath)
				, $nMode
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
