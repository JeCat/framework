<?php
namespace jc\fs;

interface IUploadManager
{
	public function upload();
	
	public function setStoreDir($sStoreDir) ;
	
	public function getStoreDir() ;
	
	public function setMaxByte($nSizeByte) ;
	
	public function getMaxByte() ;
	
	public function setFileType($arrTypes);
	
	public function getFileType();
	
	public function getTmpDir();
}

?>