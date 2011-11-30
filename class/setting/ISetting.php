<?php
namespace org\jecat\framework\setting ;

interface ISetting
{
	/**
	 * 获得一个键对象
	 * @param string $sPath 键路径
	 * @return IKey 
	 */
	public function key($sPath) ;
	
	/**
	 * 新建一个键
	 * @param string $sPath 键路径
	 * @return IKey 
	 */
	public function createKey($sPath) ;
	
	/**
	 * 检查是否存在键 
	 * @param string $sPath 键路径
	 * @return boolen 如果存在返回true ,不存在返回false
	 */
	public function hasKey($sPath) ;
	
	/**
	 * 删除一个键
	 * @param string $sPath 键路径
	 * @return boolen 删除成功返回true，失败返回false
	 */
	public function deleteKey($sPath) ;
	
	/**
	 * 保存键
	 * @param string $sPath 键路径
	 */
	public function saveKey($sPath) ;
	
	/**
	 * 获得子键的键名迭代器
	 * @param string $sPath 键路径
	 * @return \Iterator 
	 */
	public function keyIterator($sPath) ;
	
	/**
	 * 获得项的值
	 * @param string $sPath 键路径
	 * @param string $sName 项名
	 * @param mixed $defaultValue 默认值 ,如果项不存在就取默认值,并且以默认值新建项
	 */
	public function item($sPath,$sName='*',$defaultValue=null) ;
	
	/**
	 * 设置项的值
	 * @param string $sPath 键路径
	 * @param string $sName 项名
	 * @param mixed $value
	 */
	public function setItem($sPath,$sName,$value) ;
	
	/**
	 * 检查项是否存在
	 * @param string $sPath 键路径
	 * @param string $sName 项名
	 * @return boolen 如果项存在就返回true,如果不存在返回false
	 */
	public function hasItem($sPath,$sName) ;
	
	/**
	 * 删除项 
	 * @param string $sPath 键路径
	 * @param string $sName 项名
	 */
	public function deleteItem($sPath,$sName) ;
	
	/**
	 * 获得项的名字迭代器
	 * @param string $sPath 键路径
	 * @return \Iterator 
	 */
	public function itemIterator($sPath) ;
	
	/**
	 * 在指定的路径上，分离出一个setting
	 * @param string $sPath 键路径
	 * @return ISetting
	 */
	public function separate($sPath) ;
}

?>