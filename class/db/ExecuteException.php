<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  这个文件是 JeCat PHP框架的一部分，该项目和此文件 均遵循 GNU 自由软件协议
// 
//  Copyleft 2008-2012 JeCat.cn(http://team.JeCat.cn)
//
//
//  JeCat PHP框架 的正式全名是：Jellicle Cat PHP Framework。
//  “Jellicle Cat”出自 Andrew Lloyd Webber的音乐剧《猫》（《Prologue:Jellicle Songs for Jellicle Cats》）。
//  JeCat 是一个开源项目，它像音乐剧中的猫一样自由，你可以毫无顾忌地使用JCAT PHP框架。JCAT 由中国团队开发维护。
//  正在使用的这个版本是：0.7.1
//
//
//
//  相关的链接：
//    [主页]			http://www.JeCat.cn
//    [源代码]		https://github.com/JeCat/framework
//    [下载(http)]	https://nodeload.github.com/JeCat/framework/zipball/master
//    [下载(git)]	git clone git://github.com/JeCat/framework.git jecat
//  不很相关：
//    [MP3]			http://www.google.com/search?q=jellicle+songs+for+jellicle+cats+Andrew+Lloyd+Webber
//    [VCD/DVD]		http://www.google.com/search?q=CAT+Andrew+Lloyd+Webber+video
//
////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*-- Project Introduce --*/
namespace org\jecat\framework\db ;

use org\jecat\framework\lang\Exception as JcException;

class ExecuteException extends JcException
{
	public function __construct( DB $aDB, $sSql, $nDeviceErrorNo, $sDeviceErrorMsg, \Exception $aCause=null )
	{
		$this->aDevice = $aDB ;
		$this->sSql = $sSql ;
		$this->nDeviceErrorNo = $nDeviceErrorNo ;
		$this->sDeviceErrorMsg = $sDeviceErrorMsg ;
		
		$sMessage = "数据库在执行SQL语句时发生了错误(code %d): \r\n" ;
		$sMessage.= "\t%s ;\r\n" ;
		$sMessage.= "正在执行的 SQL 是: \r\n" ;
		$sMessage.= "\t%s" ;
		
		$Argvs = array($nDeviceErrorNo,$sDeviceErrorMsg,$sSql) ;
		
		parent::__construct($sMessage,$Argvs,$aCause) ;
	}

	public function device()
	{
		return $this->aDevice ;
	}
	public function sql()
	{
		return $this->sSql ;
	}
	public function deviceErrorNo()
	{
		return $this->nDeviceErrorNo ;
	}
	public function deviceErrorMsg()
	{
		return $this->sDeviceErrorMsg ;
	}

	public function isDuplicate()
	{
		// just for mysql
		return $this->deviceErrorNo()==1062 and strpos($this->deviceErrorMsg(),'Duplicate entry')===0 ;
	}
	
	public function duplicateKey()
	{
		// just for mysql
		if( preg_match("/Duplicate entry '.+?' for key '(.+?)'/i", $this->deviceErrorMsg(),$arrRes) )
		{
			return $arrRes[1] ;
		}
		else 
		{
			return null ;
		}
	}
	
	private $aDevice ;
	private $sSql ;
	private $nDeviceErrorNo ;
	private $sDeviceErrorMsg ;
}

