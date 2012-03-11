<?php
namespace org\jecat\framework\mvc\view\widget;

use org\jecat\framework\mvc\view\IView;
use org\jecat\framework\lang\Exception;

class Text extends FormWidget
{
	
	const single = 0;
	const password = 1;
	const multiple = 2;
	const hidden = 3;
	
	private static $nTypeMin = 0;
	private static $nTypeMax = 3;
	
	public function __construct($sId = null, $sTitle = null, $sValue = null, $nType = self::single, IView $aView = null)
	{
		$this->setType ( $nType );
		$this->setValue ( $sValue );
		parent::__construct ( $sId, 'org.jecat.framework:WidgetText.template.html', $sTitle, $aView );
	}
	
	static public function createBean(array & $arrConfig,$sNamespace='*',$bBuildAtOnce,\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
	{
		$sClass = get_called_class() ;
		$aBean = new $sClass() ;
		if($bBuildAtOnce)
		{
			$aBean->buildBean($arrConfig,$sNamespace,$aBeanFactory) ;
		}
		return $aBean ;
	}
	/**
	 * @wiki /MVC模式/视图窗体(控件)/表单控件
	 * ==Text==
	 * =Bean配置数组=
	 * {|
	 * !属性
	 * !类型
	 * !默认值
	 * !可选
	 * !说明
	 * |-- --
	 * |type
	 * |string
	 * |"single"
	 * |必须
	 * |此项须以下列字符串为值,"single" 将Text初始化为单行文字输入框,即html中设置input标签的type属性为"text","password" 将Text初始化为密码数据框,即html中设置input标签的type属性为"password","multiple" 将Text初始化为字符编辑区域,即html中的textarea标签,"hidden" 将Text初始化为单行文字输入框,即html中设置input标签的type属性为"hidden"
	 * |}
	 */
	public function buildBean(array & $arrConfig,$sNamespace='*',\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
	{
		parent::buildBean ( $arrConfig,$sNamespace);
		
		if (array_key_exists ( 'type', $arrConfig ))
		{
			switch ($arrConfig ['type'])
			{
				case "single" :
					$this->setType ( self::single );
					break;
				case "password" :
					$this->setType ( self::password );
					break;
				case "multiple" :
					$this->setType ( self::multiple );
					break;
				case "hidden" :
					$this->setType ( self::hidden );
					break;
			}
		}
	}
	
	public function type()
	{
		return $this->nType;
	}
	
	public function typeForHtml()
	{
		switch ($this->nType)
		{
			case self::single :
				return "text";
				break;
			case self::password :
				return "password";
				break;
			case self::hidden :
				return "hidden";
				break;
		}
		return $this->nType;
	}
	
	public function setType($nType)
	{
		if (! is_int ( $nType ) || $nType < self::$nTypeMin || $nType > self::$nTypeMax)
		{
			throw new Exception ( "调用" . __CLASS__ . "对象的" . __METHOD__ . "方法时使用了非法的nType参数(得到的nType是:%s)", array ($nType ) );
		}
		$this->nType = $nType;
	}
	
	public function setSingle($bSingle = true)
	{
		$this->nType = $bSingle ? self::single : self::multiple;
	}
	
	public function isSingle()
	{
		return $this->nType == self::single;
	}
	
	public function isMultiple()
	{
		return $this->nType == self::multiple;
	}
	
	public function setMultiple($bMul = true)
	{
		$this->nType = $bMul ? self::multiple : self::single;
	}
	
	public function isPassword()
	{
		return $this->nType == self::password;
	}
	
	public function setPassword()
	{
		$this->nType = self::password;
	}
	
	public function isHidden()
	{
		return $this->nType == self::hidden;
	}
	
	public function setHidden()
	{
		$this->nType = self::hidden;
	}
	
	private $nType;
}

?>