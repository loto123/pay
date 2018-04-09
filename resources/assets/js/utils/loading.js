import { Indicator,Toast } from 'mint-ui';

class Loading {
    static getInstance() {
        if (this._instance == null) {
            return this._instance = new Loading();
        } else {
            return this._instance;
        }
    }

    constructor() {
        this._timer = null;
        this.errSwitch = true;
    }

    open(value) {
        if(!value){
          value = "加载中...";
        }
        this.errSwitch = true;
        Indicator.open({
          value,
          spinnerType: 'fading-circle'
        });

        if (this._timer == null) {
            this._timer = setTimeout(() => {
                Indicator.close();
                // if(this.errSwitch == true){
                //     Toast("网络错误，请尝试刷新页面");
                // }
                this._timer = null;
            }, 10000);
            
        } else {
            this.errSwitch = false;
            return;
        }

    }

    close() {
        this.errSwitch = false;
        // this._timer = null;
        Indicator.close();
    }
}

export default Loading;