<?php
namespace org\jecat\framework\fs\imp ;

use org\jecat\framework\fs\IFolder;
use org\jecat\framework\fs\FileSystem;

class LocalFolder extends LocalFSO implements IFolder
{
	/**
	 * @return \IFile
	 */
	public function findFile($sPath,$nFlag=0)
	{
		return $this->fileSystem()->rootFileSystem()->findFile(
				(substr($sPath,0,1)=='/')? $sPath: ($this->path().'/'.$sPath)
				, $nFlag
		) ;
	}

	/**
	 * @return \IFolder
	 */
	public function findFolder($sPath,$nFlag=0)
	{
		return $this->fileSystem()->rootFileSystem()->findFolder(
				(substr($sPath,0,1)=='/')? $sPath: ($this->path().'/'.$sPath)
				, $nFlag
		) ;	
	}

	public function create($nMode=FileSystem::CREATE_FOLDER_DEFAULT)
	{
		$nOldMark = umask(0) ;
		$bRes = mkdir(
			$this->localPath()
			, ($nMode&FileSystem::CREATE_PERM_BITS)
			, $nMode&FileSystem::CREATE_RECURSE_DIR
		) ;
		umask($nOldMark) ;
		
		return $bRes ;
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
	public function iterator($nFlag=\org\jecat\framework\fs\FSIterator::FLAG_DEFAULT)
	{
		return new LocalFolderIterator($this,$nFlag);
	}
	
	public function exists()
	{
		return is_dir($this->localPath());
	}
} 



?>
