<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0">
	<xsl:output method="html" version="1.0" encoding="utf-8" indent="yes"/>

    <xsl:template match="/">
	
	</xsl:template>
	
	<xsl:template name="DocumentFullHeader">
		<xsl:param name="head"/>
		<xsl:param name="title"/>
		<xsl:param name="navigation"/>
		<![CDATA[<html>]]>
			<head>
				<title>
					<!-- TPL: TITLE -->
					
					<xsl:value-of select="$title"/> <xsl:value-of select="//seo-title"/> 
				</title>
				<!-- TPL: HEAD-INCLUDES -->
				<xsl:call-template name="head-includes">
					<xsl:with-param name="add" select="$head"/>
				</xsl:call-template>
				<inject id="google-analytics"></inject>
			</head>
			<![CDATA[
			
			<body>
			<div align="center" style="padding:0 0">
				
				<div style="background:white; height:110px; width:960px; margin:10px 0">
					<iframe width="960" height="110" src="http://skiworld.org.ua/img/bn/960x110/index.php" frameborder="0"></iframe>
				</div>
				
				<div class="white-rounded" style="width:966px"><b class="r3"></b><b class="r1"></b><b class="r1"></b></div>
				<div id="container">
				
				
				<!--
					<div style="height:130px">
						<div id="top_bn" style="background:#000; margin-top:10px; height:90px; width:960px"></div>
					</div>
				-->

			]]>
					<!-- TPL: HEADER -->
					<xsl:call-template name="header">
						<xsl:with-param name="navigation" select="$navigation"/>
					</xsl:call-template>
	</xsl:template>


	<xsl:template name="DocumentFullFooter">
		<div style="height:10px"></div>		
					
					<!--*** SAPE ***-->

					<!-- TPL: FOOTER -->
					<xsl:call-template name="footer"/>
					
		<![CDATA[	
					</div>
					<div class="white-rounded" style="width:966px"><b class="r1"></b><b class="r1"></b><b class="r3"></b></div>
				</div>


				</div>
				<!--
				<script type="text/javascript" src="/js/counters/ga.js">
				<script type="text/javascript" src="/js/counters/li.js">
				-->
			<inject id="mycounter"></inject>
			</body>
		</html>
		]]>
		
	</xsl:template>
	

</xsl:stylesheet>