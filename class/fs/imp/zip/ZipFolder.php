<?php
namespace org\jecat\framework\fs\imp\zip ;

use org\jecat\framework\fs\IFolder;
use org\jecat\framework\lang\Exception;

class ZipFolder extends ZipFSO implements IFolder{
	public function __construct(ZipFileSystem $aFileSystem,$sPath){
		$this->sZipInnerPath = ZipFileSystem::removePrefixLash($sPath).'/';
		parent::__construct($aFileSystem,$sPath ) ;
	}
	
	public function findFile($sPath,$nFlag=0){
		return $this->fileSystem()->rootFileSystem()->findFile(
				(substr($sPath,0,1)=='/')? $sPath: ($this->path().'/'.$sPath)
				, $nFlag
		) ;
	}
	
	public function findFolder($sPath,$nFlag=0){
		return $this->fileSystem()->rootFileSystem()->findFolder(
				(substr($sPath,0,1)=='/')? $sPath: ($this->path().'/'.$sPath)
				, $nFlag
		) ;	
	}
	
	public function create($nMode=FileSystem::CREATE_FOLDER_DEFAULT){
		return false ;
	}
	
	public function createFile($sPath,$nMode=FileSystem::CREATE_FILE_DEFAULT){
		return $this->fileSystem()->rootFileSystem()->createFile(
				(substr($sPath,0,1)=='/')? $sPath: ($this->path().'/'.$sPath)
				, $nMode
		) ;
	}
	
	public function createFolder($sPath,$nMode=FileSystem::CREATE_FOLDER_DEFAULT){
		return $this->fileSystem()->rootFileSystem()->createFolder(
				(substr($sPath,0,1)=='/')? $sPath: ($this->path().'/'.$sPath)
				, $nMode
		) ;
	}
	
	public function deleteChild($sPath,$bRecurse=false,$bIgnoreError=false){
		return false ;
	}
	
	/**
	 * @return \Iterator
	 */
	public function iterator($nFlag=FSIterator::FLAG_DEFAULT){
		throw new Exception ('这个函数没实现呢 : `%s`',__METHOD__);
	}
	
	public function exists(){
		$aZipArchive = $this->fileSystem()->zipArchive() ;
		return FALSE !== $aZipArchive->locateName($this->sZipInnerPath );
	}
}
