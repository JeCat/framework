<?php
namespace jc\mvc\view\widget;

use jc\mvc\view\IView;
use jc\lang\Exception;

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
		parent::__construct ( $sId, 'jc:WidgetCheckBtn.template.html', $sTitle, $aView );
	}
	
	public function build(array & $arrConfig,$sNamespace='*'){
		parent::build ( $arrConfig );
		
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