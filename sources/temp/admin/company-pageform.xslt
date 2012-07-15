<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" version="1.0" encoding="utf-8" indent="yes"/>
	<xsl:include href="sources/temp/admin/datalang.xslt"/>
	<xsl:include href="sources/temp/admin/pageform-template.xslt"/>
    <xsl:template match="/">
	    <xsl:variable name="cur" select="//page-edit" />
        <!--
            <textarea><xsl:copy-of select="/out/presets/uri-vars/pageid" /></textarea>
            <textarea><xsl:copy-of select="$cur" /></textarea>
        -->
        <!--<xsl:variable name="pageid" select="$cur/@id" />-->
        <xsl:variable name="pageid" select="/out/presets/uri-vars/pageid" />
        <xsl:variable name="compid" select="/out/presets/uri-vars/compid" />
		<form class="ajax winform">
			<xsl:choose>
                <xsl:when test="$cur">
                    <xsl:attribute name="action">/companies/<xsl:value-of select="$compid" />/pages/edit/</xsl:attribute>
                    <xsl:attribute name="outtype">replace</xsl:attribute>
                    <xsl:attribute name="target">page_<xsl:value-of select="$cur/@id" /></xsl:attribute>
                    <input type="hidden" name="pageid" value="{$cur/@id}" />
	                Edit document <xsl:value-of select="$cur/uri" />
                </xsl:when>
                <xsl:otherwise>
                    <xsl:attribute name="outtype">prepend</xsl:attribute>
                    <xsl:attribute name="target">pagelist_<xsl:value-of select="$pageid" /></xsl:attribute>
                    <xsl:attribute name="onsubmit">$("#page_<xsl:value-of select="$cur/@id" /> a.ajax").visible().click()</xsl:attribute>
                    <input type="hidden" name="rozdil" value="{$pageid}" />
                </xsl:otherwise>
            </xsl:choose>

            <xsl:call-template name="frm">
                <xsl:with-param name="pageid" select="$pageid" />
                <xsl:with-param name="cur" select="$cur" />
            </xsl:call-template>

		<button ico="ui-icon-disk" style="padding:2px"><span>Save</span></button>
		</form>
	</xsl:template>




</xsl:stylesheet>
