$(document).ready(function(){
	$("#carrierTable input").click(function(){
        $("#correio-extra-carrier").html("");
		var id = $(this).attr("id").replace("id_carrier", "");
        var sCepDestino = $("#correio-extra-carrier").attr("sCepDestino");
        var data = {"id_carrier": id, "sCepDestino": sCepDestino};
        $("#correio-extra-carrier").load("/modules/correios/prazo-de-entrega.php", data);
	}).filter(":checked").click();
});