<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" version="1.0" encoding="utf-8" indent="yes"/>
	<xsl:include href="sources/temp/standart/accessories.xslt"/>
    <xsl:template match="/">
		<xsl:call-template name="head-includes" />
		
		<script src="/js/geonames.js"></script>

			<div class="demo">
			
			<div class="ui-widget">
				<label for="city">Your city: </label>
				<input id="city" />
				Powered by <a href="http://geonames.org">geonames.org</a>
			</div>
			
			<div class="ui-widget" style="margin-top:2em; font-family:Arial">
				Result:
				<div id="log" style="height: 200px; width: 300px; overflow: auto;" class="ui-widget-content"></div>
			</div>
			
			</div><!-- End demo -->


	</xsl:template>
</xsl:stylesheet>