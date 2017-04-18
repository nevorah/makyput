<?php


/**
 * funcao para construir campos
 * 
 * @param String $cnx
 * @param String $table
 * @param String $fields  
 * @param Boolean $tools 
 * 
*/
function fieldsmaker_pg($cnx,$table,$fields="*",$tools=true) {
    
    $result = pg_query($cnx, "SELECT $fields
                FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE table_name = '$table';");

    $rows = pg_fetch_all($result);
    
    //print_r($rows);
    
    $name ="";
    $type ="";
    $null ="";
    $size ="";
    $frm = ""; //massa de dados finais
    
    //inicio da form e nome da tabela
    $frm = "<form>"
        ."<input type='hidden' name='table' value='$table'>";
    
    for ($i=0;$i<count($rows);$i++) { //roda campos
        
        $name = $rows[$i]["column_name"]; //nome da coluna
        $type = $rows[$i]["data_type"]; // tipo de dado
        $null = $rows[$i]["is_nullable"]; // pode nulo
        $size = $rows[$i]["character_maximum_length"]; // tamanho
        
        switch ($type) { //inicio das decisoes para campos
            case "char": 
                $frm .= "$name: <input type='text' name='$name' maxlength=$size value=''><br>";
                break;
            case "character varying": 
                $frm .= "$name: <input type='text' name='$name' maxlength=$size value=''><br>";
                break;
            case "integer":
                $frm .= "$name: <input type='number' name='$name' maxlength=$size value=''><br>";
                break;
            case "date":
                $frm .= "$name: <input type='date' name='$name' maxlength=$size value=''><br>";
                break;
            case "boolean":
                $frm .= "$name: <input type='check' name='$name' maxlength=$size value=''><br>";
                break;
            default:
                $frm .= "$name: <input type='text' name='$name' maxlength=$size value=''><br>";
                
        }
        
        
    }
    
    $frm .= "</form>";
    
    return $frm;
}



