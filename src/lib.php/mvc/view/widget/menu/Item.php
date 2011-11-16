<?php
namespace jc\mvc\view\widget\menu;

use jc\bean\BeanFactory ;

class Item extends AbstractBase
{
	public function __construct($sTitle='',$sId =null , IView $aView = null) {
        parent::__construct ( $sId , 'jc:WidgetItem.template.html', null , $aView );
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
	public function build(array & $arrConfig,$sNamespace='*'){
		parent::build($arrConfig,$sNamespace);
		if( !empty($arrConfig['menu'])){
			$this->buildSubMenu($arrConfig['menu']);
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
	
    private $parentMenu = null;
    private $subMenu = null;
}
