<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" version="1.0" encoding="utf-8" indent="yes"/>
    <xsl:template match="/">

	</xsl:template>
	<xsl:template match="page" mode="menu">
		<div class="menu">
			<h3><xsl:value-of select="title"/></h3>
			<ul>
				<xsl:apply-templates select="pages/page" mode="menuElement" />
			</ul>
		</div>
	</xsl:template>
	<xsl:template match="page" mode="menuElement">
		<li>
			<xsl:if test="params/href">
				<a href="{params/href}"><xsl:value-of select="title" /></a>
			</xsl:if>
			<xsl:if test="not(params/href)">
				<xsl:value-of select="title" />
			</xsl:if>
			<ul>
				<xsl:apply-templates select="pages/page" mode="menuElement" />
			</ul>
		</li>
	</xsl:template>
</xsl:stylesheet>