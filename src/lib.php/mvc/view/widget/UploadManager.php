<?php
namespace jc\mvc\view\widget;

use jc\lang\Object;
use jc\mvc\view\widget\IUploadManager;
use jc\message\Message ; //MessageQueue
use jc\lang\Exception;
use jc\system\Request ;
use jc\fs\DIR;
use jc\fs\FSO;


class UploadManager extends Object implements IUploadManager
{
	public function __construct( $sStoreDir , $nMaxByte , $arrAllowExt = null  ){
		if( is_array($arrAllowExt) )
		{
			$this->setAllowExts($this->arrAllowExt,true) ;
		}
		
		$this->setMaxByte($nMaxByte) ;
		$this->setStoreDir($sStoreDir) ;
		
		parent::__construct();
	}
	
	//inputName 是控件的名字
	public function upload($sInputName,Request $aRequest){
		if(!$this->sStoreDir)
		{
			throw new Exception('无法完成文件上传后的保存工作,请检查存储路径是否设置正确。') ;
		}
		
		$this->sLastInput = $sInputName ;
		
		$arrFileInfo = $aRequest->get($sInputName) ;
		if( empty($arrFileInfo['name']) )
		{
			new Message ( Message::failed, "文件{$sInputName}未上传" );
			return false ;
		}
		
		if( empty($arrFileInfo['tmp_name']) )
		{
			new Message ( Message::failed, "获得文件{$arrFileInfo['name']}信息，但文件未上传至服务器，可能文件超过服务器的长度限制（参考 php.ini 的相关设定值）" ) ;
			return false ;			
		}
		
		// 检查类型
		$sExtName = $this->getOriginalExt($sInputName) ;
		if( !$this->isAllowExt($sExtName) )
		{
			new Message ( Message::failed, "不允许上传{$sExtName}类型文件") ;
			return false ;
		}

		// 检查字节
		$nByte = $this->getByte($sInputName) ;
		if( $this->nMaxByte>0 && $this->nMaxByte<$nByte )
		{
			new Message ( Message::failed,"上传的文件字节长度{$nByte}超过限制,最大可接受" . (string)$this->getMaxByte() ) ;
			return false ;
		}
		
		// 确定 文件名
		$sStoreName = $this->makeStoreName($sInputName) ;
		$sStorePath = $this->sStoreDir.$sStoreName ;
		$sStoreRealDir = dirname($sStorePath) ;
		if( !is_dir($sStoreRealDir) )
		{
			if(!$this->bAutoCreateStoreDir)
			{
				new Message ( Message::failed,"存储目录不存在：{$sStoreRealDir}" ) ;
				return false ;
			}
			else if( !mkdir($sStoreRealDir,0777,true) )
			{
				new Message ( Message::failed,"无法创建存储目录：{$sStoreRealDir}" ) ;
				return false ;
			}
		}

		$this->arrStoreSubPath[$sInputName] = $sStoreName ;
		$this->arrStorePath[$sInputName] = $sStorePath ;
		
		// 移动文件
		$sTmpPath = $this->getTmpPath($sInputName) ;
		if( !move_uploaded_file($sTmpPath,$sStorePath )  or !is_file($sStorePath) )
		{
			new Message ( Message::failed,"将上传文件从临时路径：“{$sTmpPath}”拷贝至存储路径：“{$sStorePath}”时发生了错误" ) ;
			return false ;			
		}
		
		return true ;
	}
	
	public function setMaxByte($nByte){
		if(! is_int($nByte) and $nByte > 0){
			throw new Exception ( "调用" . __CLASS__ . "的" . __METHOD__ . "方法时使用了非法的nByte参数(得到的nByte参数是:%s)", array ($nByte ) );
		}
		if($nByte > ini_get('upload_max_filesize') or $nByte > ini_get('post_max_size')){ // upload_max_filesize, memory_limit , post_max_size
			throw new Exception("无法设置文件最大限制为{$nByte},可能nByte参数大于php配置中的上传文件大小限制") ;
		}
		$this->nMaxByte = $nByte;
	}
	
	public function getMaxByte() {
		return $this->nMaxByte;
	}
	
	//得到已上传的文件的文件名
	public function getFileName(){
		return $this->sStoreFileName;
	}
	
	//得到已上传的文件的文件扩展名
	public function getFileType(){
		// TODO 如何得到上次上传的文件?
		return mime_content_type( $this->sStorePath . $this->sStoreSubPath . $this->getFileName() );
	}
	
	public function getTmpDir(){
		return ini_get(upload_tmp_dir);
	}
	
	public function cancelUpload($sInputName=null)
	{
		if($sInputName===null)
		{
			$sInputName = $this->sLastInput ;
		}

		if( is_file($this->GetStorePath()) )
		{
			unlink($this->GetStorePath()) ;
		}

		unset($this->arrStoreFileName[$sInputName]) ;
		unset($this->arrStorePath[$sInputName]) ;
		unset($this->arrStoreSubPath[$sInputName]) ;

		$this->_PutErrorInfo($sInputName,"文件上传被取消",self::ERR_CANCEL) ;
	}
	
	public function getStorePath($sInputName=null)
	{ return $this->arrStorePath[($sInputName==null)?$this->sLastInput:$sInputName] ; }
	
	public function getStoreSubPath($sInputName=null)
	{ return $this->arrStoreSubPath[($sInputName==null)?$this->sLastInput:$sInputName] ; }
	
	public function getStoreFileName($sInputName=null)
	{ return $this->arrStoreFileName[($sInputName==null)?$this->sLastInput:$sInputName] ; }
	
	
	## -- 新文件名 -- ##
	public function makeStoreName($sInputName=null)
	{
		
		if($sInputName==null)
		{
			$sInputName = $this->sLastInput ;
		}
		
		// 通过设定的回调函数 得到新文件名
		if($this->callbackGenerateNewName!==null)
		{
			return call_user_func_array($this->callbackGenerateNewName,array(&$this,$sInputName)) ;
		}
		
		// 保持 原有文件名
		if($this->bKeepOriginalName)
		{
			return $this->sStoreSubDir.$this->GetOriginalName($sInputName) ;
		}
		
		// 产生随机文件名
		$NewName = time().'.' ;
		$box = array_merge(range('a','z'),range('A','Z'),range(0,9)) ;
		shuffle($box) ;
		$RandLen = $this->nRandStrLen ;
		while( $RandLen-- > 0)
		{
			$NewName.= $box[ array_rand($box) ] ;
		}
		
		// 原名
		$sOriginalName = $this->GetOriginalName($sInputName) ;
		
		// 扩展名
		$arrNameParts = explode('.',$sOriginalName);
		$sExtName = end($arrNameParts) ;
		
		$this->arrStoreFileName[$sInputName] = $NewName.'-'.str_replace('/','-63-',base64_encode($sOriginalName)).'.'.$sExtName ;
		return $this->sStoreSubDir.$this->arrStoreFileName[$sInputName] ;
	}
	
	public function setNewNameGenerater($sFunctionName=null,$mixedClassOrOb=null)
	{
		if($mixedClassOrOb===null)
		{
			$this->callbackGenerateNewName = $sFunctionName ;
		}
		else
		{
			$this->callbackGenerateNewName = array($mixedClassOrOb,$sFunctionName) ;
		}
	}
	
	/**
	 * 从默认的存储文件名中 取得原始文件名
	 *
	 * @access	public
	 * @param	$sStoreName		string
	 * @static
	 * @return	string
	 */
	static public function getOriginalNameFromStoreName($sStoreName)
	{
		$arrResult = array() ;
		if( preg_match('/^\d{10}\.\w+\-([\-\w\.+=]+)\.(\w*)$/',$sStoreName,$arrResult) )
		{
			return base64_decode(str_replace('-63-','/',$arrResult[1])) ;
		}
		
		else
		{
			return $sStoreName ;
		}
	}
	
	/**
	 * 检查是否上传
	 *
	 * @access	public
	 * @param	$sInputName
	 * @return	bool
	 */
	public function isUploaded($sInputName,Request $aRequest)
	{
		$arrFileInfo = $aRequest->get($sInputName) ;
		return !empty($arrFileInfo['tmp_name']) ;
	}
	
	## -- 存储目录 -- ##
	public function setStoreDir($sStoreDir)
	{
		// TODO 检验文件夹是否有足够权限
		$this->sStoreDir = DIR::formatPath($sStoreDir) ;
	}
	
	public function setStoreSubDir($sStoreSubDir)
	{
		$this->sStoreSubDir = DIR::formatPath($sStoreSubDir) ;
	}
	
	public function getStoreDir()
	{ return $this->sStoreDir ; }
	
	public function getStoreSubDir()
	{ return $this->sStoreSubDir ; }
	
	
	## -- 原始文件信息 -- ##
	
	public function getOriginalName($sInputName=null,Request $aRequest)
	{
		$arrFileInfo = $aRequest->getParam(
			($sInputName==null)?$this->sLastInput:$sInputName
		) ;

		return $arrFileInfo['name'] ;
	}
	
	public function getOriginalExt($sInputName=null,Request $aRequest)
	{ return $this->getExtName( $this->getOriginalName($sInputName,$aRequest) ) ; }
	
	public function getOriginalType($sInputName=null,Request $aRequest)
	{
		$arrFileInfo = $aRequest->getParam(
			($sInputName==null)?$this->sLastInput:$sInputName
		) ;
		
		return $arrFileInfo['type'] ;
	}
	
	public function getTempPath($sInputName=null,Request $aRequest)
	{
		$arrFileInfo = $aRequest->getParam(
			($sInputName==null)?$this->sLastInput:$sInputName
		) ;
		
		return $arrFileInfo['tmp_name'] ;
	}
	
	public function getByte($sInputName=null,Request $aRequest)
	{
		$arrFileInfo = $aRequest->getParam(
			($sInputName==null)?$this->sLastInput:$sInputName
		) ;
		
		return $arrFileInfo['size'] ;
	}
	
	## --- 扩展名/文件类型 --- ##
	
	public function setAllowExts($arrTypes){
		$arrTypes = array_unique($arrTypes);
		$this->arrAllowExt = $arrTypes;
	}
	
	public function getAllowExts(){
		return $this->arrAllowExt;
	}
	
	public function addAllowExt($sType){
		$arrTypes = $this->arrAllowExt;
		$arrTypes[] = $sType;
		$this->setAllowExts($arrTypes);
	}
	
	public function removeAllowExt($sType)
	{
		$key = array_search($sType , $this->arrAllowExt);
		if( $key != false ){
			unset($this->arrAllowExt[$key]) ;
		}
	}
	
	public function setUnallowExts($arrTypes){
		$arrTypes = array_unique($arrTypes);
		$this->arrUnallowExt = $arrTypes;
	}
	
	public function getUnallowExts(){
		return $this->arrUnallowExt;
	}
	
	public function addUnallowExt($sType){
		$arrTypes = $this->arrUnallowExt;
		$arrTypes[] = $sType;
		$this->setUnallowExts($arrTypes);
	}
	
	public function removeUnallowExt($sType)
	{
		$key = array_search($sType , $this->arrUnallowExt);
		if( $key != false ){
			unset($this->arrUnallowExt[$key]) ;
		}
	}
	
	static function getExt($sFileName)
	{
		$arr = explode('.',$sFileName) ;
		$FileExtName = $arr[ count($arr)-1 ] ;
		return strtolower($FileExtName) ;
	}
	
	public function isAllowExt($sFileName)
	{
		$sExt = $this->getExt($sFileName) ;
		if( isset($this->arrUnallowExt[$sExt]) )
		{
			return false ;
		}
		if( isset($this->arrAllowExt['*']) )
		{
			return true ;
		}
		return isset($this->arrAllowExt[$sExt]) ;
	}
	
	## --- 行为 --- ##
	public function keepOriginalName($bKeepOriginalName=true,$bOverlayIfExisted=false)
	{
		$this->bKeepOriginalName = $bKeepOriginalName ;
		$this->bOverlayIfExisted = $bOverlayIfExisted ;
	}
	
	public function setOverlayIfExisted($bOverlayIfExisted=false)
	{
		$old = $this->bOverlayIfExisted ;
		$this->bOverlayIfExisted = $bOverlayIfExisted ;
		return $old ;		
	}
	
	public function setAutoCreateStoreDir($bAutoCreateStoreDir=true)
	{
		$old = $this->bAutoCreateStoreDir ;
		$this->bAutoCreateStoreDir = $bAutoCreateStoreDir ;
		return $old ;
	}
	
	## --- ... --- ##
	static function getReadableFileSize($nByte,$nPrecision=2)
	{
		$unit = 'Byte' ;
		if( $nByte>=1024 )
		{
			$nByte/= 1024 ;
			$unit = 'KB' ;
		}
		if( $nByte>=1048576 )
		{
			$nByte/= 1024 ;
			$unit = 'MB' ;
		}
		if( $nByte>=1073741824 )
		{
			$nByte/= 1024 ;
			$unit = 'GB' ;
		}
		
		return round($nByte,$nPrecision).' '.$unit ;
	}
	
	//错误信息
	const ERR_NOupload = 1 ;								// 文件未上传
	const ERR_EXMAXBYTE_BY_PHP = 2 ;						// 超过限制字节数（在 php.ini 中设定）
	const ERR_UNALLOWEXT = 3 ;								// 不允许的文件类型
	const ERR_EXMAXBYTE = 4 ;								// 超过限制字节数
	const ERR_STOREDIR_NOTEXIST = 5 ;						// 存储目录不存在，且 FileUpdate::setAutoCreateStoreDir(false) ;
	const ERR_STOREDIR_UNCREATE = 6 ;						// 无法自动创建 存储目录
	const ERR_UNCOPY = 7 ;									// 无法将上传文件 由 临时路径拷贝至 存储路径
	const ERR_CANCEL = 8 ;									// 上传被取消
	
	private $sLastInput ;                                   // 上次存放的文件名
	private $sStorePath ;						   			// 存储路径
	private $sStoreSubPath ;								// 存储子目录+存储文件名
	private $sStoreFileName ;								// 存储文件名
	private $nMaxByte ;										// 允许上传的
	private $arrUnallowExt =array(							// 禁止上传的 文件扩展名
				'php', 'php5' ,'exe' , 'sh' 			
			) ;					                        
	private $arrAllowExt = array( '*' ) ;					// 允许的 文件类型
	private $bKeepOriginalName = false ;					// 保持原名
	private $bOverlayIfExisted = true ;						// 同名覆盖
	private $sStoreDir ;									// 存储目录
	private $sStoreSubDir ;									// 存储子目录
	private $callbackGenerateNewName = null ;				// 用于产生新文件名的 回调函数
	private $bAutoCreateStoreDir = true ;					// 自动创建存储目录
	private $nRandStrLen = 4 ;
}

?>