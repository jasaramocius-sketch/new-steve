<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:sm="http://www.sitemaps.org/schemas/sitemap/0.9"
    exclude-result-prefixes="sm">
    <xsl:output method="html" encoding="UTF-8" indent="yes" />

    <xsl:template match="/">
        <html lang="en">
            <head>
                <meta charset="UTF-8" />
                <meta name="viewport" content="width=device-width, initial-scale=1" />
                <title>XML Sitemap | StAutoParts</title>
                <style>
                    :root { color-scheme: light; --ink: #1d2927; --muted: #64716e; --line: #e2e8e6; --accent: #c93424; --wash: #f3f7f5; }
                    * { box-sizing: border-box; }
                    body { margin: 0; background: var(--wash); color: var(--ink); font: 15px/1.5 Arial, Helvetica, sans-serif; }
                    main { width: min(100% - 32px, 1040px); margin: 48px auto; }
                    header { padding: 28px 30px; color: #fff; background: #193b36; border-radius: 6px 6px 0 0; }
                    .eyebrow { margin: 0 0 6px; color: #b7d9d0; font-size: 12px; font-weight: 700; text-transform: uppercase; }
                    h1 { margin: 0; font-size: 28px; line-height: 1.2; }
                    .intro { max-width: 700px; margin: 10px 0 0; color: #e0eeea; }
                    .summary { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 16px 30px; background: #fff; border: 1px solid var(--line); border-top: 0; color: var(--muted); }
                    .count { color: var(--ink); font-weight: 700; }
                    .table-wrap { overflow-x: auto; background: #fff; border: 1px solid var(--line); border-top: 0; border-radius: 0 0 6px 6px; }
                    table { width: 100%; border-collapse: collapse; }
                    th, td { padding: 15px 20px; border-top: 1px solid var(--line); text-align: left; vertical-align: top; }
                    th { color: var(--muted); background: #f8faf9; font-size: 12px; font-weight: 700; text-transform: uppercase; }
                    tbody tr:hover { background: #fbfdfc; }
                    a { color: #176f61; text-decoration: none; overflow-wrap: anywhere; }
                    a:hover { color: var(--accent); text-decoration: underline; }
                    .url { min-width: 320px; }
                    .date { min-width: 190px; color: var(--muted); white-space: nowrap; }
                    footer { padding: 16px 4px; color: var(--muted); font-size: 12px; }
                    @media (max-width: 600px) {
                        main { width: min(100% - 20px, 1040px); margin: 20px auto; }
                        header { padding: 22px 18px; }
                        h1 { font-size: 23px; }
                        .summary { align-items: flex-start; flex-direction: column; padding: 14px 18px; }
                        th, td { padding: 12px 14px; }
                    }
                </style>
            </head>
            <body>
                <main>
                    <header>
                        <p class="eyebrow">StAutoParts · Search index</p>
                        <h1>XML Sitemap</h1>
                        <p class="intro">This sitemap is generated for search engines. Select a sitemap or URL below to open it.</p>
                    </header>
                    <xsl:choose>
                        <xsl:when test="/sm:sitemapindex">
                            <div class="summary">
                                <span>Available sitemaps</span>
                                <span class="count"><xsl:value-of select="count(/sm:sitemapindex/sm:sitemap)" /></span>
                            </div>
                            <div class="table-wrap">
                                <table>
                                    <thead><tr><th>Sitemap</th><th>Last Modified</th></tr></thead>
                                    <tbody>
                                        <xsl:for-each select="/sm:sitemapindex/sm:sitemap">
                                            <tr>
                                                <td class="url"><a href="{sm:loc}"><xsl:value-of select="sm:loc" /></a></td>
                                                <td class="date"><xsl:value-of select="sm:lastmod" /></td>
                                            </tr>
                                        </xsl:for-each>
                                    </tbody>
                                </table>
                            </div>
                        </xsl:when>
                        <xsl:when test="/sm:urlset">
                            <div class="summary">
                                <span>URLs in this sitemap</span>
                                <span class="count"><xsl:value-of select="count(/sm:urlset/sm:url)" /></span>
                            </div>
                            <div class="table-wrap">
                                <table>
                                    <thead><tr><th>URL</th><th>Last Modified</th></tr></thead>
                                    <tbody>
                                        <xsl:for-each select="/sm:urlset/sm:url">
                                            <tr>
                                                <td class="url"><a href="{sm:loc}"><xsl:value-of select="sm:loc" /></a></td>
                                                <td class="date"><xsl:value-of select="sm:lastmod" /></td>
                                            </tr>
                                        </xsl:for-each>
                                    </tbody>
                                </table>
                            </div>
                        </xsl:when>
                    </xsl:choose>
                    <footer>XML sitemap · StAutoParts</footer>
                </main>
            </body>
        </html>
    </xsl:template>
</xsl:stylesheet>
