<?php
namespace org\jecat\framework\mvc\view\layout ;

use org\jecat\framework\bean\BeanConfException;
use org\jecat\framework\pattern\composite\Container;

class ViewLayoutFrame extends LayoutableView
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
		
		$this->outputStream()->clear() ;
		
		// render wrapper header
		$sStyle = null ;
		if( $aParent=$this->parent() and ($aParent instanceof ViewLayoutFrame) and $aParent->type()==ViewLayoutFrame::type_horizontal )
		{
			$sStyle = 'float:left;' ;
		}
		$this->renderWrapperHeader($this,$this->outputStream(),'jc-view-layout-frame',$sStyle) ;
				
		// render myself
		if( $sTemplate=$this->template() )
		{
			$this->renderTemplate($sTemplate) ;
		}
		
		// render child view
		$this->renderChildren(true) ;
		
		$this->bRendered = true ;
		
		$this->outputStream()->write("<div class='jc-view-layout-end-item'></div></div>") ;
	}
	
	public function type()
	{
		return $this->sType ;
	}
	
	public function add($aView,$sName=null,$bTakeover=true)
	{
		if( !($aView instanceof ViewLayoutItem) and !($aView instanceof ViewLayoutFrame) )
		{
			$aView = new ViewLayoutItem($aView,$sName) ;
		}
		
		// 跳过 View 对同名视图的检查
		Container::add($aView,$sName,$bTakeover) ;
	}
	
	public function getByName($sName)
	{
		$aView = parent::getByName($sName) ;
		return ( $aView instanceof ViewLayoutItem )? $aView->view(): $aView ;
	}
	
	private $sType = self::type_vertical ;
}

?>