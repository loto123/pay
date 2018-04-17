var utils = {};

utils.getQueryString = function(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) return unescape(r[2]); return null;
}

//截取字符串(包括中文）
utils.SetString = function(str,len)
{
    var strlen = 0;
    var s = "";
    var _len = len*2;
    for(var i = 0;i < str.length;i++){

        if(str.charCodeAt(i) > 128){
            strlen += 2;
        }else{
            strlen++;
        }

        s += str.charAt(i);

        if(strlen >= _len){
            return s+"...";
        }
    }

    return s;
}

//检测一个输入是否为整数
utils.testStringisNumber = function (testString) {
    var t= Number(testString);
    if (isNaN(t) || testString.toString().indexOf(".")!=-1)
    {
        return false;
    }else {
        return true;
    }
}

//手机号码截取
// utils.mobileString = function (mobiles) {
//   mobiles.toString().substr(0, 3) + '****' + mobiles.toString().substr(7);  
// }
export default utils;