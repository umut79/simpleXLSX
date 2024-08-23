<?php



?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title>xlsx2sql insert v.1.1</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/css/bootstrap-reboot.min.css" integrity="sha512-Hvxqga90bvpEid1McCftiBMcfB8cPXI+TZR3GVf4KUdLu3NPx6/gPXSQTdY7AHaLLJDSJym6kOotc713b2D7gQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/css/bootstrap.min.css" integrity="sha512-rt/SrQ4UNIaGfDyEXZtNcyWvQeOq0QLygHluFQcSjaGB04IxWhal71tKuzP6K8eYXYB6vJV4pHkXcmFGGQ1/0w==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/js/bootstrap.min.js" integrity="sha512-7rusk8kGPFynZWu26OKbTeI+QPoYchtxsmPeBqkHIEXJxeun4yJ4ISYe7C6sz9wdxeE1Gk3VxsIWgCZTc+vX3g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/js/bootstrap.bundle.min.js" integrity="sha512-igl8WEUuas9k5dtnhKqyyld6TzzRjvMqLC79jkgT3z02FvJyHAuUtyemm/P/jYSne1xwFI06ezQxEwweaiV7VA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

</head>
<body>
<div class="container">
	<div>
		<h4>Dosya yüklemeden Excel dosyasını oku ve SQL tablosuna aktar</h4>
		<p class="text-muted">
		+ Ajax submit <br>
		+ Tablo otomatik oluşturma<br>
		+ Büyük tablo aktarma (Max: 20 MB)
		</p>
	</div>
	<form enctype="multipart/form-data" id="fform" method="post" data-send2="up4.2.app.php" data-restype="html" class="container"  style="width:500px">
		<div class="row mb-1">
			<div class="col-4">
				<label for="db">Veri tabanı</label>
			</div>
			<div class="col-8">
				<input name="db" type="text" id="db"  class="form-control">
			</div>
		</div>
		<div class="row mb-1">
			<div class="col-4">
				<label for="xlsx">Dosya</label>
			</div>
			<div class="col-8">
				<input name="file" type="file" id="xlsx" class="form-control" />
			</div>
		</div>
		<div class="row mb-1">
			<div class="col-12 text-right">
				<button name="sub" id="submitBtn" type="submit" class="btn btn-success">Başlat</button>
			</div>
		</div>
	</form>

	<div class="row mb-1">
		<div class="col-12">
			<span id="fsize"></span>
		</div>
	</div>

	<div class="font-monospace" id="statusMsg"></div>

</div>

<script>
function formatBytes(bytes, seperator = "") {
	const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB']
	  if (bytes == 0) return 'n/a'
	  const i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)), 10)
	  if (i === 0) return `${bytes}${seperator}${sizes[i]}`
	  return `${(bytes / (1024 ** i)).toFixed(1)}${seperator}${sizes[i]}`
}
// ---
const doc_input = document.getElementById('xlsx')

doc_input.addEventListener('change', (event) => {
    const target = event.target;
    const maxmb = 101;
    if (target.files && target.files[0]) {
        const maxAllowedSize = maxmb * 1024 * 1024;
        if (target.files[0].size > maxAllowedSize) {
            alert('Dosya boyutu en çok '+ maxmb + ' MB olabilir!');
            target.value = ''
        } else {
			document.getElementById("fsize").innerHTML = 'Dosya: <b>'+ target.files[0].name +'</b><br>Boyut: <b>' +formatBytes(target.files[0].size, 1)+'</b>';
		}
    }
})

$(document).ready(function(e){
	var frm = $('#fform');
	var smit = $('#submitBtn');
	var resp = $("#statusMsg");
	var xls = $('#xlsx');
	var dbn = $('#db');
	// Validate form
	$(frm).on('change', function(){
		if(!$(dbn).val()) {
			dbn.addClass("is-invalid");
			smit.attr("disabled","disabled");
		} else if(!$(xls).val()) {
			xls.addClass("is-invalid");
			smit.attr("disabled","disabled");
		} else {
			dbn.removeClass("is-invalid");
			xls.removeClass("is-invalid");
			smit.removeAttr("disabled");
		}
		
	});
    // Submit form data via Ajax
    $(frm).on('submit', function(e){
        e.preventDefault();
		var sendTo = $(this).data("send2");
		var dType = $(this).data("resptype");
		if($(dbn).val() && $(xls).val()) {
			$.ajax({
				type: 'POST',
				url: sendTo, // 'up4.2.app.php'
				data: new FormData(this),
				dataType: dType, // json / html
				contentType: false,
				cache: false,
				processData:false,
				beforeSend: function(){
					smit.text("Bekleyiniz...");
					smit.attr("disabled","disabled");
					frm.css("opacity",".3");
					resp.html('İşleniyor... Bekleyiniz...');
				},
				success: function(response){
					resp.html('');
					if(response){ // html response
						$(frm).find("input[type=file]").val('');
						resp.html(response);
					}
					frm.css("opacity","1");
					smit.text("Başlat").removeAttr("disabled");
				}
			});
		}
    });
});
</script>

</body>
</html>
