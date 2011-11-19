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
jc.mvc.view.widget.menu.base.isAlone=function(o){
	return ( o.className && ( o.className.match('jc-mvc-view-widget-menu-menu-alone') == 'jc-mvc-view-widget-menu-menu-alone'));
}
jc.mvc.view.widget.menu.base.isVertical=function(o){
	return ( o.className && ( o.className.match('jc-mvc-view-widget-menu-direction-v') == 'jc-mvc-view-widget-menu-direction-v'));
}
jc.mvc.view.widget.menu.base.isHorizontal=function(o){
	return ( o.className && ( o.className.match('jc-mvc-view-widget-menu-direction-h') == 'jc-mvc-view-widget-menu-direction-h'));
}
jc.mvc.view.widget.menu.base.expand=function(o){
	var childlist = o.childNodes;
	for(var i=0;i<childlist.length;++i){
		if(this.isAlone(childlist[i])){
			o = childlist[i];
			this.show(o);
			this.arrActive.push(o.id);
		}
	}
}
jc.mvc.view.widget.menu.base.contract=function(o){
	var childlist = o.childNodes;
	for(var i=0;i<childlist.length;++i){
		if(this.isAlone(childlist[i])){
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
jc.mvc.view.widget.menu.base.item_onActive=function(item){
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
jc.mvc.view.widget.menu.base.item_onDisactive=function(item){
	if(this.t == -1){
		this.t=setTimeout("jc.mvc.view.widget.menu.base.timeout()",500);
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
jc.mvc.view.widget.menu.base.jsobject=function(id){
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
		var p = this.__proto__.constructor.getObjectById(id);
		if(typeof(p)!='undefined'){
			p.addChild(this.id);
		}
	};
	this.registerObject=function(){
		this.__proto__.constructor.objectlist[this.id]=this;
	};
	this.registerObject();
}
jc.mvc.view.widget.menu.base.jsobject.objectlist={};
jc.mvc.view.widget.menu.base.jsobject.getObjectById=function(id){
	return this.objectlist[id];
}
