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
//  正在使用的这个版本是：0.8
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
use org\jecat\framework\lang\Object;

class InputStreamCache extends Object implements IInputStream
{
	public function __construct($sData='')
	{
		$this->setData($sData) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return int
	 */
	function read($nBytes=-1)
	{
		$nDataLen = $this->available() ;
		
		if( $nBytes<0 )
		{
			$nBytes = $nDataLen ;
		}
		else 
		{
			if($nBytes>$nDataLen)
			{
				$nBytes = $nDataLen ;
			}
		}
		
		if( $nBytes<=0 )
		{
			return '' ;
		}
		
		$nFrom = $this->nPostion ;
		$this->nPostion+= $nBytes ;
		
		return substr($this->sData,$nFrom,$nBytes) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return string
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
	 * @return void
	 */
	function reset()
	{
		$this->nPostion = 0 ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return int
	 */
	function available()
	{
		return strlen($this->sData)-$this->nPostion ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return void
	 */
	function seek($nPosition)
	{
		if($nPosition<0)
		{
			$nPosition = -1 ;
		}
		
		$this->nPosition = $nPosition ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return bool
	 */
	public function isEnd()
	{
		$this->nPostion == strlen($this->sData) ;
	}


	public function setData($sData)
	{
		$this->sData = $sData ;
		
		$this->reset() ;
	}
	public function data()
	{
		return $this->sData ;
	}
	
	private $sData ;
	
	private $nPostion = 0 ;
}


