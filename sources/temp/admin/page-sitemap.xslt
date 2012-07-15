<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	

	<xsl:output method="html" version="1.0" encoding="utf-8" indent="yes"/>
	<xsl:include href="sources/temp/admin/sitemap.xslt"/>
	<xsl:include href="sources/temp/standart/accessories.xslt"/>
    <xsl:template match="/">
		<xsl:call-template name="head-includes" />
		<xsl:apply-templates select="//pagelist" />
	</xsl:template>
</xsl:stylesheet>
