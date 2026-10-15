<?php

declare(strict_types=1);

final class CapivaraResearchUnserializeProbe
{
    public string $marker;

    public function __construct(string $marker)
    {
        $this->marker = $marker;
    }

    public function __wakeup(): void
    {
        file_put_contents($this->marker, "__wakeup executed\n");
    }
}

$projectRoot = $argv[1] ?? '.';
$autoload = rtrim($projectRoot, DIRECTORY_SEPARATOR) . '/vendor/autoload.php';

if (!is_file($autoload)) {
    fwrite(STDERR, "Composer autoloader not found at: {$autoload}\n");
    fwrite(STDERR, "Run this against a Composer-installed affected Pimcore checkout.\n");
    exit(1);
}

require $autoload;

$markerAllowed = sys_get_temp_dir() . '/pimcore-unserialize-allowed-' . getmypid();
$markerDenied = sys_get_temp_dir() . '/pimcore-unserialize-denied-' . getmypid();
@unlink($markerAllowed);
@unlink($markerDenied);

$allowedPayload = serialize(['probe' => new CapivaraResearchUnserializeProbe($markerAllowed)]);
$deniedPayload = serialize(['probe' => new CapivaraResearchUnserializeProbe($markerDenied)]);

$allowedResult = Pimcore\Tool\Serialize::unserialize($allowedPayload, true);
$allowedExecuted = is_file($markerAllowed);

$deniedResult = Pimcore\Tool\Serialize::unserialize($deniedPayload, false);
$deniedExecuted = is_file($markerDenied);
$deniedClass = is_array($deniedResult) && isset($deniedResult['probe'])
    ? get_class($deniedResult['probe'])
    : 'unexpected result';

printf("allowed_classes=true  magic method executed: %s\n", $allowedExecuted ? 'YES' : 'NO');
printf("allowed_classes=false magic method executed: %s\n", $deniedExecuted ? 'YES' : 'NO');
printf("allowed_classes=false reconstructed class: %s\n", $deniedClass);

@unlink($markerAllowed);
@unlink($markerDenied);

if (!$allowedExecuted || $deniedExecuted || $deniedClass !== '__PHP_Incomplete_Class') {
    fwrite(STDERR, "Unexpected result; inspect the installed PHP/Pimcore version.\n");
    exit(1);
}

echo "Controlled unrestricted-deserialization primitive confirmed.\n";
