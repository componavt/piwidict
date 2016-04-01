<?
/* Functions for definition parsing.
 */
public class Definition {
    private static $DEBUG = false;
    
    /** Gets position after /^\s+#\s+/ */
//    private static $ptrn_definition_number_sign = "\\A\\s*#\\s*";           // vim: ^\s*#\s*

    /* Strips number sign '#' and spaces (trim). */
    public static String stripNumberSign ($page_title, $text) {
        
        // gets position in text after "# "
        if (preg_match("/\A\s*#\s*(.*\z)/", $line, $regs)) 
            return trim($regs[1]);

        else {   // there is no definition section!
            if($DEBUG)
                print "Warning in Definition.stripNumberSign(): The article '$page_title' has no number sign '#' in a definition.";
            return $text;
        }
    }
}