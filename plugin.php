<?php

// workaround because of bludit plugin structure
// for future Versions: use Namespaces and PSR Standard for autoloading
include('RssExtended_XMLBuilder.php');
include('RssExtended_XMLOutput.php');
include('RssExtended_Settings.php');

class pluginRSSextended extends Plugin
{

    const SETTING_FULLTEXT = 'fulltext';
    const SETTING_SHORTTEXT = 'short';

    const DEFAULT_NO_ITEMS = 5;

    const PUBLIC_FILENAME = 'rss.xml';
    const PUBLIC_FILENAME_SHORT = 'rss_short.xml';
    const PUBLIC_FILENAME_FULLTEXT = 'rss_fulltext.xml';

    const CATEGORY_URL_PATTERN = 'category/%s/%s';


    const INTERNAL_FILE_PATTERN = 'rss_%s.xml';
    const INTERNAL_FILE_PATTERN_CATEGORIES = '%s_rss_%s.xml';

    /** @var array */
    private $categoryList;

    /** @var RssExtended_XMLBuilder */
    private $xmlBuilder;

    /** @var RssExtended_Settings */
    private $settings;

    /** @var int */
    private $numberOfItems;

    /** @var string */
    private $defaultTextSize;

    public function __construct()
    {
        parent::__construct();

        $this->numberOfItems = $this->getValue('numberOfItems');
        $this->defaultTextSize = $this->getValue('defaultTextSize');

        $this->constructBluditWorkaround();
    }

    /**
     * method to use bludit plugin structure
     */
    private function constructBluditWorkaround()
    {
        $this->categoryList = getCategories();

        $this->xmlOutput = new RssExtended_XMLOutput();
        $this->xmlOutput->constructBluditWorkaround($this->workspace());

        $this->xmlBuilder = new RssExtended_XMLBuilder();
        $this->xmlBuilder->constructBluditWorkaround($this->numberOfItems, $this->defaultTextSize);

        $this->settings = new RssExtended_Settings();
        $this->settings->constructBluditWorkaround($this->numberOfItems, $this->defaultTextSize);
    }

    public function init()
    {
        $this->dbFields = array(
            'numberOfItems' => self::DEFAULT_NO_ITEMS,
            'defaultTextSize' => self::SETTING_FULLTEXT
        );
    }

    public function form()
    {
        return $this->settings->renderSettingsPage();
    }

    public function install($position = 0)
    {
        parent::install($position);
        $this->xmlBuilder->buildXml();
    }

    public function post()
    {
        parent::post();
        $this->xmlBuilder->buildXml();
    }

    public function afterPageCreate()
    {
        $this->xmlBuilder->buildXml();
    }

    public function afterPageModify()
    {
        $this->xmlBuilder->buildXml();
    }

    public function afterPageDelete()
    {
        $this->xmlBuilder->buildXml();
    }

    public function siteHead()
    {
        return '<link rel="alternate" type="application/rss+xml" href="' . DOMAIN_BASE . 'rss.xml" title="RSS Feed">' . PHP_EOL;
    }

    public function beforeAll()
    {

        $this->registerMainFeed();

        /** @var Category $category */
        foreach ($this->categoryList as $category) {
            $this->registerCategoryFeed($category);
        }

    }

    private function registerMainFeed()
    {
        if ($this->webhook(self::PUBLIC_FILENAME)) {
            $this->handleOutput($this->defaultTextSize);
        }

        if ($this->webhook(self::PUBLIC_FILENAME_FULLTEXT)) {
            $this->handleOutput(self::SETTING_FULLTEXT);
        }

        if ($this->webhook(self::PUBLIC_FILENAME_SHORT)) {
            $this->handleOutput(self::SETTING_SHORTTEXT);
        }
    }

    private function handleOutput($size)
    {
        $this->xmlOutput->renderFile(sprintf(self::INTERNAL_FILE_PATTERN, $size));
    }

    /**
     * @param Category $category
     */
    private function registerCategoryFeed(Category $category)
    {

        $webhook = sprintf(self::CATEGORY_URL_PATTERN, $category->key(), self::PUBLIC_FILENAME);
        if ($this->webhook($webhook)) {
            $this->handleOutputForCategory($category->key(), $this->defaultTextSize);
        }

        $webhook = sprintf(self::CATEGORY_URL_PATTERN, $category->key(), self::PUBLIC_FILENAME_FULLTEXT);
        if ($this->webhook($webhook)) {
            $this->handleOutputForCategory($category->key(), self::SETTING_FULLTEXT);
        }

        $webhook = sprintf(self::CATEGORY_URL_PATTERN, $category->key(), self::PUBLIC_FILENAME_SHORT);
        if ($this->webhook($webhook)) {
            $this->handleOutputForCategory($category->key(), self::SETTING_SHORTTEXT);
        }
    }

    private function handleOutputForCategory($categoryKey, $size)
    {
        $this->xmlOutput->renderFile(sprintf(self::INTERNAL_FILE_PATTERN_CATEGORIES, $categoryKey, $size));
    }
}
