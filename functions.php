<?
function setDatabase() {
	$conn = mysqli_connect(
		'localhost',
		'root',
		'',
		'teacher'
	);
	
	if ($conn->connect_error)
		dieWithError();
	return $conn;
}

function query($conn, $query, $doClose) {
	$result = $conn->query($query);
	if ($doClose)
		$conn->close();
	
	if (!is_object($result))
		return $result;
	
	$records = [];
	while($row = $result->fetch_assoc())
		$records[] = $row;
	
	return $records;
}

function checkItemsAsValues($array) {
	foreach (_items as $item) {
		if (in_array($item, $array))
			return true;
	}
	return false;
}

function dieWithError() {
	die('BackEnd Error!');
}