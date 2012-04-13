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
namespace org\jecat\framework\db\sql\parser ;

class TableKeywordParser extends TableParser
{
	public function examineStateChange(&$sToken,ParseState $aParseState)
	{
		if( $sToken!=='TABLE' or $sToken!=='TABLES' )
		{
			return false ;
		}
		
		$aParseState->arrTree[] = $sToken ;
		
		// 遇到 TABLE ，检查下一个token 是否是有效的表名
		$sToken = next($aParseState->arrTokenList) ;
		
		return parent::examineStateChange($sToken,$aParseState) ;
	}

	public function examineStateFinish(& $sToken,ParseState $aParseState)
	{
		// 遇到 TABLE 则下一个 token 才是表名
		// 暂时用 TABLE 代替 TABLES ，即 TABLES 后面只有一个 表名被识别
		return $sToken!=='TABLE' and $sToken!=='TABLES';
	}
}
