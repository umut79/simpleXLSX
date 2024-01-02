<?php



?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title>xlsx2sql insert v.1.1</title>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<body>
<p>Dosya yüklemeye gerek olmadan excel dosyasını okuma ve SQL tablosuna aktarma.</p>
<p>
+ Ajax submit <br>
+ Tablo otomatik oluşturma<br>
+ Büyük tablo aktarma
</p>
<form enctype="multipart/form-data" id="fform" method="post" data-sendto="up4.2.app.php" data-returntype="html">
<input name="file" type="file" id="xlsx" />
<button name="sub" id="submitBtn" type="submit">Yükle</button>
</form>
<div class="statusMsg"></div>


<script>
const doc_input = document.getElementById('xlsx')

doc_input.addEventListener('change', (event) => {
const target = event.target
    if (target.files && target.files[0]) {

    const maxAllowedSize = 3 * 1024 * 1024;
    if (target.files[0].size > maxAllowedSize) {
        alert('3 mb küçük dosya olmalı!');
        target.value = ''
    }
}
})

$(document).ready(function(e){
    // Submit form data via Ajax
    $("#fform").on('submit', function(e){
        e.preventDefault();
		var sendTo = $(this).data("sendto");
		var dType = $(this).data("returntype");
        $.ajax({
            type: 'POST',
            url: sendTo, // 'up4.2.app.php'
            data: new FormData(this),
            dataType: dType, // json / html
            contentType: false,
            cache: false,
            processData:false,
            beforeSend: function(){
				$('#submitBtn').text("Bekleyiniz...");
                $('#submitBtn').attr("disabled","disabled");
                $('#ffrom').css("opacity",".3");
            },
            success: function(response){
                $('.statusMsg').html('');
				/*
                if(response.status == 1){
                    $('#ffrom')[0].reset();
                    $('.statusMsg').html('<p class="alert alert-success">'+response.message+'</p>');
                }else{
                    $('.statusMsg').html('<p class="alert alert-danger">'+response.message+'</p>');
                }
				*/
				if(response){ // html response
                    $("#fform input[type=file]").val('');
                    $('.statusMsg').html(response);
                }
                $('#ffrom').css("opacity","1");
				$('#submitBtn').text("Yükle");
                $("#submitBtn").removeAttr("disabled");
            }
        });
    });
});
</script>

</body>
</html>
