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

        return new Promise( (resolve, reject)=> {
            Axios({
                method: 'post',
                url: tempUrl,
                data: postData,
                headers:{Authorization:"Bearer "+_token}
            })
                .then( res => {
                    this._validCode(res).then(res=>{
                        resolve(res);
                    }).catch(err=>{
                        reject(err);
                    });;
                    // switch(res.data.code){
                    //     case 1:
                    //         resolve(res);
                    //         break;
                    //     case 2:
                    //         Loading.getInstance().close();
                    //         window.location.href = "/#/login";
                    //         reject(res);
                    //         break;
                    //     case 404:
                    //         console.log(404);
                    //         break;
                    //     default:
                    //         reject(res);
                    // }
                   
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

        return new Promise( (resolve, reject)=> {
            Axios({
                method: 'get',
                url: tempUrl,
                params: postData,
                headers:{Authorization:"Bearer "+_token}
            })
                .then(res=> {
                    this._validCode(res).then(res=>{
                        resolve(res);
                    }).catch(err=>{
                        reject(err);
                    });
                   // switch(res.data.code){
                   //      case 1:
                   //          resolve(res);
                   //          break;
                   //      case 2:
                   //          Loading.getInstance().close();
                   //          window.location.href = "/#/login";
                   //          reject(res);
                   //          break;
                   //      case 404:
                   //          console.log(404);
                   //          break;
                   //      default:
                   //          reject(res);
                   //  }
                })
                .catch(function (error) {
                    console.error(error);
                });
        });
    }

    _validCode(res){
        return new Promise(function(resolve,reject){
            switch(res.data.code){
                case 1:
                    resolve(res);
                    break;
                case 2:
                    Loading.getInstance().close();
                    // window.location.href = "/#/login";
                    app.$router.push("/login");
                    reject(res);
                    break;
                case 404:
                    // if(window.history.length <= 2){
                    app.$router.push("/notice?notice="+res.data.msg);
                    Loading.getInstance().close();
                    // window.location.href = '/#/notice?notice='+res.data.msg;
                      // }else {
                      //   window.history.go(-1);
                      // }
                    break;
                default:
                    reject(res);
            }
        })
    }

    // 验证token是否存在
    validToken(token){
        var url = window.location.href.indexOf("#/login");
        var urlShare = window.location.href.indexOf("#/share");

        if(!token && url==-1 && urlShare==-1){
            localStorage.setItem("url",window.location.href);
            Loading.getInstance().close();
            Toast("用户未登录,即将跳转登录...");
            window.location.href = "/#/login";
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