<?php
namespace org\jecat\framework\fs\imp\zip ;

use org\jecat\framework\fs\FSO;
use org\jecat\framework\lang\Exception;

abstract class ZipFSO extends FSO{
	public function __construct(ZipFileSystem $aFileSystem,$sPath){
		parent::__construct($aFileSystem,$sPath) ;
	}
	
	public function canRead(){
		return true ;
	}
	
	public function canWrite(){
		return false ;
	}
	
	public function canExecute(){
		return false ;
	}
	
	public function perms(){
		return 0444 ;
	}
	
	public function setPerms($nMode){
		return false ;
	}
	
	public function createTime(){
		return false ;
	}
	
	public function modifyTime(){
		$aZipArchive = $this->fileSystem()->zipArchive() ;
		$arrStat = $aZipArchive->statName( $this->innerPath() ) ;
		return $arrStat['mtime'];
	}
	
	public function accessTime(){
		return false ;
	}
	
	public function isHidden(){
		return false ;
	}
	
	public function copy($to){
		throw new Exception ('这个函数没实现呢 : `%s`',__METHOD__);
	}
	
	public function move($to){
		throw new Exception ('这个函数没实现呢 : `%s`',__METHOD__);
	}
	
	public function url($bProtocol=true){
		if($bProtocol)
		{
			return $this->fileSystem()->url() . '#' . $this->sZipInnerPath ;
		}
		else
		{
			return $this->innerPath() ;
		}
	}
	
	protected $sZipInnerPath = '' ;
}
