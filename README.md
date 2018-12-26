## Search Engine
CSCI585-HW4/5<br>
Web Search Engine for LA times article using Apache Solr

####1. Install Solr & Apache
Download Solr-7.5.0.zip file and extract in workspace folder.

####2. Create core & Indexing
Create Solr Core
>$bin/solr create -c latimes

Indexing
>$bin/post -c latimes -filetypes html.../workspace/solr/solr-7.5.0/server/solr/crawl/

####3. Generating the Direct Graph
Using JSoup library for extracting links from HTML
(extractLink.jar -> edge-list.txt)
>Document doc = Jsoup.parse(file, "UTF-8", fileUrlMap.get(file.getName())); <br>
Elements links = doc.select("a[href]"); <br>Elements page = doc.select("[src]"); <br>for(Element link : links){ <br>
&nbsp;&nbsp;&nbsp;String url = link.attr("abs:href").trim(); <br>
}

eg. sample line
>80c4c628-7d1e-479a-a8a2-a330a6809275.html 14b9e267-8a6d-473f-8367-0672b9b7f511.html

####4. Compute PageRank
Using NetworkX, compute the PageRank values
(pagerank.py -> external_pageRankFile.txt)
>alpha=0.85, personalization=None, max_iter=30, tol=1e-06, nstart=None, weight='weight',dangling=None

####5. Add external field
modify managed-schema file

>\<fieldType name="external" keyField="id" defVal="0" stored="false" indexed="false"class="solr.ExternalFileField"/>
>\<field name="pageRankFile" type="external" stored=“false" indexed=“false"/>

modify solrconfig.xml to add listeners

>\<listener event="newSearcher" class="org.apache.solr.schema.ExternalFileFieldReloader"/>>\<listener event="firstSearcher" class="org.apache.solr.schema.ExternalFileFieldReloader"/>

####6. Spell Checking* Building Dictionary : producing candidate edit words (extractBig.py -> big.txt) 
* Calculating Edit Distance (Peter Norvig’s Library : SpellCorrect.php -> serialize_dictionary.txt)
* Spelling Check (SpellCorrect.php -> correct method) 

		if(empty(self::$NWORDS)) {
			/* To optimize performance, the serialized dictionary can be saved on a file
			instead of parsing every single execution */
			if(!file_exists("/workspace/solr/searchEngine/searchengine/serialized_dictionary.txt")) {
				self::$NWORDS = self::train(self::words(file_get_contents("/workspace/solr/searchEngine/searchengine/big.txt")));
				$fp = fopen("/Users/peter.park/workspace/solr/searchEngine/searchengine/serialized_dictionary.txt","w+");
				fwrite($fp,serialize(self::$NWORDS));
				fclose($fp);
			} else {
				self::$NWORDS = unserialize(file_get_contents("/workspace/solr/searchEngine/searchengine/serialized_dictionary.txt"));
			}
		}
####7. Autocomplete* Ajax(xmlhttpRequest)
* Solr Suggest Handler (modify service_autocomplete.php)
* Autocomplete.php (suggest result parser)
####8. Snippet
* simple\_html\_dom parser (simple_html_dom.php)
* strip_tags function(snippet.php)
* building array (snippet.php$termSetArray)
* Search Keywords(compare every word with the query)
* Search Priority(snippet.php)
* Return Snippet(snippet.php)

## Skill Set
Apache Solr 7.5(solr-php-client), Python, PHP, Google App Engine

## Referencing Library/API
[Jsoup](https://jsoup.org/), [html2text](https://pypi.org/project/html2text/), [enchant](https://github.com/rfk/pyenchant), [BeautifulSoup](https://pypi.org/project/beautifulsoup4/), [lxml parser](https://lxml.de/), [networkx(PageRank)](https://pypi.org/project/networkx/2.2/), [Peter Norvig’s algorithm/custom-library](https://www.phpclasses.org/package/4859-PHP-Suggest-corrected-spelling-text-in-pure-PHP.html#download)

[![SearchEngine](http://img.youtube.com/vi/-7pTsOm5Hl0/0.jpg)](https://youtu.be/-7pTsOm5Hl0)