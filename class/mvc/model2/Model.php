<?php
namespace org\jecat\framework\mvc\model2 ;

use org\jecat\framework\db\DB;

use org\jecat\framework\lang\Exception;

class Model
{
	/**
	 * 
	 */
	public function __construct($table,$primaryKeys=null,$columns=null)
	{
		if( is_string($table) )
		{
			$this->aPrototype = new Prototype($table,$primaryKeys,$columns) ;
		}
		else if( $table instanceof Prototype )
		{
			$this->aPrototype = $table ;
		}
		else
		{
			throw new Exception("Model::__construct() 参数\$table类型错误") ;
		}
	}
	static public function create($sTable,$primaryKeys=null,$columns=null)
	{
		return new self($sTable,$primaryKeys,$column) ;
	}

	/**
	 * @alias org\jecat\framework\mvc\model\Prototype::addOrder
	 */
	public function order($columns,$bDesc=true)
	{
		$this->aPrototype->addOrder($columns,$bDesc) ;
		return $this ;
	}
	/**
	 * 设置 limit
	 */
	public function limit($nLen,$pos=null)
	{
		$this->aPrototype->setLimit($nLen,$pos) ;
		return $this ;
	}
	/**
	 * 设置一个或多个 group by 字段
	 */
	public function group($columns)
	{
		$this->aPrototype->addGroup($columns) ;
		return $this ;
	}
	/**
	 * 设置一组 where 条件
	 */
	public function where($sWhere)
	{
		$this->aPrototype->where($sWhere) ;
		return $this ;
	}
	/**
	 * 设置一组 where 条件
	 */
	public function find($values,$columns=null)
	{
		$this->aPrototype->where( $this->makeSqlFind($values,$columns) ) ;
		return $this ;
	}
	private function makeSqlFind($values,$columns=null)
	{
		if($columns===null)
		{
			$columns = $this->aPrototype->keys() ;
		}
		else
		{
			$columns = (array) $columns ;
		}
		$values = $values===null? array(null): (array)$values ;
		
		$arrWhere = '' ;
		foreach($columns as $nIdx=>&$sColumn)
		{
			$arrWhere[] = $values[$nIdx] . "='" . addslashes($sColumn) . "'" ;
		}
		
		return implode(' AND ',$arrWhere) ;
	}

	/**
	 * 执行 select 操作
	 */
	public function load($values=self::ignore,$columns=null)
	{
		Selecter::singleton()->execute(
			$this
			, $values===self::ignore? null: $this->makeSqlFind($values,$columns)
		) ;
		
		return $this ;
	}
	
	/**
	 * 执行 insert/update 操作
	 */
	public function save()
	{
		
	}
	
	/**
	 * 执行删除操作，
	 * 仅仅删除数据库中的记录，Model对像中的数据仍然保留，并且可以在 delete() 以后立即执行 save()
	 */
	public function delete()
	{
		
	}
	
	/**
	 * 返回一个“臭名昭著”的 prototype 对像
	 * 真正维护 prototype 的，应该是 Prototype 类，Model类的 order,limit 等方法仅仅提供了简单的
	 * @return Prototype
	 */
	public function prototype()
	{
		return $this->aPrototype ;
	}
	
	/**
	 * @return org\jecat\framework\db\DB
	 */
	public function db()
	{
		if(!$this->aDB)
		{
			$this->aDB = DB::singleton() ;
		}
		return $this->aDB ;
	}
	/**
	 * @return Model
	 */
	public function setDb(DB $aDB)
	{
		$this->aDB = $aDB ;
		return $this ;
	}
	
	const ignore = '~-+ignore this arg+-~' ;
		
	private $arrData ;
	
	private $aPrototype ;
	
	private $aDB ;
	
}