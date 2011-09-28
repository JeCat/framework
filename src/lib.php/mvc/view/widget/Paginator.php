<?php
namespace jc\mvc\view\widget;

use jc\mvc\view\widget\FormWidget;
use jc\mvc\view\IModelChangeObserver;
use jc\mvc\model\db\Model;
use jc\mvc\view\View;
use jc\mvc\view\IView;
use jc\mvc\view\widget\paginatorstrategy\AbstractStrategy;
use jc\mvc\model\IPaginal;
use jc\util\IDataSrc;
use jc\system\Application;
use jc\system\HttpRequest;


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
    public function __construct($sId =null ,IDataSrc $aDataSource = null , IView $aView = null) {
        parent::__construct ( $sId , 'jc:WidgetPaginator.template.html', null , $aView );
        $this->iCount=5;
        $this->iShowWidth=5;
        if( $aDataSource) $this->setDataFromSubmit($aDataSource);
    }
	
    public function setPerPageCount($iCount){
        $this->iCount=(int)$iCount;
    }
    
    public function perPageCount(){
        return $this->iCount;
    }
    
    public function totalPageCount(){
        if( null === $this->aPaginal ) return 1;
        return (int) ( ($this->aPaginal->totalCount()+$this->perPageCount()-1)/$this->perPageCount() );
    }
    
    public function setCurrentPageNum($iNum){
        $this->setValue($iNum);
    }
    
    public function currentPageNum(){
        $iNum=(int)$this->value();
        if( $iNum < 1) $iNum =1 ;
        if( $iNum > $this->totalPageCount() ) $iNum = $this->totalPageCount();
        return $iNum;
    }
    
    public function onModelChanging(View $aView){
        $this->setPaginal($aView->model());
    }
    
    public function setPaginal($aPaginal){
        $this->aPaginal=$aPaginal;
        if( $this->aPaginal === null ) return;
        $iPerPage = (int) $this->perPageCount();
        $iPageNum = (int) $this->currentPageNum();
        $aPaginal->setPagination($iPerPage,$iPageNum);
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
        $aRequest = Application::singleton()->request();
        if( $aRequest instanceof HttpRequest )
        {
            $str=$aRequest -> urlQuery();
            $arrQuery = explode( $str , '&' );
            $bFlag =  false;
            $arrQuery1 = array_map( function( $str){
                        if(substr( $str,0,strlen($this->formName())) === $this->formName() ){
                            $bFlag = true;
                            return $this->formName().'='.(string)$iPageNum;
                        }else{
                            return $str;
                        }
                    });
            if( ! $bFlag ){
                $arrQuery1[]=$this->formName().'='.(string)$iPageNum;
            }
            return '?'.implode('&',$arrQuery1);
        }
        else
        {
            return '#' ;
        }
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
	    }else if( $Strategy instanceof AbstractStrategy){
	        $this->aStrategy=$Strategy;
	    }else{
	        throw new Exception('setStrategy error : nether a string not an instanceof AbstractStrategy');
	    }
	}
	
	public function setView(IView $aView)
	{
	    $formerView = $this->view();
	    if( $formerView ){
	        if ( $former === $aView ){
	            return;
	        }else{
	            $formerView -> removeModelObserver( $this ) ;
	        }
	    }
	    parent::setView($aView);
	    $aView -> addModelObserver($this);
	    if( $aView ) $this->onModelChanging($aView);
	}
    
    private $iCount;
    private $iCurrentPageNum;
    private $aPaginal;
    private $iShowWidth;
    private $aStrategy;
}
