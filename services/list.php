<?php
//We decide if we want to use cache or not. 
$cacheMode = true;
$timeCache = 120;
if (isset(Router::$params[2]))
{
    $getChannel = htmlspecialchars(Router::$params[2]);
}
else {
    CORE::$SERVICE = new SERVICE("404");
    CORE::$SERVICE->renderPage();
    die;
}

//Array of feeds
$feeds = [
    "jobsinmalta" => "https://jobsinmalta.com/jobs.rss?exclude_recruitment_agencies=1&limit=5",
    "konnect" => "https://www.konnekt.com/opportunities/feed",
    "castille" => "https://www.castilleresources.com/en/rss"
];

//We have to compare if feed is in our Array, if not we send and error. 
if (!array_key_exists($getChannel, $feeds) || (!isset(Router::$params[2])))
{
    CORE::$SERVICE = new SERVICE("404");
    CORE::$SERVICE->renderPage();
}
else
{
    //Use FeedUrl as router parameters: for example -- $feedUrl = $feeds["konnect"];
    $feedUrl = $feeds[$getChannel];
    if ($cacheMode == true) //Cache enabled
    {
        cache($getChannel, $feedUrl, $timeCache);
    } 
    //online version
    else{
        onlineXML($feedUrl); 
    }
}


//This function get and XML local file if exists, if not, load the onlineXML function
//@input channel in Xml format, url of channel and time of cache
//@output dipslay an Xml with all vacancies cached
function cache($xmlChannel, $urlChannel, $timeCache)
{
    $pathToXml = '../tmp/'.$xmlChannel.'.xml';
    if ( (file_exists($pathToXml)) && (time() - filemtime($pathToXml) <= ($timeCache)) ) //Load cached version
    {
        $xmlFile  = new SimpleXMLElement(file_get_contents($pathToXml));
        echo $xmlFile->asXML();
    }
    else {
        try 
        {
            $xml = new SimpleXMLElement(
                loadFeed($urlChannel), LIBXML_NOCDATA | LIBXML_NOWARNING
            );
            $titleChannel = $xml->channel[0]->title;
            $channel = $xml->xpath("channel");
            $rootXml = '<?xml-stylesheet type=\'text/xsl\'  href=\'../xslt/list.xsl\'?>' . "\n"
                    . '<root xmlns:jobsinmalta="http://jobsinmalta.com/jobsinmalta-schema" '
                    . 'xmlns:atom="http://www.w3.org/2005/Atom" version="2.0" '
                    . 'jobsinmalta:noNamespaceSchemaLocation="http://jobsinmalta.com/jobsinmalta.xsd">'            
                    . "\n" . $channel[0]->asXML(). "\n" . '</root>';
            $jobs  = new SimpleXMLElement($rootXml, LIBXML_NOCDATA);
            $jobs->addChild('nodes', count($channel[0]->item));
            $jobs->addAttribute('encoding', 'utf-8');  
            $jobs->asXml("../tmp/".$xmlChannel.".xml");
            echo $jobs->asXml();
        } 
        catch (Exception $e) {
            echo IO::error($e->getMessage());
        } 
    }
    die;
}


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
                    CURLOPT_MAXREDIRS => 5,
                    CURLOPT_SSL_VERIFYPEER => false,
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


//If we can't use the XML cached XML file, we use the online version
//@input url of feed
//@output display an XmL with all vacancies not cached. 
function onlineXML($feedUrl){
    try 
    {
        $xml = new SimpleXMLElement(
            loadFeed($feedUrl), LIBXML_NOCDATA | LIBXML_NOWARNING
         );
        $titleChannel = $xml->channel[0]->title;
        $channel = $xml->xpath("channel");
        $rootXml = '<?xml-stylesheet type=\'text/xsl\'  href=\'../xslt/list.xsl\'?>' . "\n"
            . '<root xmlns:jobsinmalta="http://jobsinmalta.com/jobsinmalta-schema" '
            . 'xmlns:atom="http://www.w3.org/2005/Atom" version="2.0" '
            . 'jobsinmalta:noNamespaceSchemaLocation="http://jobsinmalta.com/jobsinmalta.xsd">'
            . "\n" . $channel[0]->asXML(). "\n" . '</root>';
        $jobs  = new SimpleXMLElement($rootXml, LIBXML_NOCDATA);
        $jobs->addChild('nodes', count($channel[0]->item));
        $jobs->addAttribute('encoding', 'utf-8');    
        echo $jobs->asXML();
    } 
    catch (Exception $e) {
        echo IO::error($e->getMessage());
    } 
} //end of onlineXML