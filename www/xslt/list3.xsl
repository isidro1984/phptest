<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="html"  encoding="UTF-8"/>
    <xsl:template match="/">
    <head>
        <title><xsl:value-of select="root/channel/title" /></title>
            <link href="../css/jobs.css" rel="stylesheet" type="text/css" />
    </head>
	<body>
		<h1>Vacancies by Category <xsl:value-of select="/root/item/category"/></h1>
		<section>		

					<xsl:for-each select="/root/item">
						<article>
							<header>                
								 <h2><xsl:value-of select="title"/></h2>
								 <p>Published on <xsl:apply-templates select="pubDate"/></p>
							</header>
							<details><summary>Job Details</summary><xsl:value-of select="description"/></details>
							<button><xsl:element name="a">
								<xsl:attribute name="href">
									<xsl:value-of select="link"/>
								</xsl:attribute>
								apply
							</xsl:element></button>
						</article>
					</xsl:for-each>
		</section>
	</body>
	</xsl:template>	
	    <xsl:template match="pubDate">
        <time><xsl:value-of select="." /></time>
    </xsl:template>

    <xsl:template match="item">
        <article>
            <header>                
				 <h2><xsl:value-of select="title"/></h2>
				 <p>Published on <xsl:apply-templates select="pubDate"/></p>
                <h3><xsl:value-of select="category"/></h3>               
            </header>
            <details><summary>Job Details</summary><xsl:value-of select="description"/></details>
            <button><xsl:element name="a">
                <xsl:attribute name="href">
                    <xsl:value-of select="link"/>
                </xsl:attribute>
                apply
            </xsl:element></button>
        </article>
    </xsl:template>
</xsl:stylesheet>