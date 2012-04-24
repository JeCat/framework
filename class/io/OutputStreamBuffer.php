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


class OutputStreamBuffer extends OutputStream implements IRedirectableStream, IBuffRemovable
{	
	public function write($Content,$nLen=null,$bFlush=false)
	{
		$nIdx = count($this->arrBuffer)-1 ;
		
		if( $nIdx>0 and !is_object($Content) and is_string($this->arrBuffer[$nIdx]) )
		{
			$this->arrBuffer[$nIdx].= strval($Content) ;
		}
		
		else 
		{
			$this->arrBuffer[] = $Content ;
		}
		
		if($bFlush)
		{
			$this->flush() ;
		}
	}
	
	public function writePrepend($content)
	{
		if( !empty($this->arrBuffer) and is_string($this->arrBuffer[0]) )
		{
			$this->arrBuffer[0] = $content.$this->arrBuffer[0] ;
		}
		else
		{
			array_unshift($this->arrBuffer,$content) ;
		}		
	}
	
	public function __toString()
	{
		return $this->bufferBytes() ;
	}
	
	public function bufferBytes($bClear=true)
	{
		$sBytes = '' ;
		
		foreach ($this->arrBuffer as $Contents)
		{
			$sBytes.= strval($Contents) ;
		}
		
		if($bClear)
		{
			$this->clear() ;
		}
		
		return $sBytes ;
	}
	
	public function clear()
	{
		$this->arrBuffer = array() ;
	}
	
	public function flush()
	{		
		$this->clear() ;
	}
	
	public function isEmpty()
	{
		return empty($this->arrBuffer) ;
	}
	
	public function removeBuff($content)
	{
		$pos=array_search($content,$this->arrBuffer,is_object($content)) ;
		if( $pos!==false )
		{
			unset($this->arrBuffer[$pos]) ;
		}
	}
	
	public function & bufferRawDatas()
	{
		return $this->arrBuffer ;
	}
	
	public function redirect(IOutputStream $aOutputStream=null)
	{
		// 从原来的重定向目标设备中解除
		if( $this->aRedirectionDev and $this->aRedirectionDev instanceof IBuffRemovable )
		{
			$this->aRedirectionDev->removeBuff($this) ;
		}
		
		// 重定向到新的目标
		if($aOutputStream)
		{
			$aOutputStream->write($this) ;
		}
		$this->aRedirectionDev = $aOutputStream ;
	}
	
	public function redirectionDev()
	{
		return $this->aRedirectionDev ;
	}
	
	protected $arrBuffer = array() ;
	
	private $aRedirectionDev ;
}


