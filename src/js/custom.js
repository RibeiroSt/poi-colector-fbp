
$(document).ready(function () {

    let url_string = window.location.href;
    let url = new URL(url_string);
    let control = url.searchParams.get("control");

    let baseurl = "https://www.fbplaces.com/getplaces.php";

    if (null == control) {

        control = 0;
    }
    let total = url.searchParams.get("total");

    if (null != total) {

        if ((!isNaN(total) && !isNaN(control)) && (total <= 1000)) {

            total = parseInt(total);
            control = parseInt(control);

            if (control < total) {

                let code = url.searchParams.get("code");
                let state = url.searchParams.get("state");

                let sufix  = "?control=" + control;
                    sufix += "&total=" + total;
                    sufix += "&code=" + code;
                    sufix += "&state=" + state;
                window.location.replace(baseurl + sufix);

            } else if (control === 0 && total === 0) {

                $.alert({
                    title: 'Error',
                    content: 'Parameters \'total\' and \'control\' are equals to 0. <br> This scenario may represent an application error.',
                });
            } else {

                $.alert({
                    title: 'Info',
                    content: 'Execution ended...',
                });
            }
        } else {

            //alert("Param 'total' must be a number between 0 and 500.")
            $.alert({
                title: 'Error',
                content: 'Param \'total\' must be a number between 0 and 500!',
            });
        }
    } else {

        //alert("Param 'total' cannot be null.")
        $.alert({
            title: 'Error',
            content: 'Param \'total\' cannot be null.',
        });
    }
});