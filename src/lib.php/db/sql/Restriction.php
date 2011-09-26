<?php
namespace jc\db\sql;

class Restriction extends Statement {
	/**
	 * 把所有条件拼接成字符串,相当于把这个对象字符串化
	 * 
	 * @param $bFormat 是否添加换行以便阅读
	 * @return string
	 */
	public function makeStatement($bFormat = false) {
		//TODO format换行和缩进,增加可读性
		$arrExpressions = array ();
		foreach ( $this->arrExpressions as $express ) {
			if ($express instanceof Restriction) {
				$sExpress = $express->makeStatement ($bFormat) ;
				if($sExpress!='1')
				{
					$arrExpressions[] = $sExpress ;
				}
			} else {
				$arrExpressions [] = $express;
			}
		}
		return empty($arrExpressions)? '1': ('('.implode($this->sLogic,$arrExpressions).')');
	}
	
	public function checkValid($bThrowException = true) {
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
	}
	
	/**
	 * 清空所有已添加的条件语句.
	 */
	public function clear() {
		$this->arrExpressions = array ();
	}
	
	/**
	 * 添加一个条件语句,判断字段的值是否和期望值相等 
	 * @param string $sClmName 字段名
	 * @param mix $value 期望值
	 * @return self
	 */
	public function eq($sClmName, $value) {
		$this->arrExpressions [] = $this->transColumn ( $sClmName ) . ' = ' . $this->tranValue ( $value );
		return $this;
	}
	
	/**
	 * 添加一个条件语句,判断2个字段的值是否相等 
	 * @param string $sClmName 其中一个需检验的字段名
	 * @param string $sOtherClmName 另外一个需检验的字段名
	 * @return self 
	 */
	public function eqColumn($sClmName, $sOtherClmName) {
		$this->arrExpressions [] = $this->transColumn ( $sClmName ) . ' = ' . $this->transColumn ( $sOtherClmName );
		return $this;
	}
	
	/**
	 * 添加一个条件语句,判断字段的值是否和期望值不相等 
	 * @param string $sClmName 字段名
	 * @param mix $value 期望值
	 * @return self
	 */
	public function ne($sClmName, $value) {
		$this->arrExpressions [] = $this->transColumn ( $sClmName ) . ' <> ' . $this->tranValue ( $value );
		return $this;
	}
	
	/**
	 * 添加一个条件语句,判断2个字段的值是否不相等 
	 * @param string $sClmName 其中一个需检验的字段名
	 * @param string $sOtherClmName 另外一个需检验的字段名
	 * @return self 
	 */
	public function neColumn($sClmName, $sOtherClmName) {
		$this->arrExpressions [] = $this->transColumn ( $sClmName ) . ' <> ' . $this->transColumn ( $sOtherClmName );
		return $this;
	}
	
	/**
	 * 添加一个条件语句,判断指定字段的值是否大于期望值
	 * @param string $sClmName 需检验的字段名
	 * @param mix $value 期望值
	 * @return self 
	 */
	public function gt($sClmName, $value) {
		$this->arrExpressions [] = $this->transColumn ( $sClmName ) . ' > ' . $this->tranValue ( $value );
		return $this;
	}
	
	/**
	 * 添加一个条件语句,判断指定的2个字段的值是否前者大于后者
	 * @param string $sClmName 大于号左边的字段名
	 * @param string $sOtherClmName 大于号右边的字段名
	 * @return self 
	 */
	public function gtColumn($sClmName, $sOtherClmName) {
		$this->arrExpressions [] = $this->transColumn ( $sClmName ) . ' > ' . $this->transColumn ( $sOtherClmName );
		return $this;
	}
	
	/**
	 * 添加一个条件语句,判断指定字段的值是否大于等于期望值
	 * @param string $sClmName 需检验的字段名
	 * @param mix $value 期望值
	 * @return self 
	 */
	public function ge($sClmName, $value) {
		$this->arrExpressions [] = $this->transColumn ( $sClmName ) . ' >= ' . $this->tranValue ( $value );
		return $this;
	}
	
	/**
	 * 添加一个条件语句,判断指定的2个字段的值是否前者大于等于后者
	 * @param string $sClmName 大于等于号左边的字段名
	 * @param string $sOtherClmName 大于等于号右边的字段名
	 * @return self 
	 */
	public function geColumn($sClmName, $sOtherClmName) {
		$this->arrExpressions [] = $this->transColumn ( $sClmName ) . ' >= ' . $this->transColumn ( $sOtherClmName );
		return $this;
	}
	
	/**
	 * 添加一个条件语句,判断指定字段的值是否小于期望值
	 * @param string $sClmName 需检验的字段名
	 * @param mix $value 期望值
	 * @return self 
	 */
	public function lt($sClmName, $value) {
		$this->arrExpressions [] = $this->transColumn ( $sClmName ) . ' < ' . $this->tranValue ( $value );
		return $this;
	}
	
	/**
	 * 添加一个条件语句,判断指定的2个字段的值是否前者小于后者
	 * @param string $sClmName 小于号左边的字段名
	 * @param string $sOtherClmName 小于号右边的字段名
	 * @return self 
	 */
	public function ltColumn($sClmName, $sOtherClmName) {
		$this->arrExpressions [] = $this->transColumn ( $sClmName ) . ' < ' . $this->transColumn ( $sOtherClmName );
		return $this;
	}
	
	/**
	 * 添加一个条件语句,判断指定字段的值是否小于等于期望值
	 * @param string $sClmName 需检验的字段名
	 * @param mix $value 期望值
	 * @return self 
	 */
	public function le($sClmName, $value) {
		$this->arrExpressions [] = $this->transColumn ( $sClmName ) . ' <= ' . $this->tranValue ( $value );
		return $this;
	}
	
	/**
	 * 添加一个条件语句,判断指定的2个字段的值是否前者小于等于后者
	 * @param string $sClmName 小于等于号左边的字段名
	 * @param string $sOtherClmName 小于等于号右边的字段名
	 * @return self 
	 */
	public function leColumn($sClmName, $sOtherClmName) {
		$this->arrExpressions [] = $this->transColumn ( $sClmName ) . ' <= ' . $this->transColumn ( $sOtherClmName );
		return $this;
	}
	
	/**
	 * 添加一个条件语句,判断指定字段的值是否和期望值相似
	 * @param string $sClmName 需检验的字段名
	 * @param mix $value 期望值
	 * @return self 
	 */
	public function like($sClmName, $value) {
		$this->arrExpressions [] = $this->transColumn ( $sClmName ) . ' LIKE ' . $this->tranValue ( $value );
		return $this;
	}
	
	/**
	 * 添加一个条件语句,判断指定字段的值是否和指定数组中的某个元素的值相等 
	 * @param string $sClmName 需要检验的字段名
	 * @param array $arrValues 比照数组
	 * @return self 
	 */
	public function in($sClmName, array $arrValues ) {
		foreach($arrValues as $v){
			$v = $this->tranValue($v);
		}
		$sValue ="('" . implode("','" , $arrValues) . "')";
		$this->arrExpressions [] = $this->transColumn ( $sClmName ) . ' IN ' . $sValue ;
		return $this;
	}
	
	/**
	 * 添加一个条件语句,判断指定字段的值是否在指定的2个值区间内
	 * @param string $sClmName
	 * @param mix $value
	 * @param mix $otherValue
	 * @return self
	 */
	public function between($sClmName, $value, $otherValue) {
		$this->arrExpressions [] = $this->transColumn ( $sClmName ) . ' BETWEEN ' 
									. $this->tranValue ( $value ) 
									. ' AND '
									. $this->tranValue ( $otherValue );
		return $this;
	}
	
	/**
	 * 添加一个条件语句,判断指定字段的值是否是null
	 * @param string $sClmName
	 * @return self
	 */
	public function isNull($sClmName) {
		$this->arrExpressions [] = $this->transColumn ( $sClmName ) . ' IS NULL';
		return $this;
	}
	
	/**
	 * 添加一个条件语句,判断指定字段的值是否不是null
	 * @param string $sClmName
	 * @return self
	 */
	public function isNotNull($sClmName) {
		$this->arrExpressions [] = $this->transColumn ( $sClmName ) . ' IS NOT NULL';
		return $this;
	}
	
	/**
	 * 直接把字符串作为条件语句的一部分使用.
	 * @param string sql条件语句
	 * @return self 调用方法的实例自身
	 */
	public function expression($sExpression) {
		$this->arrExpressions [] = $sExpression;
		return $this;
	}
	
	/**
	 * 把一个 Restriction 实例添加到条件集合中去.
	 * @param self 被添加的Restriction对象
	 * @return self 
	 */
	public function add(self $aOtherRestriction) {
		$this->arrExpressions [] = $aOtherRestriction;
		return $this;
	}
	
	/**
	 * 
	 * 创建一个新的Restriction对象并把它作为条件语句的一部分添加到方法调用者中
	 * @return self 被创建的Restriction对象
	 */
	public function createRestriction() {
		$aRestriction = new self ();
		$this->add ( $aRestriction );
		return $aRestriction;
	}
	
	/**
	 * 
	 * 对字段名进行转化,使其在组合后的sql语句中合法.
	 * @param string $sColumn 字段名
	 * @return string 转化后的合法sql语句成分
	 */
	protected function transColumn($sColumn) {
		return $this->makeSureBackQuote ( strval($sColumn) );
	}
	
	/**
	 * 
	 * 对直接量进行转化,使其在组合后的sql语句中合法.
	 * @param mix $value 条件语句中的直接量
	 * @return string 
	 */
	protected function tranValue($value) {
		if (is_string ( $value )) {
			$sValue = "'" . addslashes ( $value ) . "'";
		}else if (is_numeric ( $value )) {
			$sValue = "'" . strval ( $value ) . "'";
		} else if (is_bool ( $value )) {
			$sValue = $value ? "'1'" : "'0'";
		} else if ($value === null) {
			$sValue = "null";
		} else {
			$sValue = "'" . strval ( $value ) . "'";
		}
		return $sValue;
	}
	
	/**
	 * 确保字符串被反引号包围 (如果字符串没有反引号包围 , 用反引号包围字符串)
	 * @param string 需要加上反引号的字符串
	 * @return string 加上反引号后的字符串
	 */
	protected function makeSureBackQuote($sStr) {
		if (substr ( $sStr, 0, 1 ) == "`" and substr ( $sStr, - 1, 1 ) == "`") {
			return $sStr;
		}else{
			return "`" . $sStr . "`";
		}
	}
	
	
//	/**
//	 * 确保字符串被单引号包围 (如果字符串没有单引号包围 , 用单引号包围字符串)
//	 * @param string 需要加上单引号的字符串
//	 * @return string 加上单引号后的字符串
//	 */
//	private function makeSQ($sStr) {
//		if (substr ( $sStr, 0, 1 ) == "'") {
//			$sStr = substr ( $sStr, 1 );
//		}
//		if (substr ( $sStr, - 1, 1 ) == "'") {
//			$sStr = substr ( $sStr, 0, strlen ( $sStr ) - 1 );
//		}
//		return "'" . $sStr . "'";
//	}

	private $sLogic = ' AND ';
	
	private $arrExpressions = array ();
	
}
?>