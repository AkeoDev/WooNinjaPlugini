jQuery(document).ready(function ($) {

    $(".create-espremnica-csv").click(function (e) {
        e.preventDefault();

        var espremnica_csv_form = $(".js-espremnica-csv-form :input").serialize();

        $(this).hide();
        $(".ajax-loader-espremnica-csv").show();
        id = $(this).data("order-id");

        var data = {
            'action': 'espremnica_csv_api_generate',
            'order_id': id,
            'espremnica_csv_form': espremnica_csv_form
        };

        jQuery.post(ajaxurl, data, function (response) {
            var api_result = JSON.parse(response);

            if (typeof api_result.aResult != "undefined") {
                swal('Ups...', api_result.aResult.ResultCode, 'error').catch(swal.noop)
                $(".create-espremnica-csv").show();
            } else {
				$(".stevilka-paketa-espremnice").show();
				$(".stevilka-paketa-espremnice .number").html( api_result.data.sprejemna_stevilka );
                $(".create-espremnica-csv").hide();
				$(".print-from-espremnice-api").show();
				$(".print-from-espremnice-api").attr("href", api_result.data.pdf_url );
                $(".delete-espremnica-entry-pending").show();
				$(".ajax-loader-espremnica-csv").hide();

                swal({
                    title: "Bravo...",
                    text: "Paket je bil uspešno ustvarjen!",
                    type: "success",
                    showCancelButton: true,
                    cancelButtonText: "Close",
                    confirmButtonText: "Download PDF",
                    showLoaderOnConfirm: true,
                    preConfirm: function () {
                        return new Promise(function (resolve) {
                            setTimeout(function () {
                                resolve()
                            }, 1000)
                        })
                    }
                }).then(function () {
                    var download_url = api_result.data.pdf_url;
                    window.location.href = download_url;
                    swal({
                        title: "Bravo",
                        text: "PDF je bil uspešno prenešen!",
                        type: "success",
                    }).catch(swal.noop)
                }).catch(swal.noop);

                $(".send-to-espremnica-csv-api").hide();
            }
        });

        $(this).show();

    });

    $(".delete-espremnica-entry-pending").click(function (e) {
        e.preventDefault();

        var espremnica_sprejemna_stevilka = $(".stevilka-paketa-espremnice .number").text();

        $(this).hide();
        $(".ajax-loader-espremnica-csv").show();
        id = $(this).data("order-id");

        var data = {
            'action': 'espemnica_delete_entry',
            'order_id': id,
            'espremnica_sprejemna_stevilka': espremnica_sprejemna_stevilka
        };

        jQuery.post(ajaxurl, data, function (response) {
            $(".stevilka-paketa-espremnice").hide();
            $(".print-from-espremnice-api").hide();
            $(".ajax-loader-espremnica-csv").hide();

            swal({
                title: "Bravo...",
                text: "Paket je bil uspešno izbrisan!",
                type: "success",
                showCancelButton: true,
                cancelButtonText: "Close",
                //confirmButtonText: "Download PDF",
                //showLoaderOnConfirm: true,
                preConfirm: function () {
                    return new Promise(function (resolve) {
                        setTimeout(function () {
                            resolve()
                        }, 1000)
                    })
                }
            }).catch(swal.noop);

            $(".create-espremnica-csv").show();

        });

        $(this).hide();

    });

    $(".espremnica_send_to_api").click(function (e) {
        e.preventDefault();

        $(this).hide();
        $(".ajax-loader-espremnica-csv").show();

        var data = {
            'action': 'espremnica_send_to_api',
        };

        jQuery.post(ajaxurl, data, function (response) {
            var api_result = JSON.parse(response);
            var sprejemne_stevilke = api_result.data.sprejemne_stevilke_odposlano;
            if (typeof api_result.aResult != "undefined") {
                swal('Ups...', api_result.aResult.ResultCode, 'error').catch(swal.noop)
                $(".espremnica_send_to_api").show();
            } else {
                $(".espremnica_send_to_api").hide();
                $(".ajax-loader-espremnica-csv").hide();

                swal({
                    title: "Bravo...",
                    text: "Paketi " + sprejemne_stevilke + " so bili uspešno oddani!",
                    type: "success",
                    showCancelButton: true,
                    cancelButtonText: "Close",
                    confirmButtonText: "OK",
                    showLoaderOnConfirm: true,
                    preConfirm: function () {
                        return new Promise(function (resolve) {
                            setTimeout(function () {
                                resolve();
                                location.reload();
                            }, 1000)
                        })
                    }
                }).catch(swal.noop);
            }
        });

        $(this).show();

    });

});