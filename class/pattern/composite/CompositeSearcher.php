<?php
namespace org\jecat\framework\pattern\composite ;

use org\jecat\framework\lang\Type;
use org\jecat\framework\lang\Exception;

class CompositeSearcher extends \ArrayIterator
{
	public function __construct(IContainer $aParent,$aCallback)
	{
		if( !is_callable($aCallback) )
		{
			throw new Exception(__CLASS__."() 的参数 \$aCallback 必须是一个回调函数，传入的参数类型为：%s",Type::reflectType($aCallback)) ;
		}
		
		$arrBingo = self::searching($aParent,$aCallback) ;
		
		parent::__construct($arrBingo) ;
	}
	
	static public function searching(IContainer $aParent,$aCallback)
	{
		$arrRes = array() ;
		foreach ($aParent->iterator() as $aChild)
		{
			if( call_user_func_array($aCallback, array($aChild)) )
			{
				$arrRes[] = $aChild ;
			}
			
			// 递归child
			$arrRes = array_merge($arrRes,self::searching($aChild,$aCallback)) ;
		}
		
		return $arrRes ;
	} 
}

?>