<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="sources/temp/include.xslt"/>
	<xsl:import href="sources/temp/admin/company-list.xslt"/>
	<xsl:output method="html" version="1.0" encoding="utf-8" indent="yes"/>
	<xsl:key name="comp" match="office" use="company/@id"/>
	<xsl:template match="/">
		<xsl:variable name="content">
			<xsl:apply-templates select="//objlist" mode="tbl" />
		</xsl:variable>
		<xsl:call-template name="MainTemp" >
			<xsl:with-param name="precontent" select="$content" />
		</xsl:call-template>
	</xsl:template>


	<xsl:template match="objlist" mode="tbl">
        <div id="pagewin_companies"></div>
		<table class="tableCompanyList">
            <tr>
                <td>Name</td>
                <td>Content</td>
                <td>Logo</td>
                <td>Regional Offices</td>
                <td>Press releases</td>
            </tr>
            <xsl:apply-templates select="obj" mode="company-list" />
		</table>
	</xsl:template>



	<xsl:template match="objlist">
		<textarea><xsl:copy-of select="." /></textarea>
	</xsl:template>
	<xsl:template match="objlist" mode="table">
		<table>
			<xsl:apply-templates select="obj" mode="table" />
		</table>
	</xsl:template>
	<xsl:template match="obj" mode="table">
		<tr>
			<xsl:apply-templates select="field" mode="table" />
		</tr>
	</xsl:template>
	<xsl:template match="field" mode="table">
		<td>[<xsl:value-of select="@id"/>]<xsl:value-of select="." /></td>
	</xsl:template>
</xsl:stylesheet>