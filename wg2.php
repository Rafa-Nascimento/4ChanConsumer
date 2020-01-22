<?php
//wg.php
require_once "4chanConsumer.php";

$wg = new FourChanBoardConsumer("wg");

$aAllValidUrlsForTheBoard = $wg->allValidUrls();

var_dump($aAllValidUrlsForTheBoard);

function test20191126_1053(){
    global $aAllValidUrlsForTheBoard, $wg;
    $firstPage = $aAllValidUrlsForTheBoard[0];
    $aPairs = $wg->allHyperlinksAtBoardUrl($firstPage);

    foreach ($aPairs as $pair){
        $anchor = $pair[AmUtil::ANCHOR];
        $href = $pair[AmUtil::HREF];
        $s = sprintf("$href\t$anchor".PHP_EOL);
        file_put_contents (
            "README.TXT",
            $s,
            FILE_APPEND
        );
    }//foreach
    echo "README.TXT written";

    var_dump ($aPairs);
}//test20191126_1053

//test20191126_1053();

function test20191127_1529(){
    global $aAllValidUrlsForTheBoard, $wg;
    $firstPage = $aAllValidUrlsForTheBoard[0];
    $aPairs = $wg->allHyperlinksAtBoardUrl($firstPage);
    $aOnlySupportedImageTypesTerminatedUrls
        = $wg->allExistingImages($aPairs);
    var_dump ($aOnlySupportedImageTypesTerminatedUrls);
}//test20191127_1529

//test20191127_1529();

function test20191127_15_47(){
    global $aAllValidUrlsForTheBoard, $wg;
    $firstPage = $aAllValidUrlsForTheBoard[0];
    $aPairs = $wg->allHyperlinksAtBoardUrl($firstPage);
    $aOnlySupportedImageTypesTerminatedUrls
        = $wg->allExistingImages($aPairs);
    var_dump ($wg->downloadThesePairs($aOnlySupportedImageTypesTerminatedUrls));
}//test20191127_15_47

test20191127_15_47();



