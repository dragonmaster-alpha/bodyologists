include('js/plugins/jquery.slimscroll.js');
include('js/plugins/jquery.uniform.min.js');
include('js/plugins/jquery.validate.min.js');
include('js/plugins/jquery.tag-it.js');
include('js/plugins/jquery.charCount.js');
include('js/plugins/jquery.chosen.min.js');
include('js/plugins/jquery.dataTables.min.js');
include('js/plugins/jquery.tipsy.js');
include('js/plugins/jquery.alerts.js');
include('js/plugins/jquery.combo.js');
include('js/plugins/jquery.colorbox-min.js');
include('js/plugins/jquery.ajax.form.js');
include('js/plugins/jquery.jgrowl.js');
include('js/plugins/bootstrap.min.js');
include('js/plugins/jquery.bootstrap.datepicker.js');
include('js/plugins/jquery.bootstrap.timepicker.js');
include('js/plugins/jquery.currency.format.js');
include('js/plugins/jquery.maxlength.min.js');
include('js/plugins/tinymce/tinymce.min.js');
include('js/plugins/tinymce/plugins/moxiemanager/js/moxman.loader.min.js');

// INCLUDE FUNCTION //
function include(url)
{ 
  document.write('<script src="' + url + '" type="text/javascript"></script>'); 
}

// START JQUERY DECLARATIONS //
jQuery.noConflict();
jQuery(document).ready(function(){
								
	///// SHOW/HIDE USERDATA WHEN USERINFO IS CLICKED ///// 
	jQuery('.userinfo').click(function()
    {
		if(!jQuery(this).hasClass('active')) 
        {
			jQuery('.userinfodrop').fadeIn();
			jQuery(this).addClass('active');
		} 
        else 
        {
			jQuery('.userinfodrop').hide();
			jQuery(this).removeClass('active');
		}
		//remove notification box if visible
		jQuery('.notification').removeClass('active');
		jQuery('.noticontent').remove();
		return false;
	});
    
	///// TABLES /////
	jQuery('.stdtable, .dttable').dataTable({
        "sDom": '<"top"><"bottom"><"clear">',
        "aaSorting": [],
        "liveDrag":true,
        // "bStateSave": true, // Saves the sorting stage of the table in localStorage
        'iDisplayLength': 999999999
	});

	if(jQuery('.stdtable').length) 
	{
		var reload_url = jQuery('.stdtable').attr('data-reorder-url');
	}

    jQuery(".stdtable tbody").sortable({
        update: function (e, ui) 
        {
			var order = jQuery('.stdtable tbody').sortable('serialize'); 
            jQuery.get(reload_url + '/reorder?' + order); 
		}
    }).disableSelection();
    
	jQuery('.dataTables_filter input').attr("placeholder", "Search");

	jQuery('.regtable').dataTable({
        "sDom": '<"top">t<"bottom"><"clear">',
        "aaSorting": [],
        "iDisplayLength": 100
	});

    ///// DROP DOWN /////
    jQuery('a.dropdown').click(function () 
    {
		jQuery('.dropdown-toggle').slideToggle(200);
        return false;
	});

	///// SHOW/HIDE NOTIFICATION /////
	jQuery('.notification a').click(function()
    {
		var t = jQuery(this);
		var url = t.attr('href');
		if(!jQuery('.noticontent').is(':visible')) 
        {
			jQuery.post(url,function(data)
            {
				t.parent().append('<div class="noticontent">'+data+'</div>');
			});
			//this will hide user info drop down when visible
			jQuery('.userinfo').removeClass('active');
			jQuery('.userinfodrop').hide();
		} 
        else 
        {
			t.parent().removeClass('active');
			jQuery('.noticontent').hide();
		}
		return false;
	});

	///// SHOW/HIDE BOTH NOTIFICATION & USERINFO WHEN CLICKED OUTSIDE OF THIS ELEMENT /////
	jQuery(document).click(function(event) 
    {
		var ud = jQuery('.userinfodrop');
		var nb = jQuery('.noticontent');
		//hide user drop menu when clicked outside of this element
		if(!jQuery(event.target).is('.userinfodrop') && !jQuery(event.target).is('.userdata') && ud.is(':visible')) 
        {
			ud.hide();
			jQuery('.userinfo').removeClass('active');
        }
		//hide notification box when clicked outside of this element
		if(!jQuery(event.target).is('.noticontent') && nb.is(':visible')) 
        {
			nb.remove();
			jQuery('.notification').removeClass('active');
		}
	});

	///// NOTIFICATION CONTENT /////
	jQuery('.notitab a').live('click', function()
    {
		var id = jQuery(this).attr('href');
		jQuery('.notitab li').removeClass('current'); //reset current 
		jQuery(this).parent().addClass('current');
		if(id == '#messages')
        {
			jQuery('#activities').hide();
		}
        else 
        {
			jQuery('#messages').hide();
		}	
		jQuery(id).fadeIn();
		return false;
	});

	///// SHOW/HIDE VERTICAL SUB MENU /////	
	jQuery('.vernav > ul li a, .vernav2 > ul li a').each(function()
    {
		var url = jQuery(this).attr('href');
		jQuery(this).click(function()
        {
			if(jQuery(url).length > 0) 
            {
				if(jQuery(url).is(':visible')) 
                {
					if(!jQuery(this).parents('div').hasClass('menucoll') && !jQuery(this).parents('div').hasClass('menucoll2'))
					{
						jQuery(url).slideUp();
					}
				} 
                else 
                {
					jQuery('.vernav ul ul, .vernav2 ul ul').each(function()
                    {
						jQuery(this).slideUp();
					});
					if(!jQuery(this).parents('div').hasClass('menucoll') && !jQuery(this).parents('div').hasClass('menucoll2')) 
                    {
						jQuery(url).slideDown();
                    }
				}
				return false;	
			}
		});
	});

	///// SHOW/HIDE SUB MENU WHEN MENU COLLAPSED /////
	jQuery('.menucoll > ul > li, .menucoll2 > ul > li').live('mouseenter mouseleave',function(e)
    {
		if(e.type == 'mouseenter') 
        {
			jQuery(this).addClass('hover');
			jQuery(this).find('ul').show();	
		} 
        else 
        {
			jQuery(this).removeClass('hover').find('ul').hide();	
		}
	});

	///// HORIZONTAL NAVIGATION (AJAX/INLINE DATA) /////	
	jQuery('.hornav a').click(function()
    {
		//this is only applicable when window size below 450px
		if(jQuery(this).parents('.more').length == 0) 
		{
			jQuery('.hornav li.more ul').hide();
		}
		//remove current menu
		jQuery('.hornav li').each(function()
        {
			jQuery(this).removeClass('current');
		});
		
		jQuery(this).parent().addClass('current');	// set as current menu
		var url = jQuery(this).attr('href');
		if(jQuery(url).length > 0) 
        {
			jQuery('.contentwrapper .subcontent').hide();
			jQuery(url).show();
		} 
        else 
        {
			jQuery.post(url, function(data)
            {
				jQuery('#contentwrapper').html(data);
				jQuery('.stdtable input:checkbox').uniform();	//restyling checkbox
			});
		}
		return false;
	});

	///// SEARCH BOX WITH AUTOCOMPLETE /////
	var availableTags = [
		"ActionScript",
		"AppleScript",
		"Asp",
		"BASIC",
		"C",
		"C++",
		"Clojure",
		"COBOL",
		"ColdFusion",
		"Erlang",
		"Fortran",
		"Groovy",
		"Haskell",
		"Java",
		"JavaScript",
		"Lisp",
		"Perl",
		"PHP",
		"Python",
		"Ruby",
		"Scala",
		"Scheme"
	];
    
	jQuery('#keyword').autocomplete({
		source: availableTags
	});

	///// SEARCH BOX ON FOCUS /////
	jQuery('#keyword').bind('focusin focusout', function(e)
    {
		var t = jQuery(this);
		if(e.type == 'focusin' && t.val() == 'Enter keyword(s)') 
        {
			t.val('');
		} 
        else if(e.type == 'focusout' && t.val() == '') 
        {
			t.val('Enter keyword(s)');	
		}
	});

	///// NOTIFICATION CLOSE BUTTON /////
	jQuery('.notibar .close').click(function()
    {
		jQuery(this).parent().fadeOut(function()
        {
			jQuery(this).remove();
		});
	});

	///// COLLAPSED/EXPAND LEFT MENU /////
	jQuery('.togglemenu').click(function() 
	{
		if(!jQuery(this).hasClass('togglemenu_collapsed')) 
        {
			//if(jQuery('.iconmenu').hasClass('vernav')) {
			if(jQuery('.vernav').length > 0) 
            {
				if(jQuery('.vernav').hasClass('iconmenu')) 
                {
					jQuery('body').addClass('withmenucoll');
					jQuery('.iconmenu').addClass('menucoll');
				} 
                else 
                {
					jQuery('body').addClass('withmenucoll');
					jQuery('.vernav').addClass('menucoll').find('ul').hide();
				}
			} 
            else if(jQuery('.vernav2').length > 0) 
            {
				jQuery('body').addClass('withmenucoll2');
				jQuery('.iconmenu').addClass('menucoll2');
			}
			
			jQuery(this).addClass('togglemenu_collapsed');
			
			jQuery('.iconmenu > ul > li > a').each(function()
            {
				var label = jQuery(this).text();
				jQuery('<li><span>'+label+'</span></li>').insertBefore(jQuery(this).parent().find('ul li:first-child'));
			});
		} 
        else 
        {
			//if(jQuery('.iconmenu').hasClass('vernav')) {
			if(jQuery('.vernav').length > 0) 
            {
				if(jQuery('.vernav').hasClass('iconmenu')) 
                {
					jQuery('body').removeClass('withmenucoll');
					jQuery('.iconmenu').removeClass('menucoll');
				} 
                else 
                {
					jQuery('body').removeClass('withmenucoll');
					jQuery('.vernav').removeClass('menucoll').find('ul').show();
				}
			} 
            else if(jQuery('.vernav2').length > 0) 
            {
				jQuery('body').removeClass('withmenucoll2');
				jQuery('.iconmenu').removeClass('menucoll2');
			}
			jQuery(this).removeClass('togglemenu_collapsed');	
			jQuery('.iconmenu ul ul li:first-child').remove();
		}
	});

	///// RESPONSIVE /////
	if(jQuery(document).width() < 640) 
    {
		jQuery('.togglemenu').addClass('togglemenu_collapsed');
		if(jQuery('.vernav').length > 0) 
        {
			jQuery('.iconmenu').addClass('menucoll');
			jQuery('body').addClass('withmenucoll');
			jQuery('.centercontent').css({
				marginLeft: '56px'
			});
			if(jQuery('.iconmenu').length == 0) 
            {
				jQuery('.togglemenu').removeClass('togglemenu_collapsed');
			} 
            else 
            {
				jQuery('.iconmenu > ul > li > a').each(function()
                {
					var label = jQuery(this).text();
					jQuery('<li><span>'+label+'</span></li>').insertBefore(jQuery(this).parent().find('ul li:first-child'));
				});		
			}

		} 
        else 
        {
			jQuery('.iconmenu').addClass('menucoll2');
			jQuery('body').addClass('withmenucoll2');
			jQuery('.centercontent').css({
				marginLeft: '36px'
			});
			jQuery('.iconmenu > ul > li > a').each(function()
            {
				var label = jQuery(this).text();
				jQuery('<li><span>'+label+'</span></li>').insertBefore(jQuery(this).parent().find('ul li:first-child'));
			});		
		}
	}

	jQuery('.searchicon').live('click',function()
    {
		jQuery('.searchinner').show();
	});
	
	jQuery('.searchcancel').live('click',function()
    {
		jQuery('.searchinner').hide();
	});

	///// ON LOAD WINDOW /////
	function reposSearch() 
    {
		if(jQuery(window).width() < 520) 
        {
			if(jQuery('.searchinner').length == 0) 
            {
				jQuery('.search').wrapInner('<div class="searchinner"></div>');	
				jQuery('<a class="searchicon"></a>').insertBefore(jQuery('.searchinner'));
				jQuery('<a class="searchcancel"></a>').insertAfter(jQuery('.searchinner button'));
			}
		} 
        else 
        {
			if(jQuery('.searchinner').length > 0) 
            {
				jQuery('.search form').unwrap();
				jQuery('.searchicon, .searchcancel').remove();
			}
		}
	}
	reposSearch();
	
	///// ON RESIZE WINDOW /////
	jQuery(window).resize(function()
    {
		if(jQuery(window).width() > 640) 
        {
			jQuery('.centercontent').removeAttr('style');
        }
		reposSearch();
	});

	///// CHANGE THEME /////
	jQuery('.changetheme a').click(function()
    {
		var c = jQuery(this).attr('class');
		if(jQuery('#addonstyle').length == 0) 
        {
			if(c != 'default') 
            {
				jQuery('head').append('<link id="addonstyle" rel="stylesheet" href="css/style.'+c+'.css" type="text/css" />');
				jQuery.cookie("addonstyle", c, { path: '/' });
			}
		} 
        else 
        {
			if(c != 'default') 
            {
				jQuery('#addonstyle').attr('href','css/style.'+c+'.css');
				jQuery.cookie("addonstyle", c, { path: '/' });
			} 
            else 
            {
				jQuery('#addonstyle').remove();	
				jQuery.cookie("addonstyle", null);
			}
		}
	});

	///// LOAD ADDON STYLE WHEN IT'S ALREADY SET /////
	if(jQuery.cookie('addonstyle')) 
    {
		var c = jQuery.cookie('addonstyle');
		if(c != '') 
        {
			jQuery('head').append('<link id="addonstyle" rel="stylesheet" href="css/style.'+c+'.css" type="text/css" />');
			jQuery.cookie("addonstyle", c, { path: '/' });
		}
	}
	
	///// LOAD COMBO AS NEEDED /////
    jQuery('.combo').simpleCombo();

    if(jQuery('.submit-top').length > 0)
    {
        jQuery('.stdform').append('<div id="tool-bar"></div>');
        jQuery("#tool-bar").html(jQuery('.submit-top').html());
        jQuery("#tool-bar").hide();
    	jQuery(window).scroll(function () {
    		if(jQuery(this).scrollTop() > 170) {
    			jQuery('#tool-bar').slideDown();
    		} else {
    			jQuery('#tool-bar').slideUp();
    		}
    	});
    }    

    function parse_url()
    {
        var vars = [], hash;
        var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
        for(var i = 0; i < hashes.length; i++)
        {
            hash = hashes[i].split('=');
            vars.push(hash[0]);
            vars[hash[0]] = hash[1];
        }
        return vars;
    }
    jQuery('form').append('<input type="hidden" name="token" value="' + token + '" />');
});