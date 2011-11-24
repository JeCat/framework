<?php

namespace org\jecat\framework\locale ;

interface ILocale extends ITranslator
{	
	/**
	 * 以本地风格格式化数字
	 */
	public function number($Number) ;
	
	/**
	 * 以本地风格格式化电话号码
	 */
	public function telNumber($Number) ;
	
}

?>