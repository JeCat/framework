<?php
namespace org\jecat\framework\setting ;

interface IKey extends \ArrayAccess
{
	/**
	 * 返回键的名称
	 * @return string
	 */
	public function name() ;
	
	/**
	 * 读取特定项的值
	 * @param string $sName 项的名字
	 * @param string $sDefault 项的默认值,如果项不存在，使用这个值
	 * @return mixed 项值
	 */
	public function item($sName='*',$sDefault=null) ;
	
	/**
	 * 设置一个项
	 * @param string $sName 项的名字
	 * @param mixed $value 项的值
	 */
	public function setItem($sName,$value) ;
	
	/**
	 * 是否存在项 
	 * @param string $sName 项的名字
	 * @return boolen 存在就返回true，不存在返回false
	 */
	public function hasItem($sName) ;
	
	/**
	 * 删除项 
	 * @param string $sName 项的名字
	 */
	public function deleteItem($sName) ;
	
	/**
	 * 获得所有项的名字的迭代器
	 * @return \Iterator 
	 */
	public function itemIterator() ;
	
	/**
	 * 获得所有键的名字的迭代器
	 * @return \Iterator 
	 */
	public function keyIterator() ;
	
	/**
	 * 保存所有键 
	 */
	public function save() ;
}

?>