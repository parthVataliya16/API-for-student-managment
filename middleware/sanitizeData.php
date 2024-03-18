<?php

function sanitizeData($dataArr)
{
    foreach ($dataArr as $key => $value) {
        if (gettype($value) == 'array') {
            $sanitizeData = [];
            foreach ($value as $k => $v) {
                $sanitizeData[$k] = trim(strip_tags($v));
            }
            $dataArr[$key] = $sanitizeData;
        } else {
            $dataArr[$key] = trim(strip_tags($value));
            if ($key == 'email') {
                if (! checkMail($value)) {
                    return false;
                }
            }
        }
    }
    return $dataArr;
}

?>