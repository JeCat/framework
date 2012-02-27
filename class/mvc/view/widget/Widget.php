<?php
namespace org\jecat\framework\mvc\view\widget ;

use org\jecat\framework\bean\BeanFactory;
use org\jecat\framework\bean\IBean;
use org\jecat\framework\resrc\HtmlResourcePool;
use org\jecat\framework\util\StopFilterSignal;
use org\jecat\framework\message\Message;
use org\jecat\framework\message\IMessageQueue;
use org\jecat\framework\message\MessageQueue;
use org\jecat\framework\util\HashTable;
use org\jecat\framework\ui\UI;
use org\jecat\framework\io\IOutputStream;
use org\jecat\framework\mvc\view\IView;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\util\IHashTable;
use org\jecat\framework\lang\Object ;

class Widget extends Object implements IViewWidget, IBean
{	
	public function __construct($sId=null,$sTemplateName=null,$sTitle=null,IView $aView=null)
	{
		parent::__construct() ;
		
		$this->setId($sId) ;
		$this->setTitle($sTitle?$sTitle:$sId) ;
		$this->setTemplateName($sTemplateName) ;
		
		// 消息队列过滤器
		$this->messageQueue()->filters()->add(function ($aMsg,$aWidget){
			if($aMsg->poster()!=$aWidget)
			{
				StopFilterSignal::stop() ;
			}
			
			return array($aMsg) ;
		},$this) ;
		
		if($aView)
		{
			$aView->addWidget($this) ;
		}
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
	/**
	 * @wiki /MVC模式/视图/表单控件(Widget)
	 * ==Bean配置数组==
	 * {|
	 * !属性
	 * !类型
	 * !默认值
	 * !可选
	 * !说明
	 * |-- --
	 * |id
	 * |string
	 * |无
	 * |必须
	 * |Jecat框架区分控件的唯一参照.也会作为name属性体现在html页面上
	 * |-- --
	 * |title
	 * |string
	 * |无
	 * |可选
	 * |控件的文字说明,方便用户理解控件的内容
	 * |-- --
	 * |template
	 * |string
	 * |无
	 * |可选
	 * |指定模板文件的文件名
	 * |}
	 */
	public function buildBean(array & $arrConfig,$sNamespace='*',\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
	{
		if( !empty($arrConfig['id']) )
		{
			$this->setId($arrConfig['id']) ;
		}
		if( !empty($arrConfig['title']) )
		{
			$this->setTitle($arrConfig['title']) ;
		}
		if( !empty($arrConfig['template']) )
		{
			$this->setTemplateName($arrConfig['template']) ;
		}
		
    	$this->arrBeanConfig = $arrConfig ;
    }
    
	public function beanConfig()
	{
		return $this->arrBeanConfig ;
	}

	public function title()
	{
		return $this->sTitle ;
	}
	
	public function setTitle($sTitle)
	{
		$this->sTitle = $sTitle ;
	}
	
	/**
	 * @return IView
	 */
	public function view()
	{
		return $this->aView ;
	}

	public function setView(IView $aView=null)
	{
		$this->aView = $aView ;
	}

	public function id($bAutoId=true)
	{
	    if( $this -> sId === null and $bAutoId){
	        $this -> sId = strtr(get_class($this),'\\','.').self::$nAutoIncreaseId++;
	    }
		return $this->sId ;
	}

	public function setId($sId)
	{
		$this->sId = $sId ;
	}

	const TEMPLATETYPE_Template = 0x981 ;
	const TEMPLATETYPE_Function = 0x982 ;
	public function templateName()
	{
		if( self::TEMPLATETYPE_Template === $this->nTemplateType ){
			return $this->sTemplateName ;
		}else{
			return null ;
		}
	}

	public function setTemplateName($sTemplateName)
	{
		$this->sTemplateName = $sTemplateName ;
		$this->nTemplateType = self::TEMPLATETYPE_Template ;
	}
	
	public function subTemplateName(){
		if( self::TEMPLATETYPE_Function === $this->nTemplateType ){
			return $this->sSubTemplateName ;
		}else{
			return null ;
		}
	}
	
	public function setSubTemplateName($sSubTemplateName){
		$this->sSubTemplateName = $sSubTemplateName ;
		$this->nTemplateType = self::TEMPLATETYPE_Function ;
	}

	public function display(UI $aUI,IHashTable $aVariables=null,IOutputStream $aDevice=null)
	{
		$sTemplateName = $this->templateName() ;
		$sSubTemplateName = $this->subTemplateName() ;
		if(!$sTemplateName and !$sSubTemplateName )
		{
			throw new Exception("显示UI控件时遇到错误，UI控件尚未设置模板文件",$this->id()) ;
		}
		
		if(!$aVariables)
		{
			$aVariables = new HashTable() ;
		}

		$oldWidget=$aVariables->get('theWidget');
		$aVariables->set('theWidget',$this);
		
		if($sTemplateName){
			$aUI->display($sTemplateName,$aVariables,$aDevice) ;
		}else{
			if( !function_exists( $sSubTemplateName ) ){
				throw new Exception(
					"正在调用无效的子模板：%s %s",
					array(
						$sSubTemplateName ,
						$this->id() 
					)
				);
			}
			call_user_func_array( $sSubTemplateName , array( $aVariables ,$aDevice) ) ;
		}
		
		$aVariables->set('theWidget',$oldWidget);
	}

	/**
	 * @return IMessageQueue
	 */
	public function messageQueue($bAutoCreate=true)
	{
		if( $bAutoCreate and !$this->aMsgQueue )
		{
			$this->aMsgQueue = new MessageQueue() ;
		}
		
		return $this->aMsgQueue ;
	}
	
	public function setMessageQueue(IMessageQueue $aMsgQueue)
	{
		$this->aMsgQueue = $aMsgQueue ;
	}

	public function createMessage($sType,$sMessage,$arrMessageArgs=null,$aPoster=null)
	{
		return $this->messageQueue()->create($sType,$sMessage,$arrMessageArgs,$aPoster) ;
	}
	
	public function setAttribute($sName,$value)
	{
		$sName = strtolower($sName) ;
		$this->arrAttributes[$sName] = $value ;
	}
	public function attribute($sName,$default=null)
	{
		if(!$this->arrAttributes)
		{
			return $default ;
		}
		$sName = strtolower($sName) ;
		return isset($this->arrAttributes[$sName])? $this->arrAttributes[$sName]: $default ;
	}
    public function attributeBool($sName,$bValue=true)
    {
        $value=$this->attribute($sName,null);
        
        if($value === null)
        {
            return $bValue? true: false;
        }
        
        $value = strtolower($value) ;
        
        if($value === 'false' || $value === '0' || $value === 0 || $value === 'no' || $value === 'off' ){
            return false;
        }
        else
       {
            return true;
        }
	}
	public function attributeNameIterator()
	{
		return $this->arrAttributes? new \org\jecat\framework\pattern\iterate\ArrayIterator(array_keys($this->arrAttributes)): new \EmptyIterator() ;
	}
	public function removeAttribute($sName)
	{
		unset($this->arrAttributes[$sName]) ;
	}
	public function clearAttribute()
	{
		$this->arrAttributes = null ;
	}
	
	public function displayInputAttributes()
	{
		if(!$this->arrAttributes)
		{
			return '' ;
		}
		
		$sRet = '' ;
		foreach($this->arrAttributes as $sName=>$sValue)
		{
			$sRet.= ' ' . $sName . '="' . addcslashes($sValue,'"\\') . '"' ;
		}
		return $sRet ;
	}
	
	static private $nAutoIncreaseId=0;
	
	private $aView ;

	private $sId ;
	
	private $sTemplateName ;
	private $sSubTemplateName ;
	private $nTemplateType ;
	
	private $aMsgQueue ;

	private $sTitle ;
	
	private $arrAttributes ;
	
    private $arrBeanConfig ;
}

?>
