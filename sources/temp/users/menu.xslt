<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="html" version="1.0" encoding="utf-8" indent="yes"/>
    <xsl:template match="/">
        <xsl:call-template name="userMenu" />
    </xsl:template>
        <xsl:template name="userMenu">
            <xsl:variable name="user" select="/out/user" />
            <div style="padding:5px">Welcome, <b><xsl:value-of select="$user/name" /></b>!</div>
            <ul>
                <li><a href="/users/{$user/name}/profile/">Profile</a></li>
                <li><a href="/adm/sitemap/0/" class="ajax" target="mainTextArea">Document structure</a></li>
                <li><a href="/users/{$user/name}/logout/" class="ajax" target="loginDiv">Log out</a></li>
            </ul>
    </xsl:template>
</xsl:stylesheet>