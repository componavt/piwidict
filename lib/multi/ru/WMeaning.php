<?
/** Meaning consists of 
 * # Definition (preceded by "#", which causes automatic numbering).
 * and Quotations.      
 */
class WMeaningRu {
     /** Parses one definition line, i.e. extracts {{label}}, definition,
     * {{example|Quotation sentence.}}, creates and fills a meaning (WMeaning).
     * @param page_title    word which is described in this article 'text'
     * @param lang_section  language of this section of an article
     * @param line          definition line
     * @return WMeaning or null if the line is not started from "#" or = "# "
     */
    public static function parseOneDefinition($page_title, $lang_section, $line)
    {
        if (preg_match("/\{\{Нужен перевод\}\}/",$line))
            return null;

        // remove empty quotations: {{пример|}} and {{пример}}
        $line = preg_replace("/\{\{пример\|\}\}/", "", $line);
        $line = preg_replace("/\{\{пример\}\}/", "", $line);
        $line = preg_replace("/\{\{пример перевод\|\}\}/", "", $line); // todo check - does exist this example

        $line = preg_replace("/\[\[\]\]/", "", $line); // empty definition

        $line = Definition::stripNumberSign($page_title, $line);

        if (0 == strlen($line))
            return null;

        if (preg_match("/\A\{\{морфема/",$line))
            return null;    // skip now, todo (parse) in future

        $label_text = LabelRu.extractLabelsTrimText(line);
        if(null == label_text)
            return null;
/*
        $line = label_text.getText();
        
        // 2. extract text till first {{пример|
        String wiki_definition = WQuoteRu.getDefinitionBeforeFirstQuote(page_title, line);

        // 3. parsing wiki-text
        //StringBuffer definition = WikiWord.parseDoubleBrackets(page_title, new StringBuffer(wiki_definition));

        // 4. extract wiki-links (internal links)
        //WikiWord[] ww = WikiWord.getWikiWords(page_title, new StringBuffer(wiki_definition));

        // 5. extract quotations
        WQuote[] quote = WQuoteRu.getQuotes(page_title, line);        

        return new WMeaning(page_title, label_text.getLabels(), wiki_definition, quote, false);
*/
    }

}
