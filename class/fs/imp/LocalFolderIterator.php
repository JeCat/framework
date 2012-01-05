<?php
namespace org\jecat\framework\fs\imp ;

use org\jecat\framework\fs\FSIterator;
use org\jecat\framework\fs\FileSystem;
use org\jecat\framework\lang\Exception ;

class LocalFolderIterator extends FSIterator{
	public function __construct (LocalFolder $aFolder,$nFlags = self::FLAG_DEFAULT){
		parent::__construct($aFolder,$nFlags);
		if( $nFlags & self::RECURSIVE_BREADTH_FIRST ){
			throw new Exception('unfinished flag : RECURSIVE_BREADTH_FIRST');
		}
		if( $nFlags & self::RECURSIVE_SEARCH ){
			$this->aIterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($aFolder->localPath()),\RecursiveIteratorIterator::SELF_FIRST) ;
		}else{
			$this->aIterator = new \DirectoryIterator($aFolder->localPath());
		}
	}
	
	public function current (){
		if( $this->nFlags & self::RECURSIVE_SEARCH ){
			$sAbsolutePath = $this->aIterator->current() ;
		}else{
			$sAbsolutePath = $this->aIterator->getPathname();
		}
		$sRelativePath = FileSystem::relativePath($this->aParentFolder->localPath(),$sAbsolutePath);
		if( $this->nFlags & self::RETURN_FSO ){
			if($this->aIterator->isDir()){
				return $this->aParentFolder->findFolder($sRelativePath) ;
			}else if($this->aIterator->isFile()){
				return $this->aParentFolder->findFile($sRelativePath) ;
			}
		}else{
			if( $this->nFlags & self::RETURN_ABSOLUTE_PATH ){
			
				// 这是真实文件系统的路径，不能返回这个
				// return (string)$sAbsolutePath;
				
				if($this->aIterator->isDir()){
					return $this->aParentFolder->findFolder($sRelativePath)->path() ;
				}else if($this->aIterator->isFile()){
					return $this->aParentFolder->findFile($sRelativePath)->path() ;
				}
			}else{
				return (string)$sRelativePath;
			}
		}
	}
	
	public function key (){
		return $this->aIterator->key();
	}
	
	public function next (){
		do{
			$this->aIterator->next();
		}while( $this->valid() and !$this->isSatisfyFlag() );
	}
	
	public function rewind (){
		$this->aIterator->rewind();
		while( $this->valid() and !$this->isSatisfyFlag() ){
			$this->aIterator->next() ;
		}
	}
	
	public function valid (){
		return $this->aIterator->valid();
	}
	
	private function isSatisfyFlag(){
		if( $this->aIterator->isDot() and ! ( $this->nFlags & self::CONTAIN_DOT ) ){
			return false;
		}
		if( $this->aIterator->isFile() and ! ( $this->nFlags & self::CONTAIN_FILE ) ){
			return false;
		}
		if( $this->aIterator->isDir() and ! ( $this->nFlags & self::CONTAIN_FOLDER ) ){
			return false;
		}
		return true;
	}
	
	/////////////////////
	private $aIterator;
}
