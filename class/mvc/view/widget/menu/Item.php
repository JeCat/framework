<?php
namespace org\jecat\framework\mvc\view\widget\menu;

use org\jecat\framework\bean\BeanFactory ;

class Item extends AbstractBase
{
	public function __construct($sTitle='',$sId =null , IView $aView = null) {
        parent::__construct ( $sId , 'org.jecat.framework:WidgetItem.template.html', null , $aView );
        $this->setTitle($sTitle);
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
    	}
    }
    
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
    
    public function isShowContinue(){
    	if($this->subMenu() === null) return false;
    	if($this->showdepth() === null) return true;
    	if($this->depth() >= $this->showdepth()) return false;
    	return true;
    }
    
    public function depth(){
    	if($this->parent() === null){
			return 1;
		}else{
			return $this->parent()->depth();
		}
    }
    
    public function isActive(){
    	return $this->attribute('active',false);
    }
    
    protected function showdepth(){
    	$maxdepth_attr = $this->attribute('depth',-1);
    	if($maxdepth_attr >=0 ){
    		return $maxdepth_attr;
    	}else if($this->parent() !== null){
    		return $this->parent()->showdepth();
    	}
    	return null;
    }
    
    // from Bean
    static public function createBean(array & $arrConfig,$sNamespace='*',$bBuildAtOnce)
    {
		$sClass = get_called_class() ;
		$aBean = new $sClass() ;
    	if($bBuildAtOnce)
    	{
    		$aBean->buildBean($arrConfig,$sNamespace) ;
    	}
    	return $aBean ;
    }
    
    public function buildBean(array & $arrConfig,$sNamespace='*')
    {
		parent::buildBean($arrConfig,$sNamespace);
		if( !empty($arrConfig['menu'])){
			$this->buildSubMenu($arrConfig['menu']);
		}
		if( !empty( $arrConfig['active'])){
			$this->setAttribute('active',$arrConfig);
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
	}
	
	private function buildSubMenu($subMenu){
		if($subMenu instanceof Menu){
			$this->setSubMenu($subMenu);
		}else if(is_string($subMenu)){
			$this->setSubMenu( new Menu($subMenu) );
		}else if(is_array($subMenu)){
			$subMenu['class'] = __NAMESPACE__.'\Menu';
			$this->setSubMenu( BeanFactory::singleton()->createBean($subMenu));
		}
	}
	
	public function getCssClassString(){
		$arrClass=array(
			$this->cssClassBase().'item-depth'.$this->depth(),
		);
		if($this->parent() !== null){
			$arrClass[] = $this->cssClassBase().'direction-'.$this->parent()->getDirection();
		}
		return 'class ="'.implode(' ',$arrClass).'"';
		return 'class ="'.$this->cssClassBase().'item-depth'.$this->depth().'"';
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
    
    private $sLink ;
    private $sOnClick ;
    private $sHtml ;
}
