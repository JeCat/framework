<?php
namespace org\jecat\framework\db\sql;


class Order extends SubStatement
{
	const asc = 'ASC' ;
	const desc = 'DESC' ;
	const rand = 'RAND' ; //'随机抽取'函数
	
	/**
	 * 生成OrderBy的类
	 * @param string $sColumn 列名
	 * @param boolean $bOrderType true代表DESC , false代表ASC ，默认为true , 也可填写字符串 ,可识别"ASC","DESC","RAND"三种
	 */
	public function __construct($sColumn=null , $desc=true){
		if($sColumn)
		{
			$this->add($sColumn,$desc) ;
		}
	}
	
	/**
	 * 获得一个order实例,按照所给参数生成OrderBy语句,升序
	 * @param string $sColumn 列名
	 * @return self 
	 */
	static public function asc($sColumn){
		return new self($sColumn,false);
	}
	/**
	 * 获得一个order实例,按照所给参数生成OrderBy语句,降序
	 * @param string $sColumn 列名
	 * @return self 
	 */
	static public function decs($sColumn){
		return new self($sColumn,true);
	}
	/**
	 * 获得一个order实例,按照所给参数生成OrderBy语句,随机
	 * @return self 
	 */
	static public function rand(){
		return new self(null,true);
	}
	
	/**
	 * 增加一个需要排序的列
	 * @param string $sColumn 列名
	 * @param boolen $bOrderType 排序方式,true代表DESC , false代表ASC ，默认为true , 也可填写字符串 ,可识别"ASC","DESC","RAND"三种
	 */
	public function add($sColumn , $desc=true) {
		if($desc === true){
			$desc = 'DESC';
		}else if($desc === false){
			$desc = 'ASC';
		}else if($desc === self::rand){
			$sColumn = 0;
		}
		if($sColumn === null){
			return;
		}
		$this->arrOrderBys[$sColumn] = $desc;
		return $this ;
	}
	
	/**
	 * 根据提供的列名删除相应的OrderBy语句
	 * @param unknown_type $sColumn
	 */	
	public function removeColumn($sColumn) {
		reset($this->arrOrderBys[$sColumn]) ;
		return $this ;
	}
	
	public function clearColumns() {
		$this->arrOrderBys = null ;
		return $this ;
	}
	
	public function iterator() {
		return new \ArrayIterator($this->arrOrderBys);
	}
	
	/**
	 * @see org\jecat\framework\db\sql\Statement::makeStatement()
	 */
	public function makeStatement(StatementState $aState)
	{
		//如果arrOrderBys中什么也没有,就返回空字符串 , 以此满足空Order对象的稳定(即什么也不做的Order,sql语句没有Order部分的情况)
		if( empty($this->arrOrderBys) )
		{
			return '';
		}
		//sql中有Order部分的情况
		foreach ($this->arrOrderBys as $sColumn=>&$sOrder){
			if($sOrder== 'RAND'){
				$arrOrderBys[] = ' rand() ';
			}else{
				$arrOrderBys[] = $this->transColumn($sColumn,$aState) . ' ' . $sOrder ;
			}
		}
		return ' ORDER BY ' . implode(', ' , $arrOrderBys) ;
	}
	
	public function checkValid($bThrowException=true)
	{
		return true;
	}

	private $arrOrderBys ;
}