<?php
/**
 * User: umut
 * Date: 5.11.15
 * Time: 12:20
 */
class Database extends Factory {

    protected $Conn;
    protected $ConnStr  = 'mysql:host=localhost;dbname=localdb';
    protected $ConnUser = 'root';
    protected $ConnPass = 'root';

    function __construct()
    {
        $this->Conn = new PDO($this->ConnStr,$this->ConnUser,$this->ConnPass);
    }

    function GetResults()
    {

        $QueryString = $this->GetQueryString('SELECT');
        $Query = $this->conn->prepare($QueryString);
        $Query->execute();
        return $Query->fetchAll();

    }
 
}