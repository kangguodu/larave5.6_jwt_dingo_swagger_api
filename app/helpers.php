<?php

if(!function_exists('json_data')){
    function json_data($json_string){
        if(empty($json_string)){
            return '';
        }
        if(is_array($json_string)){
            //取出数组中,null 为空字符串
            array_walk_recursive($json_string, function (&$item, $key) {
                $item = null === $item ? '' : $item;
            });
            return json_encode($json_string);
        }else if(is_string($json_string)){
            $encode_string = json_decode(trim($json_string));
//        var_dump($encode_string);
            if($encode_string === FALSE || $encode_string == NULL){
                return '';
            }
            return $json_string;
        }

    }
}

if(!function_exists('spec_value_string')){
    function array_to_json_data($data){
        if(is_array($data)){
            return json_encode($data);
        }else if(is_string($data)){
            return $data;
        }
    }
}

if(!function_exists('array_remove_value_by_keys')){
    function array_remove_value_by_keys($keys,$array){

        if(count($keys) > 0 && count($array) > 0){
            foreach($keys as $key){
                if(array_key_exists($key,$array)){
                    unset($array[$key]);
                }
            }
        }

        return $array;
    }
}

if(!function_exists('json_decode_data')){
    function json_decode_data($data,$default = array()){
        if(!empty($data)){
            if(json_decode($data) !== false){
                return json_decode($data);
            }else{
                return $data;
            }
        }else{
            return $default;
        }
    }
}
