<?php
class Validator
{
    function clear_text($text)
    {
        $target = str_replace("","",$text);
        $target = str_replace(" ","",$text);
        $target = str_replace('"',"",$target);
        $target = str_replace(',',"",$target);
        return $target;
    }

    public function validate()
    {
        $result_filename = "cll_result";
        $original_file = "original_cll.csv";
        $merge_file = "validos_cll.csv";
        $delimiter = ";";
        $original_col = 9;
        $merge_col = 0;
        $unique_on_original = true;
        $unique_on_merge = true;
        $email_validation = true;

        if(!file_exists($original_file) || !is_readable($original_file))
            return FALSE;

        $original_array = array();

        if (($handle = fopen($original_file, 'r')) !== FALSE)
        {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE)
            {
                if($row[$original_col]!=""){
                    array_push($original_array, $row);
                }
            }
            fclose($handle);
        }
        
        if(!file_exists($merge_file) || !is_readable($merge_file))
            return FALSE;

        $valid = array();

        if (($handle = fopen($merge_file, 'r')) !== FALSE)
        {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE)
            {
                array_push($valid, $row);
            }
            
            fclose($handle);
        }

        $unique_valid = array();
        for ($vi=0; $vi < count($valid); $vi++) { 
            $valid[$vi][$merge_col] = $this->clear_text($valid[$vi][$merge_col]);
            if($email_validation == true){
                if(strstr($valid[$vi][$merge_col], '@')){
                    array_push($unique_valid, $valid[$vi][$merge_col]);
                }
            } else {
                array_push($unique_valid, $valid[$vi][$merge_col]);
            }
        }
        if($unique_on_merge == true){
            $valid = array_unique($unique_valid);
        } else {
            $valid = $unique_valid;
        }

        $valid_result = array();
        $invalid_result = array();

        for ($i_e=0; $i_e < count($original_array); $i_e++) { 
            $element = $original_array[$i_e];
            $original_array[$i_e][$original_col] = $this->clear_text($original_array[$i_e][$original_col]);
            
            if (trim($element[$original_col])!="" && count($valid_result) > 0) {
                $fl = 0;
                for($ii=0;$ii<count($valid_result);$ii++) { 
                    $repe = $valid_result[$ii];
                    if($element[$original_col] == $repe[$original_col] && $fl == 0){
                        $fl++;
                        break;
                    }
                }
                $flag = 0;
                if($fl == 0) {
                    foreach($valid as $element_valid) { 
                        if($element[$original_col] == $element_valid && $flag == 0){
                            $flag = 1;
                            array_push($valid_result, $element);
                        } else if($element[$original_col] != $element_valid || $flag > 0){
                            $fli = 0;
                            if(count($invalid_result)>0){
                                /*for($ii=0;$ii<count($invalid_result);$ii++) { 
                                $inrepe = $invalid_result[$ii];
                                if($element[$original_col] == $inrepe[$original_col] && $fli == 0){
                                    $fli++;
                                }valid_result
                                }*/
                                if(!in_array($element[$original_col],$invalid_result)){
                                    array_push($invalid_result, $element);
                                }
                                if($fli == 0) {
                                 
                                }
                            } else {
                                array_push($invalid_result, $element);
                            }
                        }
                    }
                }
                
            } else if (trim($element[$original_col])!="" && count($valid_result) == 0){
                $flag = 0;
                foreach($valid as $element_valid) { 
                    if($element[$original_col] == $element_valid && $flag == 0){
                        $flag = 1;
                        array_push($valid_result, $element);
                    } else if($element[$original_col] != $element_valid || $flag > 0){
                        
                        $fli = 0;
                        if(count($invalid_result)>0){
                            /*for($ii=0;$ii<count($invalid_result);$ii++) { 
                                $inrepe = $invalid_result[$ii];
                                if($element[$orvalid_resultiginal_col] == $inrepe[$original_col] && $fli == 0){
                                    $fli++;
                                }
                            }*/
                            if(!in_array($element[$original_col],$invalid_result)){
                                array_push($invalid_result, $element);
                            }
                            if($fli == 0) {
                                
                            }
                        } else {
                            array_push($invalid_result, $element);
                        }
                    }
                }
            }
        }

        if(count($valid_result)){
            $fp = fopen($result_filename.'_valid.csv', 'w');

            foreach ($valid_result as $fields) {
                for ($i=0; $i < count($fields); $i++) { 
                    $fields[$i] = str_replace("","",$fields[$i] );
                    $fields[$i] = str_replace(" ","",$fields[$i] );
                    $fields[$i] = str_replace('"',"",$fields[$i] );
                    $fields[$i] = str_replace(',',"",$fields[$i] );
                    $fields[$i] = str_replace(',',"",$fields[$i] );
                }
                if(strstr($fields[$original_col], '@')){
                    fputcsv($fp, $fields, $delimiter);
                }
            }

            fclose($fp);
        }

        if(count($invalid_result)){
            $fp = fopen($result_filename.'_invalid.csv', 'w');

            foreach ($invalid_result as $fields) {
                for ($i=0; $i < count($fields); $i++) { 
                    $fields[$i] = str_replace("","",$fields[$i] );
                    $fields[$i] = str_replace(" ","",$fields[$i] );
                    $fields[$i] = str_replace('"',"",$fields[$i] );
                    $fields[$i] = str_replace(',',"",$fields[$i] );
                    $fields[$i] = str_replace(',',"",$fields[$i] );
                }
                fputcsv($fp, $fields, $delimiter);
            }

            fclose($fp);
        }
    }
}

$obj = new Validator();
echo $obj->validate();
?>