<?php

// workaround because of bludit plugin structure
// there is actual no need to extend the Plugin class
class RssExtended_Settings extends Plugin
{
    /** @var Language */
    private $translator;
    /** @var array */
    private $categoryList;
    /** @var integer */
    private $numberOfItems;
    /** @var string */
    private $defaultTextSize;

    private $footer;

    /**
     * workaround because of bludit plugin structure
     */
    public function constructBluditWorkaround($numberOfItems, $defaultTextSize,$footer)
    {
        global $L;
        $this->translator = $L;
        $this->categoryList = getCategories();

        $this->numberOfItems = $numberOfItems;
        $this->defaultTextSize = $defaultTextSize;
        $this->footer = $footer;
    }

    public function renderSettingsPage()
    {
        $html = $this->renderForm();
        $html .= $this->renderFeedUrls();
        return $html;
    }

    private function renderForm()
    {
        $html = '<h6 class="mt-4 mb-2 pb-2 border-bottom text-uppercase">Settings</h6>';
        $html .= '<div>';
        $html .= '<label>' . $this->translator->get('Amount of items') . '</label>';
        $html .= '<input name="numberOfItems" type="text" value="' . $this->numberOfItems . '">';
        $html .= '<span class="tip">' . $this->translator->get('Amount of items to show on the feed') . '</span>';
        $html .= '</div>';

        $html .= '<div>';
        $html .= '<label>' . $this->translator->get('Default') . '</label>';
        $html .= '<select size="1" name="defaultTextSize">';
        $shortTextSelected = '';
        if ($this->defaultTextSize == pluginRSSextended::SETTING_SHORTTEXT) {
            $shortTextSelected = 'selected="selected"';
        }
        $html .= '<option ' . $shortTextSelected . ' value="' . pluginRSSextended::SETTING_SHORTTEXT . '">' . $this->translator->get(pluginRSSextended::SETTING_SHORTTEXT) . '</option>';
        $fullTextSelected = '';
        if ($this->defaultTextSize == pluginRSSextended::SETTING_FULLTEXT) {
            $fullTextSelected = 'selected="selected"';
        }
        $html .= '<option ' . $fullTextSelected . ' value="' . pluginRSSextended::SETTING_FULLTEXT . '">' . $this->translator->get(pluginRSSextended::SETTING_FULLTEXT) . '</option>';
        $html .= '</select>';
        $html .= '<label>Footer</label>';
        $html .= '<textarea style="height:300px" name="footer">'.$this->getValue('footer').'</textarea>';

        $html .= '</div>';
        return $html;
    }

    private function renderFeedUrls()
    {
        $html = '<h6 class="mt-4 mb-2 pb-2  text-uppercase">Feed URLs</h6>';
        $html .= '<table class="table"><tbody><tr>';
        $html .= '<td rowspan="3">' . $this->translator->get('all-content') . '</td>';
        $url = $this->getUrlForMain(pluginRSSextended::PUBLIC_FILENAME);
        $html .= '<td>' . $this->translator->get('Default') . '</td><td><a href="' . $url . '">' . $url . '</a></td>';
        $url = $this->getUrlForMain(pluginRSSextended::PUBLIC_FILENAME_SHORT);
        $html .= '<tr><td>' . $this->translator->get(pluginRSSextended::SETTING_SHORTTEXT) . '</td><td><a href="' . $url . '">' . $url . '</a></td></tr>';
        $url = $this->getUrlForMain(pluginRSSextended::PUBLIC_FILENAME_FULLTEXT);
        $html .= '<tr><td>' . $this->translator->get(pluginRSSextended::SETTING_FULLTEXT) . '</td><td><a href="' . $url . '">' . $url . '</a></td></tr>';

        foreach ($this->categoryList as $cat) {
            $html .= '<tr>';
            $html .= '<td rowspan="3">' . $cat->name() . '</td>';
            $url = $this->getUrlForCategory($cat->key(), pluginRSSextended::PUBLIC_FILENAME);
            $html .= '<td>' . $this->translator->get('Default') . '</td><td><a href="' . $url . '">' . $url . '</a></td></tr>';
            $url = $this->getUrlForCategory($cat->key(), pluginRSSextended::PUBLIC_FILENAME_SHORT);
            $html .= '<tr><td>' . $this->translator->get(pluginRSSextended::SETTING_SHORTTEXT) . '</td><td><a href="' . $url . '">' . $url . '</a></td></td>';
            $url = $this->getUrlForCategory($cat->key(), pluginRSSextended::PUBLIC_FILENAME_FULLTEXT);
            $html .= '<tr><td>' . $this->translator->get(pluginRSSextended::SETTING_FULLTEXT) . '</td><td><a href="' . $url . '">' . $url . '</a></td></td>';
        }
        $html .= '</tbody></table>';
        return $html;
    }

    private function getUrlForMain($filename)
    {
        return DOMAIN_BASE . $filename;
    }

    private function getUrlForCategory($categoryKey, $filename)
    {
        return DOMAIN_BASE . sprintf(pluginRSSextended::CATEGORY_URL_PATTERN, $categoryKey, $filename);
    }
}
