<?php
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

use org\jecat\framework\db\sql\parser\BaseParserFactory;

class Restriction extends SQL
{
	public function __construct($bLogic=true)
	{
		parent::__construct() ;
		$this->setDefaultLogic($bLogic) ;
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
			$this->arrRawSql['subtree'] = array_merge( $this->arrRawSql['subtree'], self::makeColumn($sColumn) ) ;
		}
		else
		{
			$this->arrRawSql['subtree'][] = self::createRawColumn($sTable?:'',$sColumn) ;
		}
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
		$this->arrRawSql['subtree'][] = self::transValue($value) ;
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
		$this->arrRawSql['subtree'][] = self::transValue($value) ;
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
		$this->arrRawSql['subtree'][] = self::transValue($value) ;
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
		$this->arrRawSql['subtree'][] = self::transValue($value) ;
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
		$this->arrRawSql['subtree'][] = self::transValue($value) ;
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
		$this->arrRawSql['subtree'][] = self::transValue($value) ;
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
		$this->arrRawSql['subtree'][] = self::transValue($value) ;
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
		$this->arrRawSql['subtree'][] = self::transValue($value) ;
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
		$this->arrRawSql['subtree'][] = self::transValue($value) ;
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
			$v = self::transValue($v);
		}
		$this->putLogic($bLogicAnd) ;
		$this->arrRawSql['subtree'][] = '(' ;
		$this->putColumn($sClmName,$sTable) ;
		$this->arrRawSql['subtree'][] = 'IN' ;
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
			$v = self::transValue($v);
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
		$this->arrRawSql['subtree'][] = self::transValue($value) ;
		$this->arrRawSql['subtree'][] = 'AND' ;
		$this->arrRawSql['subtree'][] = self::transValue($otherValue) ;
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
		$this->arrRawSql['subtree'][] = self::transValue($value) ;
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
		$this->arrRawSql['subtree'][] = self::transValue($value) ;
		return $this;
	}
	
	/**
	 * 直接把字符串作为条件语句的一部分使用.
	 * @param string sql条件语句
	 * @return self 调用方法的实例自身
	 */
	public function expression($sExpression, $bLogicAnd=true)
	{
		$arrTokenTree =& BaseParserFactory::singleton()->create(true,null,'where')->parse($sExpression,true) ;
		if( $arrTokenTree )
		{
			$this->putLogic($bLogicAnd) ;
			$this->arrRawSql['subtree'] = array_merge(
						$this->arrRawSql['subtree']
						, $arrTokenTree
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
		$this->putLogic($bLogicAnd) ;
		$this->arrRawSql['subtree'][] = '(' ;
		$this->arrRawSql['subtree'][] =& $aOtherRestriction->rawSql() ;
		$this->arrRawSql['subtree'][] = ')' ;
		return $this ;
	}
		
	/**
	 * 
	 * 创建一个新的Restriction对象并把它作为条件语句的一部分添加到方法调用者中
	 * @return self 被创建的Restriction对象
	 */
	public function createRestriction($bLogic=true)
	{
		$aRestriction = self($bLogic) ;
		$this->add($aRestriction);
		return $aRestriction;
	}
	    
    /**
     *
     * 对直接量进行转化,使其在组合后的sql语句中合法.
     * @param mix $value 条件语句中的直接量
     * @return string
     */
	static protected function transValue($value)
    {
    	if (is_string ( $value ))
    	{
    		return "'" . addslashes ( $value ) . "'";
    	}
    	else if (is_numeric ( $value ))
    	{
    		return "'" .$value. "'";
    	}
    	else if (is_bool ( $value ))
    	{
    		return $value ? "'1'" : "'0'";
    	}
    	else if ($value === null)
    	{
    		return "NULL";
    	}
    	else
    	{
    		return "'" . strval ( $value ) . "'";
    	}
    }

    private $sLogic = 'AND' ;
}
?>
