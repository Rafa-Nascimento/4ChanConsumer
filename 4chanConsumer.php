<?php
require_once "AmUtil.php";


class FourChanBoardConsumer{

    const PREVIOUS_DLS = "PREVIOUS_DLS.TSV";

    private $mBoardSaves; //path for saving the downloads from the board
    private $mBoard;
    //private $mUtil; //só necessário para métodos dyn

    const SUPPORTED_IMAGE_FORMATS = [".png", ".jpg", ".jp2", ".gif"];

    const DOWNLOAD_FOLDER = "dls";
    const URL_BASE = "https://www.record.pt/";

    public function __construct(string $pStrBoardName)
    {
        $this->mBoard = $pStrBoardName;
        $bTrueOnSuccessFalseOtherwise = @mkdir(self::DOWNLOAD_FOLDER);
        $this->createDownloadFolderForBoard();
    }//__construct

    const MIN_PAGE = 1;
    const MAX_PAGE = 10;
    public function allValidUrls(){
        $aValidUrls = [];

        for ($page = self::MIN_PAGE; $page<= self::MAX_PAGE; $page++){
            $strUrl = sprintf(
                "%s/%s/%s",
                self::URL_BASE,
                $this->mBoard,
                ($page == self::MIN_PAGE) ? "" : $page
            );
            $aValidUrls[] = $strUrl;
        }//for

        return $aValidUrls;
    }//allValidUrls

    public function allHyperlinksAtBoardUrl(string $pStrUrl){
        $html = AmUtil::consumeUrl($pStrUrl);
        $aPairsAnchorHref = AmUtil::getHyperlinksFromDOMFromSource($html);

        return $aPairsAnchorHref;
    }//allHyperlinksAtBoardUrl

    public function allExistingImages($aPairs){
        $aImagesFound = [];
        foreach ($aPairs as $pair){
            $anchor = $pair[AmUtil::ANCHOR];
            $href = $pair[AmUtil::HREF];
            $bHrefEndsInSupportedFormat = AmUtil::stringEndsInOneOfThese(
                $href,
                self::SUPPORTED_IMAGE_FORMATS
            );
            if ($bHrefEndsInSupportedFormat){
                $aImagesFound[] = $pair;
            }
        }//foreach

        return $aImagesFound;
    }//allExistingImages

    private function createDownloadFolderForBoard(){
        //e.g. ./dls/wg/
        $relativePath = sprintf(
            "./%s/%s",
            self::DOWNLOAD_FOLDER,
            $this->mBoard
        );
        @mkdir($relativePath);
        $this->mBoardSaves = $relativePath;
        return $relativePath;
    }//createDownloadFolderForBoard

    public function downloadThesePairs($aPairs, $pLimiter = 10){
        $iDlsCounter = 0;
        foreach ($aPairs as $pair){
            $anchor = $pair[AmUtil::ANCHOR];
            $absUrlMissingSchema = $pair[AmUtil::HREF];
            $fullUrl = "https:$absUrlMissingSchema";
            $bin = AmUtil::consumeUrl($fullUrl);
            $fileName = $anchor;

            $relativePathForSavedFile = $this->mBoardSaves."/".$fileName;
            $bytesWrittenOrFalse =
                file_put_contents(
                    $relativePathForSavedFile,
                    $bin,
                    LOCK_EX
                );
            if ($bytesWrittenOrFalse){
                $iDlsCounter++;
                echo "$fileName saved!".PHP_EOL;

                //URL \t âncora \t ficheiro local \t sha1
                $tsvLine = sprintf(
                    "%s\t%s\t%s\t%s".PHP_EOL,
                    $fullUrl,
                    $anchor,
                    $relativePathForSavedFile,
                    sha1($bin)
                );
                //ADICIONAR À BASE DE DADOS

                //ADICIONAR À BASE DE DADOS

                file_put_contents(
                    self::PREVIOUS_DLS,
                    $tsvLine,
                    FILE_APPEND
                );

                //stop if downloads counter limiter is reached
                if ($iDlsCounter===$pLimiter) return;
            }//if
        }//foreach
    }//downloadThesePairs
}//FourChanBoardConsumer
