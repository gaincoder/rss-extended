<?php

// workaround because of bludit plugin structure
// there is actual no need to extend the Plugin class
class RssExtended_XMLOutput extends Plugin
{
    /** @var string */
    private $path;

    public function constructBluditWorkaround($path)
    {
        $this->path = $path;
    }

    public function renderFile($filename)
    {
        // Send XML header
        header('Content-type: text/xml');
        $doc = new DOMDocument();

        // Load XML
        libxml_disable_entity_loader(false);
        $doc->load($this->path . $filename);
        libxml_disable_entity_loader(true);

        // Print the XML
        echo $doc->saveXML();

        // Stop Bludit execution
        exit(0);
    }
}