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
jc.mvc.view.widget.menu.base.show=function(o){
	o.style.display="block";
}
jc.mvc.view.widget.menu.base.hide=function(o){
	o.style.display="none";
}
jc.mvc.view.widget.menu.base.isShow=function(o){
	return this.getStyle(o,'display')== "block";
}
jc.mvc.view.widget.menu.base.expand=function(o){
	console.log(o);
	var childlist = o.childNodes;
	for(var i=0;i<childlist.length;++i){
		console.log("className="+childlist[i].className);
		if(childlist[i].className && childlist[i].className.match('jc-mvc-view-widget-menu-menu-alone')){
			var o = childlist[i];
			if(this.isShow(o)){
				this.hide(o);
			}else{
				this.show(o);
			}
		}
	}
}
jc.mvc.view.widget.menu.base.item_onActive=function(item){
	this.expand(item);
	this.stopBubble(event);
}
jc.mvc.view.widget.menu.base.stopBubble=function(e) {  
    var e = e ? e : window.event;
    if (window.event) { // IE
        e.cancelBubble = true;
    } else { // FF
        e.stopPropagation();
    }
}
