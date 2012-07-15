<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" version="1.0" encoding="utf-8" indent="yes"/>


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
	<xsl:variable name="current_office" select="$offices/office[uname=$current_office_name]"/>
	<xsl:variable name="same_offices" select="$offices/office[name=$current_office/name and not(id=$current_office/id)]"/>
	<xsl:variable name="office_opened" select="$offices//office[@opened and count]"/>
	<xsl:variable name="office_opened_1" select="$offices/office[@opened and count]"/> <!-- first level opened office -->
	<xsl:variable name="company" select="//company[uname=/out/presets/uri-vars/companyname]"/>

	<xsl:variable name="companyFilterName">
		<xsl:choose>
			<xsl:when test="$company">
				<a href="/companies/{$company/uname}/"><xsl:value-of select="$company/name" /></a>
			</xsl:when>
			<xsl:otherwise>Banks</xsl:otherwise>
		</xsl:choose>
	</xsl:variable>
	<xsl:variable name="geoTitle">
		<xsl:copy-of select="$companyFilterName" /> in
		<xsl:choose>
			<xsl:when test="$city/@id and not($city/@id='')">
				<xsl:value-of select="$city/name"/>
				<h3 style="font-weight: normal">(<xsl:if test="$adm2"><xsl:value-of select="$adm2/name"/>, </xsl:if> <xsl:value-of select="$adm1/name"/>, <xsl:value-of select="$country/countryName"/>)</h3>
				<p><xsl:value-of select="sum($offices/office/count)"/> offices of <xsl:value-of select="count($offices/office)"/> banks found:</p>
			</xsl:when>
			<xsl:when test="$adm2"><xsl:value-of select="$adm2/name" /> (<xsl:value-of select="$adm1/name" />)</xsl:when>
			<xsl:when test="$adm1"><xsl:value-of select="$adm1/name" /></xsl:when>
			<xsl:when test="$country"><xsl:if test="$country/countryCode='US'"> the </xsl:if><xsl:value-of select="$country/countryName" /></xsl:when>
		</xsl:choose>

		<!--<textarea><xsl:copy-of select="$country" /></textarea>-->

	</xsl:variable>


	<xsl:template match="/">
		geo
	</xsl:template>


	<xsl:template match="offices" mode="address">
		<xsl:param name="current_settlement" />
		<ul class="officelist">
			<xsl:for-each select="office">
				<li>
					<xsl:choose>
						<xsl:when test="@id=/out/presets/uri-vars/officeid">
							<strong><xsl:value-of select="address"/></strong>
						</xsl:when>
						<xsl:otherwise>
							<a href="/{//city/uri}/offices/{uname}/{@id}/">
								<xsl:value-of select="address"/>
							</a>
						</xsl:otherwise>
					</xsl:choose>
				</li>
			</xsl:for-each>
		</ul>
	</xsl:template>

	<xsl:template match="office" mode="list">
		<xsl:param name="current_settlement" />
		<xsl:param name="current_office" />
		<xsl:variable name="id" select="@id" />
		<xsl:variable name="id2" select="$current_office/@id" />
		<li class="office">
			<!--<a href="/{//city/uri}/offices/{uname}/">
				<xsl:value-of select="name"/>
			</a>-->

			<xsl:choose>
				<xsl:when test="@id=$current_office/@id">
					<strong><xsl:value-of select="name"/></strong>
				</xsl:when>
				<xsl:otherwise>
					<a href="/{//city/uri}/offices/{uname}/">
						<xsl:value-of select="name"/>
					</a>
				</xsl:otherwise>
			</xsl:choose>
			<xsl:if test="count>1">
				(<xsl:value-of select="count"/>)
			</xsl:if>

		</li>
	</xsl:template>

	<xsl:template match="offices">
		<xsl:param name="current_settlement" />
		<xsl:param name="current_office" />
		<ul class="officelist">
			<xsl:for-each select="office[generate-id(.) = generate-id(key('comp', company/@id))]">
				<xsl:variable name="id" select="company/@id"/>
				<xsl:variable name="type_count" select="count(../office[company/@id=$id])"/>

					<li class="company">
						<div class="company_block ui-corner-all">
							<xsl:call-template name="company_block"><xsl:with-param name="current" select="company"/></xsl:call-template>
							<!--<a href="/{//city/uri}/companies/{uname}/"><xsl:value-of select="company/name"/></a>&amp;nbsp;(<xsl:value-of select="$type_count"/>)-->
							<ul class="officelist">
								<xsl:apply-templates select="../office[company/@id=$id]" mode="list">
									<xsl:with-param name="current_settlement" select="$current_settlement"   />
									<xsl:with-param name="current_office"     select="$current_office"   />
								</xsl:apply-templates>
							</ul>
						</div>

					</li>

			</xsl:for-each>

			<xsl:apply-templates select="office[not(company)]"  mode="list">
				<xsl:with-param name="current_settlement" select="$current_settlement"   />
				<xsl:with-param name="current_office"     select="$current_office"   />
			</xsl:apply-templates>
		</ul>
	</xsl:template>

	<xsl:template match="offices" mode="company">
		<xsl:param name="current_settlement" />
		<xsl:param name="current_office" />
		<xsl:param name="uname" />
		<xsl:param name="id" select="office/company[uname=$uname]/@id" />
		<xsl:variable name="company" select="office/company[@id=$id]" />
		<!-- [<xsl:value-of select="$uname" />][<xsl:value-of select="$id" />] -->
		<div class="company_block ui-corner-all">
			<xsl:call-template name="company_block"><xsl:with-param name="current" select="$company"/></xsl:call-template>
			<!--<a href="/{//city/uri}/companies/{uname}/"><xsl:value-of select="company/name"/></a>&amp;nbsp;(<xsl:value-of select="$type_count"/>)-->
			<ul class="officelist">
				<xsl:apply-templates select="office[company/@id=$id]" mode="list">
					<xsl:with-param name="current_settlement" select="$current_settlement"   />
					<xsl:with-param name="current_office"     select="$current_office"   />
				</xsl:apply-templates>
			</ul>
		</div>
	</xsl:template>


	<xsl:template match="settlements">
		<xsl:param name="city" />
		<ul class="settlements">
			<xsl:for-each select="settlement">
				<li>
					<xsl:choose>
						<xsl:when test="@id=$city/@id">
							<b>
								<xsl:value-of select="name"/>
							</b>
							(<xsl:value-of select="count_banks"/>)
						</xsl:when>
						<xsl:otherwise>
							<a href="/{uri}/">
								<xsl:value-of select="name"/>
							</a>
							(<xsl:value-of select="count_banks"/>)
						</xsl:otherwise>
					</xsl:choose>
				</li>
			</xsl:for-each>
		</ul>
	</xsl:template>


	<xsl:template name="geo">
		<xsl:if test="$country">
			<ul>
				<li class="sel">
					<a href="{$company/uri}/{$countryid}/">
						<xsl:value-of select="$country/countryName"/>
					</a>
					(<xsl:value-of select="$country/count_banks"/>)

					<ul>
						<xsl:if test="$adm1">

							<li class="sel">
								<a href="{$company/uri}/{$countryid}/{$adm1id}/">
									<xsl:value-of select="$adm1/name"/>
								</a>
								&amp;nbsp;(<xsl:value-of select="$adm1/count_banks"/>)<ul>
									<xsl:for-each select="/out/geonames/regions[@level=2]/region[uri=$adm2/uri]">
										<li class="sel">
											<a href="{$company/uri}/{$country/uri}/{$adm1/uri}/{uri}">
												<xsl:value-of select="name"/>
											</a>
											&amp;nbsp;(<xsl:value-of select="count_banks"/>)
											<xsl:if test="uri=$adm2/uri">
												<ul>
													<xsl:for-each select="/out/geonames/settlements/settlement[@id=$city/@id]">
														<li class="sel"><a href="{$company/uri}/{uri}/"><xsl:value-of select="name" /></a> (<xsl:value-of select="count_banks" />)</li>
													</xsl:for-each>

													<xsl:for-each select="/out/geonames/settlements/settlement[not(@id=$city/@id)]">
														<li><a href="{$company/uri}/{uri}/"><xsl:value-of select="name" /></a> (<xsl:value-of select="count_banks" />)</li>
													</xsl:for-each>
												</ul>
											</xsl:if>
										</li>
									</xsl:for-each>
									<xsl:for-each select="/out/geonames/regions[@level=2]/region[not(uri=$adm2/uri)]">
										<li>
											<a href="{$company/uri}/{$country/uri}/{$adm1/uri}/{uri}">
												<xsl:value-of select="name"/>
											</a>
											&amp;nbsp;(<xsl:value-of select="count_banks"/>)
										</li>
									</xsl:for-each>

								</ul>
							</li>
						</xsl:if>
						<xsl:for-each select="/out/geonames/regions[@level=1 and @country=$country/countryCode]/region[not(uri=$adm1/uri)]">
							<li>
								<a href="{$company/uri}/{$countryid}/{uri}/">
									<xsl:value-of select="name"/>
								</a>
								&amp;nbsp;(<xsl:value-of select="count_banks"/>)
							</li>
						</xsl:for-each>
					</ul>

				</li>
			</ul>

		</xsl:if>

		<ul>
			<xsl:for-each select="/out/geonames/countries/country[not(uri=$countryid)]">
				<li>
					<a href="{$company/uri}/{uri}/">
						<xsl:value-of select="countryName"/>
					</a>
					(<xsl:value-of select="count_banks"/>)
				</li>
			</xsl:for-each>
		</ul>


	</xsl:template>

	<xsl:template name="company_block">
		<xsl:param name="current"/>
		<div style="text-align:center; overflow:hidden">
			<div class="title">
				<!--<a href="/bank/{$current/@id}">-->
				<a href="/companies/{$current/uname}/{//city/uri}/">
					<xsl:value-of select="$current/name"/>
				</a>
			</div>
			<xsl:if test="$current/@logo">
				<div style="background:url(/img/logo/{$current/@logo}) center no-repeat;">
					<img src="/img/logo/{$current/@logo}"  style="visibility:hidden"/>
				</div>
			</xsl:if>
		<!--<xsl:if test="not($current/@logo)"><input value="l{$current/@id}"/></xsl:if>-->
		</div>
	</xsl:template>

	<xsl:template name="geoBanks">



		<xsl:choose>
			<xsl:when test="/out/presets/uri-vars/companyname and not($city/name='')">
				<h1>
					<xsl:copy-of select="$geoTitle" />
				</h1>

				<xsl:apply-templates select="$offices" mode="company">
					<xsl:with-param name="current_settlement" select="$current_settlement"/>
					<xsl:with-param name="current_office" select="$current_office"/>
					<xsl:with-param name="uname" select="/out/presets/uri-vars/companyname" />
				</xsl:apply-templates>

			</xsl:when>
			<xsl:when test="$office_opened">
				<!-- if office opened -->

				<table width="100%">
					<tr>
						<td style="min-width: 300px; max-width: 400px;">

							<div class="company_block ui-corner-all">
								<xsl:call-template name="company_block">
									<xsl:with-param name="current" select="$office_opened/company"/>
								</xsl:call-template>
							</div>

							<h1><xsl:value-of select="$office_opened/name"/> </h1>
							<h2><xsl:value-of select="$office_opened/web/title"/></h2>

					<!--	<textarea rows="20" cols="60"><xsl:copy-of select="$current_office"/></textarea>-->
							<table style="width: 100%">
								<tr>
									<td>Address:</td>
									<td>
									<xsl:value-of select="$office_opened/address"/>,
									<xsl:value-of select="$city/name"/>,
									<xsl:if test="$adm2/name"><xsl:value-of select="$adm2/name"/>, </xsl:if>
									<xsl:value-of select="$adm1/name"/>,
									<xsl:value-of select="$country/countryName"/>,
									<xsl:value-of select="$office_opened/zip"/>
									</td>
								</tr>
								<xsl:if test="not($office_opened/phone='')">
									<tr><td>Phone:</td><td><xsl:value-of select="$office_opened/phone"/></td></tr>
								</xsl:if>
								<xsl:if test="not($office_opened/fax='')">
									<tr><td>Fax:</td><td><xsl:value-of select="$office_opened/fax"/></td></tr>
								</xsl:if>
								<xsl:if test="not($office_opened/email='')">
									<tr><td>E-mail</td><td><a href="mailto:{$office_opened/email}"><xsl:value-of select="$office_opened/email"/></a></td></tr>
								</xsl:if>
								<xsl:if test="not($office_opened/web/url='')">
									<tr><td>Website:</td><td><a href="{$office_opened/web/url}" rel="nofollow" target="_blank"><xsl:value-of select="$office_opened/web/url"/></a></td></tr>
								</xsl:if>
							</table>

						</td>
						<td style="max-width: 500px">
							<!--<textarea rows="20" cols="100"><xsl:copy-of select="$offices"/></textarea>-->
							<!--[<xsl:value-of select="count($same_offices)"/>]  -->                      <!-- ********************** -->
							<xsl:if test="count($current_office/offices/office)>1">
							<div class="tblock">
								<h2><xsl:value-of select="$current_office/name"/> in <xsl:value-of select="$city/name"/></h2>
								<!-- <textarea rows="20" cols="100"><xsl:copy-of select="$current_office"/></textarea> -->
								<xsl:apply-templates select="$current_office/offices" mode="address">
									<xsl:with-param name="current_settlement" select="$current_settlement" />
								</xsl:apply-templates>
							</div>
							</xsl:if>

							<div class="tblock">
								<h2>Banks in <xsl:value-of select="$city/name"/></h2>
								<h3>(<xsl:if test="$adm2"><xsl:value-of select="$adm2/name"/>, </xsl:if> <xsl:value-of select="$adm1/name"/>, <xsl:value-of select="$country/countryName"/>)</h3>
								<h4><xsl:value-of select="$current_settlement/count_banks"/> of banks found:</h4>
								<xsl:apply-templates select="$offices">
									<xsl:with-param name="current_settlement" select="$current_settlement"/>
									<xsl:with-param name="current_office" select="$current_office"/>
								</xsl:apply-templates>
							</div>


							<xsl:if test="count(/out/geonames/settlements/settlement)&gt;1">
							<div class="tblock">
								<h2>Banks in the next settlements:</h2>
								<xsl:apply-templates select="/out/geonames/settlements">
									<xsl:with-param name="city" select="$city"/>
								</xsl:apply-templates>
							</div>
							</xsl:if>
						</td>
					</tr>
				</table>

				<!-- -->
			</xsl:when>

			<xsl:when test="$current_office and not($office_opened) and not($current_office/name='')">
				<table width="100%">
					<tr>
						<td style="min-width: 300px; max-width: 400px;">
								<h1><xsl:value-of select="$current_office/name"/> in <xsl:value-of select="$city/name"/></h1>
								Office list of the <strong><xsl:value-of select="$current_office/name"/></strong> by address:
						<!--		 <textarea rows="20" cols="80"><xsl:copy-of select="$offices" /></textarea>-->
								<xsl:apply-templates select="$current_office/offices" mode="address">
									<xsl:with-param name="current_settlement" select="$current_settlement" />
									<xsl:with-param name="current_office" select="$current_office" />
								</xsl:apply-templates>
						</td>
						<td style="max-width: 500px">

							<div class="tblock">
								<h2>Banks in <xsl:value-of select="$city/name"/></h2>
								<h3>(<xsl:if test="$adm2"><xsl:value-of select="$adm2/name"/>, </xsl:if> <xsl:value-of select="$adm1/name"/>, <xsl:value-of select="$country/countryName"/>)</h3>
								<p><xsl:value-of select="$current_settlement/count_banks"/> of banks found:</p>
								<xsl:apply-templates select="$offices">
									<xsl:with-param name="current_settlement" select="$current_settlement"/>
									<xsl:with-param name="current_office" select="$current_office"/>
								</xsl:apply-templates>
							</div>

							<xsl:if test="count(/out/geonames/settlements/settlement)&gt;1">
							<div class="tblock">
								<h2>Banks in the next settlements:</h2>
								<xsl:apply-templates select="/out/geonames/settlements">
									<xsl:with-param name="city" select="$city"/>
								</xsl:apply-templates>
							</div>
							</xsl:if>
						</td>
					</tr>
				</table>
			</xsl:when>

			<xsl:when test="$city/@id and not($city/@id='')">
				<table width="100%">
					<tr>
						<td style="min-width: 300px; max-width: 400px;">
							<h1><xsl:copy-of select="$geoTitle" /></h1>
							<xsl:apply-templates select="$offices">
								<xsl:with-param name="current_settlement" select="$current_settlement"/>
								<xsl:with-param name="current_office" select="$current_office"/>
							</xsl:apply-templates>
						</td>
						<td style="max-width: 500px">
							<xsl:if test="count(/out/geonames/settlements/settlement)&gt;1">
							<div class="tblock">
								<h2>Banks in the neighbour settlements:</h2>
								<xsl:apply-templates select="/out/geonames/settlements">
									<xsl:with-param name="city" select="$city"/>
								</xsl:apply-templates>
							</div>
							</xsl:if>
						</td>
					</tr>
				</table>
			</xsl:when>

			<xsl:otherwise>
				<xsl:if test="count(/out/geonames/settlements/settlement)">
					<h1><xsl:copy-of select="$geoTitle" />:</h1>
					<div style="float:left; width:60%">
						<h2>Regions</h2>
						<xsl:call-template name="geo" />
					</div>
					<div style="float:left">
						<h2>Settlements</h2>
						<xsl:apply-templates select="/out/geonames/settlements">
							<xsl:with-param name="city" select="$city"/>
						</xsl:apply-templates>
					</div>
					<div style="clear:both"></div>
				</xsl:if>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

</xsl:stylesheet>