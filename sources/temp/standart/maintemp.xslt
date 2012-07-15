<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" version="1.0" encoding="utf-8" indent="yes" doctype-system="about:legacy-compat"/>
	<xsl:template match="/">

	</xsl:template>

	<xsl:template match="page" mode="display">
		<ul class="icons ui-widget ui-helper-clearfix controls ui-state-default ui-corner-all" style="right:0; top:0">
			<li class="ui-state-default ui-corner-all" onclick="winHref('currentPageWin', '/adm/sitemap/{@id}/editdata/');" title="Edit document"><span class="ui-icon ui-icon-pencil"></span></li>
			<li class="ui-state-default ui-corner-all" onclick="winHref('currentPageWin', '/adm/sitemap/{@id}/');" title="Show folder tree"><span class="ui-icon ui-icon-folder-open"></span></li>
		</ul>
		<div id="currentPageWin"></div>
		<h1><xsl:value-of select="title" /></h1>
		<div class="MainTextArea">
			<xsl:value-of select="source" />
		</div>
	</xsl:template>

	<xsl:template name="MainTemp">
		<!-- * DECLARATION CONTENT ************************************** -->
		<xsl:param name="extra" />
		<xsl:param name="precontent" />
		<xsl:param name="postcontent" />
		<xsl:param name="CurrentPage" select="//current/page" />
		<xsl:param name="title">
			<xsl:value-of select="//pagetitle" />
			<xsl:value-of select="$geoTitle" />
		</xsl:param>
		<xsl:param name="content" >
			<xsl:copy-of select="$precontent" />
			<!-- Page content -->
			<xsl:apply-templates select="$CurrentPage" mode="display" />

			<!--<textarea><xsl:copy-of select="$CurrentPage"/></textarea>-->
			<!-- News -->
            <table class="newscoumns">
                <tr>
                    <td><xsl:apply-templates select="//channel[@id='145']"/></td>
                    <td><xsl:apply-templates select="//channel[@id='146']"/></td>
                    <td><xsl:apply-templates select="//channel[@id='147']"/></td>
                </tr>
            </table>

			<!-- Banks by Geo location -->

			<xsl:call-template name="geoBanks" />

			<xsl:copy-of select="$postcontent" />
		</xsl:param>
		<xsl:param name="left">
			<xsl:apply-templates select="/out/sitemap//page[uri='/menus/main']" mode="menu" />
			<xsl:apply-templates select="/out/sitemap//page[uri='/menus/offers']" mode="menu" />

			<div class="menu">
				<h3>Banks by location</h3>
				<xsl:call-template name="geo" />
			</div>

		</xsl:param>
		<!-- ************************************************************ -->
		<html>
			<head>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
				<title><xsl:value-of select="$title" /></title>
				<link rel="stylesheet" href="/css/ui/jquery-ui-1.8.11.custom.css"/>
				<link rel="stylesheet" href="/css/std/main.css"/>
                <script>

                //    alert('<xsl:value-of select="/out/user/name"/>')
                </script>
				<script src="/js/jquery/jquery-1.5.1.min.js"></script>
				<script src="/js/jquery/jquery-ui-1.8.11.custom.min.js"></script>
                <script type="text/javascript" src="/plugins/upload/ajaxfileupload.js"></script>
				<script src="/js/iface.js"></script>
				<style type="text/css">
					.tblock{float:left; width: 250px; padding: 0 10px}
				</style>
			</head>
			<body style="margin:0; padding:0">

				<div id="header" style="height:100px"></div>
				<div id="columns">
					<table width="100%" height="100%" border="0">
						<tr>
							<td style="width:20%; min-width:300px; height:50px">
								<div style="width:180; height:38; margin:0 auto; background:url(/img/iface/allbanks.org/logo.png)"></div>
								<div style="text-align:center">Global Banks Catalogue</div>
							</td>
							<td colspan="2">
								<!-- Search bar -->
								<xsl:call-template name="searchbar" />
							</td>
						</tr>
						<tr>
							<td id="colNavigation">
								<!-- Geo menu -->
								<xsl:copy-of select="$left" />
							</td>
							<xsl:variable name="company" select="//company[uname=/out/presets/uri-vars/companyname]"/>
							<td id="colContent" style="padding: 1em 0.5em; padding-right: 50px">
								<xsl:copy-of select="$content" />
							</td>
							<td id="colExtra">
								<xsl:value-of select="$extra" />
							</td>
						</tr>
                        <tr>
                            <td colspan="3">
	                            <!--
                                <div id="footer">
                                    <div id="botMenu" style="background:#023; padding-bottom:5px; padding-left: 5px">
                                        <div class="ui-corner-bottom element" id="loginButton" onmouseover="$('#loginDiv').show()" onmouseout="$('#loginDiv').hide()">
                                            <div id="loginDiv" class="ui-widget ui-corner-top ui-widget-content login-widget body">
                                                <xsl:if test="/out/user"><xsl:call-template name="userMenu" /></xsl:if>
                                                <xsl:if test="not(/out/user)"><xsl:call-template name="login" /></xsl:if>
                                            </div>
                                            <div class="header">
                                                <xsl:if test="/out/user"><xsl:value-of select="/out/user/name" /></xsl:if>
                                                <xsl:if test="not(/out/user)">Login</xsl:if>
                                            </div>
                                        </div>
                                    </div>
                                </div>
	                            -->
                            </td>
                        </tr>
					</table>
				</div>

			</body>
		</html>
	</xsl:template>

	<xsl:template name="searchbar">
		<div class="tabs tabs-bottom" style="height:60px; margin-right:50px; min-width:600px">
			<ul>
				<li>
					<a href="#by-google">content search</a>
				</li>
				<li>
					<a href="#by-name">by name</a>
				</li>
				<li>
					<a href="#by-loc">by location</a>
				</li>
			</ul>

			<div id="by-name" style="height:30px">
				<table width="100%">
					<tr>
						<td>
							<div style="height:25px; background:#fff; margin-right:5px; border:#ccc solid 1px; overflow:hidden"
								 class="ui-corner-all">
								<input type="text" style="font-size:16px; width:99%; border:none; margin:0 2px; height:25px" />
							</div>
						</td>

						<td style="width:130px;">
							<button style="float:right; width:130px; height:25px; margin-left:auto">
								Find Bank
							</button>
						</td>
					</tr>
				</table>
			</div>



			<div id="by-loc" style="height:30px">


				<div class="ui-widget" style="height:25px; padding-left:5px">
					<label>Type or select:</label>
					<select name="country" id="combobox" style="height:20px">
						<option value="">Select one...</option>
						<option value="america">America</option>
						<option value="asia">Asia</option>
						<option value="europe">Europe</option>
						<option value="oceania">Oceania</option>
						<option value="middle-east">Middle East</option>
					</select>
				</div>
			</div>

			<div id="by-google" style="height:30px">
				<table width="100%">
					<tr>
						<td>
							<div style="height:25px; background:#fff; margin-right:5px; border:#ccc solid 1px; overflow:hidden"
								 class="ui-corner-all">
								<input type="text"
									   style="font-size:16px; width:99%; border:none; margin:0 2px; height:25px"/>
							</div>
						</td>

						<td style="width:130px;">
							<button style="float:right; width:130px; height:25px; margin-left:auto">
								Search
							</button>
						</td>
					</tr>
				</table>
			</div>

		</div>
	</xsl:template>

    <xsl:template name="channel">
        <xsl:attribute name="id" />
        <xsl:if test="$id">
            <xsl:choose>
                <xsl:when test="//channel[@id=$id]">
                    <xsl:apply-templates select="//channel[@id=$id]" />
                </xsl:when>
                <xsl:otherwise>
                    <div>
                        <xsl:call-template name="additem" mode="form"/>
                    </div>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:if>
    </xsl:template>

	<xsl:template match="channel">
        <h3><xsl:value-of select="title" /></h3>
		<div>
			<xsl:call-template name="additem" mode="form"/>
		</div>
		<ul id="channel_{@id}" style="list-style:none; padding-left:0">
			<xsl:apply-templates select="item"/>
		</ul>
	</xsl:template>

	<xsl:template match="item">
		<li id="item_{@id}" class="newsitem">
			<xsl:apply-templates select="." mode="show"/>
		</li>
	</xsl:template>
</xsl:stylesheet>