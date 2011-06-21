<?php
namespace jc\mvc\view\widget;

use jc\lang\Exception;
use jc\system\Request;

class FileUpdate extends FormWidget {
	
	public function __construct($sId, $sTitle = null , $nMaxByte = null , $sStoreDir = null , $sAllowExt = null,IViewWidget $aView = null) {
//		if (! is_int ( $type ) || $type < self::$nTypeMin || $type > self::$nTypeMax) {
//			throw new Exception ( "构建Text对象时使用了非法的type参数(得到的type是:%s)", array ($type ) );
//		}
		// 允许的类型
		if($sAllowExt==null)
		{
			$sAllowExt = self::$ALLOWEXT ;
		}
		$this->setExts($sAllowExt,true) ;
		
		// 不允许的类型
		$this->setExts(self::$UNALLOWEXT,false) ;

		$this->setMaxByte($nMaxByte) ;
		$this->setStoreDir($sStoreDir) ;
	
		parent::__construct ( $sId, 'ViewWidgetFileUpdate.template.html', $sTitle, $aView );
	}
	
	public function hasUpdate(){
		return true;
	}
	
	public function getFileName(){
		return 'fdsf.jpg';
	}
	
	public function getFileSize(){
		return '20MB';
	}
	
	public function setAccessFileType(){
		
	}
	
	public function getAccessFileType(){
		return;
	}
	
	public function setMaxFileSize(){
		return;
	}
	
	public function setAccept(){
		
	}
	
	public function getAccept(){
		
	}
	
	public function value()
	{
		return $this->value ;
	}
	
	public function setValue($data = null) {
		$this->value = $data;
	}
	
	public function valueToString() {
		return strval ( $this->value () );
	}
	
	public function setValueFromString($data) {
		$this->setValue ( $data );
	}
	
	public function setDataFromSubmit(IDataSrc $aDataSrc) {
		$this->setValueFromString ( $aDataSrc->get ( $this->formName () ) );
	}
	
	public function upload($sInputName,Request $aRequest)
	{
		if(!$this->sStoreDir)
		{
			throw new Exception('尚未设置存储路径，无法完成文件上传后的保存工作;执行此方法时需要先执行 setStoreDir() 设置存储路径。') ;
		}
		
		$this->sLastInput = $sInputName ;
		
		$arrFileInfo = $aRequest->get($sInputName);
		if( empty($arrFileInfo['name']) )
		{
			$this->_PutErrorInfo($sInputName,"文件“{$sInputName}”未上传",self::ERR_NOUPLOAD) ;
			return false ;
		}
		
		if( empty($arrFileInfo['tmp_name']) )
		{
			$this->_PutErrorInfo($sInputName,"获得文件“{$arrFileInfo['name']}”信息，但文件未上传至服务器，可能文件超过服务器的长度限制（参考 php.ini 的相关设定值）",self::ERR_EXMAXBYTE_BY_PHP) ;
			return false ;			
		}
		
		// 检查类型
		$sExtName = $this->getOriginalExt($sInputName) ;
		if( !$this->isAllowExt($sExtName) )
		{
			$this->_PutErrorInfo($sInputName,"上传的文件类型：“{$sExtName}”受限制",self::ERR_UNALLOWEXT) ;
			return false ;
		}

		// 检查字节
		$nByte = $this->getLength($sInputName) ;
		if( $this->nMaxByte>0 && $this->nMaxByte<$nByte )
		{
			$this->_PutErrorInfo($sInputName,'上传的文件字节长度（'.JCAT_UploadFile::getReadableFileSize($nByte).'）超过限制（'.JCAT_UploadFile::getReadableFileSize($this->nMaxByte).'）',self::ERR_EXMAXBYTE) ;
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
				$this->_PutErrorInfo($sInputName,"存储目录不存在：“{$sStoreRealDir}”",self::ERR_STOREDIR_NOTEXIST) ;
				return false ;
			}
			else if( !mkdir($sStoreRealDir,0777,true) )
			{
				$this->_PutErrorInfo($sInputName,"无法创建存储目录：“{$sStoreRealDir}”",self::ERR_STOREDIR_UNCREATE) ;
				return false ;
			}
		}

		$this->arrStoreSubPath[$sInputName] = $sStoreName ;
		$this->arrStorePath[$sInputName] = $sStorePath ;
		
		// 移动文件
		$sTempPath = $this->getTempPath($sInputName) ;
		if( !move_uploaded_file($sTempPath,JCAT::CharsetToServer($sStorePath)) or !is_file($sStorePath) )
		{
			$this->_PutErrorInfo($sInputName,"将上传文件从临时路径：“{$sTempPath}”拷贝至存储路径：“{$sStorePath}”时发生了错误",self::ERR_UNCOPY) ;
			return false ;			
		}
		
		return true ;
	}

	public function cancelUpload($sInputName=null)
	{
		if($sInputName===null)
		{
			$sInputName = $this->sLastInput ;
		}

		if( is_file($this->getStorePath()) )
		{
			unlink($this->getStorePath()) ;
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
			return $this->sStoreSubDir.$this->getOriginalName($sInputName) ;
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
		$sOriginalName = $this->getOriginalName($sInputName) ;
		
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
	public function isUploading($sInputName,JCAT_Request $aRequest=null)
	{
		if(!$aRequest)
		{
			$aRequest = JCAT_Request::GetGlobalInstance() ;
		}
		$arrFileInfo = $aRequest->GetParam($sInputName) ;
		return !empty($arrFileInfo['tmp_name']) ;
	}
	
	## -- 存储目录 -- ##
	public function setStoreDir($sStoreDir)
	{
		$this->sStoreDir = JCAT_Global::TidyPath($sStoreDir) ;
	}
	public function setStoreSubDir($sStoreSubDir)
	{
		$this->sStoreSubDir =  JCAT_Global::TidyPath($sStoreSubDir) ;
	}
	public function getStoreDir()
	{ return $this->sStoreDir ; }
	public function getStoreSubDir()
	{ return $this->sStoreSubDir ; }
	
	
	## -- 原始文件信息 -- ##
	
	public function getOriginalName($sInputName=null,JCAT_Request $aRequest=null)
	{
		if(!$aRequest)
		{
			$aRequest = JCAT_Request::GetGlobalInstance() ;
		}
		$arrFileInfo = $aRequest->GetParam(
			($sInputName==null)?$this->sLastInput:$sInputName
		) ;

		return $arrFileInfo['name'] ;
	}
	
	public function getOriginalExt($sInputName=null,JCAT_Request $aRequest=null)
	{ return $this->GetExtName( $this->getOriginalName($sInputName,$aRequest) ) ; }
	
	public function getOriginalType($sInputName=null,JCAT_Request $aRequest=null)
	{
		if(!$aRequest)
		{
			$aRequest = JCAT_Request::GetGlobalInstance() ;
		}
		$arrFileInfo = $aRequest->GetParam(
			($sInputName==null)?$this->sLastInput:$sInputName
		) ;
		
		return $arrFileInfo['type'] ;
	}
	
	public function getTempPath($sInputName=null,JCAT_Request $aRequest=null)
	{
		if(!$aRequest)
		{
			$aRequest = JCAT_Request::GetGlobalInstance() ;
		}
		$arrFileInfo = $aRequest->GetParam(
			($sInputName==null)?$this->sLastInput:$sInputName
		) ;
		
		return $arrFileInfo['tmp_name'] ;
	}
	
	public function getLength($sInputName=null,JCAT_Request $aRequest=null)
	{
		if(!$aRequest)
		{
			$aRequest = JCAT_Request::GetGlobalInstance() ;
		}
		$arrFileInfo = $aRequest->GetParam(
			($sInputName==null)?$this->sLastInput:$sInputName
		) ;
		
		return $arrFileInfo['size'] ;
	}
	
	
	
	## --- 错误信息 --- ##
	
	protected function _PutErrorInfo($sInputName,$Msg,$Code)
	{
		$this->arrErrors[$sInputName] = array(
			'Msg' => $Msg ,
			'Code' => $Code ,
		) ;
	}
	
	public function getErrorMsg($sInputName=null,$sLanguage=null)
	{
		if($sInputName==null)
		{
			$sInputName = $this->sLastInput ;
		}
		
		$sMsg = isset($this->arrErrors[$sInputName]['Msg'])?
					$this->arrErrors[$sInputName]['Msg']:
					"未知的 Input Name {$sInputName}" ;
		
		return JCAT_Language::SentenceEx($sMsg,'JCAT',$sLanguage) ; 
	}
	
	public function getErrorCode($sInputName=null)
	{ return $this->arrErrors[($sInputName==null)?$this->sLastInput:$sInputName]['Code'] ; }
	
	
	
	## --- 扩展名/文件类型 --- ##
	static function getExtName($sFileName)
	{
		$arr = explode('.',$sFileName) ;
		$FileExtName = $arr[ count($arr)-1 ] ;
		return strtolower($FileExtName) ;
	}
	public function setExts($mixedExtName,$bAllow=true)
	{
		if( $bAllow )
		{
			$arr =& $this->arrAllowExt ;
		}
		else
		{
			$arr =& $this->arrUnallowExt ;
		}
		$mixedExtName = (array) $mixedExtName ;
		foreach ($mixedExtName as $sExtName)
		{
			$arr[$sExtName] = self::getExtName($sExtName) ;
		}
	}
	public function removeExt($sExtName,$bAllow=true)
	{
		$arr =& $bAllow? $this->arrAllowExt: $this->arrUnallowExt ;
		$sExtName = JCAT_UploadFile::getExtName($sExtName) ;
		unset($arr[$sExtName]) ;
	}
	public function getExts($bAllow=true)
	{
		return $bAllow? $this->arrAllowExt: $this->arrUnallowExt ;
	}
	public function isAllowExt($sExtName)
	{
		$sExtName = JCAT_UploadFile::getExtName($sExtName) ;
		if( isset($this->arrUnallowExt[$sExtName]) )
		{
			return false ;
		}
		if( isset($this->arrAllowExt['*']) )
		{
			return true ;
		}
		return isset($this->arrAllowExt[$sExtName]) ;
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
	
	public function setMaxByte($nMaxByte=null)
	{
		$old = $this->nMaxByte ;
		
		if($nMaxByte==null)
		{
			$nMaxByte = self::$MAXBYTE ;
		}
		$this->nMaxByte = $nMaxByte ;

		return $old ;
	}
	
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
	const ERR_STOREDIR_NOTEXIST = 5 ;						// 存储目录不存在，且 JCAT_UploadFile::setAutoCreateStoreDir(false) ;
	const ERR_STOREDIR_UNCREATE = 6 ;						// 无法自动创建 存储目录
	const ERR_UNCOPY = 7 ;									// 无法将上传文件 由 临时路径拷贝至 存储路径
	const ERR_CANCEL = 8 ;									// 上传被取消
	
	//配置
	static public $MINBYTE = 0 ;								// 允许上传的 最小字节数
	static public $MAXBYTE = 204800 ;							// 允许上传的 最大字节数， 204800byte = 200Kb
	static public $ALLOWEXT = array('*') ;					// 允许上传的 文件扩展名
	static public $UNALLOWEXT = array(						// 禁止上传的 文件扩展名
				'php', 'php5' ,'exe' , 'sh' 				// ... ...
			) ;
	static public $PATH_STOREFILE ;
	
	private $sLastInput ;
	private $arrStorePath = array() ;						// 存储路径
	private $arrStoreSubPath = array() ;					// 存储子目录+存储文件名
	private $arrStoreFileName = array() ;					// 存储文件名
	private $arrErrors = array() ;
	private $nMaxByte ;										// 允许上传的
	private $arrUnallowExt = array() ;						// 不允许的 文件类型
	private $arrAllowExt = array() ;							// 允许的 文件类型
	private $bKeepOriginalName = false ;						// 保持原名
	private $bOverlayIfExisted = true ;						// 同名覆盖
	private $sStoreDir ;										// 存储目录
	private $sStoreSubDir ;									// 存储子目录
	private $callbackGenerateNewName = null ;				// 用于产生新文件名的 回调函数
	private $bAutoCreateStoreDir = true ;					// 自动创建存储目录
	private $nRandStrLen = 4 ;
}

?>