<?php
namespace jc\db\reflecter;



class DBStructReflecter 
{
<<<<<<< HEAD
	/**
	 * 返回对象名称
	 * @return string
	 */
	abstract public function name();
=======
	public function name() {
		;
	}
>>>>>>> parent of 6da5192... 数据库反射抽象类完成
	
	/**
	 * @return AbstractReflecterFactory
	 */
	public function factory() {
		;
	}
	
	/**
	 * @param AbstractReflecterFactory
	 */
	public function setFactory(AbstractReflecterFactory $aFacotry) {
		
	}
}

?>