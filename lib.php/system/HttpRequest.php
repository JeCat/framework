<?php
namespace jc\system ;

use jcat\util\DataSrc ;

class HttpRequest extends DataSrc implements IRequest
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
		self::FILE => '_FILES' ,
		self::SERVER => '_SERVER' ,
	) ;
	
	public function initialize()
	{
		foreach(self::$arrDataSources as $sVarName)
		{
			$this->addChild( $this->factory()->create('jc\\util\\DataSrc',array($$sVarName,true)) ) ;
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
}
?>