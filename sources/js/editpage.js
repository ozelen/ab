function switchLang(href, targ_name, lang){
	var targ = $('#'+targ_name);
	var new_name = targ_name+'_'+lang;
	if(!targ.has('#'+new_name).length){
		targ.append('<div id="'+new_name+'"></div>');
		$.post(href, targ.closest('form').serialize(), function(data){$('#'+new_name).html(data); defAll()});
	}
	var new_obj = $('#'+new_name);
	new_obj.siblings().hide();
	new_obj.show();	
}


function addLang(target_id, dataarea_id, url){
	var targ = $('#'+target_id);
	var pwin = $('#'+target_id+'_prompt');
	pwin.dialog({
		modal: true,
		buttons:{
			'Cancel': function(){$(this).dialog('close')},
			'Save'	: function(){
				var lang = $(this).find('select').val();
				$.post(url+'?multiform=true&mode=addlang&lang='+lang, $('#'+target_id).closest('form').serialize(), function(data){$('#'+target_id).html(data); defAll(); switchLang(url+'?lang='+lang, dataarea_id, lang)});
				$(this).dialog('close');
			}
		}
	});
}

function winPageEdit(uid, pageid){
	var targ_title = $('.page_'+pageid+'_title');
	var targ_source = $('.page_'+pageid+'_source');
	var params = {
		modal:true,
		width:800,
		height:550,
		buttons:{
			'Cancel': function(){$(this).dialog('close')},
			'Save'	: function(){
				var postform = $('#form_'+uid);
				var thiswin = $(this);
				
				
				$.ajax({
					type: "POST",
					url:	postform.attr('action'), 
					data:	postform.serialize(), 
					success: function(xml){
						$('#loading').hide();
						var content_link_href = postform.find('a.getfield').attr('href');
						$.post(content_link_href+'&field=title', postform.serialize(), function(html){targ_title.html(html)});
						$.post(content_link_href+'&field=source', postform.serialize(), function(html){targ_source.html(html)});
						
						//alert('post');
						//targ_source.html(data); 
						
						/*
						$(xml).find('page').each(
							function(){
								var cont_title = $(this).find('var').find('title');
								var cont_source = $(this).find('var').find('source');
								//alert(cont_source);
								targ_title.html(cont_title);
								targ_source.html(cont_source);
							}
						);
						*/
						
						defAll();
						//reCache('objects', id)
						thiswin.dialog("close");
					},
					beforeSend: function(){
						$('#loading').show();
						
					},
					dataType: 'xml'
				});
				
				$(this).dialog({ buttons: { "Close": function() { $(this).dialog("close"); } } });
			}
		}
		
	}
	
	var form 	= $('#form_'+uid);
	var win 	= $('#win_'+uid);
	var vlink 	= form.find('a').attr('href');	// link that have a viewmode href
	var targ	= $('#'+form.attr('target'));
	//$.get(form.attr('action'), form.serialize(), function(data){});
	
	$.post(vlink, form.serialize(), function(data){targ.html(data); defAll();})
	//form.submit();
	win.dialog(params);
}