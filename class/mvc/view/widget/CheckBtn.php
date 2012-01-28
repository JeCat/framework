<?php
namespace org\jecat\framework\mvc\view\widget;

use org\jecat\framework\mvc\view\IView;
use org\jecat\framework\lang\Exception;

class CheckBtn extends FormWidget
{
	const radio = 0;
	const checkbox = 1;
	
	private static $nTypeMin = 0;
	private static $nTypeMax = 1;
	
	public function __construct($sId = null, $sTitle = null, $checkedValue = '1', $nType = self::checkbox, $bChecked = false, IView $aView = null)
	{
		if (! is_int ( $nType ) || $nType < self::$nTypeMin || $nType > self::$nTypeMax)
		{
			throw new Exception ( "构建" . __CLASS__ . "对象时使用了非法的type参数(得到的type是:%s)", array ($nType ) );
		}
		
		$this->checkedValue = $checkedValue;
		
		if ($bChecked)
		{
			$this->setChecked ( true );
		}
		
		$this->nType = $nType;
		parent::__construct ( $sId, 'org.jecat.framework:WidgetCheckBtn.template.html', $sTitle, $aView );
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
	 * @wiki /mvc/视图/表单控件/选项/Bean配置数组
	 *
	 * type string 此项须以下列字符串为值,用来指定选项是checkbox还是radio
	 * "checkbox" 控件以checkbox形式体现
	 * "radio" 控件以radio形式体现
	 */
	public function buildBean(array & $arrConfig,$sNamespace='*',\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
	{
		parent::buildBean ( $arrConfig );
		
		if (array_key_exists ( 'type', $arrConfig ))
		{
			switch ($arrConfig ['type'])
			{
				case "checkbox" :
					$this->setType ( self::checkbox );
					break;
				case "radio" :
					$this->setType ( self::radio );
					break;
			}
		}
		
		$this->setChecked( !empty($arrConfig['checked']) ) ;
	}
	
	public function setType($nType)
	{
		if (! is_int ( $nType ) || $nType < self::$nTypeMin || $nType > self::$nTypeMax)
		{
			throw new Exception ( "调用" . __CLASS__ . "的" . __METHOD__ . "方法时使用了非法的type参数(得到的type是:%s)", array ($nType ) );
		}
		$this->nType = $nType;
	}
	
	public function type()
	{
		return $this->nType;
	}
	
	public function setChecked($bChecked = true)
	{
		if ($bChecked)
		{
			$this->setValue ( $this->checkedValue );
		}
		else
		{
			$this->setValue ( null );
		}
	}
	
	public function checkedValue()
	{
		return $this->checkedValue;
	}
	
	public function setCheckedValue($value)
	{
		$this->checkedValue = $value;
	}
	
	public function isChecked()
	{
		return $this->value () !== null and $this->value () == $this->checkedValue;
	}
	
	public function isRadio()
	{
		return $this->nType == self::radio;
	}
	
	public function isCheckBox()
	{
		return $this->nType == self::checkbox;
	}
	
	private $nType;
	private $checkedValue;
}

?>