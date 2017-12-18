import Axios from 'axios'
import 'babel-polyfill'
import {Toast} from 'mint-ui'
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

        var _token = sessionStorage.getItem("_token");

        if (_token != null) {
            postData.token = sessionStorage.getItem("_token");
        }

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
                        reject(res);
                        Toast("用户未登录,即将跳转登录...");
                        setTimeout(function(){
                            window.location.href = "/#/login"
                        },1000);
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

        var _token = sessionStorage.getItem("_token");

        if (_token != null) {
            postData.token = sessionStorage.getItem("_token");
        }

        return new Promise(function (resolve, reject) {
            Axios({
                method: 'get',
                url: tempUrl,
                data: postData,
                headers:{Authorization:"Bearer "+_token}
            })
                .then(function (res) {
                    if(res.data.code == 1){
                        resolve(res);
                    }else if(res.data.code == 2){
                        reject(res);
                        Toast("用户未登录,即将跳转登录...");
                        setTimeout(function(){
                            window.location.href = "/#/login"
                        },1500);
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
}