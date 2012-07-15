<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" version="1.0" encoding="utf-8" indent="yes"/>
	<xsl:template match="/">
		<xsl:apply-templates select="//item" mode="form"/>
	</xsl:template>
	<xsl:template match="item" mode="form">
		<div class="edit ui-corner-all ui-state-default" style="padding:5px">
            <div class="ui-corner-all ui-widget-content">
                <xsl:call-template name="uploadfile" />
            </div>
			<form action="/adm/news/{@id}/update/" class="ajax" target="item_{@id}" method="POST">
				<xsl:call-template name="item"></xsl:call-template>
				<div align="center">
					<button ico="ui-icon-disk">Save</button>
					<button ico="ui-icon-cancel" type="button" onclick="$('#item_{@id} .form').html(''); $('#item_{@id} .view').show()">Cancel</button>
				</div>
			</form>
		</div>
	</xsl:template>

    <xsl:template name="uploadfile">
        <form name="form" action="" method="POST" enctype="multipart/form-data">
            <img id="loading" src="/plugins/upload/loading.gif" style="display:none;" />
            <input id="fileToUpload" type="file" size="45" name="fileToUpload" class="input" />
            <input type="hidden" name="owner" value="News" />
            <input type="hidden" name="id" value="{@id}" />
            <button class="button" id="buttonUpload" onclick="return ajaxFileUpload();">Upload</button>
        </form>
    </xsl:template>

	<xsl:template name="additem">
		<xsl:param name="channelid" select="@id"/>
		<div class="ajax ui-corner-all ui-state-default" style="padding:2px">
			<div class="ui-widget-header ui-corner-all" style="padding:5px; cursor:pointer" onclick="$(this).parent().find('.additemform').toggle(); $('.newsitem .form').hide(); $('.newsitem .view').show();">
				<span class="ui-icon ui-icon-plus" style="float:left"></span>
				Add new item to the channel: <xsl:value-of select="title" />
			</div>
			<div class="additemform" style="padding:5px; display:none">
				<form action="/adm/news/channels/{$channelid}/additem/" class="ajax" target="channel_{$channelid}" outtype="prepend" method="POST">
					<xsl:call-template name="item">
                        <xsl:with-param name="ch" select="@id" />
                        <xsl:with-param name="title" />
					</xsl:call-template>
					<div align="center">
						<button ico="ui-icon-plus">Add</button>
						<button ico="ui-icon-cancel" type="button" onclick="$('.additem').hide()">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</xsl:template>


	<xsl:template name="item">
        <xsl:param name="ch" select="channel" />
        <xsl:param name="title" select="title" />
			<table width="100%">
				<tr>
					<td width="100">Date</td>
					<td><input name="pdate" class="datepicker" value="{pubDate}" /></td>
				</tr>
				<tr>
					<td>Title</td>
					<td><textarea name="title" style="width:100%" rows="2"><xsl:value-of select="$title"/></textarea></td>
				</tr>
				<tr>
					<td>Description</td>
					<td><textarea name="description" style="width:100%" rows="5"><xsl:value-of select="description"/></textarea></td>
				</tr>
				<tr>
					<td>Content</td>
					<td><textarea name="content" style="width:100%" rows="10"><xsl:value-of select="content"/></textarea></td>
				</tr>


				<tr>
					<td>Channel</td>
					<td>

						<select style="width:300px" name="channel">
							<xsl:for-each select="/out/sitemap//page[uri='/channels']/pages/page">
								<option value="{@id}">
									<xsl:if test="$ch=@id"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
									<xsl:value-of select="title" />
								</option>
							</xsl:for-each>
						</select>
					</td>
				</tr>
				<tr>
					<td>Company:</td>
					<td>
						<input id="bankid_{@id}" type="hidden" name="CompanyId" value="{company/@id}" />
						<input  style="width:300px" url="/companies/channels/json/" class="autocomplete" idfield="bankid_{@id}" description="bankdescr_{@id}" />
						<div id="bankdescr_{@id}">
							<xsl:value-of select="company" />
							<xsl:if test="not(company)">Unselected</xsl:if>

						</div>
					</td>
				</tr>



			</table>
	</xsl:template>
</xsl:stylesheet>