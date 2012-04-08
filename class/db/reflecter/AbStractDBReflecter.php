<?php
namespace org\jecat\framework\db\reflecter;


abstract class AbStractDBReflecter extends DBStructReflecter
{
	abstract function __construct($aDBReflecterFactory , $sDBName);
	
	/**
	 * 
	 * Enter description here ...
	 * @return \iterator
	 * 为何这个函数迭代出来一种很奇怪的格式？ -- elephant_liu
	 */
	abstract public function tableNameIterator();
	
	/**
	 * 库是否存在(有效)
	 * @return boolen 如果存在返回true 如果不存在返回false 
	 */
	abstract public function isExist();
}
