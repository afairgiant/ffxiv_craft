<?php
require_once __DIR__."/../apiData.inc";
require_once __DIR__."/../ffxivData.inc";

$arguments = [
    'server'        => FILTER_SANITIZE_SPECIAL_CHARS,
    'item'          => FILTER_SANITIZE_SPECIAL_CHARS,
];

$data = filter_input_array(INPUT_GET, $arguments);
if (isset($data['server'])) {
    $server = $data['server'];
}
if (isset($data['item'])) {
    $item = $data['item'];
} else {
    $item = "rakshasa dogi of healing";
}
?>
<html>
<head>
<title>Demonstrating Final Fantasy XIV Crafting Companion</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="crafting.css">
<script src="crafting.js"></script>
</head>
<body>
<div style="text-align:center;">
<h2>Demonstrating Final Fantasy XIV Crafting Companion</h2>
<a href="masterbook.php">[Master Recipe Books]</a><br><br>
<select id='server'>
<?php
$dataset = new FfxivDataSet('..');
$dataset->loadWorld();
foreach ($dataset->world as $entry) {
    if (strcasecmp($entry['Name'], $server) == 0) {
        echo "<option selected>";
    } else {
        echo "<option>";
    }
    echo $entry['Name']."</option>";
}
?>
</select>
<input id='item' value='<?php echo $item; ?>'>
<input type='button' onclick='getData()' value='Get Data'>
<progress id='progress' style='display: none;'></progress>
</div>
<hr>
<div id="output">
</div>

<?php
if (isset($data['server']) && isset($data['item'])) {
    echo "<script> getData(); </script>";
}
?>

<div style="text-align:center;">
<span>&copy; 2019 Aric Stewart
&nbsp;&nbsp;&nbsp;&nbsp;
<a href="https://github.com/AricStewart/ffxiv_craft"><img src="GitHub-Mark-32px.png"> Check the project out on GitHub</a></span>
</div>

</body>
