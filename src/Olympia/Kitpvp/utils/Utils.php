<?php

namespace Olympia\Kitpvp\utils;

final class Utils
{
    public static function durationToString(?int $t): ?string
    {
        if(is_null($t)) return null;

        if ($t === 0) return "0 seconde";

        $s = $t % 60;
        $t = ($t - $s) / 60;
        $m = $t % 60;
        $h = ($t - $m) / 60;

        $string = "";

        if ($h > 0) {
            $hour = ($h > 1) ? "$h heures" : "$h heure";
            $string .= trim($hour) . " ";
        }

        if ($m > 0) {
            $minute = ($m > 1) ? "$m minutes" : "$m minute";
            $string .= trim($minute) . " ";
        }

        if ($s > 0) {
            $second = ($s > 1) ? "$s secondes" : "$s seconde";
            $string .= trim($second) . " ";
        }

        return trim($string);
    }

    public static function durationToShortString(?int $t): ?string
    {
        if(is_null($t)) return null;

        if ($t === 0) return "0s";

        $s = $t % 60;
        $t = ($t - $s) / 60;
        $m = $t % 60;
        $h = ($t - $m) / 60;

        $string = "";

        if ($h > 0) {
            $hour = $h . "h";
            $string .= trim($hour) . " ";
        }

        if ($m > 0) {
            $minute = $m . "m";
            $string .= trim($minute) . " ";
        }

        if ($s > 0) {
            $second = $s . "s";
            $string .= trim($second) . " ";
        }

        return trim($string);
    }

    public static function numberToRomanRepresentation(int $number): string
    {
        $map = array('M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1);
        $returnValue = '';

        while ($number > 0) {
            foreach ($map as $roman => $int) {
                if($number >= $int) {
                    $number -= $int;
                    $returnValue .= $roman;
                    break;
                }
            }
        }

        return $returnValue;
    }

    public static function numberToOrdinal($number, bool $capitalizeFirstWord = false): string
    {
        $ordinals = array(
            1 => 'premier', 2 => 'deuxième', 3 => 'troisième', 4 => 'quatrième', 5 => 'cinquième',
            6 => 'sixième', 7 => 'septième', 8 => 'huitième', 9 => 'neuvième', 10 => 'dixième',
            11 => 'onzième', 12 => 'douzième', 13 => 'treizième', 14 => 'quatorzième', 15 => 'quinzième',
            16 => 'seizième', 17 => 'dix-septième', 18 => 'dix-huitième', 19 => 'dix-neuvième', 20 => 'vingtième'
        );

        $tens = array(
            30 => 'trentième', 40 => 'quarantième', 50 => 'cinquantième', 60 => 'soixantième',
            70 => 'soixante-dixième', 80 => 'quatre-vingtième', 90 => 'quatre-vingt-dixième'
        );

        if ($number >= 1 && $number <= 20) {
            $ordinal = $ordinals[$number];
        } elseif ($number > 20 && $number <= 1000) {
            if ($number < 100) {
                $tensDigit = floor($number / 10) * 10;
                $unitsDigit = $number % 10;
                if ($tensDigit > 20) {
                    $ordinal = $tens[$tensDigit];
                    if ($unitsDigit > 0) {
                        $ordinal .= '-' . $ordinals[$unitsDigit];
                    }
                } else {
                    $ordinal = $ordinals[$tensDigit] . '-' . $ordinals[$unitsDigit];
                }
            } elseif ($number == 1000) {
                $ordinal = 'millième';
            } else {
                $hundredsDigit = floor($number / 100);
                $remainder = $number % 100;
                $ordinal = $ordinals[$hundredsDigit] . '-centième';
                if ($remainder > 0) {
                    $ordinal .= '-' . self::numberToOrdinal($remainder, $capitalizeFirstWord);
                }
            }
        } else {
            $ordinal = "???";
        }

        if ($capitalizeFirstWord && $ordinal !== "???") {
            $ordinal = ucfirst($ordinal);
        }

        return $ordinal;
    }
}