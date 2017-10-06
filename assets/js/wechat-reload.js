var updateUrl = function(url, getOnly) {
	var key = 't=';
	url = url || window.location.href;
    var result;
	if (!/micromessenger/.test(navigator.userAgent.toLowerCase()))
        result = url;
    else {
        var reg = new RegExp(key+'\\d+');
        var timestamp = +new Date();
        if (url.indexOf(key)>-1) {
            result = url.replace(reg ,key+timestamp);
        } else {
            if (url.indexOf('\?')>-1) {
                var urlArr = url.split('\?');
                if(urlArr[1]) {
                    result = urlArr[0]+'?'+key+timestamp+'&'+urlArr[1];
                } else {
                    result = urlArr[0]+'?'+key+timestamp;
                }
            } else {
                if (url.indexOf('#') > -1) {
                    result = url.split('#')[0]+'?'+key+timestamp+location.hash;
                } else {
                    result = url+'?'+key+timestamp;
                }
            }
        }
    }
    if (getOnly)
        return result;
    location.href = result;
}
