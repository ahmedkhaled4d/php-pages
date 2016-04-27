class Backup_Database {
/* Host where database is located  */

  var $host = 'localhost';
  var $username = 'root';
  var $passwd = '';
  var $dbName = 'YOUR_DATABASE _NAME';
  var $charset = '';

  /* Constructor initializes database */

  function Backup_Database($host, $username, $passwd, $dbName, $charset = 'utf8') {
    $this->host = $host;
    $this->username = $username;
    $this->passwd = $passwd;
    $this->dbName = $dbName;
    $this->charset = $charset;
    $this->initializeDatabase();
  }

  protected function initializeDatabase() {
    $conn = @mysql_connect($this->host, $this->username, $this->passwd); // Ik Added @ to Hide PDO Error Message
    mysql_select_db($this->dbName, $conn);
    if (!mysql_set_charset($this->charset, $conn)) {
      mysql_query('SET NAMES ' . $this->charset);
    }
  }

  /* Backup the whole database or just some tables Use '*' for whole database or 'table1 table2 table3...' @param string $tables  */

  public function backupTables($tables = '*', $outputDir = '.') {
    try {
      /* Tables to export  */
      if ($tables == '*') {
        $tables = array();
        $result = mysql_query('SHOW TABLES');
        while ($row = mysql_fetch_row($result)) {
          $tables[] = $row[0];
        }
      } else {
        $tables = is_array($tables) ? $tables : explode(',', $tables);
      }

      $sql = 'CREATE DATABASE IF NOT EXISTS ' . $this->dbName . ";\n\n";
      $sql .= 'USE ' . $this->dbName . ";\n\n";

  /* Iterate tables */
  foreach ($tables as $table) {
    echo "Backing up " . $table . " table...";

    $result = mysql_query('SELECT * FROM ' . $table);
    $numFields = mysql_num_fields($result);

    $sql .= 'DROP TABLE IF EXISTS ' . $table . ';';
    $row2 = mysql_fetch_row(mysql_query('SHOW CREATE TABLE ' . $table));
    $sql.= "\n\n" . $row2[1] . ";\n\n";

    for ($i = 0; $i < $numFields; $i++) {
      while ($row = mysql_fetch_row($result)) {
        $sql .= 'INSERT INTO ' . $table . ' VALUES(';
        for ($j = 0; $j < $numFields; $j++) {
          $row[$j] = addslashes($row[$j]);
          // $row[$j] = ereg_replace("\n", "\\n", $row[$j]);
          if (isset($row[$j])) {
            $sql .= '"' . $row[$j] . '"';
          } else {
            $sql.= '""';
          }
          if ($j < ($numFields - 1)) {
            $sql .= ',';
          }
        }
        $sql.= ");\n";
      }
    }
    $sql.="\n\n\n";
    echo " OK <br/><br/>" . "";
  }
} catch (Exception $e) {
  var_dump($e->getMessage());
  return false;
 }

    return $this->saveFile($sql, $outputDir);
  }
