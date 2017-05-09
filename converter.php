<?php

if (PHP_SAPI !== 'cli')
    die('Error: This is only PHP CLI application' . PHP_EOL);

if (! extension_loaded('PDO'))
    die('Error: There is no PDO extension loaded' . PHP_EOL);

if ($argc != 3)
    die('Usage error: php converter.php <inifile> <sqlitefile>' . PHP_EOL);

list($_, $inifile, $sqlitefile) = $argv;

print '[ ] Loading TeamSpeak3 mysql connection ini file' . PHP_EOL;
$ini = parse_ini_file($inifile);

foreach (['host', 'port', 'username', 'password', 'database'] as $key)
    if (! array_key_exists($key, $ini))
        die('[-] Error: Invalid ini file' . PHP_EOL);

$ini['host'] = gethostbyname($ini['host']);

$dsn = sprintf('mysql:host=%s;port=%d;dbname=%s', $ini['host'], $ini['port'], $ini['database']);
print '[ ] Connecting to ' . $dsn . PHP_EOL;

try {
    $mysql = new PDO($dsn, $ini['username'], $ini['password']);
} catch (\PDOException $ex) {
    die('[-] Error: ' . $ex->getMessage() . PHP_EOL);
}

$mysql->query('SET NAMES utf8mb4;');

$dsn = sprintf('sqlite:%s', $sqlitefile);
print '[ ] Loading ' . $dsn . PHP_EOL;

try {
    $sqlite = new PDO($dsn);
} catch (\PDOException $ex) {
    die('[-] Error: ' . $ex->getMessage() . PHP_EOL);
}

$tables = [
    'bans', 'bindings', 'channel_properties', 'channels', 'client_properties', 'clients', 'complains', 'custom_fields', 'group_channel_to_client', 'group_server_to_client', 'groups_channel', 'groups_server', 'instance_properties', 'messages', 'perm_channel', 'perm_channel_clients', 'perm_channel_groups', 'perm_client', 'perm_server_group', 'server_properties', 'servers', 'tokens'
];

foreach ($tables as $table)
{
    print '[ ] table: ' . $table . PHP_EOL;
    
    try {
        $query = $sqlite->query('SELECT * FROM ' . $table);
        $data = $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (\PDOException $ex) {
        die('[-] Error: ' . $ex->getMessage() . PHP_EOL);
    }
    
    $dataCount = count($data);
    
    if ($dataCount == 0) {
        print '[+] Table is empty - skip' . PHP_EOL;
        continue;
    }
    
    print '    count: ' . $dataCount . PHP_EOL;
    
    $mysql->query('TRUNCATE TABLE ' . $table);
    
    $fields = array_keys($data[0]);
    $sql = 'INSERT INTO ' . $table . ' (' . implode(', ', $fields) . ') VALUES (:' . implode(', :', $fields) . ')';
    $statement = $mysql->prepare($sql);
    $counter = 0;
    
    print '    [  0%] ';
    $lastPercent = 0;
    
    foreach ($data as $entry) 
    {
        $status = $statement->execute($entry);
        
        if (! $status)
            die(PHP_EOL . '[-] Error: ' . $statement->errorInfo()[2] . PHP_EOL);
        
        ++$counter;
        
        $percent = ceil($counter * 100 / $dataCount);
        
        if ($percent % 20 != 0 && $percent != $lastPercent) {
            print '.';
            $lastPercent = $percent;
        }
        
        if ($percent != 0 && $percent % 20 == 0 && $percent != $lastPercent) {
            print ' (' . $counter . ' records)';
            print PHP_EOL . '    [' . str_pad($percent, 3, ' ', STR_PAD_LEFT) . '%] ';
            
            if ($percent == 100)
                print 'Success!' . PHP_EOL;
            
            $lastPercent = $percent;
        }
    }
} 

print '[+] All done - exiting!' . PHP_EOL;
