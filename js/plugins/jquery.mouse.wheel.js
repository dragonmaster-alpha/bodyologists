!function(a){a.event.special.mousewheel={setup:function(){var b=a.event.special.mousewheel.handler;a.browser.mozilla&&a(this).bind("mousemove.mousewheel",function(b){a.data(this,"mwcursorposdata",{pageX:b.pageX,pageY:b.pageY,clientX:b.clientX,clientY:b.clientY})}),this.addEventListener?this.addEventListener(a.browser.mozilla?"DOMMouseScroll":"mousewheel",b,!1):this.onmousewheel=b},teardown:function(){var b=a.event.special.mousewheel.handler;a(this).unbind("mousemove.mousewheel"),this.removeEventListener?this.removeEventListener(a.browser.mozilla?"DOMMouseScroll":"mousewheel",b,!1):this.onmousewheel=function(){},a.removeData(this,"mwcursorposdata")},handler:function(b){var c=Array.prototype.slice.call(arguments,1);b=a.event.fix(b||window.event),a.extend(b,a.data(this,"mwcursorposdata")||{});var d=0;return b.wheelDelta&&(d=b.wheelDelta/120),b.detail&&(d=-b.detail/3),b.data=b.data||{},b.type="mousewheel",c.unshift(d),c.unshift(b),a.event.handle.apply(this,c)}},a.fn.extend({mousewheel:function(a){return a?this.bind("mousewheel",a):this.trigger("mousewheel")},unmousewheel:function(a){return this.unbind("mousewheel",a)}})}(jQuery);