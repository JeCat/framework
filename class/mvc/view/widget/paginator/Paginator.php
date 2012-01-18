<?php
namespace org\jecat\framework\mvc\view\widget\paginator;

use org\jecat\framework\mvc\controller\Request;

use org\jecat\framework\mvc\view\widget\FormWidget;
use org\jecat\framework\mvc\view\IModelChangeObserver;
use org\jecat\framework\mvc\model\db\Model;
use org\jecat\framework\mvc\view\View;
use org\jecat\framework\mvc\view\IView;
use org\jecat\framework\mvc\view\widget\paginator\AbstractStrategy;
use org\jecat\framework\mvc\model\IPaginal;
use org\jecat\framework\util\IDataSrc;
use org\jecat\framework\system\Application;
use org\jecat\framework\mvc\controller\HttpRequest;

/*!
    attr.nums : int 显示几页（即分页器的宽度） 5
    attr.showFirst
    attr.showLast ： bool 是否显示“第一页”与“最后一页” true
    attr.showTotal : bool 是否显示“共*页” true
    attr.showPre
    attr.showNext : bool 是否显示“上一页”与“下一页” true
    attr.strategy : PaginatorStrategy 显示策略（显示哪些页码），可以是一个字符串（表示类名）或者一个对象（需要设置type为expression） new \org\jecat\framework\mvc\view\widget\paginatorstrategy\Middle
    attr.onclick : string js代码，onclick事件 null
    
    pageNumList() 由 PaginatorStrategy 对象控制。目前只提供一个Middle策略。
*/
/*!
    // example:
    // code:
    class TryPaginatorController extends Controller {
        protected function init() {
            $this->createFormView( "TryPaginator" );
            $aPaginator = new Paginator( 'paginator' ,$this->params);
            $this->viewTryPaginator->addWidget( $aPaginator );
            $aModel = new Model('electronicnewspaper_newspaper',true);
            $aPaginator -> setPerPageCount(2);
            $this->viewTryPaginator->setModel($aModel);
            $aPaginator -> setPerPageCount(3);
            $aModel->load();
            $arrTitle = array();
            foreach($aModel->childIterator() as $b){
                $arrTitle[] = $b['title'];
            }
            $this->viewTryPaginator->variables()->set('arrTitle',$arrTitle);
        }
    }
    // template:
    <msgqueue />
    <table border="1">
        <foreach for='{=$arrTitle}' item='title'>
            <tr><td>{=$title}</td></tr>
        </foreach>
    </table>
    <form id="theform" method='post'>
	    <widget id='paginator' attr.nums='7' attr.strategy.type='expression' attr.strategy='new \org\jecat\framework\mvc\view\widget\paginatorstrategy\Middle' attr.onclick='alert(\"hello\")' />
    </form>
*/
class Paginator extends FormWidget implements IModelChangeObserver
{
    public function __construct($sId =null ,IDataSrc $aDataSource = null , IView $aView = null) {
        parent::__construct ( $sId , 'org.jecat.framework:WidgetPaginator.template.html', null , $aView );
        $this->iCount=10;
        $this->iShowWidth=5;
        if($aDataSource)
        {
        	$this->setDataFromSubmit($aDataSource);
        }
    }
    
    public function buildBean(array & $arrConfig,$sNamespace='*',\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
    {
    	parent::buildBean($arrConfig,$sNamespace) ;
    	
    	if( !empty($arrConfig['count']) )
    	{
    		$this->setPerPageCount($arrConfig['count']) ;
    	}
    	if( !empty($arrConfig['nums']) )
    	{
    		$this->setCurrentPageNum($arrConfig['nums']) ;
    	}
    
    	$this->setDataFromSubmit(Request::singleton()) ;
    }
    
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
    
    public function setPerPageCount($iCount){
        $this->iCount=(int)$iCount;
        $this->updatePaginal();
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
        $this->updatePaginal();
    }
    
    public function currentPageNum(){
        $iNum=(int)$this->value();
        if( $iNum > $this->totalPageCount() ) $iNum = $this->totalPageCount();
        if( $iNum < 1) $iNum =1 ;
        return $iNum;
    }
    
    public function onModelChanging(View $aView){
        $this->setPaginal($aView->model());
    }
    
    public function setPaginal($aPaginal){
        $this->aPaginal=$aPaginal;
        $this->updatePaginal();
    }
    
    protected function updatePaginal(){
        if( $this->aPaginal === null ) return;
        $iPerPage = (int) $this->perPageCount();
        $iPageNum = (int) $this->currentPageNum();
        $this->aPaginal->setPagination($iPerPage,$iPageNum);
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
        $aRequest = Request::singleton();
        if( $aRequest instanceof HttpRequest )
        {
            $str=$aRequest -> urlQuery();
            if(!empty($str)) $arrQuery = explode('&',$str);
            $strKeyName = $this->formName();
            $bFlag =  false;
            $arrQuery1 = array_map( function( $str) use($strKeyName,&$bFlag,$iPageNum) {
                        if(substr( $str,0,strlen($strKeyName)) === $strKeyName ){
                            $bFlag = true;
                            return $strKeyName.'='.(string)$iPageNum;
                        }else{
                            return $str;
                        }
                    },$arrQuery);
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
	
	public function setStrategy($Strategy){
	    if( is_string($Strategy) ){
	        $this->aStrategy=AbstractStrategy::createByName($Strategy);
	    }else if( $Strategy instanceof AbstractStrategy){
	        $this->aStrategy=$Strategy;
	    }else{
	        throw new Exception('setStrategy error : nether a string not an instanceof AbstractStrategy');
	    }
	}
	
	public function setView(IView $aView=null)
	{
	    $formerView = $this->view();
	    if( $formerView ){
	        if ( $formerView === $aView ){
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
