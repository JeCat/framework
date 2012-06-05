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
namespace org\jecat\framework\db\sql\compiler ;

use org\jecat\framework\db\DB;
use org\jecat\framework\lang\Object;

class SqlNameCompiler extends Object
{	
	public function compile(SqlCompiler $aSqlCompiler,array & $arrTokenTree,array & $arrToken,array & $arrFactors=null)
	{
		// 处理表名
		if( $arrToken['expr_type']==='table' )
		{
			$sTable = $arrToken['table'] ;
			$sAlias = empty($arrToken['as'])? null: $arrToken['as'] ;

			// 表名称转换
			if($this->arrTableNameTranslaters)
			{
				foreach($this->arrTableNameTranslaters as &$translaters)
				{
					list($sTable,$sAlias) = call_user_func($translaters,$sTable,$sAlias,$arrToken,$arrTokenTree) ;
				}
			}
			
			$sSql = '`'.$sTable.'` ' ;
			if($sAlias)
			{
				$sSql.= 'AS `' . $sAlias . '` ' ;
			}
			
			// 处理 join 子句
			$sSql.= $aSqlCompiler->compile($arrToken,$arrFactors,$arrTokenTree) ;

			return $sSql ;
		}

		// 处理字段名
		else if( $arrToken['expr_type']==='column' )
		{
			$sTable = empty($arrToken['table'])? null: $arrToken['table'] ;
			$sColumn = $arrToken['column'] ;
			$sAlias = empty($arrToken['as'])? null: $arrToken['as'] ;
				
			// 字段名称转换
			if($this->arrColumnNameTranslaters)
			{
				foreach($this->arrColumnNameTranslaters as &$translaters)
				{
					list($sTable,$sColumn,$sAlias) = call_user_func($translaters,$sTable,$sColumn,$sAlias,$arrToken,$arrTokenTree) ;
				}
			}
			
			$sSql = $sTable? ('`'.$sTable.'`.'): '' ;
			$sSql.= ($sColumn==='*')? $sColumn: ('`'.$sColumn.'`') ;
			if($sAlias)
			{
				$sSql.= ' AS `' . $sAlias . '`' ;
			}
			
			return $sSql ;
		}
	}
	
	public function registerColumnNameTranslaters($fnTranslaters)
	{
		$this->arrColumnNameTranslaters[] = $fnTranslaters ;
		return $this ;
	}
	public function registerTableNameTranslaters($fnTranslaters)
	{
		$this->arrTableNameTranslaters[] = $fnTranslaters ;
		return $this ;
	}

	static public function translateTableName($sTable,$sAlias,array & $arrToken,array & $arrTokenTree)
	{
		return array( DB::singleton()->transTableName($sTable), $sAlias ) ;
	}
	
	private $arrColumnNameTranslaters ;
	
	private $arrTableNameTranslaters = array( array(__CLASS__,'translateTableName') ) ;
	
}

