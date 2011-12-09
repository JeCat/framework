<?php
namespace org\jecat\framework\mvc\view\widget\menu;

use org\jecat\framework\util\DataSrc;
use org\jecat\framework\resrc\HtmlResourcePool;
use org\jecat\framework\bean\BeanFactory ;

HtmlResourcePool::singleton()->addRequire('org.jecat.framework:style/widget/menu.css',HtmlResourcePool::RESRC_CSS) ;
HtmlResourcePool::singleton()->addRequire('org.jecat.framework:js/mvc/view/widget/menu.js',HtmlResourcePool::RESRC_JS) ;

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
    
    public function buildBean(array & $arrConfig,$sNamespace='*',\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
    {
		parent::buildBean($arrConfig,$sNamespace);
		
		if( !empty($arrConfig['menu'])){
			$this->buildSubMenu($arrConfig['menu']);
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
		
		if(!empty($arrConfig['quote']))
		{
			if($aView=$this->view())
			{
				if( $aController = $aView->controller() )
				{
					$aParams = $aController->params() ;
					
					foreach((array)$arrConfig['quote'] as $sQuote)
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
	public function eventOnClick($sOnClick)
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
			$sTitle = $this->title() ;
			
			if( $this->sLink or $this->sOnClick )
			{
				$this->sHtml = "<a" ;
				
				if($this->sLink)
				{
					$this->sHtml.= " href='{$this->sLink}'" ;
				}
				if($this->sOnClick)
				{
					$sOnClick = addslashes($this->sOnClick) ;
					$this->sHtml.= " onclick=\"{$sOnClick}\"" ;
				}
				$this->sHtml.= ">{$sTitle}</a>" ;
			}
			else
			{
				$this->sHtml = $sTitle ;
			}
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
