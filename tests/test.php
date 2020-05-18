#!/usr/bin/php
<?php

// Quellverzeichnis Daten
$testdata = __DIR__.'/.testdata/'; 
// Kopie Daten
$searchdir = __DIR__.'/searchdir/'; 

$rulesfile = __DIR__."/testrules.json";

// Prüfe, ob Verzeichnis searchdir existiert, wenn ja -> lösche es
if (is_dir($searchdir)) rrmdir($searchdir);

register_shutdown_function("rrmdir");

// Kopiere .testdata rekursiv nach searchdir
recursive_copy_dir($testdata, $searchdir); 

// Prüfe folgende Dateien $prüfdateien:
exec("php ".__DIR__."/../replacer.php --rules ".$rulesfile." --searchdir ".$searchdir);

//zu ändernde Dateien
$changedFiles = [
    "testdata_unterverz/test1_unterverz.txt",
    "test1_hauptverz.txt",
];

//NICHT zu ändernde Dateien
$NOTchangedFiles = [
    "testdata_unterverz/test2_unterverz.txt",
    "test2_hauptverz.txt",
];



//prüft, ob gewünschte Dateien geändert wurden
foreach($changedFiles as $i => $path)
    {
        $path_original = $testdata.$path;
        $path_kopie = $searchdir.$path;
                
        $hash_original = md5_file($path_original);
        $hash_kopie = md5_file($path_kopie);
    
        if ($hash_original === $hash_kopie) 
        {
            echo "Die zu ändernde Datei '".$path."' wurde nicht angepasst.\n";
            exit(1);
        }      
        
        else continue;
    }
            
// prüft ausgeschlossene Dateien auf Veränderung
foreach($NOTchangedFiles as $i => $path)    
{
        $path_original = $testdata.$path;
        $path_kopie = $searchdir.$path;
        
        $hash_original = md5_file($path_original);
        $hash_kopie = md5_file($path_kopie);
    
        if ($hash_original !== $hash_kopie) 
        {
            echo "In ".$path." wurde eine NICHT gewünschte Änderung vorgenommen. \n";
            exit(1);  
        }
 
        else continue;
};
        
// löscht rekursiv Unterverzeichnisse und Inhalte
function rrmdir($searchdir='') 
{
    if(empty($searchdir)) { $searchdir = __DIR__."/searchdir"; }
    
    $dir = opendir($searchdir);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            $full = $searchdir . '/' . $file;
            if ( is_dir($full) ) {
                rrmdir($full);
            }
            else {
                unlink($full);
            }
        }
    }
    closedir($dir);
    rmdir($searchdir);
}

// Erstelle eine Kopie von .testdata und benenne sie 'searchdir'
function recursive_copy_dir($testdata, $searchdir) 
{ 
  //Öffnet Quellverzeichnis
  $dir = @opendir($testdata);
 
  // Erstellt Zielverzeichnis
  if (!file_exists($searchdir)) @mkdir($searchdir);
 
  // Rekursives kopieren 
  while (false !== ($file = readdir($dir))) 
  { 
        if (( $file != '.' ) && ( $file != '..' )) 
             {
                if ( is_dir($testdata . '/' . $file) ) recursive_copy_dir($testdata . '/' . $file, $searchdir . '/' . $file); 
                else copy($testdata . '/' . $file, $searchdir . '/' . $file);
             }
 
  }
 closedir($dir); 
}