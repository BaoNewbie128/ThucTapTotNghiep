<?php
    class Validator {
        // 1 Validate kiểu chuỗi
        public static function required($value){
            return isset($value) && trim($value) !== "";
        }
        public static function minLength($value, $min){
            return strlen($value) >= $min;
        }
        public static function maxLength($value,$max){
            return strlen($value) <= $max;
        }
        public static function regex($value, $pattern){
            return preg_match($pattern,$value);
        }
        public static function email($value){
            return filter_var($value, FILTER_VALIDATE_EMAIL);
        }
        public static function allwowedChars($value,$allowedChars){
            return preg_match('/^[a-zA-Z0-9_]+$/',$value);
        }
        // 2 Validate kiểu số 
        public static function isNumber($value){
            return is_numeric($value);
        }
        public static function isInteger($value){
            return filter_var($value,FILTER_VALIDATE_INT) !== false;
        }
        public static function isDecimal($value){
            return filter_var($value,FILTER_VALIDATE_FLOAT) !== false;
        }
        public static function min($value,$min){
            return $value >= $min;
        }
        public static function max($value,$max){
            return $value <= $max;
        }
        public static function isPositive($value){
            return $value > 0;
        }
        public static function isNegative($value){
            return $value < 0;
        }
        // 3 Validate kiểu ngày tháng
        public static function validDateFormat($date, $format = 'Y-m-d'){
            $d = \DateTime::createFromFormat($format, $date);
            return $d && $d->format($format) === $date;
        }
        public static function isValidDate($date){
            return strtotime($date) !== false;
        }
        public static function dateRange($date,$start,$end){
            $timestamp = strtotime($date);
            return $timestamp >= strtotime($start) && $timestamp <= strtotime($end);
        }
        public static function startBeforeEnd($start,$end){
            return strtotime($start) < strtotime($end); 
        }
        public static function isFutureDate($date){
            return strtotime($date) > time();
        }
        public static function isPastDate($date){
            return strtotime($date) < time();
        }
        // 4 Validate kiểu tuối 
        public static function validAge($age){
            return self::isInteger($age) && $age > 0;
        }
        public static function minAge($age,$min){
            return $age >= $min;
        }
        public static function maxAge($age,$max){
            return $age <= $max;
        }
    }
?>