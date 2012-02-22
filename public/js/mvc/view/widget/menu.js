jc={};
jc.mvc={};
jc.mvc.view={};
jc.mvc.view.widget={};
jc.mvc.view.widget.menu={};
jc.mvc.view.widget.menu.base={};
jc.mvc.view.widget.menu.base.getStyle=function(o,styleName){
	var el = o;
	if (el.currentStyle){
		var y = el.currentStyle[styleName];
	}else if (window.getComputedStyle){
		var y = document.defaultView.getComputedStyle(el,null).getPropertyValue(styleName);  
	}
	return y;
}
jc.mvc.view.widget.menu.base.arrActive=new Array();
jc.mvc.view.widget.menu.base.show=function(o){
	o.parentNode.style.position="relative";
	o.style.position="absolute";
	if(this.isVertical(o.parentNode)){
		o.style.left = o.parentNode.offsetWidth+'px';
		o.style.top = 0;
	}else if(this.isHorizontal(o.parentNode)){
		o.style.left = 0;
		o.style.top = o.parentNode.offsetHeight+'px';
	}else{
	}
	o.style.display="block";
}
jc.mvc.view.widget.menu.base.hide=function(o){
	o.style.display="none";
}
jc.mvc.view.widget.menu.base.isShow=function(o){
	return this.getStyle(o,'display')== "block";
}
jc.mvc.view.widget.menu.base.isTearoff=function(o){
	return ( o.className && ( o.className.match('jc-widget-menu-tearoff') == 'jc-widget-menu-tearoff'));
}
jc.mvc.view.widget.menu.base.isVertical=function(o){
	return ( o.className && ( o.className.match('jc-widget-menu-direction-v') == 'jc-widget-menu-direction-v'));
}
jc.mvc.view.widget.menu.base.isHorizontal=function(o){
	return ( o.className && ( o.className.match('jc-widget-menu-direction-h') == 'jc-widget-menu-direction-h'));
}
jc.mvc.view.widget.menu.base.expand=function(o){
	var childlist = o.childNodes;
	for(var i=0;i<childlist.length;++i){
		if(this.isTearoff(childlist[i])){
			o = childlist[i];
			this.show(o);
			this.arrActive.push(o.id);
		}
	}
}
jc.mvc.view.widget.menu.base.contract=function(o){
	var childlist = o.childNodes;
	for(var i=0;i<childlist.length;++i){
		if(this.isTearoff(childlist[i])){
			var o = childlist[i];
			this.hide(o);
		}
	}
}
jc.mvc.view.widget.menu.base.isOrParentOf=function(a , b){// a is b or a is parent of b
	while(b && typeof(b)!='undefined'){
		if(a == b) return true;
		b = b.parentNode;
	}
	return false;
}
jc.mvc.view.widget.menu.base.hideActive=function(item){//隐藏所有和item无关的menu
	for(var i=0;i<this.arrActive.length;++i){
		a=this.arrActive[i];
		if(! this.isOrParentOf(document.getElementById(a) ,item) ){
			this.hide(document.getElementById(a));
			this.arrActive.splice(i,1);
		}
	}
}
jc.mvc.view.widget.menu.base.item_onmouseover=function(item){
	clearTimeout(this.t);
	this.t=-1;
	this.hideActive(item);
	this.expand(item);
	this.stopBubble(event);
}
jc.mvc.view.widget.menu.base.t=-1;
jc.mvc.view.widget.menu.base.timeout=function(){
	this.hideActive(null);
}
jc.mvc.view.widget.menu.base.item_onmouseout=function(item){
	if(this.t == -1){
		this.t=setTimeout("jc.mvc.view.widget.menu.base.timeout()",500);
	}
}
jc.mvc.view.widget.menu.base.bindItemEvent = function(elementId)
{
	var element = document.getElementById(elementId) ;
	if(element)
	{
		element.onmouseover = function(){jc.mvc.view.widget.menu.base.item_onmouseover(this)} ;
		element.onmouseout = function(){jc.mvc.view.widget.menu.base.item_onmouseout(this)} ;
	}
}

jc.mvc.view.widget.menu.base.stopBubble=function(e) {  
    var e = e ? e : window.event;
    if (window.event) { // IE
        e.cancelBubble = true;
    } else { // FF
        e.stopPropagation();
    }
}
//jsobject
jc.mvc.view.widget.menu.base.jsobject=function(id,direction,tearoff,parentMenuId){
	this.id = id;
	this.attribute={};
	this.setAttribute=function(key,value){
		this.attribute[key]=value;
	};
	this.getAttribute=function(key,defaultValue){
		console.log("getAttribute:"+typeof(this.attribute[key]));
		if(typeof(this.attribute[key]) == 'undefined'){
			return defaultValue;
		}else{
			return this.attribute[key];
		}
	};
	this.childList=[];
	this.addChild=function(id){
		this.childList.push(id);
	};
	this.parentId=-1;
	this.setParentId=function(id){
		this.parentId = id;
		var p = this.constructor.getObjectById(id);
		if(typeof(p)!='undefined'){
			p.addChild(this.id);
		}
	};
	this.registerObject=function(){
		this.constructor.objectlist[this.id]=this;
	};
	this.registerObject();
	
	// 
	if( typeof(parentMenuId)!='undefinde' && parentMenuId )
	{
		this.setParentId(parentMenuId) ;
	}

	this.setAttribute('direction',direction) ;
	this.setAttribute('tearoff',tearoff) ;
}
jc.mvc.view.widget.menu.base.jsobject.objectlist={};
jc.mvc.view.widget.menu.base.jsobject.getObjectById=function(id){
	return this.objectlist[id];
}

// functions
jc.mvc.view.widget.menu.fun = {
	onload : {
		addOnLoad : function(fun){
			this.loadList.push(fun);
		},
		loadOnLoad : function(){
			var i;
			for(i=0;i<this.loadList.length;++i){
				var fun = this.loadList[i] ;
				fun() ;
			}
		},
		loadList : [] ,
	},
} ;

window.onload = function(){
	jc.mvc.view.widget.menu.fun.onload.loadOnLoad() ;
}

jc.mvc.view.widget.menu.fun.onload.addOnLoad(function(){
	console.log('71');
});
jc.mvc.view.widget.menu.fun.onload.addOnLoad(function(){
	console.log('82');
});

// object list
jc.mvc.view.widget.menu.objectList = {
	itemList : [] ,
	menuList : [] ,
};

// add all item
var sItemClassName = 'jc-widget-menu-item' ;
var sMenuClassName = 'jc-widget-menu' ;
jc.mvc.view.widget.menu.fun.onload.addOnLoad(function(){
	var lilist = document.getElementsByTagName('li');
	var i;
	var j;
	for(i=0;i<lilist.length;++i){
		var li = lilist[i] ;
		for(j=0;j<li.classList.length;++j){
			if( sItemClassName == li.classList[j] ){
				// jc.mvc.view.widget.menu.objectList.itemList.push( li ) ;
				li.isItem = true ;
				
				li.onmouseover = function(){
					console.log('onmouseover');
					jc.mvc.view.widget.menu.base.item_onmouseover(this);
				} ;
				li.onmouseout = function(){jc.mvc.view.widget.menu.base.item_onmouseout(this)} ;
				break;
			}
		}
	}
	
	var ulList = document.getElementsByTagName('ul');
	for( i=0 ; i<ulList.length ; ++i ){
		var ul = ulList[i] ;
		for( j=0 ; j<ul.classList.length ; ++j ){
			var sClassName = ul.classList[j] ;
			if( sMenuClassName == sClassName ){
				ul.getAttr = function(sName){
					var k ;
					for( k=0 ; k<this.attributes.length ; ++k ){
						if( sName == this.attributes[k].name ){
							return this.attributes[k].value ;
						}
					}
				}
				
				if( ul.getAttr('tearoff') == 'on' ){
					ul.style.display = 'none' ;
				}
				if( ! ul.parentNode.isItem ){
					console.log(ul);
					jc.mvc.view.widget.menu.objectList.menuList.push( ul ) ;
				}
			}
		}
	}
	console.log(jc.mvc.view.widget.menu.objectList ) ;
});
