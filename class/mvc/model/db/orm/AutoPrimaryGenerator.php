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
namespace org\jecat\framework\mvc\model\db\orm ;

use org\jecat\framework\lang\Exception;
use org\jecat\framework\db\reflecter\AbStractColumnReflecter;
use org\jecat\framework\db\DB;
use org\jecat\framework\lang\Object;

class AutoPrimaryGenerator extends Object
{
	public function generate(AbStractColumnReflecter $aClmRfl,$sTable,DB $aDB=null,$nMinLen=4,$nMaxLen=10)
	{
		if( $aClmRfl->isString() )
		{
			return $this->generateCharValue($aDB,$sTable,$aClmRfl->name(),$nMinLen,$nMaxLen) ;
		}
		else if( $aClmRfl->isInts() or $aClmRfl->isFloat() )
		{
			return $this->generateNumValue($aDB,$sTable,$aClmRfl->name(),$nMinLen) ;
		}
		else 
		{
			throw new Exception("无法为数据表字段：%s.%s自动生成主键值，只接受字符或数值类型。",array($sTable,$aClmRfl->name())) ;
		}
	}
	
	private function generateCharValue(DB $aDB,$sTable,$sColumn,$nMinLen,$nMaxLen)
	{
		$sValue = '' ;
		for($nLen=0;$nLen<$nMinLen;$nLen++)
		{
			$sValue.= $this->randChar() ;
		}
		 ;
		while($nLen<=$nMaxLen)
		{
			// 检查表中的值是否存在
			$arrRow = $aDB->query("select count(*) as cnt from `{$sTable}` where `{$sColumn}`='{$sValue}' ;")->fetch(\PDO::FETCH_ASSOC) ; 
			if( $arrRow['cnt']=='0' )
			{
				return $sValue ;
			}
			
			// 增加一位
			$sValue.= $this->randChar() ;
			$nLen ++ ;
		} 
		
		return null ;
	}
	
	private function randChar()
	{
		$nFeed = rand(0,35) ;
		
		// a-z
		if($nFeed>9)
		{
			return chr($nFeed-10+97) ;
		}
		// 0-9
		else
		{
			return chr($nFeed+48) ;
		}
	}
	
	private function generateNumValue(DB $aDB,$sTable,$sColumn,$nMinVal)
	{
		$arrRow = $aDB->query("select max(`$sColumn`) as maxval from `{$sTable}` ;")->fetch(\PDO::FETCH_ASSOC) ;
		$maxVal = (int)$arrRow['maxval'] ;
		return $maxVal<(int)$nMinVal? $nMinVal: $maxVal+1 ;
	}
}



