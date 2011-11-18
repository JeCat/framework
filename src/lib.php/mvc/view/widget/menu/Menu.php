<?php
namespace jc\mvc\view\widget\menu;

use jc\lang\Exception;
use jc\util\IDataSrc;
use jc\bean\BeanFactory ;

class Menu extends AbstractBase
{
	public function __construct($sId =null , IView $aView = null) {
        parent::__construct ( $sId , 'jc:WidgetMenu.template.html', null , $aView );
    }
    
	/**
	 * @brief 添加一个item。
	 *
	 * 接受一个Item对象、一个包含Item对象的数组或一个字符串（item的title）。
	 */
	public function addItem($item){
		if($item instanceof Item){
			$this->addItemPrivate($item);
		}else if(is_string($item)){
			$aItem = new Item($item);
			$this->addItemPrivate($aItem);
		}else if(is_array($item)){
			foreach($item as $i){
				$this->addItem($i);
			}
		}
	}
	
	public function getFirstItemByTitle($sTitle){
		foreach($this->itemIterator() as $aItem){
			if($aItem->title() === $sTitle){
				return $aItem;
			}
		}
		return null;
	}
	
	public function itemIterator(){
		return new \jc\pattern\iterate\ArrayIterator ( $this->arrItems );
	}
	
	private function addItemPrivate(Item $aItem){
		$this->arrItems[]=$aItem;
		$aItem->setParentMenu($this);
	}
	
	protected function parent(){
		return $this->parentItem();
	}
	
	public function parentItem(){
		return $this->parentItem;
	}
	
	public function setParentItem(Item $aItem){
		if($this->parentItem !== $aItem){
			$this->parentItem = $aItem;
		}
	}
	
	public function depth(){
    	if($this->parent() === null){
			return 1;
		}else{
			return $this->parent()->depth() +1;
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
		if( !empty($arrConfig['items']) && is_array($arrConfig['items'])){
			foreach($arrConfig['items'] as $key =>$item){
				$this->buildItems($item,$key);
			}
		}
		if(!empty($arrConfig['top'])){
			$this->setPosTop($arrConfig['top']);
		}
		if(!empty($arrConfig['left'])){
			$this->setPosLeft($arrConfig['left']);
		}
		if(!empty($arrConfig['alone'])){
			$this->setIndependence($arrConfig['alone']);
		}
	}
	
	private function buildItems($configItems,$id=null){
		if($configItems instanceof Item){
			if(!is_int($id)) $configItems->setId($id);
			$this->addItem($configItems);
		}else if(is_string($configItems)){
			if(is_int($id)) $id=null;
			$aItem = new Item($configItems,$id);
			$this->addItem($aItem);
		}else if(is_array($configItems)){
			$configItems['class']=__NAMESPACE__.'\Item';
			if(empty($configItems['id']) && !is_int($id) ){
				$configItems['id'] = $id;
			}
			$this->addItem( BeanFactory::singleton()->createBean($configItems));
		}
	}
	
	public function setPos($left,$top){
		$this->setAttribute('left',$left);
		$this->setAttribute('top',$top);
	}
	
	public function setPosTop($top){
		$this->setAttribute('top',$top);
	}
	
	public function setPosLeft($left){
		$this->setAttribute('left',$left);
	}
	
	public function getPosTop(){
		return $this->attribute('top',null);
	}
	
	public function getPosLeft(){
		return $this->attribute('left',null);
	}
	
	public function setIndependence($bIndependence){
		$this->setAttribute('alone',$bIndependence);
	}
	
	public function getIndependence(){
		return $this->attribute('alone',false);
	}
	
	/// v-vertical h-horizontal
	public function setDirection($sD){
		$this->setAttribute('dire',$sD);
	}
	
	public function getDirection(){
		return $this->attribute('dire','v');
	}
	
	public function getStyleString(){
		if($this->getPosTop() === null or $this->getPosLeft() === null){
			return '';
		}else{
			return "style='position: absolute;left:".$this->getPosLeft()."px;top:".$this->getPosTop()."px;'";
		}
	}
	
	public function getCssClassString(){
		$arrClass=array(
			$this->cssClassBase().'menu-depth'.$this->depth(),
		);
		if($this->getIndependence()){
			$arrClass[] = $this->cssClassBase().'menu-alone';
		}
		$arrClass[] = $this->cssClassBase().'direction-'.$this->getDirection();
		return 'class ="'.implode(' ',$arrClass).'"';
	}
	
	private $arrItems = array();
	private $parentItem = null;

}
