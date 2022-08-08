<?php


// workaround because of bludit plugin structure
// there is actual no need to extend the Plugin class
class RssExtended_XMLBuilder extends Plugin
{

    private $translator;
    private $categoryList;
    private $site;
    private $pages;
    private $categories;
    private $numberOfItems;
    private $defaultTextSize;

    /**
     * workaround because of bludit plugin structure
     */
    public function constructBluditWorkaround($numberOfItems,$defaultTextSize)
    {
        global $L;
        global $site;
        global $categories;
        global $pages;
        $this->translator = $L;
        $this->site = $site;
        $this->categories = $categories;
        $this->pages = $pages;
        $this->categoryList = getCategories();
        $this->numberOfItems = $numberOfItems;
        $this->defaultTextSize = $defaultTextSize;
    }

    public function buildXml(){
        $this->createXMLforAll();
        $this->createXMLforCategories();
    }

    /**
     * @param Site $site
     * @return string
     */
    private function getXmlHead(Site $site, Category $category = null)
    {
        $url =  DOMAIN_BASE . 'rss.xml';
        $title = $site->title();
        if($category !== null){
            $url = DOMAIN_BASE .'category/'. $category->key() . '/rss.xml';
            $title .= " ".$category->name();
        }

        $xml = '<?xml version="1.0" encoding="UTF-8" ?>';
        $xml .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">';
        $xml .= '<channel>';
        $xml .= '<atom:link href="'.$url.'" rel="self" type="application/rss+xml" />';
        $xml .= '<title>' . htmlentities($title) .'</title>';
        $xml .= '<link>' . $this->encodeURL($site->url()) . '</link>';
        $xml .= '<description>' . $site->description() . '</description>';
        $xml .= '<lastBuildDate>' . date(DATE_RSS) . '</lastBuildDate>';
        return $xml;
    }

    private function encodeURL($url)
    {
        return preg_replace_callback('/[^\x20-\x7f]/', function($match) { return urlencode($match[0]); }, $url);
    }

    /**
     * @param Page $page
     * @return string
     */
    private function getItemXml(Page $page,$short=false)
    {
        $itemXml = '';
        $itemXml .= '<item>';
        $itemXml .= '<title>' . $page->title() . '</title>';
        $itemXml .= '<link>' . $this->encodeURL($page->permalink()) . '</link>';
        $itemXml .= '<image>' . $page->coverImage(true) . '</image>';
        if(count(explode(PAGE_BREAK,$page->content())) == 1){
            $short = false;
        }
        if($short) {
            $itemXml .= '<description>' . Sanitize::html($page->contentBreak().' <a href="'.$this->encodeURL($page->permalink()).'">'.$this->translator->get('read-more').'</a>') . '</description>';
        }else{
            $itemXml .= '<description>' . Sanitize::html($page->content()) . '</description>';
        }
        $itemXml .= '<pubDate>' . date(DATE_RSS, strtotime($page->getValue('dateRaw'))) . '</pubDate>';
        $itemXml .= '<guid isPermaLink="false">' . $page->uuid() . '</guid>';
        $itemXml .= '</item>';
        return $itemXml;
    }

    private function getXmlFoot(){
        return '</channel></rss>';
    }



    private function createXMLforAll()
    {

        $list = $this->pages->getList(
            1,
            $this->numberOfItems,
            true,
            true,
            true,
            false,
            false
        );


        $xml = $shortXml = $this->getXmlHead($this->site);

        foreach ($list as $pageKey) {
            try {
                $xml .= $this->getItemXml(new Page($pageKey));
                $shortXml .= $this->getItemXml(new Page($pageKey),true);
            } catch (Exception $e) {
                // NOOP
            }
        }


        $xml .= $this->getXmlFoot();
        $shortXml .= $this->getXmlFoot();

        $this->saveXml($xml,'rss_fulltext.xml');
        $this->saveXml($shortXml,'rss_short.xml');

    }

    private function createXMLforCategories()
    {
        global $categories;
        $categories->reindex();
        $this->categories = $categories;

        /** @var Category $category */
        foreach ($this->categoryList as $category) {
            $xml = $shortXml = $this->getXmlHead($this->site, $category);
            $list = $this->categories->getList($category->key(), 1, $this->numberOfItems);
            if (is_array($list)) {
                foreach ($list as $pageKey) {
                    try {
                        $xml .= $this->getItemXml(new Page($pageKey));
                        $shortXml .= $this->getItemXml(new Page($pageKey),true);
                    } catch (Exception $e) {
                        // NOOP
                    }
                }
            }
            $xml .= $this->getXmlFoot();
            $shortXml .= $this->getXmlFoot();
            $this->saveXml($xml,$category->key() . '_rss_fulltext.xml');
            $this->saveXml($shortXml,$category->key() . '_rss_short.xml');
        }
    }

    private function saveXml($xml, $filename){
        $doc = new DOMDocument();
        $doc->formatOutput = true;
        $doc->loadXML($xml);
        $doc->save($this->workspace() . $filename);
    }

}