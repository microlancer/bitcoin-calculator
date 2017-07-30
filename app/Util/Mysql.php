<?php

namespace App\Util;

use App\Util\Config;

/**
 * @codeCoverageIgnore
 */
class Mysql
{
    private static $mysqli;
    private $host;
    private $user;
    private $pass;
    private $name;
    private $optsFile;
    
    // need a separate opts file for mysqldump due to a mysql bug
    // https://bugs.mysql.com/bug.php?id=18209
    private $optsDumpFile;
    
    public function __construct(Config $config)
    {
        $this->host = $config->get('dbHost');
        $this->user = $config->get('dbUser');
        $this->pass = $config->get('dbPass');
        $this->name = $config->get('dbName');
        $this->optsFile = __DIR__ . "/../../mysql-opts";
        $this->optsDumpFile = __DIR__ . "/../../mysqldump-opts";
    }

    public function query($query, $types = '', $params = [])
    {
        $stmt = $this->getMysqli()->prepare($query);
        
        if (!$stmt) {
            throw new \Exception($this->getMysqli()->error);
        }
        
        if (!empty($params)) {
            $refs = [];
            
            foreach ($params as $key => $param) {
                $refs[$key] = &$params[$key];
            }
            
            $bind = array_merge([$types], $refs);
            call_user_func_array([$stmt, 'bind_param'], $bind);
        }
       
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        if (!$result && $stmt->errno) {
            throw new \Exception('Mysql error: ' . $stmt->error);
        }
        
        if (!$result) {
            return $stmt->affected_rows;
        }
        
        $rows = [];
        
        while ($myrow = $result->fetch_assoc()) {
            $rows[] = $myrow;
        }
        
        $stmt->close();
        
        return $rows;
    }
    
    public function upgradeDb()
    {
        try {
            $this->generateMysqlOptsFile();
            while ($this->upgradeCurrentHash());
        } finally {
            unlink($this->optsFile);
            unlink($this->optsDumpFile);
        }
        echo "--------------------------------------\n";
        echo "All done! Database upgrade successful.\n";
        echo "--------------------------------------\n";
    }
    
    public function generateMysqlOptsFile()
    {
        if (file_exists($this->optsFile)) {
            unlink($this->optsFile);
        }
        
        file_put_contents($this->optsFile, "");
        
        $this->run("chmod 600 {$this->optsFile}", true);
        
        if (file_exists($this->optsDumpFile)) {
            unlink($this->optsDumpFile);
        }
        
        file_put_contents($this->optsDumpFile, "");
        
        $this->run("chmod 600 {$this->optsDumpFile}", true);
        
        $opts = [
            "[client]",
            "user=\"{$this->user}\"",
            "password=\"{$this->pass}\"",
            "host=\"{$this->host}\"",
        ];
        
        file_put_contents($this->optsDumpFile, implode(PHP_EOL, $opts));
        
        $opts[] = "database=\"{$this->name}\"";
        
        file_put_contents($this->optsFile, implode(PHP_EOL, $opts));
    }

    private function getMysqli()
    {
        if (!isset(self::$mysqli)) {
            self::$mysqli = new \mysqli($this->host, $this->user, $this->pass, $this->name);
            if (mysqli_connect_errno()) {
                printf("Connect failed: %s\n", mysqli_connect_error());
                exit();
            }
        }
        return self::$mysqli;
    }
    
    private function upgradeCurrentHash()
    {
        $hash = $this->getDbHash();
        echo "Current DB hash is $hash\n";
        
        $mysqlParams = "--defaults-file={$this->optsFile}";
        $mysqlDumpParams = "--defaults-file={$this->optsDumpFile} {$this->name} " .
                "--single-transaction --routines --triggers --events";
        
        $upgradeFile = __DIR__ . "/../../sql/upgrade/$hash.sql";
        
        echo "Looking for " . basename($upgradeFile) . " ... ";
        
        if (file_exists($upgradeFile)) {
            echo "Upgrade found. Creating compressed DB backup.\n";
            $backupFile = __DIR__ . "/../../sql/backups/backup-" . date("Y-m-d-H-i-s") . ".sql";
            $this->run("mysqldump $mysqlDumpParams | gzip > $backupFile.gz", true);
            $this->run("ls -l $backupFile.gz", true);
            
            // Sanity check
            if (filesize($backupFile . '.gz') < 1000) {
                throw new \Exception("Failed to backup (file too small? empty backup?)");
            }
            
            echo "Applying schema changes.\n";
            $this->run("cat $upgradeFile | mysql $mysqlParams", true);
            $newHash = $this->getDbHash();
            echo "New DB hash is $newHash\n";
            
            if ($newHash == $hash) {
                throw new \Exception("No changes, empty upgrade script?");
            }
            
            return true;
        } else {
            echo "No more changes\n";
        }
        
        return false;
    }
    
    private function run($cmd, $silent = false)
    {
        if (!$silent) {
            echo "Running: $cmd\n";
        }
        
        exec($cmd, $output, $ret);
        
        if (!$silent) {
            echo "Output: \n" . implode("\n", $output) . "\n";
            echo "Return value: " . $ret . "\n";
        }
        
        if ($ret !== 0) {
            throw new \Exception("Command returned non-zero value, exiting");
        }
    }
    
    private function getDbHash()
    {
        $rows = $this->query('show tables');
        
        $tableNames = [];
        foreach ($rows as $table) {
            foreach ($table as $tableName) {
                $tableNames[] = $tableName;
            }
        }
        
        sort($tableNames);
        
        $tableCreateStatements = [];
        
        foreach ($tableNames as $tableName) {
            $output = $this->query("show create table $tableName");
            $str = $output[0]['Create Table'];
            $str = preg_replace("/AUTO_INCREMENT=\d+/", "", $str);
            $tableCreateStatements[$tableName] = $str;
        }
        
        $hash = md5(serialize($tableCreateStatements));
        
        return $hash;
    }
}
