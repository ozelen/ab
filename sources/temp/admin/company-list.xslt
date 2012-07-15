<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="html" version="1.0" encoding="utf-8" indent="yes"/>
    <xsl:template match="/">
        <xsl:apply-templates select="obj" mode="company-list" />
    </xsl:template>

    <xsl:template match="obj" mode="company-list">
        <tr id="company_{field[@id='id']}_tr">
            <xsl:apply-templates select="." mode="item" />
        </tr>
    </xsl:template>

    <xsl:template match="obj" mode="item">
        <xsl:variable name="id" select="field[@id='id']" />
        <div id="obj_{$id}_pagedata"></div>
        <td>
            <a href="/companies/{field[@id='uname']}/"  id="company_{$id}_title">
                <xsl:choose>
                    <xsl:when test="field[@id='title']"><xsl:value-of select="field[@id='title']" /></xsl:when>
                    <xsl:otherwise>
                        <xsl:attribute name="class">undef</xsl:attribute>
                            <xsl:value-of select="field[@id='name']" />
                    </xsl:otherwise>
                </xsl:choose>
            </a>
        </td>
        <td>
            <xsl:choose>
                <xsl:when test="field[@id='title']">yes</xsl:when>
                <xsl:otherwise>
                    <button ico="ui-icon-plus" onclick="var but = $(this); $.post('/companies/{field[@id='uname']}/pages/add/', function(data){{if(!data)return; winHref('obj_{$id}_pagedata', '/adm/sitemap/'+data+'/editdata/'); but.replaceWith('yes'); $('#company_{field[@id='id']}_tr a').removeClass('undef') }})">Add</button>
                    <!--
                    <form action="/adm/sitemap/150/postdata/" class="ajax">
                        <input id="Name" name="Name" value="{field[@id='uname']}" />
                        <input style="width:100%" type="text" name="content_Title_{/out/@lang}" value="{field[@id='name']}"/>
                        <button ico="ui-icon-plus">Add</button>
                    </form>
                    -->
                </xsl:otherwise>
            </xsl:choose>
        </td>
        <td onmouseover="$(this).find('div').show()"  onmouseout="$(this).find('div').hide()">
            <xsl:if test="field[@id='logo']=''"><strong>no</strong></xsl:if>
            <xsl:if test="not(field[@id='logo']='')">
                yes
                <div style="position:absolute; display:none; background:#fff; padding:5px; border:#ccc 1px solid; margin-left:10px">
                    <img src="/img/logo/{field[@id='logo']}" />
                </div>
            </xsl:if>
        </td>
        <td><xsl:value-of select="field[@id='offices']" /></td>
        <td><xsl:value-of select="field[@id='releases']" /></td>
    </xsl:template>
</xsl:stylesheet>
