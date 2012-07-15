<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" version="1.0" encoding="utf-8" indent="yes"/>
	<xsl:template match="/">
		<xsl:apply-templates select="//item" mode="show"/>
	</xsl:template>
	<xsl:template match="item" mode="show">
		<xsl:param name="id" select="@id"/>
		<div class="view">
			<ul class="icons ui-widget ui-helper-clearfix controls ui-state-default ui-corner-all" style="position:absolute; margin-top:1.5em;">
				<li class="ui-state-default ui-corner-all" onclick="$('.newsitem .view').show(); go('/adm/news/{$id}/form/', '#item_{$id} .form', function(){{ $('#item_{$id}').find('.form').show(); $('.newsitem .form').html(''); $('.additemform').hide(); $('#item_{$id} .view').hide()}});" title="Edit document"><span class="ui-icon ui-icon-pencil"></span></li>
				<li class="ui-state-default ui-corner-all" onclick="if(confirm('Delete document {field[$id='Ser']} {field[$id='Number']}?')) $.post('/ua/adm/news/{$id}/delete/', function(data){{$('#item_{$id}').remove()}})" title="Delete Article?"><span class="ui-icon ui-icon-trash"></span></li>
			</ul>
			<div>
				<a href="/news/{title}/"><xsl:value-of select="title"/></a>
			</div>
			<div>
				<div style="width:90px; height:70px; background:#ccc; float:left; margin-right:5px; margin-bottom:5px;" class="ui-corner-all"></div>
				<p style="margin:10px 0">
					<xsl:value-of select="description" />
				</p>
			</div>
		</div>
		<div class="form"></div>
		<div style="height:10px; clear:both"></div>
	</xsl:template>
</xsl:stylesheet>