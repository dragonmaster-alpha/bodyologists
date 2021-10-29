/* =========================================================
 * bootstrap-timepicker.js
 * http://www.github.com/jdewit/bootstrap-timepicker
 * =========================================================
 * Copyright 2012
 *
 * Created By:
 * Joris de Wit @joris_dewit
 *
 * Contributions By:
 * Gilbert @mindeavor
 * Koen Punt info@koenpunt.nl
 * Nek
 * Chris Martin
 * Dominic Barnes contact@dominicbarnes.us
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ========================================================= */

!function(b){var f=function(a,d){this.$element=b(a);this.options=b.extend({},b.fn.timepicker.defaults,d,this.$element.data());this.minuteStep=this.options.minuteStep||this.minuteStep;this.secondStep=this.options.secondStep||this.secondStep;this.showMeridian=this.options.showMeridian||this.showMeridian;this.showSeconds=this.options.showSeconds||this.showSeconds;this.showInputs=this.options.showInputs||this.showInputs;this.disableFocus=this.options.disableFocus||this.disableFocus;this.template=this.options.template||
this.template;this.modalBackdrop=this.options.modalBackdrop||this.modalBackdrop;this.defaultTime=this.options.defaultTime||this.defaultTime;this.open=!1;this.init()};f.prototype={constructor:f,init:function(){if(this.$element.parent().hasClass("input-append"))this.$element.parent(".input-append").find(".add-on").on("click",b.proxy(this.showWidget,this)),this.$element.on({focus:b.proxy(this.highlightUnit,this),click:b.proxy(this.highlightUnit,this),keypress:b.proxy(this.elementKeypress,this),blur:b.proxy(this.blurElement,
this)});else if(this.template)this.$element.on({focus:b.proxy(this.showWidget,this),click:b.proxy(this.showWidget,this),blur:b.proxy(this.blurElement,this)});else this.$element.on({focus:b.proxy(this.highlightUnit,this),click:b.proxy(this.highlightUnit,this),keypress:b.proxy(this.elementKeypress,this),blur:b.proxy(this.blurElement,this)});this.$widget=b(this.getTemplate()).appendTo("body");this.$widget.on("click",b.proxy(this.widgetClick,this));if(this.showInputs)this.$widget.find("input").on({click:function(){this.select()},
keypress:b.proxy(this.widgetKeypress,this),change:b.proxy(this.updateFromWidgetInputs,this)});this.setDefaultTime(this.defaultTime)},showWidget:function(a){a.stopPropagation();a.preventDefault();if(!this.open){this.$element.trigger("show");this.disableFocus&&this.$element.blur();a=b.extend({},this.$element.offset(),{height:this.$element[0].offsetHeight});this.updateFromElementVal();b("html").trigger("click.timepicker.data-api").one("click.timepicker.data-api",b.proxy(this.hideWidget,this));if("modal"===
this.template)this.$widget.modal("show").on("hidden",b.proxy(this.hideWidget,this));else this.$widget.css({top:a.top+a.height,left:a.left}),this.open||this.$widget.addClass("open");this.open=!0;this.$element.trigger("shown")}},hideWidget:function(){this.$element.trigger("hide");"modal"===this.template?this.$widget.modal("hide"):this.$widget.removeClass("open");this.open=!1;this.$element.trigger("hidden")},widgetClick:function(a){a.stopPropagation();a.preventDefault();if(a=b(a.target).closest("a").data("action"))this[a](),
this.update()},widgetKeypress:function(a){var d=b(a.target).closest("input").attr("name");switch(a.keyCode){case 9:this.showMeridian?"meridian"==d&&this.hideWidget():this.showSeconds?"second"==d&&this.hideWidget():"minute"==d&&this.hideWidget();break;case 27:this.hideWidget();break;case 38:switch(d){case "hour":this.incrementHour();break;case "minute":this.incrementMinute();break;case "second":this.incrementSecond();break;case "meridian":this.toggleMeridian()}this.update();break;case 40:switch(d){case "hour":this.decrementHour();
break;case "minute":this.decrementMinute();break;case "second":this.decrementSecond();break;case "meridian":this.toggleMeridian()}this.update()}},elementKeypress:function(a){this.$element.get(0);switch(a.keyCode){case 9:this.updateFromElementVal();this.showMeridian?"meridian"!=this.highlightedUnit&&(a.preventDefault(),this.highlightNextUnit()):this.showSeconds?"second"!=this.highlightedUnit&&(a.preventDefault(),this.highlightNextUnit()):"minute"!=this.highlightedUnit&&(a.preventDefault(),this.highlightNextUnit());
break;case 27:this.updateFromElementVal();break;case 37:this.updateFromElementVal();this.highlightPrevUnit();break;case 38:switch(this.highlightedUnit){case "hour":this.incrementHour();break;case "minute":this.incrementMinute();break;case "second":this.incrementSecond();break;case "meridian":this.toggleMeridian()}this.updateElement();break;case 39:this.updateFromElementVal();this.highlightNextUnit();break;case 40:switch(this.highlightedUnit){case "hour":this.decrementHour();break;case "minute":this.decrementMinute();
break;case "second":this.decrementSecond();break;case "meridian":this.toggleMeridian()}this.updateElement()}0!==a.keyCode&&(8!==a.keyCode&&9!==a.keyCode&&46!==a.keyCode)&&a.preventDefault()},setValues:function(a){if(this.showMeridian){var a=a.split(" "),b=a[0].split(":");this.meridian=a[1]}else b=a.split(":");this.hour=parseInt(b[0],10);this.minute=parseInt(b[1],10);this.second=parseInt(b[2],10);isNaN(this.hour)&&(this.hour=0);isNaN(this.minute)&&(this.minute=0);if(this.showMeridian){12<this.hour?
this.hour=12:1>this.hour&&(this.hour=1);if("am"==this.meridian||"a"==this.meridian)this.meridian="AM";else if("pm"==this.meridian||"p"==this.meridian)this.meridian="PM";"AM"!=this.meridian&&"PM"!=this.meridian&&(this.meridian="AM")}else 24<=this.hour?this.hour=23:0>this.hour&&(this.hour=0);0>this.minute?this.minute=0:60<=this.minute&&(this.minute=59);this.showSeconds&&(isNaN(this.second)?this.second=0:0>this.second?this.second=0:60<=this.second&&(this.second=59));this.updateElement();this.updateWidget()},
setMeridian:function(a){"a"==a||"am"==a||"AM"==a?this.meridian="AM":"p"==a||"pm"==a||"PM"==a?this.meridian="PM":this.updateWidget();this.updateElement()},setDefaultTime:function(a){if(a){if("current"===a){var b=new Date,a=b.getHours(),c=Math.floor(b.getMinutes()/this.minuteStep)*this.minuteStep,b=Math.floor(b.getSeconds()/this.secondStep)*this.secondStep,e="AM";this.showMeridian&&(0===a?a=12:12<=a?(12<a&&(a-=12),e="PM"):e="AM");this.hour=a;this.minute=c;this.second=b;this.meridian=e}else"value"===
a?this.setValues(this.$element.val()):this.setValues(a);this.update()}else this.second=this.minute=this.hour=0},formatTime:function(a,b,c,e){return(10>a?"0"+a:a)+":"+(10>b?"0"+b:b)+(this.showSeconds?":"+(10>c?"0"+c:c):"")+(this.showMeridian?" "+e:"")},getTime:function(){return this.formatTime(this.hour,this.minute,this.second,this.meridian)},setTime:function(a){this.setValues(a);this.update()},update:function(){this.updateElement();this.updateWidget()},blurElement:function(){this.highlightedUnit=
void 0;this.updateFromElementVal()},updateElement:function(){var a=this.getTime();this.$element.val(a).change();switch(this.highlightedUnit){case "hour":this.highlightHour();break;case "minute":this.highlightMinute();break;case "second":this.highlightSecond();break;case "meridian":this.highlightMeridian()}},updateWidget:function(){this.showInputs?(this.$widget.find("input.bootstrap-timepicker-hour").val(10>this.hour?"0"+this.hour:this.hour),this.$widget.find("input.bootstrap-timepicker-minute").val(10>
this.minute?"0"+this.minute:this.minute),this.showSeconds&&this.$widget.find("input.bootstrap-timepicker-second").val(10>this.second?"0"+this.second:this.second),this.showMeridian&&this.$widget.find("input.bootstrap-timepicker-meridian").val(this.meridian)):(this.$widget.find("span.bootstrap-timepicker-hour").text(this.hour),this.$widget.find("span.bootstrap-timepicker-minute").text(10>this.minute?"0"+this.minute:this.minute),this.showSeconds&&this.$widget.find("span.bootstrap-timepicker-second").text(10>
this.second?"0"+this.second:this.second),this.showMeridian&&this.$widget.find("span.bootstrap-timepicker-meridian").text(this.meridian))},updateFromElementVal:function(){var a=this.$element.val();a&&(this.setValues(a),this.updateWidget())},updateFromWidgetInputs:function(){var a=b("input.bootstrap-timepicker-hour",this.$widget).val()+":"+b("input.bootstrap-timepicker-minute",this.$widget).val()+(this.showSeconds?":"+b("input.bootstrap-timepicker-second",this.$widget).val():"")+(this.showMeridian?
" "+b("input.bootstrap-timepicker-meridian",this.$widget).val():"");this.setValues(a)},getCursorPosition:function(){var a=this.$element.get(0);if("selectionStart"in a)return a.selectionStart;if(document.selection){a.focus();var b=document.selection.createRange(),c=document.selection.createRange().text.length;b.moveStart("character",-a.value.length);return b.text.length-c}},highlightUnit:function(){this.$element.get(0);this.position=this.getCursorPosition();0<=this.position&&2>=this.position?this.highlightHour():
3<=this.position&&5>=this.position?this.highlightMinute():6<=this.position&&8>=this.position?this.showSeconds?this.highlightSecond():this.highlightMeridian():9<=this.position&&11>=this.position&&this.highlightMeridian()},highlightNextUnit:function(){switch(this.highlightedUnit){case "hour":this.highlightMinute();break;case "minute":this.showSeconds?this.highlightSecond():this.highlightMeridian();break;case "second":this.highlightMeridian();break;case "meridian":this.highlightHour()}},highlightPrevUnit:function(){switch(this.highlightedUnit){case "hour":this.highlightMeridian();
break;case "minute":this.highlightHour();break;case "second":this.highlightMinute();break;case "meridian":this.showSeconds?this.highlightSecond():this.highlightMinute()}},highlightHour:function(){this.highlightedUnit="hour";this.$element.get(0).setSelectionRange(0,2)},highlightMinute:function(){this.highlightedUnit="minute";this.$element.get(0).setSelectionRange(3,5)},highlightSecond:function(){this.highlightedUnit="second";this.$element.get(0).setSelectionRange(6,8)},highlightMeridian:function(){this.highlightedUnit=
"meridian";this.showSeconds?this.$element.get(0).setSelectionRange(9,11):this.$element.get(0).setSelectionRange(6,8)},incrementHour:function(){if(this.showMeridian)if(11===this.hour)this.toggleMeridian();else if(12===this.hour)return this.hour=1;if(23===this.hour)return this.hour=0;this.hour+=1},decrementHour:function(){if(this.showMeridian){if(1===this.hour)return this.hour=12;12===this.hour&&this.toggleMeridian()}if(0===this.hour)return this.hour=23;this.hour-=1},incrementMinute:function(){var a=
this.minute+this.minuteStep-this.minute%this.minuteStep;59<a?(this.incrementHour(),this.minute=a-60):this.minute=a},decrementMinute:function(){var a=this.minute-this.minuteStep;0>a?(this.decrementHour(),this.minute=a+60):this.minute=a},incrementSecond:function(){var a=this.second+this.secondStep-this.second%this.secondStep;59<a?(this.incrementMinute(),this.second=a-60):this.second=a},decrementSecond:function(){var a=this.second-this.secondStep;0>a?(this.decrementMinute(),this.second=a+60):this.second=
a},toggleMeridian:function(){this.meridian="AM"===this.meridian?"PM":"AM";this.update()},getTemplate:function(){if(this.options.templates[this.options.template])return this.options.templates[this.options.template];if(this.showInputs)var a='<input type="text" name="hour" class="bootstrap-timepicker-hour" maxlength="2"/>',b='<input type="text" name="minute" class="bootstrap-timepicker-minute" maxlength="2"/>',c='<input type="text" name="second" class="bootstrap-timepicker-second" maxlength="2"/>',e=
'<input type="text" name="meridian" class="bootstrap-timepicker-meridian" maxlength="2"/>';else a='<span class="bootstrap-timepicker-hour"></span>',b='<span class="bootstrap-timepicker-minute"></span>',c='<span class="bootstrap-timepicker-second"></span>',e='<span class="bootstrap-timepicker-meridian"></span>';var a='<table class="'+(this.showSeconds?"show-seconds":"")+" "+(this.showMeridian?"show-meridian":"")+'"><tr><td><a href="#" data-action="incrementHour"><b class="icon-arrow-up "></b></a></td><td class="separator">&nbsp;</td><td><a href="#" data-action="incrementMinute"><b class="icon-arrow-up "></b></a></td>'+
(this.showSeconds?'<td class="separator">&nbsp;</td><td><a href="#" data-action="incrementSecond"><b class="icon-arrow-up"></b></a></td>':"")+(this.showMeridian?'<td class="separator">&nbsp;</td><td class="meridian-column"><a href="#" data-action="toggleMeridian"><b class="icon-arrow-up"></b></a></td>':"")+"</tr><tr><td>"+a+'</td> <td class="separator">:</td><td>'+b+"</td> "+(this.showSeconds?'<td class="separator">:</td><td>'+c+"</td>":"")+(this.showMeridian?'<td class="separator">&nbsp;</td><td>'+
e+"</td>":"")+'</tr><tr><td><a href="#" data-action="decrementHour"><b class="icon-arrow-down "></b></a></td><td class="separator"></td><td><a href="#" data-action="decrementMinute"><b class="icon-arrow-down"></b></a></td>'+(this.showSeconds?'<td class="separator">&nbsp;</td><td><a href="#" data-action="decrementSecond"><b class="icon-arrow-down"></b></a></td>':"")+(this.showMeridian?'<td class="separator">&nbsp;</td><td><a href="#" data-action="toggleMeridian"><b class="icon-arrow-down"></b></a></td>':
"")+"</tr></table>",f;switch(this.options.template){case "modal":f='<div class="bootstrap-timepicker modal hide fade in" style="top: 30%; margin-top: 0; width: 200px; margin-left: -100px;" data-backdrop="'+(this.modalBackdrop?"true":"false")+'"><div class="modal-header"><a href="#" class="close" data-dismiss="modal">\u00d7</a><h3>Pick a Time</h3></div><div class="modal-content">'+a+'</div><div class="modal-footer"><a href="#" class="btn btn-primary" data-dismiss="modal">Ok</a></div></div>';break;
case "dropdown":f='<div class="bootstrap-timepicker dropdown-menu">'+a+"</div>"}return f}};b.fn.timepicker=function(a){return this.each(function(){var d=b(this),c=d.data("timepicker"),e="object"==typeof a&&a;c||d.data("timepicker",c=new f(this,e));if("string"==typeof a)c[a]()})};b.fn.timepicker.defaults={minuteStep:15,secondStep:15,disableFocus:!1,defaultTime:"current",showSeconds:!1,showInputs:!0,showMeridian:!0,template:"dropdown",modalBackdrop:!1,templates:{}};b.fn.timepicker.Constructor=f}(window.jQuery);