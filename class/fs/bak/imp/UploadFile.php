<?php
namespace org\jecat\framework\fs\imp ;

use org\jecat\framework\fs\Folder;

class UploadFile extends LocalFile
{
	public function __construct(FileSystem $aFileSystem,$sUploadTmpPath,$sFormName,array $arrFileInfo)
	{
		$this->sFormName = $sFormName;
		$this->nError = $arrFileInfo['error'] ;
		$this->sMimeType = isset($arrFileInfo['type'])? $arrFileInfo['type']: '' ;
		$this->sOriginalFilename = isset($arrFileInfo['name'])? $arrFileInfo['name']: '' ;
		$this->nSize = isset($arrFileInfo['size'])? $arrFileInfo['size']: '' ;
		
		if( !empty($arrFileInfo['tmp_name']) )
		{
			$sFilename = basename($arrFileInfo['tmp_name']) ;
			$sLocalPath = $arrFileInfo['tmp_name'] ;
			$sPath = $sUploadTmpPath.'/'.$sFilename ;
			
			parent::__construct($aFileSystem,$sPath,$sLocalPath) ;
			$aFileSystem->setFSOFlyweight($sPath,$this) ;
		}		
	}
	
	
	public function name()
	{
		return $this->originalFilename() ;
	}
	
	
	
	public function formName()
	{
		return $this->sFormName ;
	}
	public function originalFilename()
	{
		return $this->sOriginalFilename ;
	}
	public function mimeType()
	{
		return $this->mimeType() ;
	}
	public function size()
	{
		return $this->nSize ;
	}
	public function error()
	{
		return $this->nError ;
	}
	public function errorMessage()
	{
		return self::$arrErrorMessage[$this->nError] ;
	}
	/**
	 * @return org\jecat\framework\fs\File
	 */
	public function file()
	{
		return $this->aFile ;		
	}
	
	public function isSuccess()
	{
		return $this->error() == UPLOAD_ERR_OK ;
	}
	
	/**
	 * @return 返回php用于存放用户上传文件的临时文件目录
	 */
	static public function uploadTempDir()
	{
		if(!self::$sTmpDir)
		{
			if( !self::$sTmpDir=get_cfg_var('upload_tmp_dir') )
			{
				self::$sTmpDir=sys_get_temp_dir() ;
			}
		}
		
		return self::$sTmpDir ;
	}
	
	private $sFormName ;
	private $sOriginalFilename ;
	private $sMimeType ;
	private $nSize ;
	private $nError ;
	
	private $aFile ;
	
	static private $sTmpDir = '' ;
	
	static private $arrErrorMessage = array(
		UPLOAD_ERR_OK => '' ,
		UPLOAD_ERR_INI_SIZE  => '上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值' ,
		UPLOAD_ERR_FORM_SIZE  => '上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值' ,
		UPLOAD_ERR_PARTIAL  => '文件只有部分被上传' ,
		UPLOAD_ERR_NO_FILE  => '没有文件被上传' ,
		UPLOAD_ERR_NO_TMP_DIR  => '找不到临时文件夹' ,
		UPLOAD_ERR_CANT_WRITE  => '文件写入失败' ,
	) ;
}

?>