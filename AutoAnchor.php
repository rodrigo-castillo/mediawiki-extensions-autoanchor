<?php
 
$wgExtensionFunctions[] = array( 'ExtAutoAnchor', 'setup' );
$wgHooks['LanguageGetMagic'][] = 'ExtAutoAnchor::languageGetMagic';
$wgExtensionCredits['parserhook'][] = array(
    'name' => 'Auto-Anchor Extension',
    'author' => 'David M. Sledge',
    "url"    => "http://www.mediawiki.org/wiki/Extension:Auto-Anchor",
    'version' => ExtAutoAnchor::VERSION,
    "description" => "Create header-like anchors IDs without headers",
);
 
class ExtAutoAnchor
{
    const VERSION = '0.7.0';
    public static $ids = array();
 
    public static function setup()
    {
        global $wgParser;
 
        $wgParser->setFunctionHook( 'anc', array( __CLASS__, 'autoAnchor' ) );
    }
 
    public static function languageGetMagic( &$magicWords, $langCode )
    {
        require_once( dirname( __FILE__ ) . '/AutoAnchor.i18n.php' );
 
        foreach( AutoAnchor_i18n::magicWords( $langCode ) as $word => $trans )
            $magicWords[$word] = $trans;
 
        return true;
    }
 
    public static function autoAnchor( &$parser, $input, $count = '0' )
    {
        // need some input to make the id
        if ( $input === '' )
            // error:  no input
            return '';
 
        // get the value of the 'count' attribute  This attribute indicates the
        // number of wiki headers that use this text.  This extension is unable
        // to get that information itself so it must be supplied by the user; if
        // not specified, it defaults to 0.
        $count = intval( $attrs["count"] );
 
        // make sure it's not less than 0
        if ( $count < 0 )
            $count = 0;
 
        // parse the input
        $parsedInput = $parser->recursiveTagParse( $input );
        // strip out the encoded link to get the displayed text which
        // will be used to generate the span tag's id attribute value.
        $id = $parser->replaceLinkHoldersText( $parsedInput );
        // strip out any HTML markup
        $id = trim( preg_replace( '/<.*?>/m', '', $id ) );
        // escape the string so it contains valid
        // characters for the id attribute
        $id = Sanitizer::escapeId( $id, Sanitizer::NONE );
        // make sure the ID is unique
        $id = self::getUniqueID( $id, $count );
        // put it all together
        $spanid = "<span id=\"$id\">$input</span>";
 
        return $spanid;
    }
 
    // known limitation:  MediaWiki currently (v1.11.0) has no mechanism for
    // enforcing id-uniqueness within an article, so it's entirely possible that
    // another HTML tag will have the same id attribute value as the ones
    // supplied by this method.  We can however, enforce uniqueness the with IDs
    // generated within this extension
    private static function getUniqueID( $id, $count )
    {
        $count++;
 
        // if this isn't the first instance of this id, we append the number of
        // this instance to the id:  "wikitext_2", "wikitext_3",
        // "wikitext_4",... "wikitext_n".
        if ( in_array( $count == 1 ? $id : $id . '_' . $count, self::$ids ) )
        {
            // of course we have to make sure "wikitext_x" doesn't already exist
            // if it does, we increment x by one until we hit an id that doesn't
            // exist yet
            for ( $count++; in_array( $id . '_' . $count, self::$ids ); $count++ ) ;
 
            self::$ids[] = $id . '_' . $count;
 
            return end( self::$ids );
        }
 
        self::$ids[] = $count == 1 ? $id : $id . '_' . $count;
 
        return end( self::$ids );
    }
}
