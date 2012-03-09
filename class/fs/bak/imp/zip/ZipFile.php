<?php
namespace org\jecat\framework\fs\imp\zip ;

use org\jecat\framework\fs\File;
use org\jecat\framework\io\OutputStream;
use org\jecat\framework\io\InputStream;

class ZipFile extends ZipFSO implements File{
	public function __construct(ZipFileSystem $aFileSystem,$sPath){
		$this->sZipInnerPath = ZipFileSystem::removePrefixLash($sPath);
		parent::__construct($aFileSystem,$sPath ) ;
	}
	
	public function openReader(){
		$hHandle = $this->fileSystem()->zipArchive()->getStream( $this->sZipInnerPath ) ;
		
		if( !$hHandle ){
			return null ;
		}
		
		return InputStream::createInstance($hHandle,$this->application()) ;
	}
	
	public function openWriter($bAppend=false){
		return null ;
	}
	
	public function length(){
		$aZipArchive = $this->fileSystem()->zipArchive() ;
		$arrStat = $aZipArchive->statName( $this->sZipInnerPath ) ;
		return $arrStat['size'] ;
	}
	
	public function delete(){
		if( $this->exists() ){
			$aZipArchive = $this->fileSystem()->zipArchive() ;
			return $aZipArchive->deleteName($this->sZipInnerPath );
		}else{
			return false ;
		}
	}
	
	public function hash(){
		return md5($this->url() ) ;
	}
	
	public function includeFile($bOnce=false,$bRequire=false){
		throw new Exception ('这个函数没实现呢 : `%s`',__METHOD__);
	}
	
	public function create($nMode=FileSystem::CREATE_FOLDER_DEFAULT){
		throw new Exception ('这个函数没实现呢 : `%s`',__METHOD__);
	}
	
	public function exists(){
		$aZipArchive = $this->fileSystem()->zipArchive() ;
		return FALSE !== $aZipArchive->locateName($this->sZipInnerPath );
	}
}
