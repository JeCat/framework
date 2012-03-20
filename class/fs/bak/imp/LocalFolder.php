<?php
namespace org\jecat\framework\fs\imp ;

use org\jecat\framework\fs\IFolder;
use org\jecat\framework\fs\Folder;
use org\jecat\framework\fs\FSIterator ;

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
	
	public function deleteChild($sPath,$bRecurse=false,$bIgnoreError=false)
	{
		if($sPath=='*')
		{
			foreach($this->iterator(FSIterator::FILE_AND_FOLDER|FSIterator::RETURN_FSO) as $aChild)
			{
				$aChild->delete($bRecurse,$bIgnoreError) ;
			}
		}
		else 
		{
			$this->fileSystem()->rootFileSystem()->delete(
				(substr($sPath,0,1)=='/')? $sPath: ($this->path().'/'.$sPath)
				, $bRecurse
				, $bIgnoreError
			) ;
		}
		
	}
	
	/**
	 * @return \Iterator
	 */
	public function iterator($nFlag=FSIterator::FLAG_DEFAULT)
	{
		return new LocalFolderIterator($this,$nFlag);
	}
	
	public function exists()
	{
		return is_dir($this->localPath());
	}
} 



?>
