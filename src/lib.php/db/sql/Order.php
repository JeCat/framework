<?php
namespace jc\db\sql;


class Order extends SubStatement
{
	/**
	 * 生成OrderBy的类
	 * @param string $sColumn 列名
	 * @param boolean $bOrderType true代表ASC , false代表DESC ，默认为true
	 */
	public function __construct($sColumn=null , $bOrderType=true){
		if($sColumn)
		{
			$this->add($sColumn,$bOrderType) ;
		}
	}
	
	/**
	 * 获得一个order实例,按照所给参数生成OrderBy语句,升序
	 * @param string $sColumn 列名
	 * @return self 
	 */
	static public function asc($sColumn){
		return new self($sColumn);
	}
	/**
	 * 获得一个order实例,按照所给参数生成OrderBy语句,降序
	 * @param string $sColumn 列名
	 * @return self 
	 */
	static public function decs($sColumn){
		return new self($sColumn,false);
	}
	
	/**
	 * 增加一个需要排序的列
	 * @param string $sColumn 列名
	 * @param boolen $bOrderType 排序方式,true代表ASC , false代表DESC ，默认为true
	 */
	public function add($sColumn , $bAsc=true) {
		if($sColumn === null){
			return;
		}
		
		$sOrderType = 'ASC';
		if(!$bOrderType){
			$sOrderType = 'DESC';
		}
		$this->arrOrderBys[$sColumn] = array($this->transColumn($sColumn) , $sOrderType);
	}
	
	/**
	 * 根据提供的列名删除相应的OrderBy语句
	 * @param unknown_type $sColumn
	 */	
	public function removeColumn($sColumn) {
		reset($this->arrOrderBys[$sColumn]) ;
	}
	
	public function clearColumns() {
		$this->arrOrderBys = array();
	}
	
	public function iterator() {
		return new \ArrayIterator($this->arrOrderBys);
	}
	
	/**
	 * @see jc\db\sql\Statement::makeStatement()
	 */
	public function makeStatement($bFormat=false){
		$sOrderBy = ' ORDER BY ';
		$arrOrderBys = array();
		//如果arrOrderBys中什么也没有,就返回空字符串 , 以此满足空Order对象的稳定(即什么也不做的Order,sql语句没有Order部分的情况)
		if( count($this->arrOrderBys) <= 0 ){
			return '';
		}
		//sql中有Order部分的情况
		foreach ($this->arrOrderBys as $key=>$arrOrder){
			$arrOrderBys[] = implode(' ', $arrOrder);
		}
		$sOrderBy .= implode(',' , $arrOrderBys);
		return $sOrderBy;
	}
	
	public function checkValid($bThrowException=true){
		return true;
	}

	private $arrOrderBys = array();
}