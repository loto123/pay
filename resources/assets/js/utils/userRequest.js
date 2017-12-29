import Axios from 'axios'
import 'babel-polyfill'
import {Toast} from 'mint-ui'
import Loading from './loading'
export default class UserRequest {
    static getInstance() {
        if (this._instance == null) {
            return new UserRequest();
        }
        else {
            return this._instance;
        }
    }

    constructor() {
        this.baseUrl = '/'; 
    }

    // 发起请求 post
    postData(url, data) {
        var tempUrl = this.baseUrl + url;
        var postData = data||{};

        var _token = this.getToken();
        this.validToken(_token);

        return new Promise(function (resolve, reject) {
            Axios({
                method: 'post',
                url: tempUrl,
                data: postData,
                headers:{Authorization:"Bearer "+_token}
            })
                .then(function (res) {
                    if(res.data.code == 1){
                        resolve(res);
                    }else if(res.data.code == 2){
                        Loading.getInstance().close();
                        Toast("用户未登录,即将跳转登录...");
                        setTimeout(function(){
                            window.location.href = "/#/login";
                        },1000);
                        reject(res);
                    }
                    else {
                        reject(res);
                    }
                })
                .catch(function (error) {
                    console.error(error);
                });
        });
    }

    getData(url, data) {
        var tempUrl = this.baseUrl + url;
        var postData = data || {};

        var _token = this.getToken();
        this.validToken(_token);

        return new Promise(function (resolve, reject) {
            Axios({
                method: 'get',
                url: tempUrl,
                params: postData,
                headers:{Authorization:"Bearer "+_token}
            })
                .then(function (res) {
                    if(res.data.code == 1){
                        resolve(res);
                    }else if(res.data.code == 2){

                        Loading.getInstance().close();
                        Toast("用户未登录,即将跳转登录...");
                        setTimeout(function(){
                            window.location.href = "/#/login"
                        },2000);
                        reject(res);
                        
                    }
                    else {
                        reject(res);
                    }
                })
                .catch(function (error) {
                    console.error(error);
                });
        });
    }

    // 验证token是否存在
    validToken(token){
        var url = window.location.href.indexOf("#/login");
        var urlShare = window.location.href.indexOf("#/share");

        if(!token && url==-1 && urlShare==-1){
            Loading.getInstance().close();
            Toast("用户未登录,即将跳转登录...");
            setTimeout(function(){
                window.location.href = "/#/login";
            },1000);
        }
    }

    getToken(){
        var _t =localStorage.getItem("_token");
        return _t;
    }

    setToken(token){
        localStorage.setItem("_token",token);
    }

    removeToken(){
        localStorage.removeItem("_token");
    }
}