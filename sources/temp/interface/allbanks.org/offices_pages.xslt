<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" version="1.0" encoding="utf-8" indent="yes"/>

	<xsl:template name="officepage">
officepage
		<xsl:variable name="countryid" select="/out/presets/uri-vars/country"/>
		<xsl:variable name="country" select="/out/geonames/countries/country[uri=$countryid]"/>
		<xsl:variable name="country_code" select="$country/countryCode"/>
		<xsl:variable name="adm1id" select="/out/presets/uri-vars/adm1"/>
		<xsl:variable name="adm1" select="/out/geonames/regions[@level=1]/region[uri=$adm1id]"/>
		<xsl:variable name="adm2id" select="/out/presets/uri-vars/adm2"/>
		<xsl:variable name="adm2" select="/out/geonames/regions[@level=2]/region[uri=$adm2id]"/>
		<xsl:variable name="city" select="/out/geonames/city"/>
		<xsl:variable name="current_settlement" select="/out/geonames/settlements/settlement[@id=$city/@id]"/>
		<xsl:variable name="offices" select="/out/geonames/offices"/>
		<xsl:variable name="current_office_name" select="/out/presets/uri-vars/officename"/>
		<xsl:variable name="current_office" select="$offices/office[name=$current_office_name]"/>

		<!-- if office opened -->
										<table width="100%">
											<tr>
												<td style="min-width: 300px; max-width: 400px;">
													<h1><xsl:value-of select="$current_office/name"/></h1>
													<h2><xsl:value-of select="$current_office/web/title"/></h2>
											<!--	<textarea rows="20" cols="60"><xsl:copy-of select="$current_office"/></textarea>-->
													<table style="width: 100%">
														<tr>
															<td>Address:</td>
															<td>
															<xsl:value-of select="$current_office/address"/>,
															<xsl:value-of select="$city/name"/>,
															<xsl:if test="$adm2/name"><xsl:value-of select="$adm2/name"/>, </xsl:if>
															<xsl:value-of select="$adm1/name"/>,
															<xsl:value-of select="$country/countryName"/>,
															<xsl:value-of select="$current_office/zip"/>
															</td>
														</tr>
														<xsl:if test="not($current_office/phone='')">
															<tr><td>Phone:</td><td><xsl:value-of select="$current_office/phone"/></td></tr>
														</xsl:if>
														<xsl:if test="not($current_office/fax='')">
															<tr><td>Fax:</td><td><xsl:value-of select="$current_office/fax"/></td></tr>
														</xsl:if>
														<xsl:if test="not($current_office/email='')">
															<tr><td>E-mail</td><td><xsl:value-of select="$current_office/email"/></td></tr>
														</xsl:if>
														<xsl:if test="not($current_office/web/url='')">
															<tr><td>Website:</td><td><a href="{$current_office/web/url}" rel="nofollow" target="_blank"><xsl:value-of select="$current_office/web/url"/></a></td></tr>
														</xsl:if>
													</table>
												</td>
												<td style="max-width: 500px">
													<div class="tblock">
														<h2><xsl:value-of select="$current_office/name"/> in <xsl:value-of select="$city/name"/></h2>
														<!-- <textarea rows="20" cols="100"><xsl:copy-of select="$current_office"/></textarea> -->
														<xsl:apply-templates select="$current_office/offices" mode="address">
															<xsl:with-param name="current_settlement" select="$current_settlement" />
														</xsl:apply-templates>
													</div>

													<div class="tblock">
														<h2>Banks in <xsl:value-of select="$city/name"/></h2>
														<h3>(<xsl:if test="$adm2"><xsl:value-of select="$adm2/name"/>, </xsl:if> <xsl:value-of select="$adm1/name"/>, <xsl:value-of select="$country/countryName"/>)</h3>
														<h4><xsl:value-of select="$current_settlement/count_banks"/> of banks found:</h4>
														<xsl:apply-templates select="$offices">
															<xsl:with-param name="current_settlement" select="$current_settlement"/>
															<xsl:with-param name="current_office" select="$current_office"/>
														</xsl:apply-templates>
													</div>

													<div class="tblock">
														<h2>Banks in the next settlements:</h2>
														<xsl:apply-templates select="/out/geonames/settlements">
															<xsl:with-param name="city" select="$city"/>
														</xsl:apply-templates>
													</div>
												</td>
											</tr>
										</table>

										<!-- -->

	</xsl:template>

</xsl:stylesheet>