<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" version="1.0" encoding="utf-8" indent="yes"/>
    <xsl:template match="/">
        <xsl:call-template name="login" />
    </xsl:template>
	<xsl:template name="login">

		<div id="loginWin">
			<form action="/login/auth/" method="post" id="loginForm">
			<table width="100%">
				<tr>
					<td width="95px"><label for="login">Username</label></td>
					<td>
                        <div class="errorLog"></div>
                        <input name="login" id="login" />
                    </td>
				</tr>
				<tr>
					<td><label for="pass">Password</label></td>
					<td><input name="pass" id="pass" type="password" /></td>
				</tr>
				<tr>
					<td></td>
					<td>
						<button>Enter</button>
						<button type="button" onclick="$('#loginWin').hide()">Cancel</button>
					</td>
				</tr>
			</table>
			</form>
		</div>

	</xsl:template>
</xsl:stylesheet>