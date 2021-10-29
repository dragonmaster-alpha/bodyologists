window.jQuery&&function(a){if(a.browser.msie)try{document.execCommand("BackgroundImageCache",!1,!0)}catch(b){}a.fn.rating=function(b){if(0==this.length)return this;if("string"==typeof arguments[0]){if(this.length>1){var c=arguments;return this.each(function(){a.fn.rating.apply(a(this),c)})}return a.fn.rating[arguments[0]].apply(this,a.makeArray(arguments).slice(1)||[]),this}var b=a.extend({},a.fn.rating.options,b||{});return a.fn.rating.calls++,this.not(".star-rating-applied").addClass("star-rating-applied").each(function(){var c,d=a(this),e=(this.name||"unnamed-rating").replace(/\[|\]/g,"_").replace(/^\_+|\_+$/g,""),f=a(this.form||document.body),g=f.data("rating");g&&g.call==a.fn.rating.calls||(g={count:0,call:a.fn.rating.calls});var h=g[e];h&&(c=h.data("rating")),h&&c?c.count++:(c=a.extend({},b||{},(a.metadata?d.metadata():a.meta?d.data():null)||{},{count:0,stars:[],inputs:[]}),c.serial=g.count++,h=a('<span class="star-rating-control"/>'),d.before(h),h.addClass("rating-to-be-drawn"),d.attr("disabled")&&(c.readOnly=!0),h.append(c.cancel=a('<div class="rating-cancel"><a title="'+c.cancel+'">'+c.cancelValue+"</a></div>").mouseover(function(){a(this).rating("drain"),a(this).addClass("star-rating-hover")}).mouseout(function(){a(this).rating("draw"),a(this).removeClass("star-rating-hover")}).click(function(){a(this).rating("select")}).data("rating",c)));var i=a('<div class="star-rating rater-'+c.serial+'"><a title="'+(this.title||this.value)+'">'+this.value+"</a></div>");if(h.append(i),this.id&&i.attr("id",this.id),this.className&&i.addClass(this.className),c.half&&(c.split=2),"number"==typeof c.split&&c.split>0){var j=(a.fn.width?i.width():0)||c.starWidth,k=c.count%c.split,l=Math.floor(j/c.split);i.width(l).find("a").css({"margin-left":"-"+k*l+"px"})}c.readOnly?i.addClass("star-rating-readonly"):i.addClass("star-rating-live").mouseover(function(){a(this).rating("fill"),a(this).rating("focus")}).mouseout(function(){a(this).rating("draw"),a(this).rating("blur")}).click(function(){a(this).rating("select")}),this.checked&&(c.current=i),d.hide(),d.change(function(){a(this).rating("select")}),i.data("rating.input",d.data("rating.star",i)),c.stars[c.stars.length]=i[0],c.inputs[c.inputs.length]=d[0],c.rater=g[e]=h,c.context=f,d.data("rating",c),h.data("rating",c),i.data("rating",c),f.data("rating",g)}),a(".rating-to-be-drawn").rating("draw").removeClass("rating-to-be-drawn"),this},a.extend(a.fn.rating,{calls:0,focus:function(){var b=this.data("rating");if(!b)return this;if(!b.focus)return this;var c=a(this).data("rating.input")||a("INPUT"==this.tagName?this:null);b.focus&&b.focus.apply(c[0],[c.val(),a("a",c.data("rating.star"))[0]])},blur:function(){var b=this.data("rating");if(!b)return this;if(!b.blur)return this;var c=a(this).data("rating.input")||a("INPUT"==this.tagName?this:null);b.blur&&b.blur.apply(c[0],[c.val(),a("a",c.data("rating.star"))[0]])},fill:function(){var a=this.data("rating");return a?(a.readOnly||(this.rating("drain"),this.prevAll().andSelf().filter(".rater-"+a.serial).addClass("star-rating-hover")),void 0):this},drain:function(){var a=this.data("rating");return a?(a.readOnly||a.rater.children().filter(".rater-"+a.serial).removeClass("star-rating-on").removeClass("star-rating-hover"),void 0):this},draw:function(){var b=this.data("rating");return b?(this.rating("drain"),b.current?(b.current.data("rating.input").attr("checked","checked"),b.current.prevAll().andSelf().filter(".rater-"+b.serial).addClass("star-rating-on")):a(b.inputs).removeAttr("checked"),b.cancel[b.readOnly||b.required?"hide":"show"](),this.siblings()[b.readOnly?"addClass":"removeClass"]("star-rating-readonly"),void 0):this},select:function(b){var c=this.data("rating");if(!c)return this;if(!c.readOnly){if(c.current=null,"undefined"!=typeof b){if("number"==typeof b)return a(c.stars[b]).rating("select");"string"==typeof b&&a.each(c.stars,function(){a(this).data("rating.input").val()==b&&a(this).rating("select")})}else c.current="INPUT"==this[0].tagName?this.data("rating.star"):this.is(".rater-"+c.serial)?this:null;this.data("rating",c),this.rating("draw");var d=a(c.current?c.current.data("rating.input"):null);c.callback&&c.callback.apply(d[0],[d.val(),a("a",c.current)[0]])}},readOnly:function(b,c){var d=this.data("rating");return d?(d.readOnly=b||void 0==b?!0:!1,c?a(d.inputs).attr("disabled","disabled"):a(d.inputs).removeAttr("disabled"),this.data("rating",d),this.rating("draw"),void 0):this},disable:function(){this.rating("readOnly",!0,!0)},enable:function(){this.rating("readOnly",!1,!1)}}),a.fn.rating.options={cancel:"Cancel Rating",cancelValue:"",split:0,starWidth:16},a(function(){a("input[type=radio].star").rating()})}(jQuery);