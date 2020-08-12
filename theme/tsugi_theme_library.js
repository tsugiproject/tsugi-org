

function breakHEX(hexval) {
    if ( hexval.length != 7 ) {
        console.log('Cannot handle hex value', hexval);
        return [0,0,0];
    }
    var one = parseInt('0x'+hexval.substring(1,3))
    var two = parseInt('0x'+hexval.substring(3,5))
    var three = parseInt('0x'+hexval.substring(5,7))
    var retval = [one, two, three];
    return retval;
}


// https://stackoverflow.com/a/9733420/1994792
function contrast(rgb1, rgb2) {
    var lum1 = relativeLuminance(rgb1);
    var lum2 = relativeLuminance(rgb2);
    var brightest = Math.max(lum1, lum2);
    var darkest = Math.min(lum1, lum2);
    return (brightest + 0.05)
         / (darkest + 0.05);
}

function getShortLabel(value) {
    var founddash = true;
    var retval = '';
    for(var i=0; i< value.length; i++) {
        if ( value.substring(i, i+1) == '-' ) {
            founddash = true;
            continue;
        }
        if ( founddash ) {
            retval = retval + value.substring(i,i+1);
            founddash = false;
        }
    }
    if ( value.length > 1 && ! founddash) {
        var i = value.length;
        retval = retval + value.substring(i-1,i);
    }
    return retval;
}
