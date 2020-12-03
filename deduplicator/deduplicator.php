<?php
class Deduplicator
{
    function clear_text($text)
    {
        $backup = "";
        $repe_text = str_replace("","",$text);
        $repe_text = str_replace(" ","",$text);
        $repe_text = str_replace('"',"",$repe_text);
        $repe_text = str_replace(',',"",$repe_text);
        $repe_text = str_replace('<',"/",$repe_text);
        $repe_text = str_replace('>',"",$repe_text);
        $backup = $repe_text;
        $result = array();
        if (strstr($backup, '/')){
            $pieces = explode("/", $backup);   
            $repe_text = $pieces[1];
            array_push($result,$repe_text);
            array_push($result,$pieces);
            array_push($result,"/");
            return $result;
        } else {
            $repe_text = $backup;
            array_push($result,$repe_text);
            array_push($result,array());
            array_push($result,"");
            return $result;
        }
    }

    public function deduplicate()
    {
        $nombre_archivo = "renting_limpio";
        $file_empresas = "renting_sucio.csv";
        $delimiter = ";";
        $col = 5;

        if(!file_exists($file_empresas) || !is_readable($file_empresas))
            return FALSE;

        $empresas = array();

        if (($handle = fopen($file_empresas, 'r')) !== FALSE)
        {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE)
            {
                array_push($empresas, $row);
            }
            
            fclose($handle);
        }

        $validos = array();
        $count_ = count($empresas);
        for ($i=0; $i < $count_; $i++) { 
            $flag = false;
            if (trim($empresas[$i][$col])!="") {
                $first_result = $this->clear_text($empresas[$i][$col]);
                if(count($first_result)){
                    $first_possible = $first_result[0];
                    $first_data = $first_result[1];
                    $determinator = $first_result[2];
                    switch ($determinator) {
                        
                        case '/':
                            if(count($first_data)){
                                $flag = true;
                                for ($x=0; $x < count($first_data); $x++) {   
                                    if(strstr($first_data[$x], '@')){
                                        if($x == 0)
                                        {
                                            $flag =false;
                                            $empresas[$i][$col] = $first_data[$x];
                                            array_push($validos, $empresas[$i][$col]);
                                        } else if($x > 0) {
                                            if($flag == false){
                                                $new = $empresas[$i];
                                                $new[$col] = $first_data[$x];
                                                array_push($validos, $new[$col]);
                                                array_push($empresas, $new);
                                                $count_ = count($empresas);
                                            }
                                            else if($flag == true){
                                                $flag =false;
                                                $empresas[$i][$col] = $first_data[$x];
                                                array_push($validos, $empresas[$i][$col]);
                                            }
                                        }
                                    }
                                }

                            }
                            break;
                            
                        default:
                            $empresas[$i][$col] = $first_possible;
                            array_push($validos, $empresas[$i][$col]);
                            break;
                    }
                }
            }
        }

        $validos = array_unique($validos);
        print_r($empresas);
        print_r($validos);
        $empresas_validas = array();
        for ($i_e=0; $i_e < count($empresas); $i_e++) { 
            $element = $empresas[$i_e];
            
            if (trim($element[$col])!="" && count($empresas_validas) > 0) {
                $fl = 0;
                for($ii=0;$ii<count($empresas_validas);$ii++) { 
                    $repe = $empresas_validas[$ii];
                    if($element[$col] == $repe[$col] && $fl == 0){
                        $fl++;
                    }
                }
                if($fl == 0) {
                    $flag = 0;
                    foreach($validos as $element_validos) { 
                        if($element[$col] == $element_validos && $flag == 0){
                            $flag = 1;
                            array_push($empresas_validas, $element);
                        }
                    }
                }
                
            } else if (trim($element[$col])!="" && count($empresas_validas) == 0){
                $flag = 0;
                foreach($validos as $element_validos) { 
                    if($element[$col] == $element_validos && $flag == 0){
                        $flag = 1;
                        array_push($empresas_validas, $element);
                    }
                }
            }
        }

        if(count($empresas_validas)){
            $fp = fopen($nombre_archivo.'.csv', 'w');

            foreach ($empresas_validas as $fields) {
                for ($i=0; $i < count($fields); $i++) { 
                    $fields[$i] =  str_replace("","",$fields[$i] );
                    $fields[$i] = str_replace(" ","",$fields[$i] );
                    $fields[$i] = str_replace('"',"",$fields[$i] );
                    $fields[$i] = str_replace(',',"",$fields[$i] );
                }
                if(strstr($fields[$col], '@')){
                    fputcsv($fp, $fields, $delimiter);
                }
            }

            fclose($fp);
        }
    }
}

$obj = new Deduplicator();
echo $obj->deduplicate();
?>
