<?php

use Tester\Assert;

require_once __DIR__ . '/../vendor/autoload.php';

$parser = new \om\RinexNavigationParser();

$data = $parser->parseFile(__DIR__ . '/data/LongNumberExample.obs');

Assert::same('6', $data[0][0]['PRN']);
Assert::same('-.165982783074E-10', $data[0][0]['SV_clock_drift']);
Assert::same('.000000000000E+00', $data[0][0]['SV_clock_drift_rate']);

Assert::same(4, count($data));