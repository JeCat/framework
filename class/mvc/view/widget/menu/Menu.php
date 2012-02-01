<?php
namespace org\jecat\framework\mvc\view\widget\menu;

use org\jecat\framework\resrc\HtmlResourcePool;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\util\IDataSrc;
use org\jecat\framework\bean\BeanFactory ;
use org\jecat\framework\ui\UI;
use org\jecat\framework\io\IOutputStream;
use org\jecat\framework\util\IHashTable;


HtmlResourcePool::singleton()->addRequire('org.jecat.framework:style/widget/menu.css',HtmlResourcePool::RESRC_CSS) ;
HtmlResourcePool::singleton()->addRequire('org.jecat.framework:js/mvc/view/widget/menu.js',HtmlResourcePool::RESRC_JS) ;

class Menu extends AbstractBase
{
	public function __construct($sId =null , IView $aView = null)
	{        
        parent::__construct ( $sId , 'org.jecat.framework:WidgetMenu.template.html', null , $aView );
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
     * @wiki /mvc/视图/控件/导航菜单(Menu)
     * == Bean配置数组 ==
     * {|
	 * !属性
	 * !类型
	 * !默认值
	 * !可选
	 * !说明
	 * |-- --
     * |items
     * |array
     * |无
     * |可选
     * |菜单项目列表,每个元素都是一个菜单项的配置
     */
    public function buildBean(array & $arrConfig,$sNamespace='*',\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
    {
    	parent::buildBean($arrConfig,$sNamespace);
    	if( !empty($arrConfig['items']) && is_array($arrConfig['items'])){
    		foreach($arrConfig['items'] as $key =>$item){
    			$this->buildItems($item,$key);
    		}
    	}
    	if(!empty($arrConfig['direction'])){
    		$this->setDirection($arrConfig['direction']);
    	}
    	if(!empty($arrConfig['top'])){
    		$this->setPosTop($arrConfig['top']);
    	}
    	if(!empty($arrConfig['left'])){
    		$this->setPosLeft($arrConfig['left']);
    	}
    	if( array_key_exists('tearoff',$arrConfig) ){
    		$this->setTearoff($arrConfig['tearoff']);
    	}
    }
    
    
    public function view()
    {
    	if( $aView = parent::view() )
    	{
    		return $aView ;
    	}
    
    	if( $aParentItem = $this->parentItem() )
    	{
    		return $aParentItem->view() ;
    	}
    
    	return null ;
    }
    
	/**
	 * @brief 添加一个item。
	 *
	 * 接受一个Item对象、一个包含Item对象的数组或一个字符串（item的title）。
	 */
	public function addItem($item){
		if($item instanceof Item){
			return $this->addItemPrivate($item);
		}else if(is_string($item)){
			$aItem = new Item($item);
			return $this->addItemPrivate($aItem);
		}else if(is_array($item)){
			foreach($item as $i){
				$rtn = $this->addItem($i);
			}
			return $rtn;
		}
	}
	
	public function getMenuByPath($arrPath){
		if(is_string($arrPath)){
			$arrPath = explode('/',$arrPath);
		}
		if(empty($arrPath)){
			return $this;
		}
		$id = array_shift($arrPath);
		foreach($this->itemIterator() as $item){
			if($id === $item->id()){
				if($item->subMenu() === null){
					return null;
				}else{
					return $item->subMenu()->getMenuByPath($arrPath);
				}
			}
		}
		return null;
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
		return new \org\jecat\framework\pattern\iterate\ArrayIterator ( $this->arrItems );
	}
	
	private function addItemPrivate(Item $aItem){
		$this->arrItems[]=$aItem;
		$aItem->setParentMenu($this);
		
		if(!$aItem->view())
		{
			$aItem->setView($this->view()) ;
		}
		
		return $aItem;
	}
	
	/**
	 * @return Item
	 */
	public function parentItem(){
		return $this->parentItem;
	}
	
	public function setParentItem(Item $aItem){
		if($this->parentItem !== $aItem){
			$this->parentItem = $aItem;
		}
	}
	
	/**
	 * @return Menu
	 */
	public function parentMenu()
	{
		if( !$aParentItem = $this->parentItem() )
		{
			return null ;
		}
		return $aParentItem->parentMenu() ;
	}
	
	public function parentMenuId()
	{
		if( !$aParentMenu = $this->parentMenu() )
		{
			return null ;
		}
		return $aParentMenu->id() ;
	}
	
	public function depth(){
    	if($this->parentItem() === null){
			return 1;
		}else{
			return $this->parentItem()->depth() +1;
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
			
			$aItem = BeanFactory::singleton()->createBean($configItems,'*',false) ;
			$this->addItem($aItem);
			
			$aItem->buildBean($configItems) ;
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
	
	public function setTearoff($bTearoff){
		$this->setAttribute('tearoff',$bTearoff?true:false);
	}
	
	public function isTearoff(){
		return $this->attribute('tearoff',false);
	}
	
	/// v-vertical h-horizontal
	public function setDirection($sD){
		$this->setAttribute('dire',$sD);
	}
	
	public function getDirection(){
		return $this->attribute('dire','v');
	}
	
	public function getStyleString(){
		$arrStyle = array();
		if($this->getPosTop() === null or $this->getPosLeft() === null){
		}else{
			$arrStyle[] = 'position:absolute';
			$arrStyle[] = 'left:'.$this->getPosLeft().'px';
			$arrStyle[] = 'top:'.$this->getPosTop().'px';
			//return "style='position: absolute;left:".$this->getPosLeft()."px;top:".$this->getPosTop()."px;'";
		}
		if($this->isTearoff()){
			$arrStyle[] = 'z-index:'.( 1000 + $this->depth() );
		}else{
			$arrStyle[] = 'z-index:'.$this->depth();
		}
		return 'style="'.implode(';',$arrStyle).'"';
	}
	
	public function getCssClassString(){
		$arrClass=array(
			parent::CSS_CLASS_BASE.'-depth-'.$this->depth(),
		);
		if($this->isTearoff()){
			$arrClass[] = parent::CSS_CLASS_BASE.'-tearoff';
		}
		$arrClass[] = parent::CSS_CLASS_BASE.'-direction-'.$this->getDirection();
		$arrClass[] = parent::CSS_CLASS_BASE ;
		return 'class ="'.implode(' ',$arrClass).'"';
	}
	
	public function findActiveSubMenu($nDepth)
	{
		if($nDepth<1)
		{
			return $this ;
		}
		
		foreach($this->arrItems as $aItem)
		{
			if( $aItem->isActive() and $sSubMenu=$aItem->subMenu() )
			{
				return $sSubMenu->findActiveSubMenu($nDepth-1) ;
			}
		}
		
		return null ;
	}
	
	public function display(UI $aUI,IHashTable $aVariables=null,IOutputStream $aDevice=null)
	{
		if(($depth=$this->attribute('depth'))!==null)
		{
			if( $aMenu = $this->findActiveSubMenu((int)$depth) )
			{
				$aMenu->display($aUI,$aVariables,$aDevice);
			}
		}
		else if(($xpath=$this->attribute('xpath'))!==null)
		{
			if( $aMenu = $this ->getMenuByPath($xpath) )
			{
				$aMenu->display($aUI,$aVariables,$aDevice);
			}
		}
		
		else
		{
			if($this->showDepths()!=0)
			{
				parent::display($aUI,$aVariables,$aDevice) ;
			}
		}
	}
	
	public function showDepths()
	{
		$showDepths = $this->attribute('showDepths') ;
		if($showDepths===null)
		{
			if( $aParentMenu = $this->parentMenu() )
			{
				return $aParentMenu->showDepths() - 1 ;
			}
			else
			{
				return -1 ;
			}
		}
		
		return (int) $showDepths ;
	}
	
	public function isActive()
	{
		foreach($this->arrItems as $aItem)
		{
			if( $aItem->isActive() )
			{
				return true ;
			}
		}
		
		return false ;
	}
	
	public function generateJsCode()
	{
		return 'new jc.mvc.view.widget.menu.base.jsobject("'
				.$this->id().'","'.$this->getDirection().'",'.($this->isTearoff()?'true':'false').',"'.$this->parentMenuId().'");' ;
	}
	
	private $arrItems = array();
	private $parentItem = null;

}

