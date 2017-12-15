import Axios from 'axios'
import 'babel-polyfill'

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
    postData(url, data, callback) {
        var tempUrl = this.baseUrl + url;
        var postData = data;

        var _token = sessionStorage.getItem("_token");

        if (_token != null) {
            postData.token = sessionStorage.getItem("_token");
        }

        return new Promise(function (resolve, reject) {
            Axios({
                method: 'post',
                url: tempUrl,
                data: postData,
                auth: {
                    token: _token,
                }
            })
                .then(function (res) {
                    resolve(res);
                })
                .catch(function (error) {
                    console.error(error);
                });
        });
    }

    getData() {

    }
}