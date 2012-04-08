<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  这个文件是 JeCat PHP框架的一部分，该项目和此文件 均遵循 GNU 自由软件协议
// 
//  Copyleft 2008-2012 JeCat.cn(http://team.JeCat.cn)
//
//
//  JeCat PHP框架 的正式全名是：Jellicle Cat PHP Framework。
//  “Jellicle Cat”出自 Andrew Lloyd Webber的音乐剧《猫》（《Prologue:Jellicle Songs for Jellicle Cats》）。
//  JeCat 是一个开源项目，它像音乐剧中的猫一样自由，你可以毫无顾忌地使用JCAT PHP框架。JCAT 由中国团队开发维护。
//  正在使用的这个版本是：0.7.1
//
//
//
//  相关的链接：
//    [主页]			http://www.JeCat.cn
//    [源代码]		https://github.com/JeCat/framework
//    [下载(http)]	https://nodeload.github.com/JeCat/framework/zipball/master
//    [下载(git)]	git clone git://github.com/JeCat/framework.git jecat
//  不很相关：
//    [MP3]			http://www.google.com/search?q=jellicle+songs+for+jellicle+cats+Andrew+Lloyd+Webber
//    [VCD/DVD]		http://www.google.com/search?q=CAT+Andrew+Lloyd+Webber+video
//
////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*-- Project Introduce --*/
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
	 * @wiki /MVC模式/视图窗体(控件)/表单控件
	 * ==复选框==
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
	 * |无
	 * |必须
	 * |此项须以下列字符串为值,用来指定选项是checkbox还是radio,"checkbox" 控件以checkbox形式体现,"radio" 控件以radio形式体现
	 * |}
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
