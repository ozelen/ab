<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" version="1.0" encoding="utf-8" indent="yes"/>
    <xsl:template match="/" />

    <xsl:template name="frm">
        <xsl:param name="pageid" />
        <xsl:param name="cur" select="//page-edit" />
        <div id="dbg{$pageid}"></div>
        <!--<textarea><xsl:copy-of select="$cur"/></textarea>-->
        <div class="ui-widget-content ui-corner-all" style="padding:5px; margin:1px">
            <table width="100%">
                <tr>
                    <td><label for="Name">Name</label></td>
                    <td><input id="Name" name="Name" style="width:100%" value="{$cur/name}" /></td>
                </tr>
                <tr>
                    <td><label for="Template">Template</label></td>
                    <td><input id="Template" name="Template" style="width:100%" value="{$cur/template}" /></td>
                </tr>
                <tr>
                    <td><label for="Alias">Alias</label></td>
                    <td><input id="Alias" name="Alias" style="width:100%" value="{$cur/alias}" /></td>
                </tr>
                <tr>
                    <td><label for="Params">Params</label></td>
                    <td><input id="Params" name="Params" style="width:100%" value="{$cur/params}" /></td>
                </tr>
            </table>
        </div>
        <div class="ui-widget-content ui-corner-all" style="padding:5px; margin:1px">
            <table width="100%">
                <tr>
                    <td width="50" class="icons" valign="top" pageid="{$pageid}" href="/adm/sitemap/{$pageid}/data/">
                        <xsl:if test="not($pageid='')">
                            <div class="ui-state-default ui-corner-all icon addlang" style="padding:4px; width:16px; height:16px; margin:5px auto">
                                <span class="ui-icon ui-icon-plus"></span>
                            </div>
                        </xsl:if>
                        <div id="langwin_{$pageid}" class="selectlang" style="display:none" title="Select Language">
                            <select style="width:100%">
                                <xsl:for-each select="//locale/c[@id='langs']/l[not(@id=/out/@lang) and not(@id=$cur/langlist/lang/@id)]">
                                    <option value="{@id}">
                                        <xsl:if test="@id=/page/var/@lang"><xsl:attribute name="disabled">disabled</xsl:attribute></xsl:if>
                                        <xsl:value-of select="."/>
                                    </option>
                                </xsl:for-each>
                            </select>
                        </div>
                        <div style="width:30px; padding:4px 10px; display:none; margin:1px" class="radiobutton ui-state-default ui-corner-left icon sample" target="pagecontent_{$pageid}"></div>
                        <!--<textarea cols="60" rows="10"><xsl:copy-of select="$cur/langlist" /></textarea>-->
                        <div style="width:30px; padding:4px 10px; margin:1px" class="radiobutton ui-state-default ui-state-active ui-corner-left icon langlink" target="pagecontent_{$pageid}" href="/{/out/@lang}/adm/sitemap/{$pageid}/data/" lang="{/out/@lang}"><xsl:value-of select="/out/@lang" /></div>
                        <xsl:for-each select="$cur/langlist/lang[not(@id=/out/@lang)]">
                            <div style="width:30px; padding:4px 10px; margin:1px" class="radiobutton ui-state-default ui-corner-left icon langlink" target="pagecontent_{$pageid}" href="/{@id}/adm/sitemap/{$pageid}/data/"  lang="{@id}"><xsl:value-of select="@id" /></div>
                        </xsl:for-each>
                        <!--
                              <div class="ui-state-default ui-corner-left icon" style="padding:4px 10px; margin:1px" onclick="$(this).addClass('ui-state-active').siblings().removeClass('ui-state-active'); ajax('pcontent_{$uid}', '')">ua</div>
                              <div class="ui-state-default ui-corner-left icon" style="padding:4px 10px; margin:1px" onclick="$(this).addClass('ui-state-active').siblings().removeClass('ui-state-active')">ru</div>
                              -->
                    </td>
                    <td>
                        <div id="pagecontent_{$pageid}" class="ui-widget-content ui-corner-all pagecontent" style="min-height:280px; padding:5px">
                            <div class="lang_{/out/@lang}">
                                <xsl:call-template name="pagedata" />
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </xsl:template>

</xsl:stylesheet>
