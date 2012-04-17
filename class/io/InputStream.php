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
namespace org\jecat\framework\io ;

use org\jecat\framework\util\String;
use org\jecat\framework\io\IInputStream;

class InputStream extends Stream implements IInputStream, ILockable
{
	const MAX_READ_BYTES = 8192 ;
	
	/**
	 * Enter description here ...
	 * 
	 * @return string
	 */
	function read($nBytes=self::MAX_READ_BYTES,$bBlock=true)
	{
		if($nBytes<0)
		{
			$sReaded = '' ;
			while(!feof($this->hHandle))
			{
				$sReaded.= fread($this->hHandle,10240) ;
			}
			return $sReaded ;
		}
		else 
		{
			return fread($this->hHandle,$nBytes) ;
		}
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return int
	 */
	function readInString(String $aString,$nBytes=-1)
	{
		$sBytes = $this->read($nBytes) ;
		$aString->append( $sBytes ) ;
		
		return strlen($sBytes) ;
	}

	/**
	 * Enter description here ...
	 *
	 * @return org\jecat\framework\util\String
	 */
	function readToString(String $aString=null)
	{
		if(!$aString)
		{
			$aString = new String() ;
		}
		
		while(!$this->isEnd())
		{
			$this->readInString($aString,10240) ;
		}
	
		return $aString ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return void
	 */
	function reset()
	{
		fseek($this->hHandle,0,SEEK_SET) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return int
	 */
	function available()
	{
		$arrInfo = stream_get_meta_data($this->hHandle) ;
		return isset($arrInfo['unread_bytes'])? $arrInfo['unread_bytes']: -1 ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return void
	 */
	function seek($nPosition)
	{
		fseek($this->hHandle,$nPosition,SEEK_SET) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return void
	 */
	public function skip($nBytes)
	{
		fseek($this->hHandle,$nBytes,SEEK_CUR) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	public function isEnd()
	{
		return feof($this->hHandle) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	public function lock()
	{
		flock($this->hHandle,LOCK_SH) ;
	}
}
