<?php
namespace jc\mvc\view\widget;

use jc\mvc\view\widget\FormWidget;
use jc\mvc\view\IModelChangeObserver;
use jc\mvc\model\db\Model;
use jc\mvc\view\View;
use jc\mvc\view\IView;
use jc\mvc\view\widget\paginatorstrategy\AbstractStrategy;

/*!
    attr.nums : int 显示几页 5
    attr.showFirst
    attr.showLast ： bool 是否显示“第一页”与“最后一页” true
    attr.showTotal : bool 是否显示“共*页” true
    attr.showPre
    attr.showNext : bool 是否显示“上一页”与“下一页” true
    
    pageNumList() 由 PaginatorStrategy 对象控制。
*/
class Paginator extends FormWidget implements IModelChangeObserver{
    public function __construct($sId, $sTitle = null, IView $aView = null) {
        parent::__construct ( $sId, 'jc:WidgetPaginator.template.html', $sTitle, $aView );
        $this->setCurrentPageNum((int)$_GET[$this->UrlKey()]);
        $this->aPaginal = $aView->model();
        $this->iCount=5;
        $this->iShowWidth=5;
    }
	
    public function setPerPageCount($iCount){
        $this->iCount=(int)$iCount;
    }
    
    public function perPageCount(){
        return $this->iCount;
    }
    
    public function totalPageCount(){
        return (int) ( ($this->aPaginal->totalCount()+$this->perPageCount()-1)/$this->perPageCount() );
    }
    
    public function setCurrentPageNum($iNum){
        $this->iCurrentPageNum=(int)$iNum;
    }
    
    public function currentPageNum(){
        return $this->iCurrentPageNum;
    }
    
    public function onModelChanging(View $aView){
        $this->setPaginal($aView);
    }
    
    public function setPaginal(IPaginal $aPaginal){
        $this->aPaginal=$aPaginal;
    }
    
    public function pageNumList(){
        if( $this->aStrategy === null){
            $this->setStrategy($this->attribute('strategy','middle'));
        }
        return $this->aStrategy->pageNumList(
                        $this ->attribute('nums',5),//width
                        $this->currentPageNum(),//current
                        $this->totalPageCount());//total
    }
    
    public function pageUrl($iPageNum){
        return '?'.$this->UrlKey().'='.(string)$iPageNum;
    }
    
    protected function UrlKey(){
        return $this->id().'_page';
    }
    
    public function attributeBool($sName,$bValue=true){
        $aValue=$this->attribute($sName,null);
        if($aValue === null ){
            return (bool)$bValue;
        }else if($aValue === 'true' || $aValue === 'True' || $aValue === 1 || $aValue === '1' ){
            return true;
        }else{
            return false;
        }
	}
	
	public function setStrategy($Strategy){
	    if( is_string($Strategy) ){
	        $this->aStrategy=AbstractStrategy::createByName($Strategy);
	    }
	}
    
    private $iCount;
    private $iCurrentPageNum;
    private $aPaginal;
    private $iShowWidth;
    private $aStrategy;
}
