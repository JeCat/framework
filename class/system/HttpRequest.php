<?php
namespace jc\system ;

use jc\fs\imp\UploadFile;
use jc\fs\imp\LocalFileSystem;
use jc\util\DataSrc ;
use jc\fs\FileSystem ;

class HttpRequest extends Request
{
	const GET = 1 ;
	const POST = 2 ;
	const COOKIE = 4 ;
	const FILE = 8 ;
	const SERVER = 16 ;
	const CUS = 256 ;
	
	const REQUEST = 7 ;	// GET|POST|COOKIE
	
	static private $arrDataSources = array(
		self::GET => '_GET' ,
		self::POST => '_POST' ,
		self::COOKIE => '_COOKIE' ,
		// self::FILE => '_FILES' ,
		self::SERVER => '_SERVER' ,
	) ;
	
	public function __construct(Application $aApp=null)
	{
		parent::__construct() ;
		
		foreach(self::$arrDataSources as $sVarName)
		{
			if( isset($GLOBALS[$sVarName]) )
			{
				$this->addChild( new DataSrc($GLOBALS[$sVarName],true) ) ;
			}
		}
		
		// $_FILES 
		$this->buildUploadFiles($aApp?:Application::singleton()) ;
		
		// 
		$this->set('REQUEST_URL',(empty($_SERVER['HTTPS'])?'http://':'https://').$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']) ;
	}

	public function referer()
	{
		return $this->get('HTTP_REFERER') ;
	}
	
	public function urlInfo($sName=null)
	{
		if(!$this->arrUrlPathInfo)
		{
			$this->arrUrlPathInfo = parse_url($this->url()) ;
		}
		
		if(!$sName)
		{
			return $this->arrUrlPathInfo ;
		}
		else 
		{
			return isset($this->arrUrlPathInfo[$sName])?
				$this->arrUrlPathInfo[$sName]: null ;
		}
	}
	
	public function url($excludeQueryArgvs=null)
	{
		if(!$excludeQueryArgvs)
		{
			return $this->get('REQUEST_URL') ;
		}
		else
		{
			return $this->urlInfo('scheme') . '://' . $this->urlInfo('host') . $this->urlInfo('path') . $this->urlQuery(true,$excludeQueryArgvs) ;
		}
	}

	public function urlScheme()
	{
		return $this->urlInfo('scheme') ;
	}
	public function urlHost()
	{
		return $this->urlInfo('host') ;
	}
	public function urlPath()
	{
		return $this->urlInfo('path') ;
	}
	public function urlQuery($bQuestionMark=false,$excludes=null)
	{
		if(!$excludes)
		{
			return ($bQuestionMark?'?':'').$this->urlInfo('query') ;
		}
		else
		{
			parse_str($this->urlInfo('query'),$arrQuerys) ;
	
			foreach((array) $excludes as $sKey)
			{
				unset($arrQuerys[$sKey]) ;
			}
			
			return ($bQuestionMark?'?':'').http_build_query($arrQuerys) ;
		}
	}
	public function urlAnchor()
	{
		return $this->urlInfo('fragment') ;
	}
	public function urlUsername()
	{
		return $this->urlInfo('user') ;
	}
	public function urlPassword()
	{
		return $this->urlInfo('pass') ;
	}
	
	public function uri($excludeQueryArgvs=null)
	{
		if(!$excludeQueryArgvs)
		{
			if(!$this->sUri)
			{
				$this->sUri = $this->urlPath() . $this->urlQuery(true) ;
			}
			
			return $this->sUri ;
		}
		else
		{
			return $this->urlPath() . $this->urlQuery(true,$excludeQueryArgvs) ;
		}
	}

	public function quoteString($sName)
	{
		// 检查数据来源是否受 get_magic_quotes_gpc() 影响 
		if( ($this->paramType($sName)&REQUEST) )
		{
			return get_magic_quotes_gpc()?		// 检查 magic_quotes_gpc 是否打开
					$this->getString($sName) :
					addslashes( $this->getString($sName) ) ;
		}
		
		else 
		{
			return addslashes( $this->getString($sName) ) ;
		}
	}
	
	public function paramType($sName) 
	{
		if( isset($this->arrDatas[$sName]) )
		{
			return self::CUS ;
		}
		
		else
		{
			foreach(self::$arrDataSources as $nType=>$sVarName)
			{
				if( isset($$sVarName[$sName]) )
				{
					return $nType ;
				}
			}
		}
		
		return 0 ;
	}
	

	
	private function buildUploadFiles(Application $aApp)
	{
		// $_FILES 
		if( empty($_FILES) or !is_array($_FILES) )
		{
			return ;
		}
		
		$aFs = FileSystem::singleton() ;
		$this->mountUploadTmp($aFs) ;
		
		$aDataSrc = new DataSrc() ;
		$this->addChild($aDataSrc) ;
		
		foreach($_FILES as $sName=>$arrFileInfo)
		{
			$aDataSrc->set($sName,UploadFile::createInstance(array($aFs,self::$sUploadTmpPath,$sName,$arrFileInfo))) ;
		}
	}
	
	private function mountUploadTmp(FileSystem $aFs)
	{
		if( !$aFs->exists(self::$sUploadTmpPath) )
		{
			$aFs->mount(self::$sUploadTmpPath,LocalFileSystem::createInstance(UploadFile::uploadTempDir())) ;
		}
	}
	
	private $sUri ;
	private $arrUrlPathInfo ;
	static private $sUploadTmpPath = '/tmp/upload' ;
}
?>