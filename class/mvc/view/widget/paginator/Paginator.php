<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  这个文件是 JeCat PHP框架的一部分，该项目和此文件 均遵循 GNU 自由软件协议
// 
//  Copyleft 2008-2012 JeCat.cn(http://team.JeCat.cn)
//
//
//  JeCat PHP框架 的正式全名是：Jellicle Cat PHP Framework。
//  “Jellicle Cat”出自 Andrew Lloyd Webber的音乐剧《猫》（《Prologue:Jellicle Songs for Jellicle Cats》）。
//  JeCat 是一个开源项目，它像音乐剧中的猫一样自由，你可以毫无顾忌地使用JCAT PHP框架。JCAT 由中国团队开发维护。
//  正在使用的这个版本是：0.8
//
//
//
//  相关的链接：
//    [主页]			http://www.JeCat.cn
//    [源代码]		https://github.com/JeCat/framework
//    [下载(http)]	https://nodeload.github.com/JeCat/framework/zipball/master
//    [下载(git)]	git clone git://github.com/JeCat/framework.git jecat
//  不很相关：
//    [MP3]			http://www.google.com/search?q=jellicle+songs+for+jellicle+cats+Andrew+Lloyd+Webber
//    [VCD/DVD]		http://www.google.com/search?q=CAT+Andrew+Lloyd+Webber+video
//
////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*-- Project Introduce --*/
namespace org\jecat\framework\mvc\view\widget\paginator;

use org\jecat\framework\lang\Exception;
use org\jecat\framework\mvc\controller\Request;
use org\jecat\framework\mvc\view\widget\FormWidget;
use org\jecat\framework\mvc\view\IModelChangeObserver;
use org\jecat\framework\mvc\view\View;
use org\jecat\framework\mvc\view\IView;
use org\jecat\framework\mvc\view\widget\paginator\AbstractStrategy;
use org\jecat\framework\util\IDataSrc;
use org\jecat\framework\mvc\controller\HttpRequest;

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
    /**
     * @wiki /MVC模式/视图窗体(控件)/分页控件
     * ==使用方法==
     *  与model一起组合使用,单独使用无效
     * ==Bean配置数组==
     * {|
	 * !属性
	 * !类型
	 * !默认值
	 * !可选
	 * !说明
	 * |-- --
     * |count
     * |int
     * |10
     * |可选
     * |每页显示的条目数量
     * |}
     * ==模板属性配置==
     * {|
	 * !属性
	 * !类型
	 * !默认值
	 * !可选
	 * !说明
     * |-- --
     * |attr.nums
     * |int
     * |5
     * |可选
     * |显示页码的个数
	 * |-- --
     * |attr.showFirst
     * |mixed
     * |true
     * |可选
     * |是否显示“第一页”
     * |-- --
     * |attr.showLast
     * |mixed
     * |true
     * |可选
     * |是否显示“最后一页”
     * |-- --
     * |attr.showTotal
     * |mixed
     * |true
     * |可选
     * |是否显示“共*页”
     * |-- --
     * |attr.showPre
     * |mixed
     * |true
     * |可选
     * |是否显示“上一页”
     * |-- --
     * |attr.showNext
     * |mixed
     * |true
     * |可选
     * |是否显示“下一页”
     * |-- --
     * |attr.strategy
     * |mixed
     * |middle
     * |可选
     * |显示策略（显示哪些页码），可以是一个字符串（表示类名）或者一个对象（需要设置type为expression） new \org\jecat\framework\mvc\view\widget\paginatorstrategy\Middle
     * |}
     * [example php frameworktest template/test-mvc/testviewwindow/TestPaginaterWidget.html 15 16]
     */
    public function buildBean(array & $arrConfig,$sNamespace='*',\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
    {
    	parent::buildBean($arrConfig,$sNamespace) ;
    	
    	if( !empty($arrConfig['count']) )
    	{
    		$this->setPerPageCount($arrConfig['count']) ;
    	}
    	if( !empty($arrConfig['page']) )
    	{
    		$this->setCurrentPageNum($arrConfig['page']) ;
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
        $nTotalCount = $this->totalCount() ;
        $nPerPageCount = $this->perPageCount() ;
        
        return (int) ( ( $nTotalCount + $nPerPageCount - 1 ) / $nPerPageCount );
    }
    
    public function totalCount(){
		if( null !== $this->iTotalCount ){
			return $this->iTotalCount ;
		}else
        if( $this->aPaginal ){
            return $this->iTotalCount = (int) $this->aPaginal->paginationTotalCount() ;
        }else{
            return 0 ;
        }
    }
	
	public function setTotalCount($iTotalCount){
		$this->iTotalCount = $iTotalCount ;
	}
    
    public function setCurrentPageNum($iNum)
    {
        $this->setValue($iNum);
    }
    
    public function currentPageNum()
    {
        $iNum=(int)$this->value();
        return $iNum<1? 1: $iNum ;
    }
    
    public function onModelChanging(View $aView)
    {
    	if( $aModel=$aView->model() and ($aModel instanceof IPaginal) )
    	{
    		$this->setPaginal($aModel);
    	}
    }
    
    public function setPaginal(IPaginal $aPaginal){
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
            $arrQuery = explode('&',$str);
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
	
	public function setAttribute($sName,$value)
	{
		$sName = strtolower($sName) ;
		if( 'count' === $sName ){
			$this->setPerPageCount($value);
		}
		parent::setAttribute($sName,$value) ;
	}
    
	private $iTotalCount = null ;
    private $iCount;
    private $iCurrentPageNum;
    private $aPaginal;
    private $iShowWidth;
    private $aStrategy;
}


