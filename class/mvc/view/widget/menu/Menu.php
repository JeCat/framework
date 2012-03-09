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
     * @wiki /MVC模式/视图窗体(控件)/菜单控件
     * ==Menu==
     * =Bean配置数组=
     * {|
	 * !属性
	 * !类型
	 * !默认值
	 * !可选
	 * !说明
	 * |-- --
     * |item
     * |array
     * |无
     * |可选
     * |菜单项目列表,每个元素都是一个菜单项的配置
	 * |-- --
     * |menu
     * |mixed
     * |无
     * |可选
     * |menu设置为1，含有子菜单，设置为0，不含有子菜单
     * |-- --
     * |tearoff
     * |mixed
     * |无
     * |可选
     * |tearoff设置为1，菜单弹出显示，设置为0，菜单列表显示
     * |-- --
     * |showDepths
     * |mixed
     * |无
     * |可选
     * |菜单项目列表显示层级数量
     * |-- --
     * |link
     * |mixed
     * |无
     * |可选
     * |菜单项目列表的链接
     * |}
     * [example php frameworktest template/test-mvc/testviewwindow/TestMenuTemplate.html 8 40]
     */
	public function buildBean(array & $arrConfig,$sNamespace='*',\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
	{
		parent::buildBean($arrConfig,$sNamespace);
		foreach($arrConfig as $key=>$value){
			if(
				preg_match('`^item:(.*)$`',$key,$arrMatch) 
				
				// 用xml配置bean的时候不能使用冒号，只能用减号代替
				or preg_match('`^item-(.*)$`',$key,$arrMatch)
			){
				$sItemName = $arrMatch[1] ;
				
				if( is_array( $value ) ){
					$this->buildItemFromBean( $value , $sItemName );
				}
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
		$this->arrItems[$aItem->id()]=$aItem;
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
	
	private function buildItemFromBean(array $arrItemBean , $id=null ){
		$arrItemBean['class']=__NAMESPACE__.'\Item';
		if(empty($arrItemBean['id']) && !is_int($id) ){
			$arrItemBean['id'] = $id;
		}
		
		$aItem = BeanFactory::singleton()->createBean($arrItemBean,'*',false) ;
		
		// 在BuildBean中，通过query设置active需要先获得知道view()对象
		if(!$aItem->view())
		{
			$aItem->setView($this->view()) ;
		}
		$aItem->buildBean($arrItemBean);
		
		// addItem时，为了避免重复，需要先知道id值，所以只能放在buildBean之后
		$this->addItemPrivate($aItem);
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
			if($this->showDepths()!=0 || $this->isRenderAll() )
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
				return 10000 ;
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
	
	public function isRenderAll(){
		$aParentMenu = $this->parentMenu() ;
		if($aParentMenu){
			$b = $aParentMenu->isRenderAll() ;
			return  $b ;
		}
		$b = $this->attributeBool('renderall' , false ) ;
		return $b ;
	}
	
	public function isShowOnMouseOver(){
		return $this->showDepths() > 0 ;
	}
	
	public function generateJsCode()
	{
		return 'new jc.mvc.view.widget.menu.base.jsobject("'
				.$this->id().'","'.$this->getDirection().'",'.($this->isTearoff()?'true':'false').',"'.$this->parentMenuId().'");' ;
	}
	
	private $arrItems = array();
	private $parentItem = null;

}

