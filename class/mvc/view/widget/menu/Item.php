<?php
namespace org\jecat\framework\mvc\view\widget\menu;

use org\jecat\framework\util\DataSrc;
use org\jecat\framework\resrc\HtmlResourcePool;
use org\jecat\framework\bean\BeanFactory ;

class Item extends AbstractBase
{
	public function __construct($sTitle='',$sId =null , IView $aView = null)
	{
        parent::__construct ( $sId , 'org.jecat.framework:WidgetItem.template.html', null , $aView ) ;
        $this->setTitle($sTitle);
    }
    
    // from Bean
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
     * @wiki /MVC模式/视图窗体(控件)/菜单控件
     * ==Item==
     * =Bean配置数组=
     * {|
	 * !属性
	 * !类型
	 * !默认值
	 * !可选
	 * !说明
	 * |-- --
     * |menu
     * |array
     * |无
     * |可选
     * |子菜单项目列表,每个元素都是一个菜单项的配置
     * |-- --
     * |link
     * |string
     * |无
     * |可选
     * |url地址
     * |-- --
     * |onclick
     * |string
     * |无
     * |可选
     * |点击后触发的javascript的代码
     * |-- --
     * |html
     * |string
     * |无
     * |可选
     * |用来代替菜单项内容的html代码
     * |-- --
     * |query
     * |-- -- 
     * |active
     * |boolean
     * |true
     * |可选
     * |设置菜单是否可用
     * |}
     */
    public function buildBean(array & $arrConfig,$sNamespace='*',\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
    {
		parent::buildBean($arrConfig,$sNamespace);
		
		if( !empty($arrConfig['menu'])){
			$this->buildSubMenu($arrConfig);
		}
		
		if( !empty( $arrConfig['link'])){
			$this->setLink($arrConfig['link']);
		}
		if( !empty( $arrConfig['onclick'])){
			$this->setEventOnClick($arrConfig['onclick']);
		}
		if( !empty( $arrConfig['html'])){
			$this->setHtml($arrConfig['html']);
		}
		
		if(!empty($arrConfig['query']))
		{
			if($aView=$this->view())
			{
				if( $aController = $aView->controller() )
				{
					$aParams = $aController->params() ;
					
					foreach((array)$arrConfig['query'] as $sQuote)
					{
						if( DataSrc::compare($aParams,$sQuote) )
						{
							$this->setActive(true);
							break ;
						}
					}
				}
			}
		}
		
		if( array_key_exists('active',$arrConfig) )
		{
			$this->setActive($arrConfig['active']);
		}
		
		if( $aSubMenu=$this->subMenu() and $aSubMenu->isActive() )
		{
			$this->setActive(true);
		}
	}
	
	public function view()
	{
		if( $aView = parent::view() )
		{
			return $aView ;
		}
		
		if( $aMenu = $this->parentMenu() )
		{
			return $aMenu->view() ;
		}
		
		return null ;
	}
    
    public function createSubMenu(){
    	$aMenu = new Menu;
    	$this->setSubMenu($aMenu);
    	return $this->subMenu();
    }
    
    public function setSubMenu(Menu $aMenu){
    	if($this->subMenu !== $aMenu){
	    	$this->subMenu = $aMenu;
	    	$aMenu->setParentItem($this);
	    	
	    	if(!$aMenu->view())
	    	{
	    		$aMenu->setView($this->view()) ;
	    	}
	    	
    	}
    }
    
    /**
     * @return Menu
     */
    public function subMenu(){
    	return $this->subMenu;
    }
    
    public function setParentMenu(Menu $aMenu){
    	if($this->parentMenu !== $aMenu){
    		$this->parentMenu = $aMenu;
    	}
    }
    
    protected function parent(){
    	return $this->parentMenu();
    }
    
    public function parentMenu(){
    	return $this->parentMenu;
    }
    
    public function isDisplaySubMenu()
    {
    	if(!$this->subMenu())
    	{
    		return false;
    	}
    	if(!$aMenu=$this->parentMenu())
    	{
    		return true;
    	}
    	return $aMenu->showDepths()!=0 ;
    }
    
    public function depth(){
    	if($this->parent() === null){
			return 1;
		}else{
			return $this->parent()->depth();
		}
    }
    
    public function setActive($bActive){
    	$this->bActive = $bActive?true:false ;
    }
    public function isActive(){
    	return $this->bActive ;
    }
	
	private function buildSubMenu($subMenu){
		if($subMenu instanceof Menu){
			$this->setSubMenu($subMenu);
		}else if(is_string($subMenu)){
			$this->setSubMenu( new Menu($subMenu) );
		}else if(is_array($subMenu)){
			$subMenu['class'] = __NAMESPACE__.'\Menu';
			$aMenu = BeanFactory::singleton()->createBean($subMenu,'*',false) ;
			$this->setSubMenu($aMenu);
			$aMenu->buildBean($subMenu) ;
		}
	}
	
	public function getCssClassString(){
		$arrClass=array(
			parent::CSS_CLASS_BASE.'-item',
			parent::CSS_CLASS_BASE.'-item-depth-'.$this->depth(),
		);
		if($this->parent() !== null){
			$arrClass[] = parent::CSS_CLASS_BASE.'-item-direction-'.$this->parent()->getDirection();
		}
		if($this->isActive())
		{
			$arrClass[] = parent::CSS_CLASS_BASE.'-item-active' ;
		}
		return 'class ="'.implode(' ',$arrClass).'"';
	}
	
	public function link()
	{
		return $this->sLink ;
	}
	public function setLink($sLink)
	{
		$this->sLink = $sLink ;
	}
	public function eventOnClick()
	{
		return $this->sOnClick ;
	}
	public function setEventOnClick($sOnClick)
	{
		$this->sOnClick = $sOnClick ;
	}
	public function html()
	{
		if(!$this->sHtml)
		{
			$sLink = $this->link()?: 'javascript:void(0)' ;
			$sOnClick = $this->eventOnClick() ;
			$this->sHtml = "<a href=\"{$sLink}\" onclick=\"{$sOnClick}\">".$this->title()."</a>" ;
		}
		
		return $this->sHtml ;
	}
	public function setHtml($sHtml)
	{
		$this->sHtml = $sHtml ;
	}
	
    private $parentMenu = null;
    private $subMenu = null;
    
    private $bActive = false ;
    private $sLink ;
    private $sOnClick ;
    private $sHtml ;
}
