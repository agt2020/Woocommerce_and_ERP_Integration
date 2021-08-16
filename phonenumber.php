<?php
/**
 * Regular expression to validate different types of phone numbers
 */

// simple pattern
$pattern = '/^[0-9\-\(\)\/\+\s]*$/';
//$pattern = '/ ([+ \ \ s]) {1,3} ([0-9 \ \ s] {2,5}) -? ([0-9 \ \ s] {2,5}) -? ([0-9 \ \ s] {2,20}) / ';

// example phone numbers
$phoneNumbers = '
9121234567
09121234567
912 123 4567
912 1234 567
912-123-4567
912(123)4567
912 (123) 4567
+989121234567
09121234567
9121234567
09
02155491311
';

// convert the examples to an array
$phoneNumbers = explode("\n", trim($phoneNumbers));

// loop thru them and run preg_match for each number.
// the variable $matches should contain the number in case of a successful validation.
foreach ($phoneNumbers as $number) {
    preg_match($pattern, $number, $matches);
    echo '<pre>Number: ' . $number . "\n"
       . 'Match: ' . print_r($matches, true) . '</pre>';
}