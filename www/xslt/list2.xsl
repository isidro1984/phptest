<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="html"  encoding="UTF-8"/>
    <xsl:template match="/">
    <head>
        <title><xsl:value-of select="root/channel/title" /></title>
            <link href="./css/jobs.css" rel="stylesheet" type="text/css" />
    </head>
	<body>
		<section>
			<h1>Vacancy Categories - Click in the category list below</h1>
			<section>
				<xsl:for-each select="/root/categories/category">
					<a>
						<xsl:attribute name="href">./get/<xsl:value-of select="."/></xsl:attribute>
						<h6>							
							<xsl:value-of select="."/>
						</h6>
					</a>
				</xsl:for-each>
			</section>
		</section>
	</body>
	</xsl:template>
</xsl:stylesheet>