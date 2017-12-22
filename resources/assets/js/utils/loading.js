import { Indicator } from 'mint-ui';

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
    }

    open(value) {
        if(!value){
            value = "加载中...";
        }

        Indicator.open(value);

        if (this._timer == null) {
            this._timer = setTimeout(() => {
                Indicator.close();
                this._timer = null;
            }, 10000);
        } else {
            return;
        }

    }

    close() {
        Indicator.close();
    }
}

export default Loading;