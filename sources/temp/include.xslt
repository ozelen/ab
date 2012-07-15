<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="interface/allbanks.org/geo.xslt" />
	<xsl:import href="standart/maintemp.xslt" />
	<xsl:import href="standart/menu.xslt" />
	<xsl:import href="admin/item.xslt"/>
	<xsl:import href="forms/login.xslt"/>
	<xsl:import href="users/menu.xslt"/>
	<xsl:output method="html" version="1.0" encoding="utf-8" indent="yes"/>
    <xsl:template match="/">
		includes template
	</xsl:template>
</xsl:stylesheet>