<?php
namespace org\jecat\framework\mvc\model2 ;

use org\jecat\framework\lang\Object;

class Prototype extends Object
{
	
	public function __construct($table,$primaryKeys=null,$columns=null)
	{
		$this->arrPrototype['table'] = $table ;
		$this->arrPrototype['columns'] = $columns ;
		$this->arrPrototype['keys'] = $primaryKeys ;
	}

	/**
	 * 设置一个或多个 order by 字段
	 *
	 * $columns 可以用以下类型：
	 * $columns = 'column' ;
	 * $columns = array(
	 * 		'column_a' ,
	 * 		'column_b' => false , // asc
	 * 		'column_c' => true ,  // desc
	 * ) ;
	 * @param $columns	string,array	可以字符串类型表示一个字段，或数组类型表示多个字段；当传入数组类型时，可以用数组的键名表示字段名，值以bool类型表示是否为 desc 顺序，也可以只提供字符串类型元素值表示字段名
	 * @return Model
	 */
	public function addOrder($columns,$bDesc=true)
	{
		if( is_string($columns) )
		{
			unset($this->arrPrototype['order'][$columns]) ;
			$this->arrPrototype['order'][$columns] = $bDesc ;
		}
		else if( is_array($columns) )
		{
			foreach($columns as $key=>&$item)
			{
				if( is_int($key) )
				{
					unset($this->arrPrototype['order'][$item]) ;
					$this->arrPrototype['order'][$item] = $bDesc ;
				}
				else
				{
					unset($this->arrPrototype['order'][$key]) ;
					$this->arrPrototype['order'][$key] = $item? true: false ;
				}
			}
		}
		else
		{
			throw new Exception("Prototype::addOrder() 传入的参数 \$columns 类型错误，必须为 string 或 array 类型。") ;
		}
		return $this ;
	}
	/**
	 * 设置 limit
	 */
	public function setLimit($nLen,$pos=null)
	{
		$this->arrPrototype['limit-pos'] = $pos ;
		$this->arrPrototype['limit-length'] = $nLen ;
	}
	/**
	 * 设置一个或多个 group by 字段
	 */
	public function addGroup($columns)
	{
		if( is_string($columns) )
		{
			$this->arrPrototype['group'][] = $columns ;
		}
		else if( is_array($columns) )
		{
			foreach($columns as &$sColumn)
			{
				$this->arrPrototype['group'][] = $sColumn ;
			}
		}
		else
		{
			throw new Exception("Prototype::addGroup() 传入的参数 \$columns 类型错误，必须为 string 或 array 类型。") ;
		}
		return $this ;
	}
	/**
	 * 设置一组 where 条件
	 */
	public function where($where)
	{
		$this->arrPrototype['where'][] = $where ;
		return $this ;
	}
	
	private $arrPrototype ;

}

