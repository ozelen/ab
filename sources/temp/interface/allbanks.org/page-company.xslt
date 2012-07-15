<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="sources/temp/include.xslt"/>
	<xsl:import href="sources/temp/admin/company-list.xslt"/>
	<xsl:output method="html" version="1.0" encoding="utf-8" indent="yes"/>
	<xsl:key name="comp" match="office" use="company/@id"/>
	<xsl:template match="/">
        <xsl:variable name="title" select="//showItem/page/title" />
        <xsl:variable name="source" select="//showItem/page/source" />
		<xsl:variable name="content">
            <!--
            <h1><xsl:value-of select="$title" /></h1>
            <div class="mainTextArea">
                <xsl:value-of select="$source" />
            </div>
            -->
            <xsl:apply-templates select="//showItem/page" mode="display" />


		</xsl:variable>

		<xsl:call-template name="MainTemp" >
			<xsl:with-param name="content" select="$content" />
			<xsl:with-param name="title" select="$title" />
		</xsl:call-template>
	</xsl:template>


	<xsl:template match="objlist" mode="tbl">

	</xsl:template>

</xsl:stylesheet>