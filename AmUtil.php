<?php
class AmUtil{
    const HREF = "href";
    const ANCHOR = "anchor";

    public static function stringEndsInOneOfThese(
        string $pStr,
        array $pAcceptableTerminations,
        bool $pCaseSensitiveComparison = false
    ){
        foreach ($pAcceptableTerminations as $term){
            $bCurrentTermIsTermination = self::stringEndsWith(
                $pStr,
                $term,
                $pCaseSensitiveComparison
            );
            if ($bCurrentTermIsTermination) return true;
        }//foreach
        return false;
    }//stringEndsInOneOfThese

    /*
     * receives any string $pStr
     * receives any (string) termination $pStrTermination
     * return true if $pStr ends in $pStrTermination
     * returns false, otherwise
     */
    public static function stringEndsWith(
        string $pStr,
        string $pStrTermination,
        bool $pCaseSensitiveComparison = false
    ){
        $iSizeStr = strlen ($pStr);
        $iSizeTerm = strlen ($pStrTermination);
        $bCanBeATermination = $iSizeTerm<=$iSizeStr;
        if ($bCanBeATermination){
            $originalWithoutTermination =
                substr($pStr, 0, $iSizeStr-$iSizeTerm);

            $originalFinalSymbols =
                substr($pStr, $iSizeStr-$iSizeTerm);

            if ($pCaseSensitiveComparison){
                $bMatch = strcmp($originalFinalSymbols, $pStrTermination) === 0;
            }
            else{
                //case insensitive
                $bMatch = strcasecmp(
                        $originalFinalSymbols,
                        $pStrTermination
                    ) === 0;
            }
            return $bMatch;
        }//if
        else{
            return false; //termination is lengthier than then sentence
        }
    }//stringEndsWith

    public static function consumeUrl (
        string $pUrl
    )
    {
        $curlHandler = curl_init();
        $bResult = curl_setopt(
            $curlHandler,
            CURLOPT_URL,
            $pUrl
        );
        //if (!$bResult) return;

        //if you don't have cacert.pem, disable SSL checking
        $bResult = curl_setopt(
            $curlHandler,
            CURLOPT_SSL_VERIFYPEER,
            false
        );

        $bResult = curl_setopt(
            $curlHandler,
            CURLOPT_RETURNTRANSFER,
            true
        );
        $bResult = curl_setopt(
            $curlHandler,
            CURLOPT_BINARYTRANSFER,
            true
        );

        $bResult = curl_setopt(
            $curlHandler,
            CURLOPT_ENCODING,
            "" //automatic encoding handling
        );

        $bResult = curl_setopt(
            $curlHandler,
            CURLOPT_USERAGENT,
            "Mozilla\/5.0 (Windows NT 6.3; WOW64; rv:54.0) Gecko\/20100101 Firefox\/54.0"
        );
        $bin = curl_exec($curlHandler);
        return $bin;
    }//consumeUrl

    public static function getHyperlinksFromDOMFromSource(
        string $pHtml
    ) : array //tipo de retorno da function
    {
        $ret = [] ;
        $bCaution = is_string($pHtml) && strlen($pHtml)>0;
        if ($bCaution){
            $oDOM = new DOMDocument();
            if ($oDOM){
                //@ - "silencer" stops warnings
                @$bSuccessOrFalse = $oDOM->loadHTML($pHtml);
                if ($bSuccessOrFalse){
                    $as = $oDOM->getElementsByTagName("a");
                    /*
                     * anchor
                     * <a href="https://xpto/d/file">âncora</a>
                     */
                    foreach ($as as $a){
                        $href = $a->getAttribute('href');
                        $anchor = $a->nodeValue;

                        $ret[] = array(
                            self::HREF => $href,
                            self::ANCHOR => $anchor
                        );
                    }//foreach
                }//if
            }//if
        }//if
        return $ret;
    }//getHyperlinksFromDOMFromSource
}//AmUtil