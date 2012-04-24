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
namespace org\jecat\framework\db\sql;

/**
 *  @wiki /MVC模式/数据库模型/模型加载的条件
 *  ==模型加载的条件==
 *	
 *  模型是对数据表的封装，数据表的过滤条件同样也被封装为对象来进行处理。
 *
 *	{|
 *	 !判断依据
 *	 !说明
 *	 |---
 *	 |logic
 *	 |默认情况下是AND，可以通过setLogic()方法设置logic的值，true为AND，false为OR
 *	 |---
 *	 |eq
 *	 |添加一个条件语句,判断字段的值是否和期望值相等
 *	 |---
 *	 |eqColumn
 *	 |添加一个条件语句,判断2个字段的值是否相等
 *	 |---
 *	 |ne
 *	 |添加一个条件语句,判断字段的值是否和期望值不相等
 *	 |---
 *	 |neColumn
 *	 |添加一个条件语句,判断2个字段的值是否不相等
 *	 |---
 *	 |gt
 *	 |添加一个条件语句,判断指定字段的值是否大于期望值
 *	 |---
 *	 |gtColumn
 *	 |添加一个条件语句,判断指定的2个字段的值是否前者大于后者
 *	 |---
 *	 |ge
 *	 |添加一个条件语句,判断指定字段的值是否大于等于期望值
 *	 |---
 *	 |geColumn
 *	 |添加一个条件语句,判断指定的2个字段的值是否前者大于等于后者
 *	 |---
 *	 |lt
 *	 |添加一个条件语句,判断指定字段的值是否小于期望值
 *	 |---
 *	 |ltColumn
 *	 |添加一个条件语句,判断指定的2个字段的值是否前者小于后者
 *	 |---
 *	 |le
 *	 |添加一个条件语句,判断指定字段的值是否小于等于期望值
 *	 |---
 *	 |leColumn
 *	 |添加一个条件语句,判断指定的2个字段的值是否前者小于等于后者
 *	 |---
 *	 |like
 *	 |添加一个条件语句,判断指定字段的值是否和期望值相似
 *	 |---
 *	 |in
 *	 |添加一个条件语句,判断指定字段的值是否和指定数组中的某个元素的值相等
 *	 |---
 *	 |between
 *	 |添加一个条件语句,判断指定字段的值是否在指定的2个值区间内
 *	 |---
 *	 |isNull
 *	 |添加一个条件语句,判断指定字段的值是否是null
 *	 |---
 *	 |isNotNull
 *	 |添加一个条件语句,判断指定字段的值是否不是null
 *	 |}
 */


class Restriction extends SQL
{
	public function __construct($bDefaultLogic=true,array & $arrRawSql=null)
	{
		$this->setDefaultLogic($bDefaultLogic) ;
		parent::__construct($arrRawSql) ;
	} 
	
	/**
	 * 清空所有已添加的条件语句.
	 */
	public function clear()
	{
		$this->arrRawSql['subtree'] = array() ;
		return $this ;
	}
	
	/**
	 * 返回这个对象用'AND'还是'OR'来拼接条件.
	 *
	 * @return boolean 如果用'AND'拼接返回true,用'OR'拼接返回false
	 */
	public function defaultLogic() {
		return $this->sLogic == ' AND ';
	}
	
	/**
	 *
	 * 设置用'AND'还是用'OR'来拼接条件.
	 * @param boolean 使用'AND'拼接条件则传入true,使用'OR'拼接则传入false
	 */
	public function setDefaultLogic($bLogic) {
		$this->sLogic = $bLogic ? ' AND ' : ' OR ';
		return $this ;
	}
	
	protected function putLogic($bLogicAnd)
	{
		if( !empty($this->arrRawSql['subtree']) )
		{
			$this->arrRawSql['subtree'][] = ($bLogicAnd===null)? $this->sLogic: ($bLogicAnd? 'AND': 'OR') ;
		}
	}
	protected function putColumn($sColumn,$sTable=null)
	{
		if($sTable===null)
		{
			list($sTable,$sColumn) = SQL::splitColumn($sColumn) ;
		}
		
		$this->arrRawSql['subtree'][] = self::createRawColumn($sTable?:'',$sColumn) ;
	}
	
	/**
	 * 添加一个条件语句,判断字段的值是否和期望值相等 
	 * @param string $sClmName 字段名
	 * @param mix $value 期望值
	 * @return self
	 */
	public function eq($sClmName, $value, $sTable=null, $bLogicAnd=null)
	{
		$this->putLogic($bLogicAnd) ;
		$this->putColumn($sClmName,$sTable) ;
		$this->arrRawSql['subtree'][] = '=' ;
		$this->arrRawSql['subtree'][] = SQL::transValue($value) ;
		return $this;
	}
	
	/**
	 * 添加一个条件语句,判断2个字段的值是否相等 
	 * @param string $sClmName 其中一个需检验的字段名
	 * @param string $sOtherClmName 另外一个需检验的字段名
	 * @return self 
	 */
	public function eqColumn($sClmName,$sOtherClmName, $sTable=null, $sOtherClmTable=null, $bLogicAnd=true)
	{
		$this->putLogic($bLogicAnd) ;
		$this->putColumn($sClmName,$sTable) ;
		$this->arrRawSql['subtree'][] = '=' ;
		$this->putColumn($sOtherClmName,$sOtherClmTable) ;
		return $this;
	}
	
	/**
	 * 添加一个条件语句,判断字段的值是否和期望值不相等 
	 * @param string $sClmName 字段名
	 * @param mix $value 期望值
	 * @return self
	 */
	public function ne($sClmName, $value, $sTable=null, $bLogicAnd=true)
	{
		$this->putLogic($bLogicAnd) ;
		$this->putColumn($sClmName,$sTable) ;
		$this->arrRawSql['subtree'][] = '<>' ;
		$this->arrRawSql['subtree'][] = SQL::transValue($value) ;
		return $this;
	}
	
	/**
	 * 添加一个条件语句,判断2个字段的值是否不相等 
	 * @param string $sClmName 其中一个需检验的字段名
	 * @param string $sOtherClmName 另外一个需检验的字段名
	 * @return self 
	 */
	public function neColumn($sClmName, $sOtherClmName, $sTable=null, $sOtherClmTable=null, $bLogicAnd=true)
	{
		$this->putLogic($bLogicAnd) ;
		$this->putColumn($sClmName,$sTable) ;
		$this->arrRawSql['subtree'][] = '<>' ;
		$this->putColumn($sOtherClmName,$sOtherClmTable) ;
		return $this;
	}
	
	/**
	 * 添加一个条件语句,判断指定字段的值是否大于期望值
	 * @param string $sClmName 需检验的字段名
	 * @param mix $value 期望值
	 * @return self 
	 */
	public function gt($sClmName, $value, $sTable=null, $bLogicAnd=true)
	{
		$this->putLogic($bLogicAnd) ;
		$this->putColumn($sClmName,$sTable) ;
		$this->arrRawSql['subtree'][] = '>' ;
		$this->arrRawSql['subtree'][] = SQL::transValue($value) ;
		return $this;
	}
	
	/**
	 * 添加一个条件语句,判断指定的2个字段的值是否前者大于后者
	 * @param string $sClmName 大于号左边的字段名
	 * @param string $sOtherClmName 大于号右边的字段名
	 * @return self 
	 */
	public function gtColumn($sClmName, $sOtherClmName, $sTable=null, $sOtherClmTable=null, $bLogicAnd=true)
	{
		$this->putLogic($bLogicAnd) ;
		$this->putColumn($sClmName,$sTable) ;
		$this->arrRawSql['subtree'][] = '>' ;
		$this->putColumn($sOtherClmName,$sOtherClmTable) ;
		return $this;
	}
	
	/**
	 * 添加一个条件语句,判断指定字段的值是否大于等于期望值
	 * @param string $sClmName 需检验的字段名
	 * @param mix $value 期望值
	 * @return self 
	 */
	public function ge($sClmName, $value, $sTable=null, $bLogicAnd=true)
	{
		$this->putLogic($bLogicAnd) ;
		$this->putColumn($sClmName,$sTable) ;
		$this->arrRawSql['subtree'][] = '>=' ;
		$this->arrRawSql['subtree'][] = SQL::transValue($value) ;
		return $this;
	}
	
	/**
	 * 添加一个条件语句,判断指定的2个字段的值是否前者大于等于后者
	 * @param string $sClmName 大于等于号左边的字段名
	 * @param string $sOtherClmName 大于等于号右边的字段名
	 * @return self 
	 */
	public function geColumn($sClmName, $sOtherClmName, $sTable=null, $sOtherClmTable=null, $bLogicAnd=true)
	{
		$this->putLogic($bLogicAnd) ;
		$this->putColumn($sClmName,$sTable) ;
		$this->arrRawSql['subtree'][] = '>=' ;
		$this->putColumn($sOtherClmName,$sOtherClmTable) ;
		return $this;
	}
	
	/**
	 * 添加一个条件语句,判断指定字段的值是否小于期望值
	 * @param string $sClmName 需检验的字段名
	 * @param mix $value 期望值
	 * @return self 
	 */
	public function lt($sClmName, $value, $sTable=null, $bLogicAnd=true)
	{
		$this->putLogic($bLogicAnd) ;
		$this->putColumn($sClmName,$sTable) ;
		$this->arrRawSql['subtree'][] = '<' ;
		$this->arrRawSql['subtree'][] = SQL::transValue($value) ;
		return $this;
	}
	
	/**
	 * 添加一个条件语句,判断指定的2个字段的值是否前者小于后者
	 * @param string $sClmName 小于号左边的字段名
	 * @param string $sOtherClmName 小于号右边的字段名
	 * @return self 
	 */
	public function ltColumn($sClmName, $sOtherClmName, $sTable=null, $sOtherClmTable=null, $bLogicAnd=true)
	{
		$this->putLogic($bLogicAnd) ;
		$this->putColumn($sClmName,$sTable) ;
		$this->arrRawSql['subtree'][] = '<' ;
		$this->putColumn($sOtherClmName,$sOtherClmTable) ;
		return $this;
	}
	
	/**
	 * 添加一个条件语句,判断指定字段的值是否小于等于期望值
	 * @param string $sClmName 需检验的字段名
	 * @param mix $value 期望值
	 * @return self 
	 */
	public function le($sClmName, $value, $sTable=null, $bLogicAnd=true)
	{
		$this->putLogic($bLogicAnd) ;
		$this->putColumn($sClmName,$sTable) ;
		$this->arrRawSql['subtree'][] = '<=' ;
		$this->arrRawSql['subtree'][] = SQL::transValue($value) ;
		return $this;
	}
	
	/**
	 * 添加一个条件语句,判断指定的2个字段的值是否前者小于等于后者
	 * @param string $sClmName 小于等于号左边的字段名
	 * @param string $sOtherClmName 小于等于号右边的字段名
	 * @return self 
	 */
	public function leColumn($sClmName, $sOtherClmName, $sTable=null, $sOtherClmTable=null, $bLogicAnd=true)
	{
		$this->putLogic($bLogicAnd) ;
		$this->putColumn($sClmName,$sTable) ;
		$this->arrRawSql['subtree'][] = '<=' ;
		$this->putColumn($sOtherClmName,$sOtherClmTable) ;
		return $this;
	}
	
	/**
	 * 添加一个条件语句,判断指定字段的值是否和期望值相似
	 * @param string $sClmName 需检验的字段名
	 * @param mix $value 期望值
	 * @return self 
	 */
	public function like($sClmName, $value, $sTable=null, $bLogicAnd=true)
	{
		$this->putLogic($bLogicAnd) ;
		$this->putColumn($sClmName,$sTable) ;
		$this->arrRawSql['subtree'][] = 'LIKE' ;
		$this->arrRawSql['subtree'][] = SQL::transValue($value) ;
		return $this;
	}
	
	/**
	 * 添加一个条件语句,判断指定字段的值是否和期望值相反
	 * @param string $sClmName 需检验的字段名
	 * @param mix $value 期望值
	 * @return self 
	 */
	public function notLike($sClmName, $value, $sTable=null, $bLogicAnd=true)
	{
		$this->putLogic($bLogicAnd) ;
		$this->putColumn($sClmName,$sTable) ;
		$this->arrRawSql['subtree'][] = 'NOT LIKE' ;
		$this->arrRawSql['subtree'][] = SQL::transValue($value) ;
		return $this;
	}
	
	/**
	 * 添加一个条件语句,判断指定字段的值是否符合正则
	 * @param string $sClmName 需检验的字段名
	 * @param mix $value 正则
	 * @return self 
	 */
	public function regexp($sClmName, $value, $sTable=null, $bLogicAnd=true)
	{
		$this->putLogic($bLogicAnd) ;
		$this->putColumn($sClmName,$sTable) ;
		$this->arrRawSql['subtree'][] = 'REGEXP' ;
		$this->arrRawSql['subtree'][] = SQL::transValue($value) ;
		return $this;
	}
	
	/**
	 * 添加一个条件语句,判断指定字段的值是否和指定数组中的某个元素的值相等 
	 * @param string $sClmName 需要检验的字段名
	 * @param array $arrValues 比照数组
	 * @return self 
	 */
	public function in($sClmName, array $arrValues, $sTable=null, $bLogicAnd=true)
	{
		foreach($arrValues as &$v)
		{
			$v = SQL::transValue($v);
		}
		$this->putLogic($bLogicAnd) ;
		$this->putColumn($sClmName,$sTable) ;
		$this->arrRawSql['subtree'][] = 'IN' ;
		$this->arrRawSql['subtree'][] = '(' ;
		$this->arrRawSql['subtree'][] = implode(",",$arrValues) ;
		$this->arrRawSql['subtree'][] = ')' ;
		return $this;
	}
	
	/**
	 * 添加一个条件语句,判断指定字段的值是否和指定数组中的某个元素的值相反 
	 * @param string $sClmName 需要检验的字段名
	 * @param array $arrValues 比照数组
	 * @return self 
	 */
	public function notIn($sClmName, array $arrValues, $sTable=null, $bLogicAnd=true)
	{
		foreach($arrValues as $v)
		{
			$v = SQL::transValue($v);
		}
		$this->putLogic($bLogicAnd) ;
		$this->arrRawSql['subtree'][] = '(' ;
		$this->putColumn($sClmName,$sTable) ;
		$this->arrRawSql['subtree'][] = 'NOT IN' ;
		$this->arrRawSql['subtree'][] = implode(",",$arrValues) ;
		$this->arrRawSql['subtree'][] = ')' ;
		return $this;
	}
	
	/**
	 * 添加一个条件语句,判断指定字段的值是否在指定的2个值区间内
	 * @param string $sClmName
	 * @param mix $value
	 * @param mix $otherValue
	 * @return self
	 */
	public function between($sClmName, $value, $otherValue, $sTable=null, $bLogicAnd=true)
	{
		$this->putLogic($bLogicAnd) ;
		$this->putColumn($sClmName,$sTable) ;
		$this->arrRawSql['subtree'][] = 'BETWEEN' ;
		$this->arrRawSql['subtree'][] = SQL::transValue($value) ;
		$this->arrRawSql['subtree'][] = 'AND' ;
		$this->arrRawSql['subtree'][] = SQL::transValue($otherValue) ;
		return $this;
	}
	
	/**
	 * 添加一个条件语句,判断指定字段的值是否是null
	 * @param string $sClmName
	 * @return self
	 */
	public function isNull($sClmName, $sTable=null, $bLogicAnd=true)
	{
		$this->putLogic($bLogicAnd) ;
		$this->putColumn($sClmName,$sTable) ;
		$this->arrRawSql['subtree'][] = 'IS NULL' ;
		return $this;
	}
	
	/**
	 * 添加一个条件语句,判断指定字段的值是否不是null
	 * @param string $sClmName
	 * @return self
	 */
	public function isNotNull($sClmName, $sTable=null, $bLogicAnd=true)
	{
		$this->putLogic($bLogicAnd) ;
		$this->putColumn($sClmName,$sTable) ;
		$this->arrRawSql['subtree'][] = 'IS NOT NULL' ;
		return $this;
	}
	
	/**
	 * 直接把字符串作为条件语句的一部分使用.
	 * @param string sql条件语句
	 * @return self 调用方法的实例自身
	 */
	public function expression($expression, $bLogicAnd=true, $bRawTokenExpr=false)
	{
		if(!$bRawTokenExpr)
		{
			$expression =& SQL::parseSql($expression,'where',true) ;
		}
		if( $expression )
		{			
			$this->putLogic($bLogicAnd) ;
			if(!isset($this->arrRawSql['subtree']))
			{
				$this->arrRawSql['subtree'] = array() ;
			}
			$this->arrRawSql['subtree'] = array_merge(
						$this->arrRawSql['subtree']
						, $expression
			) ;
		}
		return $this;
	}
	
	/**
	 * 把一个 Restriction 实例添加到条件集合中去.
	 * @param self 被添加的Restriction对象
	 * @return self 
	 */
	public function add(self $aOtherRestriction, $bLogicAnd=true)
	{
		$arrRawSql =& $aOtherRestriction->rawSql() ;
		$sIndex = spl_object_hash($aOtherRestriction) ;
		$bEmpty = empty($this->arrRawSql['subtree']) ;
		
		$this->arrRawSql['subtree'][ $sIndex ] = array(
				'expr_type' => 'expression' ,
				'subtree' => &$arrRawSql['subtree'] ,
		) ;
		
		if( !empty($arrRawSql['factors']) )
		{
			$this->arrRawSql['subtree'][ $sIndex ]['factors'] =& $arrRawSql['factors'] ;
		}
		
		if( !$bEmpty )
		{
			$this->arrRawSql['subtree'][$sIndex]['pretree'][] = $bLogicAnd? 'AND': 'OR' ;
		}
		
		return $this ;
	}

	/**
	 * 删除通过 Restriction::add() 添加的 Restriction对象
	 * @return self
	 */
	public function remove(self $aOtherRestriction)
	{
		unset($this->arrRawSql['subtree'][ spl_object_hash($aOtherRestriction) ]) ;
		return $this ;
	}
	
	/**
	 * 
	 * 创建一个新的Restriction对象并把它作为条件语句的一部分添加到方法调用者中
	 * @return self 被创建的Restriction对象
	 */
	public function createRestriction($bLogic=true)
	{
		$aRestriction = new self($bLogic) ;
		$this->add($aRestriction);
		return $aRestriction;
	}
	    

    private $sLogic = 'AND' ;
}

