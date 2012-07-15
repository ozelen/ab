<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" version="1.0" encoding="utf-8" indent="yes"/>
    <xsl:template match="/">
    	<xsl:call-template name="pagedata" />
	</xsl:template>
	
	<xsl:template name="pagedata">
		<xsl:variable name="cur" select="//page-edit"/>
		Title
		<input style="width:100%" type="text" name="content_Title_{/out/@lang}" value="{$cur/title}"/>
		Seo Title
		<input style="width:100%" type="text" name="content_SeoTitle_{/out/@lang}" value="{$cur/seo-title}"/>
		Content
		<textarea rows="13" style="width:100%" name="content_Source_{/out/@lang}"><xsl:value-of select="$cur/source" /></textarea>
	</xsl:template>
	
</xsl:stylesheet>