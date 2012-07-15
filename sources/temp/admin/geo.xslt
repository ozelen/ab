<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" version="1.0" encoding="utf-8" indent="yes"/>
	<xsl:include href="sources/temp/standart/accessories.xslt"/>
	<xsl:template match="/">
		<html>
			<head>
				<title>Geo</title>
				<xsl:call-template name="head-includes" />
			</head>
			<body>
				<xsl:apply-templates select="//items" />
			</body>
		</html>
	</xsl:template>

	<xsl:template match="items">
		<ul>
		<xsl:for-each select="item">
			<li><xsl:value-of select="."/></li>
		</xsl:for-each>
		</ul>
	</xsl:template>

</xsl:stylesheet>