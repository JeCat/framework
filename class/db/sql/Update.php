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
namespace org\jecat\framework\db\sql ;

use org\jecat\framework\db\sql\compiler\SqlCompiler;

class Update extends MultiTableSQL implements IDataSettableStatement
{
	function __construct($sTableName=null,$sTableAlias=null)
	{
		$this->nTablesClause = SQL::CLAUSE_UPDATE ;
		parent::__construct($sTableName,$sTableAlias) ;
	}
	public function data($sColumnName)
	{
		$arrRawSet =& $this->rawClause(SQL::CLAUSE_SET) ;
		if( isset($arrRawSet['subtree'][$sColumnName]) )
		{
			return SqlCompiler::singleton()->compile($arrRawSet['subtree'][$sColumnName]['subtree']) ;
		}
	}
	public function setData($sColumnName,$value=null,$bValueExpr=false)
	{
		list($sTableName,$sColumnName) = SQL::splitColumn($sColumnName) ;
		
		$arrRawSet =& $this->rawClause(SQL::CLAUSE_SET) ;
		if( !isset($arrRawSet['subtree'][$sColumnName]) and !empty($arrRawSet['subtree']) )
		{
			$arrRawSet['subtree'][] = ',' ;
		}
		
		$arrRawSet['subtree'][$sColumnName] = array(
				'expr_type' => 'assignment' ,
				'pretree' => array(
						SQL::createRawColumn($sTableName,$sColumnName), '='
				) ,
				//'subtree' => array( $bValueExpr? SQL::transValue($value): SQL::transValue($value) ) ,
		) ;

		if($bValueExpr)
		{
			$arrRawSet['subtree'][$sColumnName]['subtree'] =& SQL::parseSql($value,'values',true) ;
		}
		else
		{
			$arrRawSet['subtree'][$sColumnName]['subtree'] = array( SQL::transValue($value) ) ;
		}
	}
	
	public function removeData($sColumnName)
	{
		unset($this->arrRawSql['subtree'][SQL::CLAUSE_SET]) ;
	}

	public function clearData()
	{
		unset($this->arrRawSql['subtree'][SQL::CLAUSE_SET]) ;
	}
}

