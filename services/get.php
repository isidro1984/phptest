<?php
function loadFeed($feedUrl)
{
    $curl = curl_init();
    $header[] = "Accept: application/rss+xml,application/xml,text/xml";
    $header[] = "Cache-Control: max-age=0";
    $header[] = "Accept-Charset: utf-8;q=0.7,*;q=0.7";
    $header[] = "Accept-Language: en,en-us,en-gb;q=0.5";
    curl_setopt_array(
            $curl, [
                    CURLOPT_URL => $feedUrl,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_CONNECTTIMEOUT => 10,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => 'gzip,deflate',
                    CURLOPT_FAILONERROR => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_MAXREDIRS => 5
            ]
    );
    $feed = curl_exec($curl);

    if (curl_errno($curl)) {
        throw new RuntimeException('Curl error (' . curl_errno($curl) . '): ' . curl_error($curl), 8801);
    } else {
        $httpcode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if (!in_array($httpcode, [200, 301, 302], true)) {
            throw new RuntimeException("Unexpected HTTP status code: $httpcode", 8802);
        }
    }

    curl_close($curl);

    return $feed;
}

$feeds = [
        "jobsinmalta" => "https://jobsinmalta.com/jobs.rss?exclude_recruitment_agencies=1&limit=5"
];

$feedUrl = $feeds["jobsinmalta"];
try {
    $xml = new SimpleXMLElement(
            loadFeed($feedUrl), LIBXML_NOCDATA | LIBXML_NOWARNING
    );
    $channel = $xml->xpath("channel/item[category = 'Manufacturing']");
    $rootXml = '<?xml-stylesheet type=\'text/xsl\'  href=\'../xslt/list.xsl\'?>' . "\n"
            . '<root xmlns:jobsinmalta="http://jobsinmalta.com/jobsinmalta-schema" '
            . 'xmlns:atom="http://www.w3.org/2005/Atom" version="2.0" '
            . 'jobsinmalta:noNamespaceSchemaLocation="http://jobsinmalta.com/jobsinmalta.xsd">'
            . "\n<channel>\n" . $channel[0]->asXML(). "\n</channel>\n" . '</root>';
    $jobs  = new SimpleXMLElement($rootXml, LIBXML_NOCDATA);
    $jobs->addAttribute('encoding', 'utf-8');
    echo $jobs->asXML();
} catch (Exception $e) {
    echo IO::error($e->getMessage());
}
