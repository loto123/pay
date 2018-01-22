

<style>
body{
    font-family:"Microsoft Yahei" !important;
}

#pet{
    width:100%;
    min-height:600px;
    background:#fff;
}

#pet .title{
    width:100%;
    height:30px;
    line-height:30px;
}

#pet .title h3{
    font-size:20px;
    line-height:30px;
    padding-left:20px;
    box-sizing:border-box;
}

/*********** 左边部分 ************/
#pet .left{
    margin-top:20px;
    width:500px;
    height:450px;
    background:#eee;
    float:left;
    margin-left:20px;
}

#pet .left ul{}

#pet .left ul li{
    width:100%;
    height:30px;
    line-height:30px;
    padding-left:10px;
    padding-right:10px;
    box-sizing:border-box;
}

#pet .left ul li >div{
    width:40px;
    float:left;
}

#pet .left ul li >select{
    float:left;
    margin-left:10px;
    display:block;
    margin-top:3px;
}
/*********** 左边部分end ************/


/*********** 右边部分 ************/
#pet .right{
    margin-top:20px;
    width:450px;
    height:450px;
    background:#d6f0f7;
    float:left;
    margin-left:20px;
    position:relative;
}   

/****** 身体组成部分 *******/
#pet .right #body{
    position:absolute;
    top:15%;
    left:10%;
    z-index:1000;
}

#pet .right #eye{
    position:absolute;
    top:3%;
    left:14%;
    z-index:1000;
}

#pet .right #beard{
    position:absolute;
    top:23%;
    left:-3%;
     z-index:1000;
}

#pet .right #ear{
    position: absolute;
    top: 3.7%;
    left: 20.5%;
     z-index:999;
}

#pet .right #figure{
    position: absolute;
    top: -16.5%;
    left: 12%;
     z-index:1000;
}

#pet .right #mouse{
    position: absolute;
    top: 26%;
    left: 30.6%;
     z-index:1000;
}

#pet .right #paw{
    position: absolute;
    top: 81.5%;
    left: 14%;
    z-index:1000;
}

#pet .right #tail{
    position: absolute;
    top: 38%;
    left: 70.5%;
     z-index:999;
}
/****** 身体组成部分end *******/

/*********** 右边部分end ************/
</style>

<div id="pet">
  <div class="title">
    <h3>宠物配置器</h3>   
  </div>

  <div class="left">
      <ul>
          <li>
            <div>耳朵:</div>
            <select name="" id="earChoise">
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
                <option value="6">6</option>
                <option value="7">7</option>
            </select>
          </li>
          
          <li>
            <div>胡子:</div>
            <select name="" id="beardChoise">
            <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
                <option value="6">6</option>
                <option value="7">7</option>
            </select>
          </li>

          <li>
            <div>花纹:</div>
            <select name="" id="figureChoise">
            <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
                <option value="6">6</option>
                <option value="7">7</option>
            </select>
          </li>

          <li>
            <div>前爪:</div>
            <select name="" id="pawChoise">
            <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
                <option value="6">6</option>
                <option value="7">7</option>
            </select>
          </li>

          <li>
            <div>身子:</div>
            <select name="" id="bodyChoise">
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
                <option value="6">6</option>
                <option value="7">7</option>
            </select>
          </li>

          <li>
            <div>眼睛:</div>
            <select name="" id="eyeChoise">
               <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
                <option value="6">6</option>
                <option value="7">7</option>
            </select>
          </li>

          <li>
            <div>嘴巴:</div>
            <select name="" id="mouseChoise">
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
                <option value="6">6</option>
                <option value="7">7</option>
            </select>
          </li>

          <li>
            <div>尾巴:</div>
            <select name="" id="tailChoise">
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
                <option value="6">6</option>
                <option value="7">7</option>
            </select>
          </li>
      </ul>
      
      <button type="button" id="buildDog">生成狗狗</button>

  </div>
  <div class="right">
    <div id="ear">
        <img src="/images/dog/ear/1.png" alt="">
    </div>
   <div id="body">
        <img src="/images/dog/body/7.png" alt="">

        <div id="figure">
            <img src="/images/dog/figure/2.png" alt="">
        </div>
        <div id="eye">
            <img src="/images/dog/eye/1.png" alt="">
        </div>
        <div id="beard">
            <img src="/images/dog/beard/1.png" alt="">
        </div>
        <div id="mouse">
            <img src="/images/dog/mouse/1.png" alt="">
        </div>
       
        <div id="paw">
            <img src="/images/dog/paw/3.png" alt="">
        </div>
   </div>
   <div id="tail">
        <img src="/images/dog/tail/1.png" alt="">
    </div>
  
  </div>
</div>


<!-- <script src="/js/"></script> -->
<script>

$(function(){
    var commonUrl = "/images/dog/"
    $("#buildDog").click(function(){
        var earChoiseValue = $("#earChoise").val();
        var beardChoiseValue = $("#beardChoise").val();
        var mouseChoiseValue = $("#mouseChoise").val();
        var figureChoiseValue = $("#figureChoise").val();
        var pawChoiseValue = $("#pawChoise").val();
        var bodyChoise = $("#bodyChoise").val();
        var eyeChoiseValue = $("#eyeChoise").val();
        var tailChoiseValue = $("#tailChoise").val();

        // 生成各部分结构
        $("#ear >img").attr("src",commonUrl+"ear/"+earChoiseValue+".png");

        $("#beard >img").attr("src",commonUrl+"beard/"+beardChoiseValue+".png");
        
        $("#mouse >img").attr("src",commonUrl+"mouse/"+mouseChoiseValue+".png");

        $("#figure >img").attr("src",commonUrl+"figure/"+figureChoiseValue+".png");

        $("#paw >img").attr("src",commonUrl+"paw/"+pawChoiseValue+".png");

        $("#eye >img").attr("src",commonUrl+"eye/"+eyeChoiseValue+".png");

        $("#body >img").attr("src",commonUrl+"body/"+bodyChoise+".png");

        $("#tail >img").attr("src",commonUrl+"tail/"+tailChoiseValue+".png");

        console.log(earChoiseValue);
    });
    
    
});

</script>