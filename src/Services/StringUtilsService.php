<?php

namespace LA87\AIPromptBuilder\Services;

class StringUtilsService {
    public function reverseString($string) {
        return strrev($string);
    }

    public function toUpperCase($string) {
        return strtoupper($string);
    }
}
