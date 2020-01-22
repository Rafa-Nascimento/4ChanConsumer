<?php

class FourChanDB {
    private $mHost, $mUser, $mPass;
    private $mDB;

    const DEBUG = true;

    const CREATE_SCHEMA = "CREATE SCHEMA IF NOT EXISTS `schema_4chanTable`;";
    const DROP_SCHEMA = "DROP SCHEMA IF EXISTS ´schema_4chanTable´;"; //destroy!!!

    const CREATE_TABLE_BOARDS = "
        CREATE TABLE IF NOT EXISTS `schema_4chanTable`.`tboards` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(45) NOT NULL,
        `description` TEXT NULL,
        PRIMARY KEY (`id`));
    ";
    const CREATE_TABLE_DLS = "
        CREATE TABLE IF NOT EXISTS `schema_4chanTable`.`tdls` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `href` TEXT NOT NULL,
        `anchor` TEXT NULL,
        `foundDate` DATETIME NULL,
        `board` INT NULL,
        PRIMARY KEY (`id`),
        INDEX `fkBoard_idx` (`board` ASC),
        CONSTRAINT `fkBoard`
        FOREIGN KEY (`board`)
        REFERENCES `schema_aca4chan`.`tboards` (`id`)
        ON DELETE NO ACTION
        ON UPDATE NO ACTION);
    ";

    public function __construct($pHost = "127.0.0.1", $pUser = "aca1920", $pPass = "1234"){
        $this->mDB = mysqli_connect($this->mHost = $pHost, $this->mUser = $pUser, $this->mPass = $pPass);
        $e = mysqli_connect_errno(); //connect error code
        $eM = mysqli_connect_error();//connect error message
        if ($e!==0){ exit; }
    }//__construct

    public function install(){
        $installProcedure = [
            self::CREATE_SCHEMA,
            self::CREATE_TABLE_BOARDS,
            self::CREATE_TABLE_DLS
        ];

        for ($idx=0; $idx<=count($installProcedure); $idx++){
            $i = $installProcedure[$idx];
            $r = $this->queryExecutor($i, $e, $eM, $strFeedback);
            echo $strFeedback;
        }
    }//install

    /*
     * & notation for pass by-reference
     */
    private function queryExecutor(
        $pQ, //the query
        &$pE, //error code
        &$pEMsg, //error msg
        &$pStrFeedback //description of everything that happened
    ){
        if ($this->mDB && !empty($pQ)){
            $r = $this->mDB->query($pQ);
            $pE = $this->mDB->errno; //error code
            $pEMsg = $this->mDB->error; //error message
            $strResult = gettype($r)." ";
            if (is_bool($r)){
                $strResult.=$r ? "true" : "false";
            }
            $strResult.= PHP_EOL;

            $pStrFeedback = sprintf(
                "query= %s\nerror code=%d (%s)\n".
                "result= %s",
                $pQ,
                $pE,
                $pEMsg,
                $strResult
            );

            return $r;
        }//if
        else{
            $pEMsg = "No database pointer!";
            return false;
        }
    }//queryExecutor

    public function insertBoard(
        $pBoardName,
        $pDescription="",
        $pDebug = self::DEBUG
    ){
        $idWhereBoardAlreadyExists = $this->idForBoard($pBoardName);
        if ($idWhereBoardAlreadyExists===false){
            $q = "INSERT INTO `schema_aca4chan`.`tBoards` VALUES (null, '$pBoardName', '$pDescription');";
            $r = $this->queryExecutor($q, $e, $eM, $strFeedback);

            if ($pDebug) echo $strFeedback;

            if (is_bool($r) && ($r===true) && ($e===0)){
                $idWhereInserted = $this->mDB->insert_id;
                return $idWhereInserted;
            }//if
        }//if

        return false;
    }//insertBoard

    public function idForBoard($pBoardName){
        $q = "SELECT `id` FROM `schema_aca4chan`.`tBoards` WHERE `name`='$pBoardName' limit 1;";
        $r = $this->queryExecutor($q, $e, $eM, $strF);

        if ($e===0 && ($r instanceof mysqli_result)){
            $aAllResults = $r->fetch_all(MYSQLI_ASSOC);
            $bOK = is_array($aAllResults) && count($aAllResults)===1;
            if ($bOK){
                $id = $aAllResults[0]['id'];
                return $id;
            }
        }
        return false;
    }//idForBoard

    public function selectAllBoards(
        $pDebug = self::DEBUG
    ){
        $q = "SELECT * FROM `schema_aca4chan`.`tBoards`;";
        $r = $this->queryExecutor($q, $e, $eM, $strFeedback);
        if ($pDebug) echo $strFeedback;
        if ($e===0 /*&& ($r instanceof mysqli_result)*/){
            $aAllBoards = $r->fetch_all(MYSQLI_ASSOC);
            return $aAllBoards;
        }//if
        return false;
    }//selectAllBoards

    public function genericTablePresenter(
        $pTableAssocArray
    ){
        $ret = "";
        $iHowManyRows = count($pTableAssocArray);
        for ($row=0; $row<$iHowManyRows; $row++){
            $line = "";
            $record = $pTableAssocArray[$row];
            foreach ($record as $k=>$v){
                $line.="$k:$v\t";
            }//foreach
            $line.="\n";
            $ret.=$line;
        }//for
        return $ret;
    }//genericTablePresenter

    public function insertDl(
        $pHref,
        $pAnchor="",
        $pWhenFound=false,
        $pBoard="new board",
        $pDebug = self::DEBUG
    ){
        $idBoard = $this->idForBoard($pBoard);
        if ($idBoard===false){
            $idBoard = $this->insertBoard($pBoard);
        }//if

        $strWhenFound = $pWhenFound===false ? date("Y-m-d H:i:s") : $pWhenFound;
        $q =
            "INSERT INTO `schema_aca4chan`.`tDls` ".
            "VALUES (null, '$pHref', '$pAnchor', '$strWhenFound', $idBoard);";

        $r = $this->queryExecutor($q, $e, $eM, $strF);
        if ($pDebug) echo $strF;

        if ($e===0){
            $idWhereInserted = $this->mDB->insert_id;
            return $idWhereInserted;
        }
        else{
            return false;
        }
    }////insertDl

    //DELETE FROM `schema_aca4chan`.`tboards` WHERE (`id` = '2');
}//FourChanDB
/*
$o = new FourChanDB();
//$o->install();
$o->insertBoard ("outra board", "esta board é feia");
echo $o->idForBoard('xpto');
$tBoards = $o->selectAllBoards();
$strPresent = $o->genericTablePresenter($tBoards);
echo $strPresent;

$id = $o->insertDl("https://blabla", "some desc", false, 'wg');
echo "id= $id";*/