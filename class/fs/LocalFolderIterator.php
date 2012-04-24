<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  这个文件是 JeCat PHP框架的一部分，该项目和此文件 均遵循 GNU 自由软件协议
// 
//  Copyleft 2008-2012 JeCat.cn(http://team.JeCat.cn)
//
//
//  JeCat PHP框架 的正式全名是：Jellicle Cat PHP Framework。
//  “Jellicle Cat”出自 Andrew Lloyd Webber的音乐剧《猫》（《Prologue:Jellicle Songs for Jellicle Cats》）。
//  JeCat 是一个开源项目，它像音乐剧中的猫一样自由，你可以毫无顾忌地使用JCAT PHP框架。JCAT 由中国团队开发维护。
//  正在使用的这个版本是：0.7.1
//
//
//
//  相关的链接：
//    [主页]			http://www.JeCat.cn
//    [源代码]		https://github.com/JeCat/framework
//    [下载(http)]	https://nodeload.github.com/JeCat/framework/zipball/master
//    [下载(git)]	git clone git://github.com/JeCat/framework.git jecat
//  不很相关：
//    [MP3]			http://www.google.com/search?q=jellicle+songs+for+jellicle+cats+Andrew+Lloyd+Webber
//    [VCD/DVD]		http://www.google.com/search?q=CAT+Andrew+Lloyd+Webber+video
//
////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*-- Project Introduce --*/
namespace org\jecat\framework\fs ;

use org\jecat\framework\lang\Exception;

class LocalFolderIterator extends FSIterator{
	public function __construct (Folder $aFolder,$nFlags = self::FLAG_DEFAULT){
		parent::__construct($aFolder,$nFlags);
		if( $nFlags & self::RECURSIVE_BREADTH_FIRST ){
			throw new Exception('unfinished flag : RECURSIVE_BREADTH_FIRST');
		}
		
		if(!$aFolder->exists())
		{
			$this->aIterator = new \EmptyIterator() ;
		}
		else if( $nFlags & self::RECURSIVE_SEARCH )
		{
			$this->aIterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($aFolder->path()),\RecursiveIteratorIterator::SELF_FIRST) ;
		}
		else
		{
			$this->aIterator = new \DirectoryIterator($aFolder->path());
		}
	}
	
	public function current (){
		if( $this->nFlags & self::RECURSIVE_SEARCH ){
			$sAbsolutePath = $this->aIterator->current() ;
		}else{
			$sAbsolutePath = $this->aIterator->getPathname();
		}
		$sRelativePath = FSO::relativePath($this->aParentFolder->path(),$sAbsolutePath);
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
	
	public function getFSO(){
		$sRelativePath = $this->relativePath();
		if($this->isFolder()){
			return $this->aParentFolder->findFolder($sRelativePath) ;
		}else{
			return $this->aParentFolder->findFile($sRelativePath) ;
		}
	}
	
	public function absolutePath(){
		return $this->aParentFolder->path().'/'.$this->relativePath();
	}
	
	public function relativePath(){
		if( $this->nFlags & self::RECURSIVE_SEARCH ){
			return $this->aIterator->getSubPathName() ;
		}else{
			return $this->aIterator->current();
		}
	}
	
	public function isFolder(){
		return $this->aIterator->isDir();
	}
	
	public function isFile(){
		return ! $this->isFolder();
	}
	
	/////////////////////
	
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


