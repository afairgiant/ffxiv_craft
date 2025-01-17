#!/usr/bin/php
<?php
/**
 * Crafting data engine
 *
 * PHP version 5
 *
 * @category  Final_Fantasy_XIV
 * @package   FfxivDataSet
 * @author    Aric Stewart <aricstewart@google.com>
 * @copyright 2019 Aric Stewart
 * @license   Apache License, Version 2.0
 * @link      <none>
 **/

/*.
    require_module 'standard';
.*/

require_once __DIR__."/ffxivData.inc";
require_once __DIR__."/universalis.inc";
require_once __DIR__."/craft.inc";
require __DIR__.'/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::create(__DIR__);
$dotenv->load();

$xiv = new Universalis($_ENV['server']);
$dataset = new FfxivDataSet();
$fullHistory = true;


function _sortByProfit(array $a, array $b): int
{
    $p = max($a['Profit']['Market_HQ'], $a['Profit']['Market_LQ']);
    $wc1 = $a['Week']['HQ']['Count'] + $a['Week']['LQ']['Count'];
    $p2 = max($b['Profit']['Market_HQ'], $b['Profit']['Market_LQ']);
    $wc2 = $b['Week']['HQ']['Count'] + $b['Week']['LQ']['Count'];
    return floor((1000 * $p * $wc1) - (1000 * $p2 * $wc2));

}


$i = 0;


function tick($stage, $data = null)
{
    global $i;
    if ($stage != 'progress') {
        return;
    }
    $i++;
    if ($i % 10 == 0) {
        fwrite(STDERR, '|');
    } elseif ($i % 5 == 0) {
        fwrite(STDERR, '-');
    } else {
        fwrite(STDERR, '.');
    }

}


$priceList = array();
if (file_exists(__DIR__."/item.cost.csv")) {
    $handle = fopen(__DIR__."/item.cost.csv", "r");
    while (($data = fgetcsv($handle, 2000, ",")) !== false) {
        $priceList[$data[0]] = $data[1];
    }
}

$i = 1;
if (count($argv) > 1) {
    if ($argv[1] == '-c') {
        array_map('unlink', glob("data/*.json"));
        $itemID = $argv[2];
        $i = 3;
    } elseif ($argv[1] == '-m') {
        $a = $dataset->getMastercraft($argv[2]);
        fwrite(STDERR,count($a).PHP_EOL);
        if (count($a) > 0) {
            $output = [];
            foreach ($a as $key => $i) {
                fwrite(STDERR, " $i (".($key + 1)."|".count($a).") ");
                $output[] = doRecipie($i, $dataset, $xiv, 'tick', $priceList);
            }
            usort($output, '_sortByProfit');
            foreach ($output as $recipe) {
                printRecipe($recipe, true);
            }
        }
        exit();
    } elseif ($argv[1] == '-t') {
        $crafter = $argv[2];
        $tier = intval($argv[3]);
        if ($argc == 5) {
            $tiertop = intval($argv[4]);
        } else {
            $tiertop = intval($argv[3]);
        }
        if (strtolower($crafter) == 'all') {
            $crafter = null;
        }
        $set = $dataset->getRecipeSet($crafter, ($tier - 1) * 5, $tiertop * 5);
        foreach ($set as $i => $r) {
            fwrite(STDERR, " ".$r." (".($i + 1)."|".count($set).") ");
            $output[] = doRecipie($r, $dataset, $xiv, 'tick', $crafter, $priceList);
        }
        usort($output, '_sortByProfit');
        printRecipeSummaryTitle();
        foreach ($output as $recipe) {
            printRecipeSummary($recipe);
        }
        exit();
    } elseif ($argv[1] == '-x') {
        $company = $dataset->loadCompanyCrafting();
        $recipe = getCompanyRecipe($argv[2], $dataset, 'tick');
        $output = doCompanyRecipe($recipe, $dataset, $xiv, 'tick');
        printCompanyRecipe($output);
        exit();
    } else {
        $itemID = $argv[1];
        $i = 2;
    }
} else {
    $itemID = 23815;
}

$crafter = 'Armorcraft';
if (count($argv) > $i) {
    $crafter = $argv[$i];
}

if (!is_numeric($itemID)) {
    $result = $dataset->getItem($itemID);
    if ($result === null) {
        fwrite(STDERR, 'Could not find item \''.$itemID.PHP_EOL);
        exit();
    }
    $itemID = $result->Index;
}

$data = getRecipe($itemID, $dataset, $crafter, 'tick');
$output = doRecipieFromRecipe($data, $dataset, $xiv, 'tick', $priceList);
printRecipe($output);
