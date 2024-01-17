<?
define('_basename', basename(__FILE__, '.php'));
define('_items', [
	'spell',
	'meaning',
	'synonyms',
	'category',
	'kinds'
]);

require_once('backend/functions.php');
$conn = setDatabase();

$query = 'SELECT * FROM `constants`';
$records = query($conn, $query, true);

if (!is_array($records) || !count($records))
	dieWithError();
$constants = $records[0];
?>
<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="icon" href="images/favicon.svg" sizes="any" type="image/svg+xml">
    <title>Teacher | Home</title>
	<link rel="stylesheet" type="text/css" href="styles/main.css?ver=1.1.3">
	<link rel="stylesheet" type="text/css" href="styles/<?=_basename?>.css?ver=1.1.3">
	<script>
		const _items = <?=json_encode(_items)?>;
	</script>
	<script src="scripts/main.js?ver=1.1.3"></script>
	<script src="scripts/<?=_basename?>.js?ver=1.1.3"></script>
</head>
<body>
<div id="container">
	<div id="header">
		<span id="title" class="title">Test Specifications</span>
		<div class="content">
			<div id="infTotal" class="info">
				<svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 576 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M264.5 5.2c14.9-6.9 32.1-6.9 47 0l218.6 101c8.5 3.9 13.9 12.4 13.9 21.8s-5.4 17.9-13.9 21.8l-218.6 101c-14.9 6.9-32.1 6.9-47 0L45.9 149.8C37.4 145.8 32 137.3 32 128s5.4-17.9 13.9-21.8L264.5 5.2zM476.9 209.6l53.2 24.6c8.5 3.9 13.9 12.4 13.9 21.8s-5.4 17.9-13.9 21.8l-218.6 101c-14.9 6.9-32.1 6.9-47 0L45.9 277.8C37.4 273.8 32 265.3 32 256s5.4-17.9 13.9-21.8l53.2-24.6 152 70.2c23.4 10.8 50.4 10.8 73.8 0l152-70.2zm-152 198.2l152-70.2 53.2 24.6c8.5 3.9 13.9 12.4 13.9 21.8s-5.4 17.9-13.9 21.8l-218.6 101c-14.9 6.9-32.1 6.9-47 0L45.9 405.8C37.4 401.8 32 393.3 32 384s5.4-17.9 13.9-21.8l53.2-24.6 152 70.2c23.4 10.8 50.4 10.8 73.8 0z"/></svg>
				<span class="value"></span>
			</div>
			<div id="infTime" class="info">
				<svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 448 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M176 0c-17.7 0-32 14.3-32 32s14.3 32 32 32h16V98.4C92.3 113.8 16 200 16 304c0 114.9 93.1 208 208 208s208-93.1 208-208c0-41.8-12.3-80.7-33.5-113.2l24.1-24.1c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L355.7 143c-28.1-23-62.2-38.8-99.7-44.6V64h16c17.7 0 32-14.3 32-32s-14.3-32-32-32H224 176zm72 192V320c0 13.3-10.7 24-24 24s-24-10.7-24-24V192c0-13.3 10.7-24 24-24s24 10.7 24 24z"/></svg>
				<span class="value"></span>
			</div>
		</div>
	</div>
	<div id="main" data-filename="<?=_basename?>">
		<div class="part specifier">
			<span class="title">Test Items</span>
			<div class="content">
				<?
				foreach (_items as $item) {
				?>
					<label class="input checkBox <?=$constants['has'.ucfirst($item)]?'checked':''?>" id="spcItem<?=ucfirst($item)?>" data-scale="<?=$constants['scale'.ucfirst($item)]?>">
						<span class="hint"><?=ucfirst($item)?></span>
					</label>
				<?
				}
				?>
			</div>
		</div>
		<span class="delimiter"></span>
		<div class="part specifier">
			<span class="title">Number of Words</span>
			<div class="content">
				<label class="input">
					<input type="number" id="spcTotal" name="spcTotal" min="<?=$constants['totalMin']?>" max="<?=$constants['totalMax']?>" step="1" value="<?=$constants['totalLast']?>">
				</label>
			</div>
		</div>
		<span class="delimiter"></span>
		<div class="part specifier">
			<span class="title">Seconds per Word</span>
			<div class="content">
				<label class="input">
					<input type="number" id="spcSeconds" name="spcSeconds" min="<?=$constants['secondsMin']?>" value="<?=$constants['secondsLast']?>">
				</label>
			</div>
		</div>
		<span class="delimiter"></span>
		<div class="part buttonBox">
			<div class="content">
				<a href="statistics.php" class="button" id="btnStat">Statistics</a>
				<a href="test.php" class="button" id="btnStart">Start</a>
			</div>
		</div>
	</div>
</div>
<div id="message" class="hidden">
	<div class="body">
		<span class="text"></span>
		<div class="buttonBox">
			<div class="content">
				<a class="button" id="btnNo">No</a>
				<a class="button" id="btnYes">Yes</a>
			</div>
		</div>
	</div>
</div>
</body>
</html>