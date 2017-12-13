import Axios from 'axios'

export class UserRequest{
    getInstance(){
        if(this._instance == null){
            return  new Request();
        }
        else {
            return this._instance;
        }
    }
}