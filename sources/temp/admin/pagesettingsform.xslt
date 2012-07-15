<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" version="1.0" encoding="utf-8" indent="yes"/>
    <xsl:template match="/">
    	<xsl:variable name="pageid" select="//block-list/@pageid" />
			<div style="margin:10px 2px; cursor:pointer; clear:both" class="ui-corner-top ui-widget-header">
				<div style="width:60%; float:left; padding:5px;">Name</div>
				<div style="width:20%; float:left; padding:5px;">Place</div>
				<div style="clear:both"></div>
			</div>
		
		<div id="blocklist_{$pageid}" class="">
			<xsl:for-each select="//block-list/block">
				<xsl:call-template name="block" />
			</xsl:for-each>
		</div>	
		
		
		<div class="ui-corner-all ui-widget-content edit" style="margin:10px 0; padding:5px; clear:both">				
			
			<div>Add:</div>
			<form action="/blank/adm/sitemap/{$pageid}/settings/0/edit/" class="addblock" onsubmit="var n = $(this).next().clone().appendTo('#blocklist_{$pageid}'); $.post($(this).attr('action'), $(this).serialize(), function(data){{n.show(); n.find('input[name=blockid]').val(data.id); n.attr('id', 'block_'+data.id); n.find('.name').html(data.name); n.find('.place').html(data.place); }}, 'json'); return false;">
				<div style="width:60%; float:left; margin-right:2px;"><input type="text" name="name" style="width:100%; margin:2px" /></div>
				<div style="width:20%; float:left; margin-right:2px;"><input type="text" name="place" style="width:100%; margin:2px" /></div>
				<button ico="ui-icon-disk" style="width:100px; padding:2px; margin:2px">Save</button>
			</form>
			
			<xsl:call-template name="block">
				<xsl:with-param name="id">sample_<xsl:value-of select="@pageid" /></xsl:with-param>
				<xsl:with-param name="style" select="'display:none;'" />
			</xsl:call-template>
			
		</div>

	</xsl:template>
	<xsl:template name="block">
		<xsl:param name="style" />
		<xsl:param name="blockid" select="@id" />
		<xsl:param name="id">block_<xsl:value-of select="@id" /></xsl:param>
			<div style="{$style}" id="{$id}">
				<input type="hidden" name="{$blockid}" />
				<div style="margin:10px 2px; cursor:pointer; clear:both" onclick="var par = $(this).parent(); $(this).hide().next().show(); par.find('input').each(function(){{$(this).val(par.find('.'+$(this).attr('name')).html())}})" class="ui-corner-all ui-widget-content">
					<div style="width:60%; float:left; padding:5px;" class="name"><xsl:value-of select="name" /></div>
					<div style="width:20%; float:left; padding:5px;" class="place"><xsl:value-of select="place" /></div>
					
					<ul class="icons ui-widget ui-helper-clearfix controls" style="float:right">
						<li style="margin:2 5px; width:25px" class="ui-corner-all" onclick="$.post('/adm/sitemap/{@pageid}/settings/'+$(this).closest('input[name=blockid]')+'/delete/')" title="Delete Block"><div style="margin:auto" class="ui-icon ui-icon-close"></div></li>
					</ul>
					
					<div style="clear:both"></div>
					
				</div>
				<div class="ui-corner-all ui-widget-content edit" style="margin:10px 0; clear:both; display:none;">
					<form action="/adm/sitemap/{@pageid}/settings/{@id}/edit/" onsubmit="var par = $(this).parent(); par.hide().siblings().show().find('.name').html($(this).find('input[name=name]').val()); par.siblings().find('.place').html($(this).find('input[name=place]').val())" class="ajax">
						<div style="width:60%; float:left; margin-right:2px;"><input type="text" name="name" value="{name}" style="width:100%; margin:2px" /></div>
						<div style="width:20%; float:left; margin-right:2px;"><input type="text" name="place" value="{place}" style="width:100%; margin:2px" /></div>
						<button ico="ui-icon-disk" style="width:100px; padding:2px; margin:2px">Save</button>
					</form>
				</div>
			</div>
	</xsl:template>
</xsl:stylesheet>