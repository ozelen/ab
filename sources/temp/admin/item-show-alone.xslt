<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="sources/temp/admin/item-show.xslt" />
	<xsl:output method="html" version="1.0" encoding="utf-8" indent="yes"/>
	<xsl:template match="/">
		<xsl:apply-templates select="//item" mode="alone"/>
	</xsl:template>

	<xsl:template match="item" mode="alone">
		<li id="item_{@id}" class="newsitem">
			<xsl:apply-templates select="." mode="show"/>
		</li>
	</xsl:template>

</xsl:stylesheet>