#!/usr/bin/php
<?php
$recursiv = false;
$opts = getopt('', ['rules:', 'searchdir:', 'verbose:', 'help', 'no-recursive', 'dry']);

if(!isset($opts["verbose"]) )
{
   $output  = 1; 
}
else $output  = $opts["verbose"]; 

// Hilfstext --help
if(isset($opts["help"]) )
{
    echo " Funktionsweise des 'Replacers': \n \n"
    ." - Aufruf des Scripts: 'php Pfad/zum/Script/replacer.php' \n\n"        
    ." Das Script benötigt mindestens zwei Parameter:\n \n"
    ." - Pfad zur 'Regeldatei': '--rules /Pfad/zur/Regeldatei.json'\n"
    ." - Pfad zum zu bearbeitenden Verzeichnis: '--searchdir /Pfadangabe/zum/zu/durchsuchenden/Verzeichnis'\n \n"
    ." Beispiel: \n"
    ." 'php  /home/mein_name/htdocs/replacer/replacer.php --rules /home/mein_name/htdocs/regeln.json --searchdir /home/mein_name/htdocs/serverumzug/' \n \n"
    ." Die Regeldatei muss im json-Format vorliegen, siehe 'example.rules.json' \n \n \n"      
            
    ." Des Weiteren sind folgende optionale Parameter verfügbar : \n\n"
    ." – mittels '--no-recursive' wird nur das mit '--searchdir' angegebene Verzeichnis ausgelesen und bearbeitet, Unterverzeichnisse werden ignoriert.\n \n" 
            
    ." – mittels '--dry' wird nur ein Trockenlauf durchgeführt und keine Änderung vorgenommen.\n \n" 
            
    ." – mittels '--verbose' kann ausgegeben werden, welche Dateien untersucht und welche bearbeitet wurden (oder mittels --dry bearbeitet werden WÜRDEN). \n"
    ." optionale Parameter:\n"        
    ."  '--verbose 0' -> keine Ausgabe \n"       
    ."  '--verbose 1' -> Ausgabe, welche Dateien geändert wurden [Standardoption, wenn User keine Angabe gemacht hat] \n"   
    ."  '--verbose 2' -> Ausgabe, welche Dateien untersucht wurden \n \n"   
            
            
    ." Die Hilfe wird mittels 'php pfad/angabe/replacer.php --help' aufgerufen \n \n";
      
    exit(0);
}

//Pflichtparameter rules und searchdir validieren
if( ! isset($opts["rules"]) )
{
    print("Es fehlt die Pflicht-Pfadangabe zur 'Regel-json', verwende:\n \"-- rules hier/und/da/xyz.json\"\n"
        . "  Wenn Du Hilfe benötigst, rufe bitte 'php replacer.php --help' auf \n");
    exit(1);
}
if( ! isset($opts["searchdir"]) )
{
    print("  Es fehlt die Pflicht-Pfadangabe des zu durchsuchenden Verzeichnisses, verwende:\n \"-- searchdir hier/soll/gesucht/und/ersetzt/werden/\" \n"
        . "  Wenn Du Hilfe benötigst, rufe bitte 'php replacer.php --help' auf \n");
    exit(1);
}

// Abfrage, ob User rekursiv sucht
if(!isset($opts["no-recursive"]) ) $recursiv = true;

// rtrim — Entfernt Leerraum (oder andere Zeichen) vom Ende eines Strings
$searchdir  = rtrim($opts["searchdir"],DIRECTORY_SEPARATOR);

//Prüfung ob das Verzeichnis  existiert
if ( !is_dir ( $searchdir ))
{
    print("  Das zu durchsuchende Verzeichnis existiert nicht oder die Pfadangabe stimmt nicht. \n"
        . "  Wenn Du Hilfe benötigst, rufe bitte 'php replacer.php --help' auf \n");
    exit(1);
}

$rulefile  = $opts["rules"];

// Prüft Existenz der Regeldatei
if (!file_exists($rulefile)) {
    echo "  Die Regeldatei existiert nicht oder die Pfadangabe stimmt nicht \n"
       . "  Wenn Du Hilfe benötigst, rufe bitte 'php replacer.php --help' auf \n" ;
    exit(1);
} 

// realpath — Löst einen Pfad in einen absoluten und eindeutigen auf
$opts["rules"] = realpath($opts["rules"]); 
if ($opts["rules"]=== false)
{
    print("  Irgendwas stimmt nicht :/ \n");
    exit(1);
} 

function search_and_replace($path , array $rules, $output, $dry)
{
 // file_get_contents — Liest die gesamte Datei in einen String
    $content = file_get_contents($path);
 
    
    $arrSearch= [];
    $arrReplace = [];
    foreach($rules as $i => $rule)
    {
        $arrSearch[$i] = $rule[0];
        $arrReplace[$i] = $rule[1];
    }
 
   $newcontent = str_replace($arrSearch, $arrReplace, $content);    
    
 //  verbose 2 - Ausgabe aller untersuchten Dateien   (bearbeitet und nichtbearbeitet) 
    if( $output == 2) echo "[ ]: ".$path."\n";
    
//   verbose 1 - Ausgabe geänderte Dateien
    if($output == 1 && $newcontent !== $content)
    {
        echo "[x]: ".$path."\n";
    }
   
    if(!$dry && false === file_put_contents($path, $newcontent))
    {
        print("  Fehler beim Bearbeiten von ".$path."\n");
    }   
   
/*
 if($dry) $content = file_put_contents($path, $newcontent);
 */   
  }

function app($searchdir , $rulesArray, $rulefile, $recursiv, $output, $dry)
{ 
//Gehe durch alle Dateien in dem Verzeichnis und durchsuche und ersetze jeweils alle Dateien laut Regeln
    foreach(scandir($searchdir) as $dir_entry)
    {
        if(in_array($dir_entry, [".",".."]) ) continue;
        
        $path = $searchdir.DIRECTORY_SEPARATOR.$dir_entry;   

// wenn Datei = replacer.php = übergehen
        if($path === __FILE__) continue;
// wenn Datei = regel.json = übergehen       
        if($path === $rulefile) continue;      
// wenn ignoriere Dateien, die im selben oder Unter-Verzeichnis des replacer-Scripts liegen 
        if ( false !== strpos($searchdir, __DIR__)) continue;
      
// Wenn Verzeichnis, aber no-recursive gewünscht-> übergehen / wenn rekursiv gewünscht-> abarbeiten       
        if ($recursiv == false && is_dir($path) ) continue;
        if ($recursiv == true  && is_dir($path) )  app($path , $rulesArray, $rulefile, $recursiv, $output, $dry); 
         
        if(!is_file($path)) continue;

// Bearbeite nur Textdateien
        if( false === strpos(mime_content_type($path) , "text/") ) continue;
        
// Suche & ersetze        
        search_and_replace($path , $rulesArray, $output, $dry);        
             
    }
}

$regeln = file_get_contents($rulefile);

//Prüfe ob 1.Parameter der Pfad zu einer existierenden Datei(en) ist und lies die Datei(en) ein
$rulesArray =json_decode($regeln, true);     
if($rulesArray === null)
{
    print("  Ungültiges Regelformat - es muss JSON sein.\n");
    exit(1);
}

//Prüft Regeldatei auf Vorhandensein von 2 Werten und ob diese Strings sind
foreach($rulesArray as $wert)
{
    $count = count($wert);

    if ($count != 2)
    {
        echo "  Die Austauschregeln benötigen nur jeweils zwei Werte, Deine *.json hat auch ".$count." Werte, bitte korrigiere dies.".PHP_EOL;
        exit(1);
    }
    
    if( !is_string($wert[0]) || !is_string($wert[1]) )
    {
        print("  Suchwort und Ersatzwort müssen vom Typ String sein");
        exit(1);
    }
}


app($searchdir,$rulesArray,$rulefile,$recursiv, $output, isset($opts["dry"]));
// echo Diff::toTable(Diff::compareFiles('testsubdir/test.txt', 'new.txt'));