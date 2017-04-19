<?php


/**
 * POSTGRE - Build form fields
 * 
 * @param String $cnx connection of database
 * @param String $table name of table
 * @param String $fields how fields show in form
 * @param Boolean $tools indicate that this form have a buttons
 * @param String $savetype "direct","inList", "none"
 * 
*/
function fieldsmaker_pg($cnx,$table,$fields="*", $savetype) {
    
    $result = pg_query($cnx, "SELECT $fields
                FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE table_name = '$table';");

    $rows = pg_fetch_all($result);
    
    //print_r($rows);
    
    //Variables
    $name ="";
    $type ="";
    $null ="";
    $size ="";
    $frm = ""; //data of fields
    
    //Start form to put fields and tools
    $frm = "<form>"
        ."<input type='hidden' name='table' value='$table'>";
    
    for ($i=0;$i<count($rows);$i++) { //run looping to build fields
        
        $name = $rows[$i]["column_name"]; //get field name
        $type = $rows[$i]["data_type"]; // get type name
        $null = $rows[$i]["is_nullable"]; // if field is nullable
        $size = $rows[$i]["character_maximum_length"]; // get size
        
        switch ($type) { //switch to decide how type of tag ipunt and others little things
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
    
    
    /* @todo create button
     * button must be:
     * if variable $savetype equal direct 
     *      New
     *      Clear
     *      Save
     * if variable $savetype equal inList
     *      Add
     *      Cancel
     * 
     */
    
    
    $frm .= "</form>"; //close form tag
    
    return $frm; //return html
}


