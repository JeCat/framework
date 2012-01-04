<?php
namespace org\jecat\framework\mvc\view\layout ;

use org\jecat\framework\lang\Exception;
use org\jecat\framework\io\IBuffRemovable;
use org\jecat\framework\mvc\view\IView;
use org\jecat\framework\mvc\view\View;
use org\jecat\framework\bean\BeanConfException;
use org\jecat\framework\pattern\composite\Container;

class ViewLayoutFrame extends View
{
	const type_vertical = 'v' ;
	const type_horizontal = 'h' ;
	const type_tab = 'tab' ;
	
	static public $arrFrameCssClass = array(
				self::type_horizontal => 'jc-view-layout-frame-horizontal' ,
				self::type_vertical => 'jc-view-layout-frame-vertical' ,
				self::type_tab => 'jc-view-layout-frame-tab' ,
	) ;
	
	public function __construct($sType=self::type_vertical,$sName=null,UI $aUI=null)
	{
		parent::__construct($sName,null,$aUI) ;
		if(!$sName)
		{
			$sName = $this->id() ;
		}
		
		$this->setType($sType) ;
	}
	
	public function buildBean(array & $arrConfig,$sNamespace='*',\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
    {
    	if( empty($arrConfig['type']) )
    	{
    		$arrConfig['type'] = 'v' ;
    	}
    	$aTypes = array(
    		'v'=>self::type_vertical ,
    		'h'=>self::type_horizontal ,
    		'tab'=>self::type_tab ,
    	) ;
    	if( !isset($aTypes[$arrConfig['type']]) )
    	{
    		throw new BeanConfException("ViewLayoutFrame bean 配置的type属性无效:%s",$arrConfig['type']) ;
    	}
    	
		array(
			'class' => 'layout' ,
			'type' => $aTypes[$arrConfig['type']] ,
			'views' => array(
				'view path' ,
				array(
				
				) ,
			)
		) ;
	}

	public function setType($sType)
	{
		$this->sType = $sType ;
		
		$arrClasses =& $this->variables()->getRef('wrapper.classes') ;
		if($arrClasses===null)
		{
			$arrClasses = array() ;
		}
		
		foreach(self::$arrFrameCssClass as $type=>$sCss)
		{
			if( $type == $sType )
			{
				if( !in_array($sCss,$arrClasses) )
				{
					$arrClasses[] = $sCss ;
				}
			}
			else
			{
				$key = array_search($sCss, $arrClasses) ;
				if($key!==false)
				{
					unset($arrClasses[$key]) ;
				}
			}
		}
		
	}
	
	public function render($bRerender=true)
	{
		if(!$this->isEnable())
		{
			return ;
		}
		
		if( !$this->parent() or !($this->parent() instanceof self) )
		{
			$this->outputStream()->write(self::renderWrapperHeader($this, 'jc-view-layout-frame xx')) ;
			$bInOtherFrame = false ;
		}
		else
		{
			$bInOtherFrame = true ;
		}

		// render myself
		if( $sTemplate=$this->template() )
		{
			$this->renderTemplate($sTemplate) ;
		}
		
		// render child view
		if($this->arrChildDevices)
		{
			foreach ($this->arrChildDevices as $aChildViewDev)
			{
				$this->outputStream()->write($aChildViewDev) ;
			}
		}
		
		$this->outputStream()->write("<div class='jc-view-layout-end-item'></div>") ;
		
		if(!$bInOtherFrame)
		{
			$this->outputStream()->write("</div>") ;
		}
	}
	
	public function add($aView,$sName=null,$bTakeover=false)
	{
		// 跳过父类 View::add() 对同名视图的检查
		Container::add($aView,$sName,$bTakeover) ;
		
		// 通过 ViewLayoutItemDevice 包装 $aView 的输出设备
		$this->arrChildDevices[] = new ViewLayoutItemDevice($this,$aView) ;
	}
	
	public function clear()
	{
		parent::clear() ;
		$this->arrChildDevices = null ;
	}
	
	public function type()
	{
		return $this->sType ;
	}


	static public function addWrapperCssClass(IView $aView,$sCssClass)
	{
		$arrClasses =& $aView->variables()->getRef('wrapper.classes') ;
		if($arrClasses===null)
		{
			$arrClasses = array() ;
		}
		
		if(!in_array($sCssClass,$arrClasses))
		{
			$arrClasses[] = $sCssClass ;
		}
	}
	
	static public function removeWrapperCssClass(IView $aView,$sCssClass)
	{
		$arrClasses =& $aView->variables()->getRef('wrapper.classes') ;
		if($arrClasses===null)
		{
			$arrClasses = array() ;
		}
		$pos = array_search($sCssClass,$arrClasses) ;
		if($pos!==false)
		{
			unset($arrClasses[$pos]) ;
		}
	}
	
	static public function setWrapperStyle(IView $aView,$sStyle)
	{
		$aView->variables()->set('wrapper.style',$sStyle) ;
	}
	static public function wrapperStyle(IView $aView)
	{
		return $aView->variables()->getRef('wrapper.style') ;
	}
	
	static public function addWrapperAttr(IView $aView,$sName,$sValue)
	{
		$arrAttrs =& $aView->variables()->getRef('wrapper.attrs') ;
		if($arrAttrs===null)
		{
			$arrAttrs = array() ;
		}
		
		$sName = strtolower($sName) ;
		$arrAttrs[$sName] = $sValue ;
	}
	
	static public function removeWrapperAttr(IView $aView,$sName)
	{
		$arrAttrs =& $aView->variables()->getRef('wrapper.attrs') ;
		if($arrAttrs===null)
		{
			$arrAttrs = array() ;
		}
		
		$sName = strtolower($sName) ;
		unset($arrAttrs[$sName]) ;
	}
	
	static public function renderWrapperHeader(IView $aView,$sClass=null,$sStyle=null)
	{
		// id
		$sId = self::htmlWrapperId($aView) ;
	
		// name
		$sViewNameEsc = addslashes($aView->name()) ;
	
		// class
		$arrClasses = $aView->variables()->get('wrapper.classes')?: array() ;
		if($sClass)
		{
			$arrClasses[] = $sClass ;
		}
		$sClasses = implode(' ',$arrClasses) ;
	
		// style
		if( $sStyle = self::wrapperStyle($aView).$sStyle )
		{
			$sStyle = ' style="' . $sStyle . '"' ;
		}
	
		// attrs
		$sAttrs = '' ;
		foreach($aView->variables()->get('wrapper.attrs')?: array() as $sName=>$value)
		{
			$sAttrs.= " {$sName}=\"".addslashes($value).'"' ;
		}
	
		return "<div{$sAttrs} id='{$sId}' class='{$sClasses}' name='{$sViewNameEsc}'{$sStyle}>" ;
	}
	
	static public function htmlWrapperId(IView $aView)
	{
		return 'layout-item-'.$aView->id() ;
	}
	
	private $sType = self::type_vertical ;
	
	private $arrChildDevices ;
}

?>