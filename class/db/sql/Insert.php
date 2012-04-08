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

class Insert extends SQL
{
	public function __construct($sTableName="")
	{
		$this->arrRawSql = array(
				'expr_type' => 'query' ,
				'subtree' => array() ,
				'command' => 'INSERT' ,
		) ;
		$this->setTableName($sTableName) ;
	}
	
	public function tableName() 
	{
		$arrRawInto =& $this->rawClause(SQL::CLAUSE_INTO,true,$this->rawClause(SQL::CLAUSE_INSERT)) ;
		if( empty($arrRawInto['subtree'][0]['table']) or $arrRawInto['subtree'][0]['expr_type']!=='table' )
		{
			return null ;
		}
		return $arrRawInto['subtree'][0]['table'] ;
	}
	
	public function setTableName($sTableName,$sDBName=null) 
	{
		$arrRawInto =& $this->rawClause(SQL::CLAUSE_INTO,true,$this->rawClause(SQL::CLAUSE_INSERT)) ;
		$arrRawInto['subtree'] = array(
				self::createRawTable($sTableName,null,$sDBName)
		) ;
		
		return $this ;
	}
	
	public function setData($sColumn,$value=null,$bValueExpr=false,$nRow=0)
	{
		$arrRawValues =& $this->rawClauseValue() ;
		
		$sValueRowKey = 'ROW'.$nRow ;
		
		// 更改已经存在的数据
		if( isset($arrRawValues['pretree']['COLUMNS']['subtree'][$sColumn]) )
		{
			$arrRawValues['subtree'][$sValueRowKey]['subtree'][$sColumn]['subtree'] = $bValueExpr? array( $value ): array( SQL::transValue($value) ) ;
		}
		
		// 插入新数据
		else 
		{
			if(!empty($arrRawValues['pretree']['COLUMNS']['subtree']))
			{
				$arrRawValues['pretree']['COLUMNS']['subtree'][] = ',' ;
			}
			$arrRawValues['pretree']['COLUMNS']['subtree'][$sColumn] = self::createRawColumn(null,$sColumn) ;
			
			// 插入行
			if( !isset($arrRawValues['subtree'][$sValueRowKey]) )
			{
				// 删除开始的 (  和 删除结尾的 )
				array_shift($arrRawValues['subtree']) ;
				array_pop($arrRawValues['subtree']) ;
				
				if( !empty($arrRawValues['subtree']) )
				{
					$arrRawValues['subtree'][] = ',' ;
				}
				$arrRawValues['subtree'][$sValueRowKey] = array(
							'expr_type' => 'values_row' ,
							'subtree' => array() ,
				) ;
				
				// 套上 ( 和 )
				array_unshift($arrRawValues['subtree'],'(') ;
				array_push($arrRawValues['subtree'],')') ;
			}
			
			// 写入数据
			if( !empty($arrRawValues['subtree'][$sValueRowKey]['subtree']) )
			{
				$arrRawValues['subtree'][$sValueRowKey]['subtree'][] = ',' ;
			}
			
			if($bValueExpr)
			{
				// todo ...
			}
			else
			{
				$arrRawValues['subtree'][$sValueRowKey]['subtree'][$sColumn] = SQL::transValue($value) ;
			}
		}
		
		return $this ;
			
	}

	public function addRow(array $arrDatas)
	{
		return $this ;
	}
	
	public function data($sColumnName)
	{
		return isset($this->mapData[$sColumnName])? $this->mapData[$sColumnName]: null ;
	}
	
	public function clearData()
	{
		$arrRawInsert = $this->rawClause(SQL::CLAUSE_INSERT) ;
		unset($arrRawInsert['subtree'][SQL::CLAUSE_VALUES]) ;
		return $this ;
	}

	
	static public function createRawInsertValues()
	{
		return array(
				'expr_type' => 'clause_values' ,
				'pretree' => array(
						'(' ,
						'COLUMNS'=>array(
								'expr_type' => 'values_clmlst' ,
								'subtree' => array() ,
						) ,
						')' ,
						'VALUE' ,
				) ,
				'subtree' => array() ,
		) ;
	}
	
	protected function & rawClauseValue()
	{
		$arrRawInsert =& $this->rawClause(SQL::CLAUSE_INSERT) ;
		
		if( !isset($arrRawInsert['subtree'][SQL::CLAUSE_VALUES]) )
		{
			$arrRawInsert['subtree'][SQL::CLAUSE_VALUES] = self::createRawInsertValues() ;
		}
		return $arrRawInsert['subtree'][SQL::CLAUSE_VALUES] ;
	}
	
	
	/**
	 *
	 * @return string
	 */
	public function toString(SqlCompiler $aSqlCompiler=null)
	{
		ksort($this->arrRawSql['subtree'][SQL::CLAUSE_INSERT]['subtree']) ;
		
		return parent::toString($aSqlCompiler) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @var string
	 */
	private $sTableName = "" ;
	
	private $mapData = array() ;
}

