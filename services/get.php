<?php
if (isset(Router::$params[2]))
{
    $getCategory = (Router::$params[2]);
}

//Array of feeds
$feeds = [
    "jobsinmalta" => "https://jobsinmalta.com/jobs.rss?exclude_recruitment_agencies=1&limit=5",
    "konnect" => "https://www.konnekt.com/opportunities/feed",
    "castille" => "https://www.castilleresources.com/en/rss"
];

//If we don't have a category we list every category. 
if (empty($getCategory))
{
    $xmlFinal = categoriesXML($feeds);
}
else //we list the vacancies of the category of the url variable
{
    searchVacanciesByCategory($getCategory, $feeds);
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


//We have to pass the array to this function 
//@input urls of everyfeeds
//@output display an Xml with all categories of the feeds without repeat
function categoriesXML($feedUrls){	
    try 
    {
        $categories = array();
        $rootXml = 
            ' <?xml-stylesheet type="text/xsl" href="./xslt/list2.xsl"?>'
            .'<root xmlns:jobsinmalta="http://jobsinmalta.com/jobsinmalta-schema" '
            . 'xmlns:atom="http://www.w3.org/2005/Atom" version="2.0" '
            . 'jobsinmalta:noNamespaceSchemaLocation="http://jobsinmalta.com/jobsinmalta.xsd">'            
            . '</root>';	
        foreach ($feedUrls as $feedUrl):
            $xml = new SimpleXMLElement(
                loadFeed($feedUrl), LIBXML_NOCDATA | LIBXML_NOWARNING
            );
            $categories = returnCategories($xml, $categories);		
        endforeach;

        $xmlCategories  = new SimpleXMLElement($rootXml);
        $xmlCategories->addChild('categories');
        foreach ($categories as $categorie):
            $categoria = (string)$categorie;
            $xmlCategories->categories->addChild('category', htmlspecialchars($categoria));
        endforeach; 
        $xmlCategories->addAttribute('encoding', 'utf-8');  
        echo $xmlCategories->asXml();
        } 
    catch (Exception $e) 
    {
        echo IO::error($e->getMessage());
    }
} //end of onlineXML


//This function return an array of categories
//@input XML and Categories
//@output all categories of feed
function returnCategories($xml, $categories)
{
    foreach ($xml->channel[0]->item as $item)
    {
        if (!in_array($item->category, $categories))
        {
            $valor =  (string) ($item->category);
            array_push($categories, $valor); 
        }
    }
    return ($categories);
}


//This function will return an xml with every vacancy of the category transfered to the function
//@input Category and Xml feeds url
//@output display a Xml document
function searchVacanciesByCategory($category, $feedUrls){
    try 
    {
        $categories = array();
        $rootXml = 
            '<?xml-stylesheet type="text/xsl" href="../xslt/list3.xsl"?>'
            .'<root xmlns:jobsinmalta="http://jobsinmalta.com/jobsinmalta-schema" '
            .'xmlns:atom="http://www.w3.org/2005/Atom" version="2.0" '
            .'jobsinmalta:noNamespaceSchemaLocation="http://jobsinmalta.com/jobsinmalta.xsd">';	
        $final = array();
        foreach ($feedUrls as $feedUrl):
            $xml = new SimpleXMLElement(
                loadFeed($feedUrl), LIBXML_NOCDATA | LIBXML_NOWARNING
            );
            $vacan = vacanciesByCategory($xml->channel->item, $category);
            array_push($final, $vacan);
        endforeach;
        $vacancies = unifyXLM($final);
        foreach ($vacancies as $k=> $vacancie)
        {
            $rootXml .= "\n" . $vacancies[$k]->asXML(). "\n";
        }
        $rootXml .= "\n\n" . '</root>';
        echo $rootXml;
    } 
    catch (Exception $e)
    {
        echo IO::error($e->getMessage());
    }
}

//This function removes any vacancy which no is identical to category
//@input all vacancies and category to match
//@output vacancies with the matched category
function vacanciesByCategory($vacancies, $category)
{
    $category = str_replace("%20", " ", $category);
    $vcn = array();
    foreach ($vacancies as $vacancy)
    {
        $categoria = (string)$vacancy->category[0];
        $categoria = str_replace("%20"," ",$categoria);
        if ($categoria == $category)
        {
            array_push($vcn, $vacancy);
        }
    }
    return $vcn;
}


//This function unify the object Xml of different Feeds to join in one array
//@input vacancies by category
//@output unified version of these vacancies
function unifyXLM($vcn){
    $arrayFinal = array();
    foreach ($vcn as $vc)
    {
        $cantidad = count($vc);
        if ($cantidad > 0):
            for ($i = 0; $i < $cantidad; $i++) {
                $arrayFinal[] = $vc[$i];
            }
        endif;
    }
    return $arrayFinal;
}