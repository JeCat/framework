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

class ShellPrintStream extends PrintStream
{
	// 前景色
	const color1 = '30' ;
	const color2 = '31' ;
	const color3 = '32' ;
	const color4 = '33' ;
	const color5 = '34' ;
	const color6 = '35' ;
	const color7 = '36' ;
	const color8 = '37' ;
	const color_red = self::color2 ;
	const color_green = self::color3 ;
	const color_yellow = self::color4 ;
	const color_pink = self::color6 ;
	const color_white = self::color8 ;
	
	// 背景色
	const bgcolor1 = '40' ;
	const bgcolor2 = '41' ;
	const bgcolor3 = '42' ;
	const bgcolor4 = '43' ;
	const bgcolor5 = '44' ;
	const bgcolor6 = '45' ;
	const bgcolor7 = '46' ;
	const bgcolor8 = '47' ;
	const bgcolor_red = self::bgcolor2 ;
	const bgcolor_green = self::bgcolor3 ;
	const bgcolor_yellow = self::bgcolor4 ;
	const bgcolor_pink = self::bgcolor6 ;
	const bgcolor_white = self::bgcolor8 ;
	
	const font_alert = '1' ;
	const font_underline = '4' ;
	const font_flash = '5' ;
	const font_revert = '7' ;
	
	const normal = null ;

	public function println($sBytes)
	{
		$this->write($sBytes) ;
	}
	
	public function enableEscape($bEnable=true)
	{
		$this->bEnableEscape = $bEnable ;
	}
	
	public function printfont($sText,$sAttr=self::normal,$sFrontColor=self::normal,$sBackgroundColor=self::normal)
	{
		$arrVal = array() ;
		if( $sAttr!=self::normal )
		{
			$arrVal[] = $sAttr ;
		}
		if( $sFrontColor!=self::normal )
		{
			$arrVal[] = $sFrontColor ;
		}
		if( $sBackgroundColor!=self::normal )
		{
			$arrVal[] = $sBackgroundColor ;
		}
		
		$sVal = count($arrVal)? implode(';', $arrVal): '0' ;
		
		$this->write( "\\e[{$sVal}m{$sText}\\e[0m" ) ; 
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return int
	 */
	public function write($Contents,$nLen=null,$bFlush=false)
	{
		$sOutput = addcslashes(
			$nLen===null? strval($Contents): substr(strval($Contents),0,$nLen), '"' 
		) ;
		
		if( $this->bEnableEscape )
		{
			echo `echo -e "{$sOutput}"` ;
		}
		else
		{
			echo $sOutput ;
		}
		
		$this->flush() ;
	}
	
	private $bEnableEscape = false ;
}


