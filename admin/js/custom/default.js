jQuery(document).ready(function(){
    jQuery('input:checkbox, input:radio, select.uniformselect, select.state, select.country').uniform();
    jQuery('.tags').tagit();
    jQuery('#tabs').tabs();
    jQuery('.scroll').slimscroll({
        color: '#666',
        size: '8px',
        width: 'auto',
        height: '200px'                  
    });
    ///// SELECT WITH SEARCH /////
    if(jQuery(".chzn-select").length) {
        jQuery(".chzn-select").chosen({
            width: '100%', 
            no_results_text: 'Oops, nothing here!', 
            disable_search_threshold: 10
        });
    }
    if(jQuery(".select-search").length) {
        jQuery(".select-search").chosen({
            width: '100%', 
            no_results_text: 'Oops, nothing here!'
        });
    }
    if(jQuery(".text-counter").length) {
        jQuery(".text-counter").charCount({
    		allowed: 120,		
    		warning: 20,
    		counterText: 'Characters left: '	
    	});
    }
    if(jQuery('.validate-form').length > 0) {
        jQuery('.validate-form').validate({
            ignore: ':hidden',
            invalidHandler: function(form, validator) {
                var errors = validator.numberOfInvalids();
                if (errors) {
                    jAlert('There are some errors on the provided information. Please check it and try again.<br />All errors has being <b style="color: red;">highlighted</b> for easier recognition.', 'Errors');
                }
            }
        });
    }
    ///// Submit form id Ctr+S is hit /////
    if(jQuery('.stdform').length) {
        jQuery(window).bind('keydown', function(event) {
            if (event.ctrlKey || event.metaKey) {
                switch (String.fromCharCode(event.which).toLowerCase()) {
                    case 's':
                        event.preventDefault();
                        jQuery('#save_and').val(2);
                        jQuery('.stdform').submit();
                    break;
                }
            }
        });
    }
    jQuery('#title-area').live('keyup', function() {
        var title_val = jQuery('#title-area').val();
        var url_val = title_val.replace(/[^a-z0-9\s]/gi, '').replace(/[_\s]/g, '-').substring(0,50);
        jQuery('#url-area').val(url_val).prop('placeholder', url_val);
        jQuery('.google-url span').text(url_val);
        jQuery('#page-title-area').val(title_val.substring(0,64)).prop('placeholder', title_val.substring(0,64));
        jQuery('.google-title').text(title_val.substring(0,64));
    });
    jQuery('[data-mirror="entrance"]').live('keyup', function(e) {
        var holder = jQuery(this).data('image');
        var val = jQuery(this).val();
        var key = event.keyCode || event.which;
        if (key !== 13) {
            jQuery('.' + holder).text(val);
        }
        return false;
    });
    jQuery('#title-area, #url-area').live('blur', function() {
        jQuery.get('admin.php?plugin=system&file=settings&op=check_url&url=' + jQuery('#url-area').val().toLowerCase(), function(a){
            if (a.answer == 'error') {
                jQuery('#url-area').css('border-color', 'red');
                jQuery('#url-check-area').html(a.message);
            } else {
                jQuery('#url-area').css('border-color', '');
                jQuery('#url-check-area').html('');
            }
        });
    });
    ///// DUAL BOX /////
    var db     = jQuery('#dualselect').find('.ds_arrow .arrow');	//get arrows of dual select
	var sel1   = jQuery('#dualselect select:first-child');		    //get first select element
	var sel2   = jQuery('#dualselect select:last-child');			//get second select element
	sel2.empty(); //empty it first from dom.
	db.click(function() {
		var t = (jQuery(this).hasClass('ds_prev'))? 0 : 1;	// 0 if arrow prev otherwise arrow next
		if(t) {
			sel1.find('option').each(function() {
				if(jQuery(this).is(':selected')) {
					jQuery(this).attr('selected',false);
					var op = sel2.find('option:first-child');
					sel2.append(jQuery(this));
				}
			});	
		} else {
			sel2.find('option').each(function() {
				if(jQuery(this).is(':selected')) {
					jQuery(this).attr('selected',false);
					sel1.append(jQuery(this));
				}
			});		
		}
	});
	///// DATE / TIME PICKER /////
	jQuery( "#datepickfrom, #datepickto, .datepick" ).datepicker();
    jQuery( ".timepick" ).timepicker();
    ///// FORMAT CURRENCY /////
    jQuery('.price-field').live('blur', function() {
        var value = jQuery(this).val();
        value = isNaN(value) || value === '' || value === null ? 0.00 : value;
        jQuery(this).val(parseFloat(value).toFixed(2));
    });
    jQuery('.number-field').live('blur', function() {
        var value = jQuery(this).val();
        value = isNaN(value) || value === '' || value === null ? 0 : value;
        jQuery(this).val(parseFloat(value).toFixed(0));
    });
	///// SLIM SCROLL /////
	jQuery('#scroll1').slimscroll({
		color: '#666',
		size: '10px',
		width: 'auto',
		height: '175px'                  
	});
	///// ACCORDION /////
	jQuery('#accordion').accordion({autoHeight:  false});
    ///// TOOLTIPS /////
	jQuery('.tipN').tipsy({
        gravity: 'n',
        fade: true, 
        html:true
    });
	jQuery('.tipS').tipsy({
        gravity: 's',
        fade: true, 
        html:true
    });
	jQuery('.tipW').tipsy({
        gravity: 'w',
        fade: true, 
        html:true
    });
	jQuery('.tipE').tipsy({
        gravity: 'e',
        fade: true, 
        html:true
    });
    jQuery("#mass-print").submit(function (e) {
        var mass_print = [];
        var op = jQuery(':input[name="op"]').val();
        jQuery('input[type="checkbox"]:checked').each(function () {
            mass_print.push(jQuery(this).val());
        });
        window.open('admin.php?plugin=orders&op=' + op + '&ids=' + mass_print.toString().split(',').join('|'), 'invoices', 'width=1000, height=700, resizable=1');
        e.preventDefault();
    });
    ///// MODAL ALERT BOXES /////
	jQuery('.alertboxbutton').click(function() {
		jAlert('This is a custom alert box', 'Alert Dialog');
		return false;
	});
    jQuery('.deletion').live('click', function() {
        var ajax_url        = jQuery(this).attr('href');
        var main_parent     = jQuery(this).closest('tr');
		jConfirm('Are you sure you want to delete this item?', 'Confirm Deletion', function(r) {
            if(r) {
                jQuery.ajax({
                    url: ajax_url,
                    type: "get",
                    dataType: "json",
                    cache: true,
                    crossDomain: true,
                    success: function (a) {
                        if (a.answer == 'error') {
                            jAlert('ERROR: ' + a.message, 'There was an error');
                        } else {
                            main_parent.css('background-color', '#FFB3B3').fadeOut('slow');
                        }
                    }
                });
			}
		});
		return false;
	});
    jQuery('.run-background').live('click', function(){
        var ajax_url        = jQuery(this).attr('href');
        var main_parent     = jQuery(this).parent().parent();
        var question        = jQuery(this).attr('data-question');
        jConfirm(question, 'Confirm', function(r) {
            if(r) {
                jQuery.ajax({
                    url: ajax_url,
                    type: "get",
                    dataType: "json",
                    cache: true,
                    crossDomain: true,
                    success: function (a) {
                        if (a.answer == 'error') 
                        {
                            jAlert('ERROR: ' + a.message, 'There was an error');
                        } 
                        else 
                        {
                            main_parent.css('background-color', '#FFB3B3').fadeOut('slow');
                        }
                    }
                });
            }
        });
        return false;
    });
	jQuery('.confirmbutton').click(function() {
        var confirm_title 	= jQuery(this).attr('title');
        var confirm_title 	= jQuery(this).attr('description');
		jConfirm('Can you confirm this?', 'Confirmation Dialog', function(r) {
			jAlert('Confirmed: ' + r, 'Confirmation Results');
		});
		return false;
	});
	jQuery('.need-confirm').live('click', function() {
		var el 				= jQuery(this);
		var ajax_url        = el.attr('href');
        var dialog_title    = el.attr('title');
        var dialog_content  = el.attr('data-desc');
		jConfirm(dialog_content, dialog_title, function(r) {
			if(r) {
                jQuery.ajax({
                    url: ajax_url,
                    type: "get",
                    dataType: "json",
                    cache: true,
                    crossDomain: true,
                    success: function (a) {
                        if (a.answer == 'error') {
                            jAlert('ERROR: ' + a.message, 'There was an error');
                        } else {
                            el.hide();
                        }
                    },
                    error: function (a) {
                        jAlert('ERROR', 'There was an error, please try again later');
                    }
                });
        	}
		});
		return false;
	});
	jQuery('.select-country').live('change', function() {
		var el 				= jQuery(this);
		var country        	= el.val();
        var state    		= el.attr('data-state');
        var holder    		= el.attr('data-holder');
        jQuery.ajax({
            url: '../index.php?plugin=pages&op=load_states&country=' + country + '&name=' + state,
            type: "get",
            cache: true,
            crossDomain: true,
            success: function (a) {
                jQuery('#' + holder).html(a);
                jQuery(':input[name="' + state +'"]').uniform();
            },
            error: function (a) {
                jAlert('ERROR', 'There was an error, please try again later');
            }
        });
	});
    jQuery('.promp-alert').live('click', function() {
        var dialog_title    = jQuery(this).attr('title');
        var dialog_content  = jQuery(this).attr('data-desc');
        var ajax_url        = jQuery(this).attr('href');
		jPrompt(dialog_content, '', dialog_title, function(r) {
			if(r) {
                jQuery.ajax({
                    url: ajax_url + '?album=' + r,
                    type: "get",
                    dataType: "json",
                    cache: true,
                    crossDomain: true,
                    success: function(a) {
                        if (a.answer == 'error') {
                            jAlert('ERROR: ' + a.message, 'There was an error');
                        } else {
                            window.location.href = a.redirect;
                        }
                    }
                });
			}
		});
		return false;
	});
	jQuery('.promptbutton').click(function() {
		jPrompt('Type something:', 'Prefilled value', 'Prompt Dialog', function(r) {
			if(r) {
                alert('You entered ' + r);
            }
		});
		return false;
	});
	if(jQuery('.alert-error').length) {
        var alert_content = jQuery('.alert-error').html();
        var alert_title = jQuery('.alert-error').attr('title');
		jAlert(alert_content, alert_title);
	}
	jQuery('.alert-inline-button').live('click', function() {
        var alert_content = jQuery('.alert-inline').html();
        var alert_title = jQuery('.alert-inline').attr('title');
		jAlert(alert_content, alert_title);
		return false;
	});
	///// EXTRA INFO HANDLER /////
	jQuery('select.extra-info-handler').live('change', function() {
		var el = jQuery(this).parent().find("div.extra-info");
        (this).attr('value') == 1 ? alert((this).attr('value')) : alert((this).attr('value'));
    });
	///// SIMPLE CHART /////
	function showTooltip(x, y, contents) {
		jQuery('<div id="tooltip" class="tooltipflot">' + contents + '</div>').css({
			position: 'absolute',
			display: 'none',
			top: y + 5,
			left: x + 5
		}).appendTo("body").fadeIn(200);
	}
    ///// SWITCHING LIST FROM 3 COLUMNS TO 2 COLUMN LIST /////
    function rearrangeShortcuts() {
    	if(jQuery(window).width() < 430) {
    		if(jQuery('.shortcuts li.one_half').length == 0) {
    			var count = 0;
    			jQuery('.shortcuts li').removeAttr('class');
    			jQuery('.shortcuts li').each(function() {
    				jQuery(this).addClass('one_half');
    				if(count%2 != 0) jQuery(this).addClass('last');
    				count++;
    			});	
    		}
    	} else {
    		if(jQuery('.shortcuts li.one_half').length > 0) {
    			jQuery('.shortcuts li').removeAttr('class');
    		}
    	}
    }
    rearrangeShortcuts();
    ///// ON RESIZE WINDOW /////
    jQuery(window).resize(function()
    {
    	rearrangeShortcuts();
    });
    // Toggles events used for activate and feture items
    jQuery('a.toggle-menu').live('click', function() {
        jQuery.ajax({
            url: jQuery(this).attr('href')
        });
        if(jQuery(this).children("span").attr('class') == 'icon-checkmark-green') {
            jQuery(this).children("span").removeClass("icon-checkmark-green"); 
            jQuery(this).children("span").addClass("icon-cancel-red");
        } else {
            jQuery(this).children("span").removeClass("icon-cancel-red"); 
            jQuery(this).children("span").addClass("icon-checkmark-green"); 
        }
        return false;
    });
    jQuery('.colorbox-inline').colorbox({
        inline: true, 
        width: "680px",
        onComplete: function() {
            jQuery('.cancel').click(function() {
                jQuery.fn.colorbox.close();
                return false;
            });
        }
    });
    jQuery('body').on('click', '.send-message', function() {
        jQuery(this).colorbox({
            onComplete: function() {
                jQuery('.cancel').click(function() {
                    jQuery.fn.colorbox.close();
                    return false;
                });
                jQuery('#email-sent').submit(function() {
                    var formdata = jQuery(this).serialize();
                    var url = jQuery(this).attr('action');
                    jQuery.post(url, formdata, function(a) {     
                        if (a.answer == 'error') {
                            jQuery('.notifyMessage').removeClass('notifySuccess').addClass('notifyError').text(a.message); 
                        } else {
                            jQuery('.notifyMessage').removeClass('notifyError').addClass('notifySuccess').text(a.message);
                            jQuery('.submit').hide();
                            setTimeout(function() {
                                jQuery.fn.colorbox.close();
                            }, 1500);
                        }                    
                        jQuery.fn.colorbox.resize();    
                    });
                    return false;
                });
            }
        });
    });
    jQuery('body').on('click', '.ajax', function(){
        jQuery(this).colorbox({
    		onComplete: function(){
    			jQuery('.cancel').click(function(){
    				jQuery.fn.colorbox.close();
    				return false;
    			});
    			jQuery('#editphoto').submit(function() {
    				var formdata = jQuery(this).serialize();
    				var url = jQuery(this).attr('action');
    				jQuery.post(url, formdata, function(data){
    					jQuery('.notifyMessage').addClass('notifySuccess');
    					jQuery.fn.colorbox.resize();
                        setTimeout(function(){
                            jQuery.fn.colorbox.close();
                        }, 2000);
    				});
    				return false;
    			});
    		}
    	});
	});
	jQuery('.imagelist img').hover(function(){
		jQuery(this).stop().animate({opacity: 0.75});
	},function(){
		jQuery(this).stop().animate({opacity: 1});
	});
    jQuery('body').on('click', '.view', function() {
        jQuery(this).colorbox();
    });
	jQuery('.upload-images').colorbox({
        onClosed: function(){
            window.location.href = window.location.href;
        },
		onComplete: function()  {
			jQuery('.cancel').click(function() {
				jQuery.fn.colorbox.close();
                window.location.href = window.location.href;
				return false;	//we use return false because we use button and to prevent form submission 
			});
		}
	});
	//delete image in grid list	
	jQuery('.imagelist .delete').live('click', function(){
        var parent          = jQuery(this).parents('li');
        var ajax_url        = jQuery(this).attr('href');
		jConfirm('Can you confirm this?', 'Confirmation Dialog', function(r){
			if(r){
                jQuery.ajax({
                    url: ajax_url,
                    type: "get",
                    dataType: "json",
                    cache: true,
                    crossDomain: true,
                    success: function (a) {
                        if (a.answer == 'error') {
                            jAlert('ERROR: ' + a.message, 'There was an error');
                        } else {
                            parent.hide('explode',500);
                        }
                    }
                });
			}
		});
		return false;
	});
    jQuery(".imagelist").sortable({
        update: function (e, ui) {
            var order = jQuery('.imagelist').sortable('serialize'); 
            jQuery.get('pages/gallery/reorder?' + order); 
        }
    }).disableSelection();
	jQuery('.mediatable .delete').live('click', function(){
		var c = confirm("Continue delete?");
		if(c) {
            jQuery(this).parents('tr').fadeOut();
        }
		return false; //to prevent page reload
	});
    jQuery('.stdtable a.toggle').click(function(){
        jQuery(this).parents('table').find('tr').each(function(){
            jQuery(this).removeClass('hiderow');
            if(jQuery(this).hasClass('togglerow')){
                jQuery(this).remove();
            }
        });
        
        var parentRow = jQuery(this).parents('tr');
        var numcols = parentRow.find('td').length + 1;     
        var url = jQuery(this).attr('href');
        parentRow.after('<tr class="togglerow"><td colspan="' + numcols + '"><div class="toggledata"></div></td></tr>');
        var toggleData = parentRow.next().find('.toggledata');
        return false;
    });
    ///// REMOVE TOGGLED QUICK VIEW WHEN CLICKING SUBMIT/CANCEL BUTTON /////    
    jQuery('.toggledata button.cancel, .toggledata button.submit').live('click',function() {
        jQuery(this).parents('.toggledata').animate({height: 0},200, function(){
            jQuery(this).parents('tr').prev().removeClass('hiderow');                                                            
            jQuery(this).parents('tr').remove();
        });
        return false;
    });
    ///// ORDER AREA /////
    
    jQuery('a.popup').live('click', function() {
        jQuery.colorbox({
            href:jQuery(this).prop('href'),
            onComplete: function(){
                jQuery('input:checkbox, input:radio, select.uniformselect').uniform();
                jQuery('.cancel').click(function(){
                    jQuery.fn.colorbox.close();
                    return false;   //we use return false because we use button and to prevent form submission 
                });
            }
        });
        return false;
    });
    jQuery('a.order-statuses').live('click', function(){
        var status_element = jQuery(this).data('status-element');
        jQuery.colorbox({
            href:jQuery(this).prop('href'),
            onComplete: function(){
                jQuery('input:checkbox, input:radio, select.uniformselect').uniform();
                jQuery('.cancel').click(function(){
                    jQuery.fn.colorbox.close();
                    return false;   //we use return false because we use button and to prevent form submission 
                });
                jQuery('#note-sent').submit(function(){
                    var formdata = jQuery(this).serialize();    //get all form data
                    var url = jQuery(this).attr('action');      //get the url to be submitted
                    jQuery.post(url, formdata, function(a){     
                        if (a.answer == 'error'){
                            jQuery('.notifyMessage').removeClass('notifySuccess').addClass('notifyError').text(a.message); 
                        } else {
                            jQuery('.notifyMessage').removeClass('notifyError').addClass('notifySuccess').text(a.message);
                            jQuery('.submit').hide();
                            if(typeof status_element !== 'undefined'){
                                jQuery('#' + status_element).removeClass().addClass(a.icon).css('color', a.color).text(' ' + a.status);
                            }
                            setTimeout(function(){
                                jQuery.fn.colorbox.close();
                            }, 2000);
                        }                    
                        jQuery.fn.colorbox.resize();    
                    });
                    return false;
                });
            }
        });
        return false;
    });
    jQuery('a.orders_actions_invoice').live('click', function() {
        window.open(jQuery(this).prop('href'), 'invoices', 'width=1000, height=700, resizable=1');
        return false;
    });
    jQuery('a.orders_actions_package_slip').live('click', function(){
        window.open(jQuery(this).prop('href'), 'packing_slip', 'width=1000, height=700, resizable=1');
        return false;
    });
    jQuery('a.order-delete').live('click', function() {
        var holder = jQuery(this).data('main-holder');
        var href = jQuery(this).prop('href');
        jConfirm('Are you sure you want to delete this item?', 'Confirm Deletion', function(r) {
            if(r) {
                jQuery.get(href, function(a){
                    if (a.answer == 'error') {
                        jAlert('ERROR: ' + a.message, 'There was an error');
                    } else {
                        if(typeof holder !== 'undefined'){
                            jQuery('#' + holder).addClass('red-bg').fadeOut('slow');
                        }
                    }
                })
            }
        });
        return false;
    });
    jQuery('a.terminal_print_receipt').live('click', function() {
        var url = jQuery(this).prop('href');
        window.open(url, 'print_receipt', 'width=800, height=700, resizable=1');
        return false;
    });
    jQuery('.add-custom-field').live('click', function() {
        jQuery('#custom-field-holder').append(jQuery('#custom-field-template').html());
        jQuery('.combo').simpleCombo();
        return false;
    });
    jQuery('a.view_notes').live('click', function() {
        var notes_url   = jQuery(this).prop('href');
        var user_id 	= jQuery(this).attr('data-item-id');
        var count_text 	= jQuery('#notes-count-' + user_id).text();
        jQuery.colorbox({
            href: notes_url,
            onComplete: function() {
                jQuery('input:checkbox, input:radio, select.uniformselect').uniform();
                jQuery('.cancel').click(function() {
                    jQuery.fn.colorbox.close();
                    return false;   //we use return false because we use button and to prevent form submission 
                });
                jQuery('#note-sent').submit(function() {
                    var formdata = jQuery(this).serialize();    //get all form data
                    var url = jQuery(this).attr('action');      //get the url to be submitted
                    jQuery.post(url, formdata, function(a) {     
                        if (a.answer == 'error') {
                            jQuery('.notifyMessage').removeClass('notifySuccess').addClass('notifyError').text(a.message); 
                        } else {
                            jQuery('.notifyMessage').removeClass('notifyError').addClass('notifySuccess').text(a.message);
                            jQuery('.submit').hide();
                            jQuery('#notes-count-' + user_id).text(count_text * 1 + 1);
                            setTimeout(function() {
                                jQuery.fn.colorbox.close();
                            }, 2000);
                        }                    
                        jQuery.fn.colorbox.resize();    
                    });
                    return false;
                });
            }
        });
        return false;
    });    
    // Stats Area
    jQuery('.stats-area a').live('click', function(){
        jQuery('#stats-info').html('<div style="text-align: center;"><img src="images/loaders/loader6.gif" /></div>');
        var url = jQuery(this).prop('href');
        jQuery.ajax({
            url: url,
            type: "get",
            cache: true,
            success: function (html) {
                jQuery('#stats-info').html(html);
            }
        });
        return false;
    });
    var full_loaded = false;
    function scroll_area(){
        var mostOfTheWayDown = (jQuery(document).height() - jQuery(window).height()) * 2 / 3;
        if (jQuery(window).scrollTop() >= mostOfTheWayDown){
            if(full_loaded == false && jQuery('div#ajax-load-more-content').length >0){
                full_loaded = true;
                var query = (jQuery('div#ajax-load-more-content').data('url').indexOf('?') == -1) ? '?' : '&';
                jQuery.get(jQuery('div#ajax-load-more-content').data('url') + query + 's=' + jQuery('.stdtable tbody tr').length, function(a){
                    if(!jQuery.isEmptyObject(a)) {
                        jQuery('.stdtable tbody').append(a);
                        full_loaded = false;
                    } else {
                        jQuery('div#ajax-load-more-content').remove(); 
                    }
                });
            }
        }
    }
    jQuery(document).on('keyup', '#search-area', function(e) {
        var value = jQuery(this).val();
        var url = jQuery(this).attr('data-url');
        full_loaded = (value != '') ? true : false;
        jQuery.get(url + '?q=' + value, function(a){
            jQuery('.stdtable tbody').html(a);
        });
    });
    jQuery(document).on('keyup keypress', 'input#search-area', function(e) {
    if(e.keyCode == 13) {
        e.preventDefault();
            return false;
        }
    });
    jQuery(window).on('scroll', scroll_area);
});