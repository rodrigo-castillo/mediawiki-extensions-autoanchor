<?php
 
class AutoAnchor_i18n
{
    private static $words = array(
    // English
        'en' => array(
            'anc'  => array( 0, 'anc' ),
        ),
    );
 
    private static $messages = array(
    // English
        'en' => array(),
    );
 
    public static function getMessages()
    {
        return self::$messages;
    }
 
    /**
     * Get translated magic words, if available
     *
     * @param string $lang Language code
     * @return array
     */
    public static function magicWords( $lang )
    {
        // English is used as a fallback, and the English synonyms are
        // used if a translation has not been provided for a given word
        return ( $lang == 'en' || !isset( self::$words[$lang] ) ) ?
            self::$words['en'] :
            array_merge( self::$words['en'], self::$words[$lang] );
    }
}
