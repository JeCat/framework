<?php
namespace org\jecat\framework\mvc\controller ;

use org\jecat\framework\system\Application;
use org\jecat\framework\util\DataSrc ;
use org\jecat\framework\fs\Folder ;

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
				if( !$aDataSrc=DataSrc::flyweight(array('request',$sVarName),false) )
				{
					$aDataSrc = new DataSrc($GLOBALS[$sVarName],true) ;
					DataSrc::setFlyweight($aDataSrc,array('request',$sVarName)) ;
				}
				
				$this->addChild($aDataSrc) ;
			}
		}
		
		// $_FILES 
		$this->buildUploadFiles($aApp?:Application::singleton()) ;
		
		// 
		$this->sRequestUrl = (empty($_SERVER['HTTPS'])?'http://':'https://').$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] ;
		
		$this->set(parent::DATANAME_USERCALL, true) ;
	}
	
	public function exportToArray(array &$arrToArray)
	{
		// get 参数
		$aDataSrc = DataSrc::flyweight(array('request','_GET'),false) ;
		$aDataSrc->exportToArray($arrToArray) ;
		
		foreach($this->nameIterator() as $sDataName)
		{
			$arrToArray[$sDataName] = $this->get($sDataName) ;
		}
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
	
	public function url($excludeQueryArgvs=null,$includeQueryArgvs=null)
	{
		if(!$excludeQueryArgvs and !$includeQueryArgvs)
		{
			return $this->sRequestUrl ;
		}
		else
		{
			return $this->urlInfo('scheme') . '://' . $this->urlInfo('host') . $this->urlInfo('path') . $this->urlQuery(true,$excludeQueryArgvs,$includeQueryArgvs) ;
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
	public function urlQuery($bQuestionMark=false,$excludes=null,$includes=null)
	{
		if( !$excludes and !$includes )
		{
			return ($bQuestionMark?'?':'').$this->urlInfo('query') ;
		}
		else
		{
			parse_str($this->urlInfo('query'),$arrQuerys) ;
	
			foreach((array)$excludes as $sKey)
			{
				unset($arrQuerys[$sKey]) ;
			}
			
			foreach((array)$includes as $key=>$sValue)
			{
				if( is_int($key) )
				{
					$arrQuerys[$sValue] = '1' ;
				}
				else
				{
					$arrQuerys[$key] = $sValue ;
				}
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
	
	public function urlNoQuery()
	{
		return $this->urlInfo('scheme') . '://' . $this->urlInfo('host') . $this->urlInfo('path') ;
	}
	
	public function uri($excludeQueryArgvs=null,$includeQueryArgvs=null)
	{
		if(!$excludeQueryArgvs and !$includeQueryArgvs)
		{
			if(!$this->sUri)
			{
				$this->sUri = $this->urlPath() . $this->urlQuery(true) ;
			}
			
			return $this->sUri ;
		}
		else
		{
			return $this->urlPath() . $this->urlQuery(true,$excludeQueryArgvs,$includeQueryArgvs) ;
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
		
		$aDataSrc = new DataSrc() ;
		$this->addChild($aDataSrc) ;
		
		foreach($_FILES as $sName=>$arrFileInfo)
		{
			$aDataSrc->set($sName,$arrFileInfo) ;
		}
	}
	
	private $sUri ;
	private $arrUrlPathInfo ;
	private $sRequestUrl ;
	static private $sUploadTmpPath = '/tmp/upload' ;
}
?>