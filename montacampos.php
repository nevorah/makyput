<?php
if (!isset($scriptbusca)) {
$scriptbusca = "";
}
$tiposdebusca = "";
if (!function_exists('cdt')) {
    include("/var/www/webroot/mtcerp/class/cdt.php");
}
include("/var/www/webroot/mtcerp/class/estearquivo.php");



/**
 * montacampos
 * 
 * @global $scriptbusca
 * PARA O CAMPO DE BUSCA É NECESSÁRIO CAPTURAR A VARIÁVEL GLOBAL $criptbusca
 * E imprimir como JS
 * 
 * @param {String} $tabela
 * @param {Conncetion} $cnx
 * @param {String} $oid Numero de Id para chamar o cadastro, caso não exista
 * Digite "", ou nao digite.
 * 
 * @return String
 */
$form = Array();
$semails = "";
function montacampos($tabela, $cnx, $oid=null,$seqc=null,$relacionamento=false,$coluna=null) {
    if (isset($seqc)==false || $seqc==null) {
        $seqc ="";
    }
    if (isset($oid)==false || $oid==null) {
        $oid ="";
    }
    

    $tabelasql = preg_replace("/\d$|\d\d$|\d\d\d$/", "", $tabela);
    
    $result = mysql_query("SHOW FULL COLUMNS FROM $tabelasql", $cnx);
    $er = mysql_error($cnx);
     if ($er != "") {
        mysql_close($cnx);
        die('alert("' . $er . ' (1)")');
    }
    
    if ($oid!="") {
        $nid = "id";
        if ($relacionamento) { $nid = "relacionamento"; }
        if ($coluna!=null) {
            $nid = $coluna;
        }
        $sql = "SELECT SQL_CACHE * FROM $tabelasql WHERE $nid = $oid";
        $osdados = mysql_query($sql);
        echo $oid;
        if (mysql_error($cnx)=="") {
            $regs = mysql_fetch_array($osdados, MYSQL_BOTH);
        } else {
            $oid="";
        }
    }
    
    global $scriptbusca;
    global $tiposdebusca;
    global $form;
    $cruzado = '';
    //$scriptbusca="";
    $nomecruzado = array();
    $codemirror = false;
    $montagem = '';
    $campodebusca = false;
    $montagem .="<div id='divfrm$tabela$seqc'><form name='frm$tabela$seqc' id='frm$tabela$seqc' onsubmit='return false' autocomplete='off'>";
    $ctc = 0;
    $readonly = '';
    while ($r = mysql_fetch_array($result, MYSQL_NUM)) {
        $tipor = strval($r[1]);
        $detalhes = $r[8];
        if (preg_match("/\&[^;]+;/", $detalhes)!==1) {
            $detalhes = htmlentities(utf8_encode($detalhes));
        }
        //$cdetalhe = html_entity_decode($detalhe)
        $name = $r[0];
        $padrao = $r[5];
        $extra = $r[6];
        $odado = '';
        
        $tiposdebusca .= ';'.$name;
        if ($oid!='') { $odado = $regs[$name]; }
        if ($odado=='') { $odado=$padrao; }
        $tipo = preg_replace("/\(.*\)/", "", $tipor);
        if ($tipo!='enum' && $tipo!='set') {
            $tipoclass = ' '.$tipo.' '.$tipor;
        } else {
        $tipoclass=" ".$tipo;
            
        }
        //$montagem .=$tipo."<br>";
        $tam = str_replace(array($tipo, "(", ")"), "", $tipor);
        if (intval($tam) > 35 && ($tipo == "varchar" || $tipo == "char")) {
            $tam = '35';
        }
        $max = str_replace(array($tipo, "(", ")"), "", $tipor);

        $disabled = '';
        $disabled2 = '';
        if (strtoupper($extra) == 'AUTO_INCREMENT') {
            //foi retirado o readonly do auto_increment pois para 
            //facilitar migracao de dados
            $disabled = " readonly='true' style='border: 0px; background-color: transparent'";
        }
        $campodebusca = false;
        $campodebuscacruzada = false;
        if (substr($detalhes, 0, 1) == '_') {
            preg_match("/\_.*\_/", $detalhes, $paraproc);
            $detalhes = str_replace($paraproc[0], "", $detalhes);
            $pp = $paraproc[0];
            $campodebusca = true;
            if ('_'==$name[0]) {
                $campodebuscacruzada=true;
            }
        }
        $sels = '';
        $setabelas = '';
        if (substr($detalhes, 0,5) == '(sel)') {
            $tipo = 'sels';
            $rsels = mysql_query("SELECT SQL_CACHE $name FROM $tabelasql GROUP BY $name ORDER BY $name;",$cnx);
            while ($rs = mysql_fetch_array($rsels)) {
                $sels .= "<option>".$rs[0]."</option>";
            }
            $detalhes = str_replace('(sel)','',$detalhes);
            if (isset($POST["tabela"])) { 
                $setabelas=$POST["tabela"];
            }
            if ($setabelas!='detalhes_produtos') {
             $montagem .="<div id='cp$tabela$seqc$name' class='campos cp$tabela$name' type='$tipo' id='cp$tabela$seqc$name'>
                     <span class='campotitulo' id='tit$tabela$name'>$detalhes <help onclick='window.open(\\\"http://env-6461067.jelasticlw.com.br/wiki/index.php/Campo ".preg_replace("/[\d]+/",'',$tabela)." $detalhes\\\");'>?</help></span>
                         <select name='$name' id='sel$tabela$seqc$name' class='input$tabela$tipoclass' onchange='adicionacombo(this);'>
                             $sels
                          <option value='-'></option><option>Novo</option></select></div>";
            } else {
             $montagem .="<div id='cp$tabela$seqc$name' class='campos cp$tabela$name' type='$tipo' id='cp$tabela$seqc$name'>
                     <span class='campotitulo' id='tit$tabela$name'>$detalhes <help onclick='window.open(\\\"http://env-6461067.jelasticlw.com.br/wiki/index.php/Campo ".preg_replace("/[\d]+/",'',$tabela)." $detalhes\\\");'>?</help></span>
                         <select name='$name' id='sel$tabela$seqc$name' class='input$tabela$tipoclass' onchange='adicionacombo(this);'>
                             $sels
                          <option value='-'></option><option>Novo</option></select></div>";
            }
        }
        
        if (substr($detalhes, 0,5) == '(sel(') {
            $tipo = 'sels';
            preg_match("/\([^\(]+\)/",$detalhes,$tabelasqlb);
            $sql = "SELECT SQL_CACHE $name FROM $tabelasqlb[0] GROUP BY $name ORDER BY $name;";
            
            //echo "\n//$sql\n";
            
            $rsels = mysql_query($sql,$cnx);

            while ($rs = mysql_fetch_array($rsels)) {
                $sels .= "<option>".$rs[0]."</option>";
            }
            $detalhes =  preg_replace('/\(sel\(.+\)/','',$detalhes);
             $montagem .="<div id='cp$tabela$seqc$name' class='campos cp$tabela$name' type='$tipo' id='cp$tabela$seqc$name'>
                     <span class='campotitulo' id='tit$tabela$name'>$detalhes <help onclick='window.open(\\\"http://env-6461067.jelasticlw.com.br/wiki/index.php/Campo ".preg_replace("/[\d]+/",'',$tabela)." $detalhes\\\");'>?</help></span>
                         <select name='$name' id='sel$tabela$seqc$name' class='input$tabela$tipoclass' onchange='adicionacombo(this);$(this).css(\\\"background\\\",\\\"#ffc\\\")'>
                             <option value=''>Nenhum</option>
                             $sels
                          </select></div>";
        }
        
        
        if ($name == 'reg_usuario') {
            $montagem .="<input type='hidden' value='".$_COOKIE["us"]."' name='" . $name . "' id='in$idcampo' size='" . ($tam) . "' maxlength='$max' class='input$tabela$tipoclass reg_usuario' />";
            $tipo = 'reg_usuario';
        }
        
        if (substr($name, 0,3) == 'tel') {
            $montagem .="<div id='cp$tabela$seqc$name' class='campos cp$tabela$name' type='$tipo' id='cp$tabela$seqc$name'>
                     <span class='campotitulo' id='tit$tabela$name'>$detalhes <help onclick='window.open(\\\"http://env-6461067.jelasticlw.com.br/wiki/index.php/Campo ".preg_replace("/[\d]+/",'',$tabela)." $detalhes\\\");'>?</help></span>
                         <input type='text' onblur='tels(this)' value='$odado' name='" . $name . "' id='in$idcampo' size='" . ($tam) . "' maxlength='$max' class='input$tabela$tipoclass tel'  onkeypress='$(this).css(\\\"background\\\",\\\"#ffc\\\")'/></div>";
            $tipo = "oktels";
        }

        if (substr($detalhes, 0, 2) == 'J\$') {
            $tipo = 'javascript';
            $detalhes = "A&ccedil;&otilde;o JavaScript";
            $odado = str_replace("\"", "\\\"", $odado);
            $montagem .= "<div id='cp$tabela$seqc$name' class='campos cp$tabela$name' type='$tipo' id='cp$tabela$seqc$name'>
                 <span class='campotitulo'>$detalhes <help onclick='window.open(\\\"http://env-6461067.jelasticlw.com.br/wiki/index.php/Campo ".preg_replace("/[\d]+/",'',$tabela)." $detalhes\\\");'>?</help></span>
            <textarea id='cp$tabela$seqc$name' 
                     name='$name' class='input$tabela$tipoclass' onkeypress='$(this).css(\\\"background\\\",\\\"#ffc\\\")'>$padrao"."$odado</textarea></div>";
            $codemirror = true;
        }
        if (is_array($detalhes)) {
        if ($detalhes[0]=='*') {
            $tipo = 'passw';
            $detalhes = substr($detalhes,1);
            $montagem .="<div id='cp$tabela$seqc$name' class='campos cp$tabela$name' type='$tipo' id='cp$tabela$seqc$name'>
                     <span class='campotitulo' id='tit$tabela$name'>$detalhes <help onclick='window.open(\\\"http://env-6461067.jelasticlw.com.br/wiki/index.php/Campo ".preg_replace("/[\d]+/",'',$tabela)." $detalhes\\\");'>?</help></span>
                         <input type='password' value='$odado' name='" . $name . "' size='" . ($tam) . "' maxlength='$max' class='input$tabela$tipoclass'  onkeypress='$(this).css(\\\"background\\\",\\\"#ffc\\\")'/></div>";
        }
        }
        
        if ($detalhes=='auto') {
            $tipo = 'auto';
            $detalhes = "";
            $resauto = mysql_query("SELECT SQL_CACHE 
                ifnull(max(relacionamento),0)+1 
                FROM $tabelasql",$cnx);
            $autor = mysql_fetch_array($resauto);
            $odado =$autor[0];
            $montagem .="<div id='cp$tabela$seqc$name' class='campos cp$tabela$name camposhidden' type='$tipo' id='cp$tabela$seqc$name'>
                         <input type='hidden' value='$odado' name='" . $name . "' size='" . ($tam) . "' maxlength='$max' class='input$tabela$tipoclass' /></div>";
        }
        
        if ($detalhes=='hidden') {
            $tipo = 'hidden';
            $detalhes = '';
            $montagem .="<div id='cp$tabela$seqc$name' class='campos cp$tabela$name' type='$tipo' id='cp$tabela$seqc$name'>
                         <input type='hidden' value='$odado' name='" . $name . "' size='" . ($tam) . "' maxlength='$max' class='input$tabela$tipoclass' /></div>";
        }
        
        if ($detalhes=='some!') {
            $tipo = 'some!';
            $detalhes = '';
            $montagem .='';
        }
        
        if (substr($detalhes, 0, 5) == '(img)') {
            $tipo = 'selector de imagem';
            $detalhes = str_replace('(img)', '', $detalhes);
             $montagem .= "<div id='cp$tabela$seqc$name' class='campos imgcad cp$tabela$name' type='$tipo' id='cp$tabela$seqc$name'>
                 <span class='campotitulo'>$detalhes <help onclick='window.open(\\\"http://env-6461067.jelasticlw.com.br/wiki/index.php/Campo ".preg_replace("/[\d]+/",'',$tabela)." $detalhes\\\");'>?</help></span>";
             if (file_exists($odado) && $tabelasql=="menu") {
                 $montagem .= "<div class='imagemcad'><img src='$odado' id='img$tabela$name'></div>";
             } else if($tabelasql=="menu") {
                 $montagem .= "<div class='imagemcad'><img src='imgs/padrao.png' id='img$tabela$name'></div>";
             } else {
                 $montagem .= "<div class='imagemcad'><img style='cursor:pointer' title='Mudar Imagem...' onclick=carregaimagem(\'".$tabelasql."\',this) alt='' src='imgs/padrao.png' id='img$tabela$name'></div>";
             }
             if($tabelasql=="menu") {
            $montagem .= "<select name='$name' class='input$tabela' onchange='$(\\\"#img$tabela$name\\\")[0].src=this.value;$(this).css(\\\"background\\\",\\\"#ffc\\\")'>";
            
            $dir = "imgs/"; 
            if (file_exists($dir)) {
            $dh = opendir($dir); 
            } else {
                $dh = opendir("../".$dir);
            }
            if ($oid!="") { "<option SELECTED>$odado</option>"; }
            while (false !== ($filename = readdir($dh))) {
                if (preg_match("/^([\d]{14}|temp2?|)\.(jpg|png|tif|tiff|jpeg|)$/",$filename)!=1) {
                    $montagem .= "<option>imgs/$filename</option>";
                }
            }
            $montagem .= "</select><input type='button' value='Carregar Imagem' onclick='carregarimg(\\\"$tabela-$name\\\")' >";
             }
            $montagem .='</div>';
        }
        
        if ((substr($name, -6, 6) == "_senha" || substr($name, -6, 6) == "__pass") && $tipo!='passw') {
            $tipo = 'resolvido';
            $names = $name;
            $montagem .="<div id='cp$tabela$seqc$name' class='campos cp$tabela$name' type='$tipo' id='cp$tabela$seqc$name'>
                     <span class='campotitulo' id='tit$tabela$name'>$detalhes <help onclick='window.open(\\\"http://env-6461067.jelasticlw.com.br/wiki/index.php/Campo ".preg_replace("/[\d]+/",'',$tabela)." $detalhes\\\");'>?</help></span>
                         <input type='password' value='$odado' name='" . $names . "' size='" . ($tam) . "' maxlength='$max' class='input$tabela$tipoclass'  onkeypress='$(this).css(\\\"background\\\",\\\"#ffc\\\")'/></div>";
            
        }
        
        if (is_array($detalhes)) {
        if ($detalhes[0]=="{") {
                $tipo = 'oarray';
                $djson = array();
                $deto = preg_replace("/\"|\{|\}|Acrecimo\/Desconto/",'',$detalhes);
                preg_match_all('/[^\d ,]+\:\-?[\d]{1,3}/',$deto, $djson);
                for ($i=0;$i<count($djson);$i++) {
                    
                }
                $detalhes = preg_replace("/\{[^\{]+\}/", "", $detalhes);
                $montagem .="<div id='cp$tabela$seqc$name' class='campos cp$tabela$name' type='$tipo' id='cp$tabela$seqc$name'>
                     <span class='campotitulo' id='tit$tabela$name'>$detalhes <help onclick='window.open(\\\"http://env-6461067.jelasticlw.com.br/wiki/index.php/Campo ".preg_replace("/[\d]+/",'',$tabela)." $detalhes\\\");'>?</help></span>
                         <select name='$name' class='input$tabela$tipoclass' onchange='$(this).css(\\\"background\\\",\\\"#ffc\\\")'>";
                for ($i=0;$i<count($djson[0]);$i++) {
                    $opts = explode(":",$djson[0][$i]);
                    for($si=0;$si<count($opts[$i]);$si++) {
                        $opts[0] = str_replace('"','',$opts[0]);
                        $montagem .= "<option value='".$opts[1]."'>".$opts[0]."</option>";
                    }
                    
                }
                $montagem .= "</select></div>";
        }
        }
        
        if ($name=='municipio') { $tam='25'; }
        
        $idcampo = 'in'.$tabela.$seqc.$name;
        
        $tipoespecial = "";
        
        switch ($tipo) {
            case 'int':
                $names = $name;
                if ($campodebusca) {
                  
                    $names = "no_" . $name;
                }
                if ($name=='user_resp') {
                    $odado = $_COOKIE["us"];
                    $readonly = "readonly='true'";
                }
                $montagem .="<div id='cp$tabela$seqc$name' class='campos cp$tabela$name' type='$tipo' id='cp$tabela$seqc$name'>
                     <span class='campotitulo' id='tit$tabela$name'>$detalhes <help onclick='window.open(\\\"http://env-6461067.jelasticlw.com.br/wiki/index.php/Campo ".preg_replace("/[\d]+/",'',$tabela)." $detalhes\\\");'>?</help></span>
                         <input type='text' name='" . $names . "' id='$idcampo' $readonly 
                             size='" . ($tam + 1) . "' value='$odado' maxlength='$max' $disabled class='input$tabela$tipoclass $name'  onkeypress='$(this).css(\\\"background\\\",\\\"#ffc\\\")'/>";
                $readonly='';
                if ($campodebusca) {
                    $cruzado = "";
                    if ($campodebuscacruzada) {
                        preg_match("/_([^_]+)_/",$name,$nomecruzado);
                        $cruzado = ",cruzado : '$nomecruzado[1]|'+$('#in$tabela$seqc$nomecruzado[1]-id').val()";
                    }
                    $montagem .= "
                            <input type='hidden' name='$name' id='$idcampo-id' value='$odado' class='input$tabela$tipoclass $name' />";
                    
                    $montagem = str_replace("id='$idcampo'","id='$idcampo' onchange='zerarelacionamento(this)'",$montagem);
                    //if ($idcampo!="inoscontato") {
                    
                    $scriptbusca .= "
                        $('#$idcampo').autocomplete({
                            //source: 'search.php?".$cruzado."paraproc=$pp',
                                source: function(request, response) {
                                $.ajax({
                                  url: \"search.php?paraproc=$pp\",
                                       dataType: \"json\",
                                  data: {
                                    term : request.term
                                    $cruzado
                                  },
                                  success: function(data) {
                                    response(data);
                                  }
                                });
                              },
                            minLength: 2,
                            select: function( event, ui ) {
                                $('#$idcampo-id').val(ui.item.id);
                                _ultAchado=ui.item.id;
                            },
                            messages: {
                                noResults: '',
                                results: function() {}
                            }
                        });
                        ";
                   /* } else {

                         $scriptbusca .= "
                        $('#$idcampo').autocomplete({
                            source: 'search.php?osentidade='
                            +_ultAchado
                            +'&paraproc=$pp',
                            minLength: 2,
                            select: function( event, ui ) {
                                $('#$idcampo-id').val(ui.item.id);
                            },
                            messages: {
                                noResults: '',
                                results: function() {}
                            }
                        });
                        ";
                    } */
                }
                $montagem .= '</div>';
                break;
            case 'varchar':
                $names = $name;
                if ($campodebusca) {
                    $names = "no_" . $name;
                }
                $semails="'";
                if (preg_match("/email/",$name)==1) {
                    $semails = " onblur='confereEmail(this)' ";
                }
                
                $montagem .="<div id='cp$tabela$seqc$name' class='campos cp$tabela$name' type='$tipo' id='cp$tabela$seqc$name'>
                     <span class='campotitulo' id='tit$tabela$name'>$detalhes <help onclick='window.open(\\\"http://env-6461067.jelasticlw.com.br/wiki/index.php/Campo ".preg_replace("/[\d]+/",'',$tabela)." $detalhes\\\");'>?</help></span>
                         <input type='text' id='in$tabela$seqc$name' value='$odado' name='" . $names . "' size='" . ($tam) . "' maxlength='$max' class='input$tabela$tipoclass $name'$semails  onkeypress='$(this).css(\\\"background\\\",\\\"#ffc\\\")'/>";
                
                if ($campodebusca) {
                    $montagem .= "
                            <input type='hidden' name='$name' id='$idcampo-id' value='$odado' class='input$tabela$tipoclass $name' />";
                    $scriptbusca .= "
                        $('#$idcampo').autocomplete({
                            source: 'search.php?paraproc=$pp',
                            minLength: 2,
                            select: function( event, ui ) {
                                $('#$idcampo-id').val(ui.item.id);
                            },
                            messages: {
                                noResults: '',
                                results: function() {}
                            }
                        });
                        ";
                    $montagem = str_replace("id='in$tabela$seqc$name'","id='in$tabela$seqc$name' onchange='zerarelacionamento(this)'",$montagem);
                }
                $montagem .='</div>';
                break;
            case 'char':
                $names = $name;
                if ($campodebusca) {
                    $names = "no_" . $name;
                }
                $montagem .="<div id='cp$tabela$seqc$name' class='campos cp$tabela$name' type='$tipo' id='cp$tabela$seqc$name'>
                     <span class='campotitulo' id='tit$tabela$name'>$detalhes <help onclick='window.open(\\\"http://env-6461067.jelasticlw.com.br/wiki/index.php/Campo ".preg_replace("/[\d]+/",'',$tabela)." $detalhes\\\");'>?</help></span>
                         <input type='text'  value='$odado' name='" . $names . "' id='in$idcampo' size='" . ($tam) . "' maxlength='$max' class='input$tabela$tipoclass'  onkeypress='$(this).css(\\\"background\\\",\\\"#ffc\\\")'/>";
                
                 if ($campodebusca) {
                    $montagem .= "
                            <input type='hidden' name='$name' id='$idcampo-id' value='$odado' class='inputhide$tabela$tipoclass $name' />";
                    $scriptbusca .= "
                        $('#cp$tabela$seqc$name .input$tabela').autocomplete({
                            source: 'search.php?paraproc=$pp',
                            minLength: 1,
                            select: function( event, ui ) {
                                $('#$idcampo-id').val(ui.item.id);
                            },
                            messages: {
                                noResults: '',
                                results: function() {}
                            }
                        });
                        ";
                    $montagem = str_replace("id='in$idcampo'","id='in$idcampo' onchange='zerarelacionamento(this)'",$montagem);
                }
                $montagem .= '</div>';
                break;
            case 'enum':
                $ss = explode(",", str_replace("'", "", $tam));
                $select = '';
                $style='';
                
                $sct = ' SELECTED';
                if ("cor"==$name || 
                        "cor1"==$name || 
                        "cor2"==$name || 
                        "cor3"==$name) {
                    for ($i = 0; $i < count($ss); $i++) {
                        $style = " style='background-color:" . $ss[$i] . ";color:" . $ss[$i] . "'";
                        $select .= "<option$style>" . htmlentities($ss[$i]) . "</option>";
                    }
                    if ($oid!="") { 
                        $style = " style='background-color:" . $odado . ";color:" . $odado . "'";
                        $select.="<option SELECTED>$odado</option>"; } 
                    else { 
                        $style = " style='background-color:" . $padrao . ";color:" . $padrao . "'";
                        $select.="<option SELECTED>$padrao</option>"; }
                        $style .= " onchange='$(this).css({backgroundColor:this.value,color:this.value});' ";
                } else {
                    for ($i = 0; $i < count($ss); $i++) {
                        $select .= "<option$style>" . htmlentities($ss[$i]) . "</option>";
                    }
                    if ($oid!="") { $select.="<option SELECTED>$odado</option>"; } 
                    else { $select.="<option SELECTED>$padrao</option>"; }
                }
                $montagem .="<div id='cp$tabela$seqc$name' class='campos cp$tabela$name' type='$tipo' id='cp$tabela$seqc$name'>
                     <span class='campotitulo' id='tit$tabela$name'>$detalhes <help onclick='window.open(\\\"http://env-6461067.jelasticlw.com.br/wiki/index.php/Campo ".preg_replace("/[\d]+/",'',$tabela)." $detalhes\\\");'>?</help></span>
                         <select name='$name' $style class='input$tabela$tipoclass' onchange='$(this).css(\\\"background\\\",\\\"#ffc\\\")'>$select</select></div>";
                break;
            case 'set':
                $ss = explode(",", str_replace("'", "", $tam));
                $select = "";
                for ($i = 0; $i < count($ss); $i++) {
                    $select .= "<option>" . $ss[$i] . "</option>";
                }
                if ($oid!="") { $select.="<option SELECTED>$odado</option>"; }  
                $montagem .="<div id='cp$tabela$seqc$name' class='campos cp$tabela$name' type='$tipo' id='cp$tabela$seqc$name'>
                     <span class='campotitulo' id='tit$tabela$name'>$detalhes <help onclick='window.open(\\\"http://env-6461067.jelasticlw.com.br/wiki/index.php/Campo ".preg_replace("/[\d]+/",'',$tabela)." $detalhes\\\");'>?</help></span>
                         <select name='$name'  class='input$tabela$tipoclass' onchange='$(this).css(\\\"background\\\",\\\"#ffc\\\")'>$select</select></div>";
                break;
            case 'timestamp':
                if ($odado=='CURRENT_TIMESTAMP') { $odado=date("Y-m-d H:i:s");
                $odado = cdt($odado,"br");
                $disabled2 = "readOnly style='border:0px;background-color:transparent;' "; }
                $montagem .="<div id='cp$tabela$seqc$name' class='campos cp$tabela$name' type='$tipo' id='cp$tabela$seqc$name'>
                     <span class='campotitulo' id='tit$tabela$name'>$detalhes <help onclick='window.open(\\\"http://env-6461067.jelasticlw.com.br/wiki/index.php/Campo ".preg_replace("/[\d]+/",'',$tabela)." $detalhes\\\");'>?</help></span>
                         <input type='text' value='$odado' name='" . $name . "' size='15' maxlength='$max' class='input$tabela$tipoclass' $disabled2  onkeypress='$(this).css(\\\"background\\\",\\\"#ffc\\\")'/></div>";
                $temdata=true;
                break;
            case 'date':
                if ($odado=="") { $odado=date("d/m/Y"); }
                $montagem .="<div id='cp$tabela$seqc$name' class='campos cp$tabela$name' type='$tipo' id='cp$tabela$seqc$name'>
                     <span class='campotitulo'>$detalhes <help onclick='window.open(\\\"http://env-6461067.jelasticlw.com.br/wiki/index.php/Campo ".preg_replace("/[\d]+/",'',$tabela)." $detalhes\\\");'>?</help></span>
                         <input type='text' value='$odado' name='" . $name . "' size='10' maxlength='10' class='input$tabela cpdate cpdate$tabela$seqc$tipoclass' onkeyup='formatar(this,\\\"##/##/####\\\")'  onkeypress='$(this).css(\\\"background\\\",\\\"#ffc\\\")'/></div>";
                $temdata=true;
                break;
            case 'datetime':
                if ($odado=='1999-01-01 00:00:00' ||
                        $odado=='0000-00-00 00:00:00') { $odado=date("d/m/Y h:i:s"); 
                $disabled2 = "readOnly style='border:0px;background-color:transparent;' "; }
                $montagem .="<div id='cp$tabela$seqc$name' class='campos cp$tabela$name' type='$tipo' id='cp$tabela$seqc$name'>
                     <span class='campotitulo' id='tit$tabela$name'>$detalhes <help onclick='window.open(\\\"http://env-6461067.jelasticlw.com.br/wiki/index.php/Campo ".preg_replace("/[\d]+/",'',$tabela)." $detalhes\\\");'>?</help></span>
                         <input type='text' value='$odado' name='" . $name . "' size='19' maxlength='$max' class='input$tabela cpdate cpdate$tabela$seqc$tipoclass' $disabled2  onkeypress='$(this).css(\\\"background\\\",\\\"#ffc\\\")'/></div>";
                $temdata=true;
                break;
            case 'time':
                $montagem .="<div id='cp$tabela$seqc$name' class='campos cp$tabela$name' type='$tipo' id='cp$tabela$seqc$name'>
                     <span class='campotitulo' id='tit$tabela$name'>$detalhes <help onclick='window.open(\\\"http://env-6461067.jelasticlw.com.br/wiki/index.php/Campo ".preg_replace("/[\d]+/",'',$tabela)." $detalhes\\\");'>?</help></span>
                         <input type='text' value='$odado' name='" . $name . "' size='" . ($tam) . "' class='input$tabela$tipoclass'  onkeypress='$(this).css(\\\"background\\\",\\\"#ffc\\\")'/></div>";
                break;
            
            case 'text':
                $montagem .="<div id='cp$tabela$seqc$name' class='campos cp$tabela$name' type='$tipo' id='cp$tabela$seqc$name'>
                     <span class='campotitulo' id='tit$tabela$name'>$detalhes <help onclick='window.open(\\\"http://env-6461067.jelasticlw.com.br/wiki/index.php/Campo ".preg_replace("/[\d]+/",'',$tabela)." $detalhes\\\");'>?</help></span>
                         <textarea id='id$tabela$name' name='" . $name . "' maxlength='" . ($tam) . "' class='input$tabela$tipoclass' onkeypress='$(this).css(\\\"background\\\",\\\"#ffc\\\")'>$odado</textarea></div>";
                break;
            case 'mediumtext':
                $montagem .="<div id='cp$tabela$seqc$name' class='campos cp$tabela$name' type='$tipo' id='cp$tabela$seqc$name'>
                     <span class='campotitulo' id='tit$tabela$name'>$detalhes <help onclick='window.open(\\\"http://env-6461067.jelasticlw.com.br/wiki/index.php/Campo ".preg_replace("/[\d]+/",'',$tabela)." $detalhes\\\");'>?</help></span>
                         <textarea id='id$tabela$name' name='" . $name . "' maxlength='" . ($tam) . "' class='input$tabela$tipoclass' onkeypress='$(this).css(\\\"background\\\",\\\"#ffc\\\")'>$odado</textarea></div>";
                break;
            case 'blob':
                $montagem .="<div id='cp$tabela$seqc$name' class='campos cp$tabela$name' type='$tipo' id='cp$tabela$seqc$name'>
                     <span class='campotitulo' id='tit$tabela$name'>$detalhes <help onclick='window.open(\\\"http://env-6461067.jelasticlw.com.br/wiki/index.php/Campo ".preg_replace("/[\d]+/",'',$tabela)." $detalhes\\\");'>?</help></span>
                         <textarea  id='id$tabela$name' name='" . $name . "' maxlength='" . ($tam) . "' class='input$tabela$tipoclass' onkeypress='$(this).css(\\\"background\\\",\\\"#ffc\\\")'>$odado</textarea></div>";
                break;
            case 'longtext':
                $montagem .="<div id='cp$tabela$seqc$name' class='campos cp$tabela$name' type='$tipo' id='cp$tabela$seqc$name'>
                     <span class='campotitulo' id='tit$tabela$name'>$detalhes <help onclick='window.open(\\\"http://env-6461067.jelasticlw.com.br/wiki/index.php/Campo ".preg_replace("/[\d]+/",'',$tabela)." $detalhes\\\");'>?</help></span>
                         <textarea rows=7 id='id$tabela$name' name='" . $name . "' maxlength='" . ($tam) . "' class='input$tabela$tipoclass' onkeypress='$(this).css(\\\"background\\\",\\\"#ffc\\\")'>$odado</textarea></div>";
                break;
            case 'double':
                $montagem .="<div id='cp$tabela$seqc$name' class='campos cp$tabela$name' type='$tipo' id='cp$tabela$seqc$name'>
                     <span class='campotitulo' id='tit$tabela$name'>$detalhes <help onclick='window.open(\\\"http://env-6461067.jelasticlw.com.br/wiki/index.php/Campo ".preg_replace("/[\d]+/",'',$tabela)." $detalhes\\\");'>?</help></span>
                         <input type='text' value='$odado' name='" . $name . "' size='9' class='input$tabela$tipoclass' style='text-align: right' onkeydown='if(((event.keyCode<45 || event.keyCode>57 || event.keyCode==47) && (event.keyCode<96 || event.keyCode>109)) && event.keyCode!=8 && event.keyCode!=9 && event.keyCode!=190 && event.keyCode!=110 && event.keyCode!=46 && event.keyCode!=127 && event.keyCode!=189){ event.keyCode=0; event.preventDefault(); return false;}'  onkeypress='$(this).css(\\\"background\\\",\\\"#ffc\\\")'/></div>";
                break;
            case 'real':
                $montagem .="<div id='cp$tabela$seqc$name' class='campos cp$tabela$name' type='$tipo' id='cp$tabela$seqc$name'>
                     <span class='campotitulo' id='tit$tabela$name'>$detalhes <help onclick='window.open(\\\"http://env-6461067.jelasticlw.com.br/wiki/index.php/Campo ".preg_replace("/[\d]+/",'',$tabela)." $detalhes\\\");'>?</help></span>
                         <input type='text' value='$odado' name='" . $name . "' size='9' class='input$tabela$tipoclass' style='text-align: right' onkeydown='if(((event.keyCode<45 || event.keyCode>57 || event.keyCode==47) && (event.keyCode<96 || event.keyCode>109)) && event.keyCode!=8 && event.keyCode!=9 && event.keyCode!=190 && event.keyCode!=110 && event.keyCode!=46 && event.keyCode!=127 && event.keyCode!=189){ event.keyCode=0; event.preventDefault(); return false;}'   onkeypress='$(this).css(\\\"background\\\",\\\"#ffc\\\")'/></div>";
                break;
            case 'tinyint':
                $montagem .="<div id='cp$tabela$seqc$name' class='campos cp$tabela$name checkbox$tipoclass' type='$tipo' id='cp$tabela$seqc$name'>
                     <span class='campotitulo' id='tit$tabela$name'>$detalhes <help onclick='window.open(\\\"http://env-6461067.jelasticlw.com.br/wiki/index.php/Campo ".preg_replace("/[\d]+/",'',$tabela)." $detalhes\\\");'>?</help></span>
                         <input type='checkbox' name='" . $name . "' value='1' class='checkboxinput'  onclick='$(this).css(\\\"background\\\",\\\"#ffc\\\")'>
                            <div class='assinatura'></div></div>";
                break;
        }
        $form[$tabela][$name] = $odado;
    }
    
    $montagem .= "<input type='hidden' name='no_chave' value='" . base64_encode($tabela) . "' class='input$tabela' />";
    
    $montagem .='</form></div>';

    return str_replace(array("	", "
", "\n", "\s\s", "  "), '', $montagem);
}
