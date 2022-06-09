var vErrorElement = 'span', vErrorClass = 'validate-has-error';

function vHighlight(element) {
    $(element).closest('.form-group').addClass('validate-has-error');
}

function vUnhighlight(element) {
    $(element).closest('.form-group').removeClass('validate-has-error');
}

function vErrorPlacement(error, element) {
    if (element.closest('.has-switch').length) {
        error.insertAfter(element.closest('.has-switch'));
    } else {
        if (element.parent('.checkbox, .radio').length || element.parent('.input-group').length) {
            error.insertAfter(element.parent());
        } else {
            error.insertAfter(element);
        }
    }
}

$.padLeft = function (i, l, s) {
    var o = i.toString();
    if (!s) { s = '0'; }
    while (o.length < l) {
        o = s + o;
    }
    return o;
};

$.padRight = function (i, l, s) {
    var o = i.toString();
    if (!s) { s = '0'; }
    while (o.length < l) {
        o = o + s;
    }
    return o;
};

function padCurrency(currency) {
    currency = currency + "";
    //
    if (currency.indexOf(".") != -1) {
        var arr = currency.split(".");
        //
        if (arr[1].length < 3) {
            return arr[0] + '.' + $.padRight(arr[1], 3);
        } else {
            return arr[0] + '.' + arr[1].substring(0, 3);
        }
    } else {
        return currency + ".000";
    }
}

function parseFloatNum(num) {
    num = num.toString().replace(/,/g, "");
    return parseFloat(num);
}

function clearValidation(formElement) {
    var validator = $("#" + formElement).data('validator');
    //
    validator.resetForm();
    validator.reset();
    //
    $("#" + formElement + ' div.validate-has-error').removeClass('validate-has-error');
}


$.validator.addMethod("valueNotEquals", function (value, element, arg) {
    return arg != value;
}, "Value must not equal arg.");


/* Data Tables Sort  */

function calculate_date(date) {
    var date = date.replace(" ", "");

    if (date.indexOf('.') > 0) {
        /*date a, format dd.mn.(yyyy) ; (year is optional)*/
        var eu_date = date.split('.');
    } else {
        /*date a, format dd/mn/(yyyy) ; (year is optional)*/
        var eu_date = date.split('/');
    }

    /*year (optional)*/
    if (eu_date[2]) {
        var year = eu_date[2];
    } else {
        var year = 0;
    }

    /*month*/
    var month = eu_date[1];
    if (month.length == 1) {
        month = 0 + month;
    }

    /*day*/
    var day = eu_date[0];
    if (day.length == 1) {
        day = 0 + day;
    }

    return (year + month + day) * 1;
}

/*
jQuery.fn.dataTableExt.oSort['eu_date-asc'] = function (a, b) {
    x = calculate_date(a);
    y = calculate_date(b);

    return ((x < y) ? -1 : ((x > y) ? 1 : 0));
};

jQuery.fn.dataTableExt.oSort['eu_date-desc'] = function (a, b) {
    x = calculate_date(a);
    y = calculate_date(b);

    return ((x < y) ? 1 : ((x > y) ? -1 : 0));
};
*/

/* ****************** */

/**
 * This plug-in for DataTables represents the ultimate option in extensibility
 * for sorting date / time strings correctly. It uses
 * [Moment.js](http://momentjs.com) to create automatic type detection and
 * sorting plug-ins for DataTables based on a given format. This way, DataTables
 * will automatically detect your temporal information and sort it correctly.
 *
 * For usage instructions, please see the DataTables blog
 * post that [introduces it](//datatables.net/blog/2014-12-18).
 *
 * @name Ultimate Date / Time sorting
 * @summary Sort date and time in any format using Moment.js
 * @author [Allan Jardine](//datatables.net)
 * @depends DataTables 1.10+, Moment.js 1.7+
 *
 * @example
 *    $.fn.dataTable.moment( 'HH:mm MMM D, YY' );
 *    $.fn.dataTable.moment( 'dddd, MMMM Do, YYYY' );
 *
 *    $('#example').DataTable();
 */



    $.fn.dataTable.moment = function (format, locale) {
        var types = $.fn.dataTable.ext.type;

        // Add type detection
        types.detect.unshift(function (d) {
            // Strip HTML tags if possible
            if (d && d.replace) {
                d = d.replace(/<.*?>/g, '');
            }

            // Null and empty values are acceptable
            if (d === '' || d === null) {
                return 'moment-' + format;
            }

            return moment(d, format, locale, true).isValid() ?
                'moment-' + format :
                null;
        });

        // Add sorting method - use an integer for the sorting
        types.order['moment-' + format + '-pre'] = function (d) {
            return d === '' || d === null ?
                -Infinity :
                parseInt(moment(d.replace ? d.replace(/<.*?>/g, '') : d, format, locale, true).format('x'), 10);
        };
    };