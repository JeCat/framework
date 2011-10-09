<?php
namespace jc\db\reflecter;

abstract class DBStructReflecter
{
	/**
	 * 返回对象名称
	 * @return string
	 */
	abstract public function name();
	
	/**
	 * 获取数据库反射工厂对象
	 * @return AbstractReflecterFactory
	 */
	public function factory()
	{
		return $this->aFactory;
	}
	
	/**
	 * 设置数据库反射工厂
	 * @param AbstractReflecterFactory
	 */
	public function setFactory(AbstractReflecterFactory $aFacotry)
	{
		$this->aFactory = $aFacotry;
	}
	
	private $aFactory;
}
?>