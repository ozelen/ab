// JavaScript Document

var User = {
    data : {}
};
$(function(){

	$("a.ajax").live('click', function(){
		var target = $('#'+$(this).attr('target'));
		$.post($(this).attr('href'), function(data){target.html(data); defAll();});
		return false;
	});

	$('form.ajax').submit(function(){submitForm($(this)); return false});
	$('form.ajax').live('submit', function(){submitForm($(this)); return false});

	$('.radiobutton').live('click', function(){$(this).addClass('ui-state-active').siblings().removeClass('ui-state-active');})
	$('.langlink').live('click', function(){
		var but = $(this);
		var targ = $('#'+$(this).attr('target'));
		var cls = 'lang_'+but.attr('lang');
		var langdiv = targ.find('.'+cls);
		if(langdiv.length<=0){
			langdiv = $('<div class="'+cls+'">');
			targ.append(langdiv);
			$.post($(this).attr('href'), function(data){langdiv.html(data);});
		}
		langdiv.show().siblings().hide();
		//alert(cls);

	})

	$('.addlang').live('click', function(){
		var par = $(this).parent();
		var smpl = par.find('div.sample');
		var newbut = smpl.clone();
		var pageid = par.attr('pageid');
		var pagecontent = $('#pagecontent_'+pageid);
		var langwin = $('#langwin_'+pageid);
		var url = par.attr('href');
		langwin.dialog({
			modal: true,
			buttons:{
				'Save'	: function(){
					var sel = $(this).find('select');
					var lang = sel.val();
					// alert(lang);
					if(!lang || lang=='')return false;
					sel.find('option[value="'+lang+'"]').remove();
					newbut.html(lang);
					newbut.removeClass('sample').addClass('langlink').appendTo(par).show();
					newbut.attr('href', '/'+lang+url);
					newbut.attr('lang', lang);
					//alert(newbut.html());
					// $.post(url+'?multiform=true&mode=addlang&lang='+lang, $('#'+target_id).closest('form').serialize(), function(data){$('#'+target_id).html(data); defAll(); switchLang(url+'?lang='+lang, dataarea_id, lang)});
					$(this).dialog('close');
					defAll();
				},
				'Cancel': function(){$(this).dialog('close')}
			}
		});

		/*
		newbut.attr('href', '/adm/sitemap/'+par.attr('pageid')+'/data/en/');
		newbut.find('.content').html('en');
		*/
	});

    loginForm();


	defAll();

});

function defAll(){
	drawButtons();
	drawTabs();
	drawAutoFields();
	autoComplete();
	$('.icons li').hover(function(){$(this).addClass('ui-state-hover');}, function(){$(this).removeClass('ui-state-hover');});
	$('.icon').hover(function(){$(this).addClass('ui-state-hover');}, function(){$(this).removeClass('ui-state-hover');});
}

function go(uri, selector, func){
	$.post(uri, function(data){{func(); $(selector).html(data); defAll();}})
}

function loginForm(){
    $('#loginForm').live('submit', function(){

        submitForm($(this), function(data){
            switch(data.result){
                case 'error':
                    $('#loginForm .errorLog').html(data.descr);
                break;
                default:
                    User.data = data;
                    $('#loginButton .header').html(data.username);
                    $.post('/users/'+data.username+'/menu/', function(data){$('#loginDiv').html(data);});
                    defAll();
                break;
            }
        },'json');
        return false;
    });
}

function addLang(elt){

}

function autoComplete(){
	$('.autocomplete').each(function(){
		var elt = $(this);
		elt.autocomplete({
			source: function( request, response ) {
				var url = elt.attr('url');
				$.ajax({
					url: url,
					dataType: "json",
					type: "POST",
					data: {
						str: request.term
					},
					success: function( data ) {
						response( $.map( data, function( item ) {
							return {
								id: item.id,
								description: item.name + ' ('+item.name+')',
								value: item.name
							}
						}));
					}
				});
			},
			minLength: 2,
			select: function( event, ui ) {
				$(this).val(ui.item.name);
				$('#'+$(this).attr('idfield')).val(ui.item.id);
				$('#'+$(this).attr('description')).html(ui.item.description);
			},
			open: function() {
				//$( this ).removeClass( "ui-corner-all" ).addClass( "ui-corner-top" );
			},
			close: function() {
				//$( this ).removeClass( "ui-corner-top" ).addClass( "ui-corner-all" );
			}
		});
	});

}


function drawButtons(){
	$( "button, input:submit, a.btn").each(function(){
		$(this).button({
			icons:{
				primary: $(this).attr('ico'),
				secondary: $(this).attr('ico2')
			}
		})
	});
}

function drawTabs(){
	$( ".tabs" ).tabs();
	$( ".tabs.tabs-bottom .ui-tabs-nav, .tabs-bottom .ui-tabs-nav > *" )
		.removeClass( "ui-corner-all ui-corner-top" )
		.addClass( "ui-corner-bottom" );
}

function winHref(divid, href, postdata, datatype){
	_winHref($('#'+divid), href, postdata, datatype)
}

function _winHref(win, href, postdata, datatype){

	//alert(href);
	var dtype = datatype || 'html';
	$.ajax({
		type: "POST",
		url: href, 
		data: postdata, 
		success: function(data){
			win.html(data); defAll();
			var form = win.find('form.winform');
			var closewin = function(){win.dialog('close');}
			var savewin = function(){submitForm(form, closewin);}
			win.dialog({
				modal: true,
				width:800,
				height:500
			});
			form.submit(function(){closewin();});
		},
		dataType: dtype
	});
}


function drawAutoFields(){
	(function( $ ) {
		$.widget( "ui.combobox", {
			_create: function() {
				var self = this,
					select = this.element.hide(),
					selected = select.children( ":selected" ),
					value = selected.val() ? selected.text() : "";
				var input = this.input = $( "<input>" )
					.insertAfter( select )
					.val( value )
					.autocomplete({
						delay: 0,
						minLength: 0,
						source: function( request, response ) {
							var matcher = new RegExp( $.ui.autocomplete.escapeRegex(request.term), "i" );
							response( select.children( "option" ).map(function() {
								var text = $( this ).text();
								if ( this.value && ( !request.term || matcher.test(text) ) )
									return {
										label: text.replace(
											new RegExp(
												"(?![^&;]+;)(?!<[^<>]*)(" +
												$.ui.autocomplete.escapeRegex(request.term) +
												")(?![^<>]*>)(?![^&;]+;)", "gi"
											), "<strong>$1</strong>" ),
										value: text,
										option: this
									};
							}) );
						},
						select: function( event, ui ) {
							ui.item.option.selected = true;
							self._trigger( "selected", event, {
								item: ui.item.option
							});
						},
						change: function( event, ui ) {
							if ( !ui.item ) {
								var matcher = new RegExp( "^" + $.ui.autocomplete.escapeRegex( $(this).val() ) + "$", "i" ),
									valid = false;
								select.children( "option" ).each(function() {
									if ( $( this ).text().match( matcher ) ) {
										this.selected = valid = true;
										return false;
									}
								});
								if ( !valid ) {
									// remove invalid value, as it didn't match anything
									$( this ).val( "" );
									select.val( "" );
									input.data( "autocomplete" ).term = "";
									return false;
								}
							}
						}
					})
					.addClass( "ui-widget ui-widget-content ui-corner-left" );

				input.data( "autocomplete" )._renderItem = function( ul, item ) {
					return $( "<li></li>" )
						.data( "item.autocomplete", item )
						.append( "<a>" + item.label + "</a>" )
						.appendTo( ul );
				};

				this.button = $( "<button type='button' style='height:20px;'>&nbsp;</button>" )
					.attr( "tabIndex", -1 )
					.attr( "title", "Show All Items" )
					.insertAfter( input )
					.button({
						icons: {
							primary: "ui-icon-triangle-1-s"
						},
						text: false
					})
					.removeClass( "ui-corner-all" )
					.addClass( "ui-corner-right ui-button-icon" )
					.click(function() {
						// close if already visible
						if ( input.autocomplete( "widget" ).is( ":visible" ) ) {
							input.autocomplete( "close" );
							return;
						}

						// pass empty string as value to search for, displaying all results
						input.autocomplete( "search", "" );
						input.focus();
					});
			},

			destroy: function() {
				this.input.remove();
				this.button.remove();
				this.element.show();
				$.Widget.prototype.destroy.call( this );
			}



		});
	})( jQuery );



	$( "#combobox" ).combobox();
	$( "#toggle" ).click(function() {
		$( "#combobox" ).toggle();
	});

}

function submitFormPrepend(current){

}

function submitForm(current, fn, dtype){

	var win = current.closest('.win');
	var form_data = $.param(current.serializeArray());
	var method = current.attr('method') || 'post';
	var dtype = dtype || 'html';
	var target;
	var output_method = current.attr('outtype');
	var fn = fn || function(){}
	if(current.attr('target')!=''){
		target = $('#'+current.attr('target'));
	}else target = $('emptyDiv');

    $.ajax({
		type: 		'POST',
		url:		current.attr('action'),
		data:		form_data,
		//dataType:	current.attr('datatype') || 'html',
		success:	function(data){
			//alert(current.attr('action')+' \n['+data+']');
            //alert(output_method+" "+current.attr('target')+" with:\n"+data);
			switch(output_method){
				case 'prepend': target.prepend(data); break;
				case 'replace': target.replaceWith(data); break;
				default: target.html(data); break;
			}

			defAll();

			if(current.hasClass('clearaftersubmit')){
				current.find('input[type!=hidden], textarea').val('');
			}
			fn(data);
			//win.dialog('close');
		},
		dataType: dtype
	});
}


// upload files via ajax
function ajaxFileUpload(){
    $(".loading_img")
            .ajaxStart(function(){
                $(this).show();
            })
            .ajaxComplete(function(){
                $(this).hide();
            });

    $.ajaxFileUpload
            (
                    {
                        url:'/plugins/upload/doajaxfileupload.php',
                        secureuri:false,
                        fileElementId:'fileToUpload',
                        dataType: 'json',
                        data:{name:'logan', id:'id'},
                        success: function (data, status)
                        {
                            if(typeof(data.error) != 'undefined')
                            {
                                if(data.error != '')
                                {
                                    alert(data.error);
                                }else
                                {
                                    alert(data.msg);
                                }
                            }
                        },
                        error: function (data, status, e)
                        {
                            alert(e);
                        }
                    }
            )

    return false;

}