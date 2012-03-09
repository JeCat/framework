<?php
namespace org\jecat\framework\db\sql;

use org\jecat\framework\db\sql\name\NameTransfer;

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

class Restriction extends SubStatement
{
	public function __construct($bLogic=true)
	{
		$this->setLogic($bLogic) ;
	} 
	
	/**
	 * 把所有条件拼接成字符串,相当于把这个对象字符串化
	 * 
	 * @param $aState
	 * @return string
	 */
	public function makeStatement(StatementState $aState)
	{
		if(!$this->arrExpressions)
		{
			return '1' ;
		}
		
		$arrExpressions = array ();
		foreach ( $this->arrExpressions as $express )
		{
			if ($express instanceof Restriction)
			{
				$sExpress = $express->makeStatement ($aState) ;
				if($sExpress!='1')
				{
					$arrExpressions[] = $sExpress ;
				}
			}
			else if(is_array($express))
			{				
				// 字段名
				$n = count($express) ;
				for($i=1;$i<$n;$i++)
				{
					$express[$i] = $this->transColumn($express[$i],$aState) ;
				}
				
				$arrExpressions [] = call_user_func_array('sprintf',$express) ;
			}
			else
			{
				$arrExpressions [] = $express;
			}
		}
		
		switch (count($arrExpressions))
		{
			case 0 :
				return '1' ;
			case 1 :
				return $arrExpressions[0] ;
			default :
				return '('.implode($this->sLogic,$arrExpressions).')' ;
		}
	}
	
	public function checkValid($bThrowException = true)
	{
		return true;
	}
	
	/**
	 * 返回这个对象用'AND'还是'OR'来拼接条件.
	 * 
	 * @return boolean 如果用'AND'拼接返回true,用'OR'拼接返回false
	 */
	public function logic() {
		return $this->sLogic == ' AND ';
	}
	
	/**
	 * 
	 * 设置用'AND'还是用'OR'来拼接条件.
	 * @param boolean 使用'AND'拼接条件则传入true,使用'OR'拼接则传入false
	 */
	public function setLogic($bLogic) {
		$this->sLogic = $bLogic ? ' AND ' : ' OR ';
		return $this ;
	}
	
	/**
	 * 清空所有已添加的条件语句.
	 */
	public function clear()
	{
		$this->arrExpressions = null ;
		return $this ;
	}
	
	/**
	 * 添加一个条件语句,判断字段的值是否和期望值相等 
	 * @param string $sClmName 字段名
	 * @param mix $value 期望值
	 * @return self
	 */
	public function eq($sClmName, $value)
	{
		$this->arrExpressions[] = array("%s = ".$this->transValue($value),$sClmName) ;
		return $this;
	}
	
	/**
	 * 添加一个条件语句,判断2个字段的值是否相等 
	 * @param string $sClmName 其中一个需检验的字段名
	 * @param string $sOtherClmName 另外一个需检验的字段名
	 * @return self 
	 */
	public function eqColumn($sClmName,$sOtherClmName)
	{
		$this->arrExpressions[] = array("%s = %s",$sClmName,$sOtherClmName) ;
		return $this;
	}
	
	/**
	 * 添加一个条件语句,判断字段的值是否和期望值不相等 
	 * @param string $sClmName 字段名
	 * @param mix $value 期望值
	 * @return self
	 */
	public function ne($sClmName, $value)
	{
		$this->arrExpressions[] = array("%s <> ".$this->transValue($value),$sClmName) ;
		return $this;
	}
	
	/**
	 * 添加一个条件语句,判断2个字段的值是否不相等 
	 * @param string $sClmName 其中一个需检验的字段名
	 * @param string $sOtherClmName 另外一个需检验的字段名
	 * @return self 
	 */
	public function neColumn($sClmName, $sOtherClmName)
	{
		$this->arrExpressions[] = array("%s <> %s",$sClmName,$sOtherClmName) ;
		return $this;
	}
	
	/**
	 * 添加一个条件语句,判断指定字段的值是否大于期望值
	 * @param string $sClmName 需检验的字段名
	 * @param mix $value 期望值
	 * @return self 
	 */
	public function gt($sClmName, $value)
	{
		$this->arrExpressions[] = array("%s > ".$this->transValue($value),$sClmName) ;
		return $this;
	}
	
	/**
	 * 添加一个条件语句,判断指定的2个字段的值是否前者大于后者
	 * @param string $sClmName 大于号左边的字段名
	 * @param string $sOtherClmName 大于号右边的字段名
	 * @return self 
	 */
	public function gtColumn($sClmName, $sOtherClmName)
	{
		$this->arrExpressions[] = array("%s > %s",$sClmName,$sOtherClmName) ;
		return $this;
	}
	
	/**
	 * 添加一个条件语句,判断指定字段的值是否大于等于期望值
	 * @param string $sClmName 需检验的字段名
	 * @param mix $value 期望值
	 * @return self 
	 */
	public function ge($sClmName, $value)
	{
		$this->arrExpressions[] = array("%s >= ".$this->transValue($value),$sClmName) ;
		return $this;
	}
	
	/**
	 * 添加一个条件语句,判断指定的2个字段的值是否前者大于等于后者
	 * @param string $sClmName 大于等于号左边的字段名
	 * @param string $sOtherClmName 大于等于号右边的字段名
	 * @return self 
	 */
	public function geColumn($sClmName, $sOtherClmName)
	{
		$this->arrExpressions[] = array("%s >= %s",$sClmName,$sOtherClmName) ;
		return $this;
	}
	
	/**
	 * 添加一个条件语句,判断指定字段的值是否小于期望值
	 * @param string $sClmName 需检验的字段名
	 * @param mix $value 期望值
	 * @return self 
	 */
	public function lt($sClmName, $value)
	{
		$this->arrExpressions[] = array("%s < ".$this->transValue($value),$sClmName) ;
		return $this;
	}
	
	/**
	 * 添加一个条件语句,判断指定的2个字段的值是否前者小于后者
	 * @param string $sClmName 小于号左边的字段名
	 * @param string $sOtherClmName 小于号右边的字段名
	 * @return self 
	 */
	public function ltColumn($sClmName, $sOtherClmName)
	{
		$this->arrExpressions[] = array("%s < %s",$sClmName,$sOtherClmName) ;
		return $this;
	}
	
	/**
	 * 添加一个条件语句,判断指定字段的值是否小于等于期望值
	 * @param string $sClmName 需检验的字段名
	 * @param mix $value 期望值
	 * @return self 
	 */
	public function le($sClmName, $value)
	{
		$this->arrExpressions[] = array("%s <= ".$this->transValue($value),$sClmName) ;
		return $this;
	}
	
	/**
	 * 添加一个条件语句,判断指定的2个字段的值是否前者小于等于后者
	 * @param string $sClmName 小于等于号左边的字段名
	 * @param string $sOtherClmName 小于等于号右边的字段名
	 * @return self 
	 */
	public function leColumn($sClmName, $sOtherClmName)
	{
		$this->arrExpressions[] = array("%s <= %s",$sClmName,$sOtherClmName) ;
		return $this;
	}
	
	/**
	 * 添加一个条件语句,判断指定字段的值是否和期望值相似
	 * @param string $sClmName 需检验的字段名
	 * @param mix $value 期望值
	 * @return self 
	 */
	public function like($sClmName, $value)
	{
		$this->arrExpressions[] = array("%s LIKE ".$this->transValue($value),$sClmName) ;
		return $this;
	}
	
	/**
	 * 添加一个条件语句,判断指定字段的值是否和指定数组中的某个元素的值相等 
	 * @param string $sClmName 需要检验的字段名
	 * @param array $arrValues 比照数组
	 * @return self 
	 */
	public function in($sClmName, array $arrValues )
	{
		foreach($arrValues as $v)
		{
			$v = $this->transValue($v);
		}
		$this->arrExpressions[] = array("%s IN (".implode(",",$arrValues).")",$sClmName) ;
		return $this;
	}
	
	/**
	 * 添加一个条件语句,判断指定字段的值是否在指定的2个值区间内
	 * @param string $sClmName
	 * @param mix $value
	 * @param mix $otherValue
	 * @return self
	 */
	public function between($sClmName, $value, $otherValue)
	{
		$this->arrExpressions[] = array("%s BETWEEN "
									. $this->transValue($value) 
									. ' AND '
									. $this->transValue($otherValue)
								,$sClmName
		) ;
		return $this;
	}
	
	/**
	 * 添加一个条件语句,判断指定字段的值是否是null
	 * @param string $sClmName
	 * @return self
	 */
	public function isNull($sClmName)
	{
		$this->arrExpressions[] = array("%s IS NULL ",$sClmName) ;
		return $this;
	}
	
	/**
	 * 添加一个条件语句,判断指定字段的值是否不是null
	 * @param string $sClmName
	 * @return self
	 */
	public function isNotNull($sClmName)
	{
		$this->arrExpressions[] = array("%s IS NOT NULL ",$sClmName) ;
		return $this;
	}
	
	/**
	 * 直接把字符串作为条件语句的一部分使用.
	 * @param string sql条件语句
	 * @return self 调用方法的实例自身
	 */
	public function expression($sExpression)
	{
		$this->arrExpressions [] = $sExpression;
		return $this;
	}
	
	/**
	 * 把一个 Restriction 实例添加到条件集合中去.
	 * @param self 被添加的Restriction对象
	 * @return self 
	 */
	public function add(self $aOtherRestriction)
	{
		$this->arrExpressions [] = $aOtherRestriction;
		return $this;
	}
	
	/**
	 * 把一个 Restriction 实例添加到条件集合中去.
	 * @param self 被添加的Restriction对象
	 * @return self 
	 */
	public function remove(self $aOtherRestriction)
	{
		$nIdx = array_search($aOtherRestriction,$this->arrExpressions,true) ;
		if($nIdx!==false)
		{
			unset($this->arrExpressions[$nIdx]) ;
		}
	}
	
	/**
	 * 
	 * 创建一个新的Restriction对象并把它作为条件语句的一部分添加到方法调用者中
	 * @return self 被创建的Restriction对象
	 */
	public function createRestriction($bLogic=true)
	{
		$aRestriction = $this->statementFactory()->createRestriction($bLogic) ;
		$this->add($aRestriction);
		return $aRestriction;
	}
	
    function __clone()
    {
    	if( $this->arrExpressions )
    	{
	        foreach( $this->arrExpressions as &$expression)
	        {
	            if(is_object($expression))
	            {
	                $expression = clone $expression;
	            }
	        }
    	}
    }
    
	private $sLogic = ' AND ';
	
	private $arrExpressions = null ;
	
}
?>
