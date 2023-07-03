<?php

/**
 * ------------------------------------------------------------------------------------------------
 * 
 *      -_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-
 * 
 *                      ------------------------------------------------
 *                      |                  MicroLib SQL                |
 *                      ------------------------------------------------
 *                      |                                              |
 *                      |  Author : Julien Jacobs (Micromachine)       |
 *                      |  Version : 1.0                               |
 *                      |  Date : 05/06/2021                           |
 *                      |  Type : PHP librairy for SQL with PDO Object |
 *     _-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_
 * 
 * Utilisation :
 * 
 * Remplacez à la ligne 55 ('URL', 'user', 'nbName', 'pass', 'charsetCustom') : Voir exemple
 * 
 * Création de votre DB à la ligne 60 (optionnel)
 * 
 * Possibilités : -bd-select
 *                -bd-insert
 *                -bd-delete
 *                -bd-update
 *                -db-addColumnTable
 *                -db-deleteColummnTable
 *                -db-updateColumnTable
 *                -db-createColumnTable
 *                -db-nextVersion....
 * ------------------------------------------------------------------------------------------------
 */

//#########################################-Début-#############################################

/**
 * ----------------------------------------------
 *          Exemples : 
 * ----------------------------------------------
 * 
 * operator : Valeurs possible : AND et OR.
 * 
 *          !!!Version indisponnible!!!
 * 
 */

/**
 * Use for lib
 */

//  link, user, nbName, dbPass, charset

$db = new microDb('localhost', 'root', 'dbName', 'password', 'charsetCustom');

/**
 * For connect
 */
//$db->connexionBd();

class microDb
{
    /**
     * Attr... à rentrer manuellement
     */
    private $host; // IP:URL de la BD
    private $user; // nom d'utilisateur de la BD
    private $dbName; // Nom de la BD
    private $dbPass; // password de la BD
    private $charset; // Paramètres de l'encodage de la DB

    /**
     * Attr... Automatique
     */
    private $dns; // DNS nécéssaire à la connexion
    private $sql; // Requête en cours
    private $pdo; // Object PDO de la BD

    /**
     * Constructor of the class
     */
    public function __construct($host, $user, $dnName, $dbPass, $charset)
    {
        $this->host = $host;
        $this->user = $user;
        $this->dbName = $dnName;
        $this->dbPass = $dbPass;
        //Charset optionnel
        if ($charset != null || $charset != '') {
            $this->charset = $charset;
        } else {
            $this->charset = 'utf8mb4';
        }
        $this->connexionBd();
    }

    /**
     * Generate String for Dns
     */
    private function generateDns()
    {
        $this->dns = "mysql:host=$this->host;dbname=$this->dbName;charset=$this->charset";
    }

    /**
     * connect To DB
     */
    public function connexionBd()
    {
        $this->generateDns();
        try {
            $this->pdo = new PDO($this->dns, $this->user, $this->dbPass);
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    /**
     * for generate sql slelect
     * Génère des columns à sélectionner
     */
    private function generateColumn($elements)
    {
        $str = '';
        if (count($elements) > 0) {
            for ($i = 0; $i < count($elements); ++$i) {
                $str .= '`' . $elements[$i] . '`';
                if ($i < count($elements) - 1) {
                    $str .= ', ';
                } else {
                    $str .= ' ';
                }
            }
        } else {
            $str .= '* ';
        }
        return $str;
    }

    /**
     * Where select and values
     */
    private function generateWhere($whereValue, $operator)
    {
        $str = '';
        $i = 0;
        if (count($whereValue) > 0) {
            foreach ($whereValue as $clef => $val) {
                $str .= $clef . '=:' . $clef . ' ';
                if ($i < (count($whereValue) - 1)) {
                    $str .= $operator[$i] . ' ';
                    ++$i;
                }
            }
            return $str;
        } else {
            return 1;
        }
    }

    /**
     * Where the values are replace
     */
    private function generateWhereValues($whereValue, $operator)
    {
        $str = '';
        $i = 0;
        if (count($whereValue) > 0) {
            foreach ($whereValue as $clef => $val) {
                $str .= $clef . '=' . $val . ' ';
                if ($i < (count($whereValue) - 1)) {
                    $str .= $operator[$i] . ' ';
                    ++$i;
                }
            }
            return $str;
        } else {
            return 1;
        }
    }


    /**
     * Generate value
     */
    private function generateValues($values)
    {
        $str = '';
        $i = 0;
        foreach ($values as $clef => $col) {
            $str .= '`' . $clef . '` ';
            ++$i;
            if ($i < (count($values))) {
                $str .= ', ';
            }
        }
        return $str;
    }

    /**
     * Where the values are shear
     */
    private function generateValueOf($value)
    {
        $str = '';
        $i = 0;
        foreach ($value as $clef => $col) {
            $str .= '`' . $clef . '`=:' . $clef;
            ++$i;
            if ($i < (count($value))) {
                $str .= ', ';
            }
        }
        return $str;
    }

    /**
     * Values to insert in the bd
     */
    private function generateValInsert($values)
    {
        $str = '';
        $i = 0;
        foreach ($values as $clef => $col) {
            $str .= ':' . $clef . ' ';
            if ($i < count($values) - 1) {
                $str .= ', ';
            }
            ++$i;
        }
        return $str;
    }

    /**
     * For debug :
     * User this if probleme
     */
    public function displaySQL()
    {
        echo $this->sql;
    }

    /**
     * Execute the sql line
     */
    private function excecuteSql($value)
    {
        $req = $this->pdo->prepare($this->sql);
        $req->execute($value);
        return $req;
    }

    /**
     * Use Select
     */
    public function select($table, $elements, $whereValue, $operator)
    {
        $this->generateSelect($table, $elements, $whereValue, $operator);
        return $this->excecuteSql($whereValue);
    }

    /**
     * Use Insert
     */
    public function insert($table, $values)
    {
        $this->generateInsert($table, $values);
        $this->excecuteSql($values);
    }

    /**
     * Use Delete
     */
    public function delete($table, $where, $operator)
    {
        $this->generateDelete($table, $where, $operator);
        $this->excecuteSql($where);
    }

    /**
     * Use Update
     */
    public function update($table, $value, $whereOf, $operator)
    {
        $this->generateUpdate($table, $value, $whereOf, $operator);
        $this->excecuteSql($value, $whereOf);
    }

    /**
     * Use addColumnTable
     */
    public function addColumnTable($nameTable, $nameColumn, $typeColumn, $default)
    {
        $infoAddColumn = [
            'nameTable' => $nameTable,
            'nameColumn' => $nameColumn,
            'typeColumn' => $typeColumn,
            'default' => $default
        ];
        $params = [];
        $this->generateAddColumnTable($infoAddColumn);
        $this->excecuteSql($params);
    }

    /**
     * Use deleteTable
     */
    public function deleteColumnTable($nameTable, $nameColumn)
    {
        $infoColumn = [
            'nameTable' => $nameTable,
            'nameColumn' => $nameColumn
        ];
        $params = [];
        $this->generateDeleteColumnTable($infoColumn);
        $this->excecuteSql($params);
    }
    
    public function customSQL($sql){
        $this->sql = $sql;
        return $this->executeSql([]);
    }

    /**
     * For Select
     * Génère une requête SQL pour Sélectionner
     */
    private function generateSelect($table, $elements, $whereValue, $operator)
    {
        $this->sql = 'SELECT ' . $this->generateColumn($elements) . 'FROM `' . $table . '` WHERE ' . $this->generateWhere($whereValue, $operator) . ';';
    }

    /**
     * For Insert
     * Génères une requete SQL pour insérer
     */
    private function generateInsert($table, $values)
    {
        $this->sql = 'INSERT INTO `' . $table . '` (' . $this->generateValues($values, '`') . ') VALUES (' . $this->generateValInsert($values) .  ');';
    }

    /**
     * for Delete
     * Génère une requête SQL pour supprimer
     */
    private function generateDelete($table, $where, $operator)
    {
        $this->sql = 'DELETE FROM `' . $table . '` WHERE ' . $this->generateWhere($where, $operator) . ';';
    }

    /**
     * for Update
     * Génère une requête SQL pour mettre à jours
     */
    private function generateUpdate($table, $value, $whereOf, $operator)
    {
        $this->sql = 'UPDATE `' . $table . '` SET ' . $this->generateValueOf($value) . ' WHERE ' . $this->generateWhereValues($whereOf, $operator) . ';';
    }

    /**
     * Génère une requête SQL pour ajouter une colonne dans une table
     */
    private function generateAddColumnTable($infos)
    {
        $this->sql = 'ALTER TABLE ' . $infos['nameTable'] . ' ADD COLUMN ' . $infos['nameColumn'] . ' ' . $infos['typeColumn'] . ' DEFAULT ' . $infos['default'] . ';';
    }

    /**
     * Génère une requête SQL pour supprimer une colonne dans une table
     */
    private function generateDeleteColumnTable($infos)
    {
        $this->sql = 'ALTER TABLE ' . $infos['nameTable'] . ' DROP COLUMN ' . $infos['nameColumn'] . ';';
    }
}
