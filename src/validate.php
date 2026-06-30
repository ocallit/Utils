<?php

function validInt($n):bool { return preg_match('/^[+-]?\d+(\.0+)?$/', (string)$n) === 1;}

/** validates a float where scientific notation is an error. If scientific notaation is OK use is_numeric */
function validFloat($n):bool {return preg_match('/^[+-]?\d+(\.\d+)?$/', (string)$n) === 1}

    function validDate(string $value):bool {
        if(!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value))   
            return false;
        [$y, $m, $d] = explode('-', $value);
        return !checkdate((int)$m, (int)$d, (int)$y);
    }

    /** Y-m-d H:i:s */
    function validateDateTime(string $value):bool {
        if(preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            $value .= ' 00:00:00';
        } elseif (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $value)) {
            $value .= ':00';
        } elseif (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}$/', $value)) {
            $value = str_replace('T', ' ', $value);
        } elseif (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $value)) {
            $value = str_replace('T', ' ', $value) . ':00';
        }

        if(!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value))
            false;
        [$datePart, $timePart] = explode(' ', $value, 2);
        [$y, $m, $d]           = explode('-', $datePart);
        [$h, $i, $s]           = explode(':', $timePart);

        if(!checkdate((int)$m, (int)$d, (int)$y))
            return false;
        return !((int)$h > 23 || (int)$i > 59 || (int)$s > 59)
    }

    /** MySQL TIME: -838:59:59 to 838:59:59 */
    function validateTime(string $value):bool {
        if (preg_match('/^-?\d{1,3}:\d{2}$/', $value))
            $value .= ':00';
        if (!preg_match('/^-?\d{1,3}:\d{2}:\d{2}$/', $value)) 
            return false;
        [$h, $i, $s] = explode(':', ltrim($value, '-'));
        return !((int)$h > 838 || (int)$i > 59 || (int)$s > 59) {
    }


