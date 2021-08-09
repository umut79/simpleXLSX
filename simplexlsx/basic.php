<?php
require_once "src/SimpleXLSX.php";

$xfile = 'xlsx/ornek-1.xlsx';

// ornek 1 - basic
echo "<h3>Örnek 1 - Basic</h3>";
if ( $xlsx = SimpleXLSX::parse($xfile) ) {
	echo "<pre style='max-height:300px; overflow:auto;'>";
	print_r( $xlsx->rows() );
	echo "<p><b>Aralık oku:</b>1->10</p>";
	print_r( array_slice( $xlsx->rows(), 1, 10) );
	echo "</pre>";
} else {
	echo SimpleXLSX::parseError();
}

echo "<hr>";

// ornek - 2 toHTML
echo "<h3>Örnek 2 - toHTML</h3>";
echo SimpleXLSX::parse($xfile)->toHTML();

echo "<hr>";
echo "<h3>Örnek 2.1 - toHTML-2</h3>";
if ( $xlsx = SimpleXLSX::parse($xfile) ) {
	echo '<table border="1" cellpadding="3" style="border-collapse: collapse">';
	foreach( $xlsx->rows() as $r ) {
		echo '<tr><td>'.implode('</td><td>', $r ).'</td></tr>';
	}
	echo '</table>';
	// aralık belirt
	
	echo "<p><b>Belirli aralıklar: 1->END</b></p>";
	$newRows = array_slice( $xlsx->rows(), 1, count($xlsx->rows()));
	echo '<table border="1" cellpadding="3" style="border-collapse: collapse">';
	foreach( $newRows as $r ) {
		echo '<tr><td>'.implode('</td><td>', $r ).'</td></tr>';
	}
	echo '</table>';
	
	
	
} else {
	echo SimpleXLSX::parseError();
}


echo "<hr>";
echo "<h3>Gets extend cell info by ->rowsEx()</h3>";
echo "<pre style='max-height:300px; overflow:auto;'>";
print_r( SimpleXLSX::parse($xfile)->rowsEx() );
echo "</pre>";


echo "<hr>";
echo "<h3>Rows with header values as keys</h3>";
echo "<pre style='max-height:300px; overflow:auto;'>";
if ( $xlsx = SimpleXLSX::parse($xfile)) {
	// Produce array keys from the array values of 1st array element
	$header_values = $rows = [];
	foreach ( $xlsx->rows() as $k => $r ) {
		if ( $k === 0 ) {
			$header_values = $r;
			continue;
		}
		$rows[] = array_combine( $header_values, $r );
	}
	print_r( $rows );
}
echo "</pre>";


echo "<hr>";
echo "<h3>XLSX read cells, out commas and bold headers</h3>";
echo '<pre>';
if ( $xlsx = SimpleXLSX::parse( $xfile ) ) {
	foreach ( $xlsx->rows() as $r => $row ) {
		foreach ( $row as $c => $cell ) {
			echo ($c > 0) ? ', ' : '';
			echo ( $r === 0 ) ? '<b>'.$cell.'</b>' : $cell;
		}
		echo '<br/>';
	}
} else {
	echo SimpleXLSX::parseError();
}
echo '</pre>';
?>