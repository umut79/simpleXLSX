<?php
require_once __DIR__."/src/SimpleXLSXGen.php";
// ornek 1- basic
$books = [
    ['ISBN', 'title', 'author', 'publisher', 'ctry' ],
    [618260307, 'The Hobbit', 'J. R. R. Tolkien', 'Houghton Mifflin', 'USA'],
    [908606664, 'Slinky Malinki', 'Lynley Dodd', 'Mallinson Rendel', 'NZ']
];
$xlsx = SimpleXLSXGen::fromArray( $books );
# $xlsx->downloadAs('books.xlsx'); // or
#$xlsx->saveAs('books.xlsx'); // or
#$xlsx->$xlsx_content = (string) $xlsx

// ornek 2 - data tipleri
$data = [
    ['Integer', 123],
    ['Float', 12.35],
    ['Percent', '12%'],
    ['Datetime', '2020-05-20 02:38:00'],
    ['Date','2020-05-20'],
    ['Time','02:38:00'],
    ['Datetime PHP', new DateTime('2021-02-06 21:07:00')],
    ['String', 'Long UTF-8 String in autoresized column'],
    ['Hyperlink', 'https://github.com/shuchkin/simplexlsxgen'],
    ['Hyperlink + Anchor', '<a href="https://github.com/shuchkin/simplexlsxgen">SimpleXLSXGen</a>'],
    ['RAW string', "\0".'2020-10-04 16:02:00']
];
$xlsxName = date("Y_m_d_his")."_".rand(0,9999).".xlsx";
SimpleXLSXGen::fromArray( $data )->downloadAs($xlsxName); //saveAs('datatypes.xlsx');

// ornek 3 - bicimlendirme
$data = [
    ['Normal', '12345.67'],
    ['Bold', '<b>12345.67</b>'],
    ['Italic', '<i>12345.67</i>'],
    ['Underline', '<u>12345.67</u>'],
    ['Strike', '<s>12345.67</s>'],
    ['Bold + Italic', '<b><i>12345.67</i></b>'],
    ['Hyperlink', 'https://github.com/shuchkin/simplexlsxgen'],
    ['Italic + Hyperlink + Anchor', '<i><a href="https://github.com/shuchkin/simplexlsxgen">SimpleXLSXGen</a></i>'],
    ['Left', '<left>12345.67</left>'],
    ['Center', '<center>12345.67</center>'],
    ['Right', '<right>Right Text</right>'],
    ['Center + Bold', '<center><b>Name</b></center>']
];
/*
SimpleXLSXGen::fromArray( $data )
    ->setDefaultFont( 'Courier New' )
    ->setDefaultFontSize( 14 )
    ->saveAs('styles_and_tags.xlsx');
*/
?>
