<?php
namespace org\jecat\framework\fs\imp\zip ;

use org\jecat\framework\fs\Folder;
use org\jecat\framework\fs\imp\LocalFile;
use org\jecat\framework\lang\Exception;

class ZipFileSystem extends FileSystem{
	public function __construct( LocalFile $aZipFileFSO ){
		$this->aZipArchive = new \ZipArchive ;
		$this->aFileForZip = $aZipFileFSO ;
		$sFilePath = $aZipFileFSO->path() ;
		
		$nRstOpen = $this->aZipArchive->open($sFilePath) ;
		if( TRUE !== $nRstOpen ){
			throw new Exception (
				'无法创建 ZipFileSystem : 无法打开zip文件`%s` ， 错误代码：`%d`',
				array(
					$sFilePath , 
					$nRstOpen ,
				)
			);
		}
	}
	
	public function iterator($sPath){
	}
	
	public function FSOForZip(){
		return $this->aFileForZip ;
	}
	
	public function zipArchive(){
		return $this->aZipArchive ;
	}
	
	public function url(){
		return 'zip://' . $this->aFileForZip->path() ;
	}
	
	/////////////////////////////////////////////////////////////////////////
	protected function existsOperation(&$sPath){
		return $this->isFileOperation($sPath)  or $this->isFolderOperation($sPath ) ;
	}
	
	protected function isFileOperation(&$sPath){
		$sPath = self::removePrefixLash($sPath) ;
		return FALSE !== $this->aZipArchive->locateName($sPath );
	}
	
	protected function isFolderOperation(&$sPath){
		$sPath = self::removePrefixLash($sPath) ;
		$sFolderPath = $sPath.'/';
		return FALSE !== $this->aZipArchive->locateName($sFolderPath );
	}
	
	protected function deleteFileOperation(&$sPath){
		return $this->aZipArchive->deleteName($sPath);
	}
	
	protected function deleteDirOperation(&$sPath,$bRecurse=false,$bIgnoreError=false){
		if($bRecurse){
			$this->aZipArchive->deleteName($sPath.'/');
		}else{
			if( $this->isFolderEmpty($sPath) ){
				$this->aZipArchive->deleteName($sPath.'/');
			}else{
				if( !$bIgnoreError ){
					throw new Exception(
						'目录非空，无法删除：`%s`',
						$sPath
					);
				}
			}
		}
	}
	
	protected function createFileObject(&$sPath){
		$aZipFile = new ZipFile($this,$sPath);
		return $aZipFile ;
	}
	
	protected function createFolderObject(&$sPath){
		$aZipFolder = new ZipFolder($this,$sPath);
		return $aZipFolder ;
	}
	
	/////////////////////////////////////////////////////////////////////////
	private function isFolderEmpty($sPath){
		throw new Exception ('这个函数没实现呢 : `%s`',__METHOD__);
		if( ! $this->isFolderOperation($sPath) ){
			return false;
		}
		return true ;
	}
	
	static public function removePrefixLash($sPath){
		if( preg_match('`^/(.*)$`',$sPath,$arrMatch) ){
			$sPath = $arrMatch[1] ;
		}
		return $sPath ;
	}
	
	
	// File
	private $aFileForZip = null ;
	// \ ZipArchive
	private $aZipArchive = null ;
}
