<?php

namespace Graviton\Rql;

class Lexer extends \Doctrine\Common\Lexer
{
    const T_NONE              = 1;
    const T_INTEGER           = 2;
    const T_STRING            = 3;

    const T_CLOSE_PARENTHESIS = 6;
    const T_OPEN_PARENTHESIS  = 7;
    const T_COMMA             = 8;


    const T_EQ    = 100;
    const T_NE    = 101;
    const T_AND   = 102;
    const T_OR    = 103;
    const T_LT    = 104;
    const T_GT    = 105;
    const T_LTE   = 106;
    const T_GTE   = 107;
    const T_SORT  = 108;
    const T_PLUS  = 109;
    const T_MINUS = 110;

    protected function getCatchablePatterns()
    {
        return array(
            '\(',
            '\)',
            '[\w\s]+',
        );
    }

    protected function getOperators()
    {
        return array(
            'eq',
            'ne',
            'and',
            'or',
            'lt',
            'gt',
            'lte',
            'gte',
            'sort',
            '+',
            '-',
        );
    }

    protected function getNonCatchablePatterns()
    {
        return array();
    }

    protected function getType(&$value)
    {
        $type = self::T_NONE;

        if (is_numeric($value)) {
            $type = self::T_INTEGER;
            if (strpos($value, '.') !== false || stripos($value, 'e') !== false) {
                $type = self::T_FLOAT;
            }
        } elseif (in_array($value, $this->getOperators())) {
            $origValue = $value;
            $constName = sprintf('self::T_%s', strtoupper($value));
            if ($value == '+') {
                $constName = 'self::T_PLUS';
            } elseif ($value == '-') {
                $constName = 'self::T_MINUS';
            }
            if (defined($constName)) {
                $type = constant($constName);
            }
        } else {
            switch ($value) {
                case ',':
                    $type = self::T_COMMA;
                    break;
                case '(':
                    $type = self::T_OPEN_PARENTHESIS;
                    break;
                case ')':
                    $type = self::T_CLOSE_PARENTHESIS;
                    break;
                default:
                    $type = self::T_STRING;
            }
        }

        return $type;
    }
}
