<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	
	<xsl:output method="html" version="1.0" encoding="utf-8" indent="yes"/>
    <xsl:template match="/">
		<xsl:apply-templates select="//pagelist" />
	</xsl:template>
	
	<xsl:template match="pagelist">
        <xsl:choose>
            <xsl:when test="@mode='list'">
		        <ul id="pagelist_{@node}"><xsl:apply-templates select="page" mode="sitemap" /></ul>
            </xsl:when>
            <xsl:otherwise><xsl:apply-templates select="page" mode="sitemap" /></xsl:otherwise>
        </xsl:choose>

	</xsl:template>
    <xsl:template match="page" mode="sitemap">
        <li id="page_{@id}" style="margin:0.5em -20px; list-style:none; clear:both; min-height:20px">

        	<a class="ajax" target="inner_{@id}" href="/adm/sitemap/{@id}/">
                <xsl:if test="not(@children>0)">
                    <xsl:attribute name="style">display:none;</xsl:attribute>
                </xsl:if>
        		<div class="ui-icon ui-icon-circlesmall-plus" style="float:left; margin-right:2px;" onclick="$(this).toggleClass('ui-icon-circlesmall-plus ui-icon-circlesmall-minus'); $('#inner_{@id}').toggle()"></div>
        	</a>
        	<xsl:if test="@children=0">
        		<div class="ui-icon ui-icon-radio-on children0" style="float:left; margin-right:2px;" />
        	</xsl:if>
        	<div style="float:left; cursor:pointer;" onclick="$('.controls').fadeOut(); $('#page_{@id} .controls:first').fadeIn();">
        		<span style="text-decoration:underline; color:#03F"><xsl:value-of select="name" /></span> <span style="color:#ccc; margin:0 1em" class="">[<xsl:value-of select="@id" />] <xsl:value-of select="title" /></span>
        	</div>

            <xsl:call-template name="pageEditControls" />


        	<div id="pagewin_{@id}" title="Edit Page"></div>

        	<div id="inner_{@id}" style="display:none"></div>
        </li>
    </xsl:template>
    <xsl:template name="pageEditControls">
        <xsl:param name="id" select="@id" />
        <ul class="icons ui-widget ui-helper-clearfix controls ui-state-default ui-corner-all" style="position:absolute; display:none; margin-top:1.5em">
            <li class="ui-state-default ui-corner-all" onclick="winHref('pagewin_{$id}', '/adm/sitemap/{$id}/addform/'); $(this).closest('.controls').hide()" title="Add child node" style="margin-right:10px"><span class="ui-icon ui-icon-plus"></span></li>
            <li class="ui-state-default ui-corner-all" onclick="winHref('pagewin_{$id}', '/adm/sitemap/{$id}/editdata/'); $(this).closest('.controls').hide()" title="Edit document"><span class="ui-icon ui-icon-pencil"></span></li>
            <li class="ui-state-default ui-corner-all" onclick="winHref('pagewin_{$id}', '/adm/sitemap/{$id}/settings/'); $(this).closest('.controls').hide()" title="Program modules"><span class="ui-icon ui-icon-wrench"></span></li>
            <li class="ui-state-default ui-corner-all" onclick="if(confirm('Delete document {field[@id='Ser']} {field[@id='Number']}?')) $.post('/ua/adm/sitemap/{$id}/delete/', function(data){{$('#page_{$id}').remove()}})" title="Delete document"><span class="ui-icon ui-icon-trash"></span></li>
            <li style="margin:2 5px; width:25px" class="ui-corner-all" onclick="$(this).closest('.controls').fadeOut()" title="Hide this toolbar"><div style="margin:auto" class="ui-icon ui-icon-close"></div></li>
        </ul>
    </xsl:template>
</xsl:stylesheet>
