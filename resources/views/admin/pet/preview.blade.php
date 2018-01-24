
<style>
body{
    font-family:"Microsoft Yahei" !important;
}

input[type="text"]{
    height: 25px;
    width: 40px;
    float: left;
}

#pet{
    width:100%;
    min-height:800px;
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
    padding-top:12px;
    box-sizing: border-box;
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
    margin-left: 10px;
}

#pet .left ul li >select{
    float:left;
    margin-left:10px;
    display:block;
    margin-top:3px;
}

#buildDog{
    display: block;
    margin-left: 20px;
    margin-top:10px;
}

#random-buildDog{
    display: block;
    margin-left: 20px;
    margin-top:10px;
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

#pet .right .background{
    position: absolute;
    top:0px;
    left:0px;
    z-index: 900;
    border: 1px solid #eee;
}


#pet .right .components{
    position: absolute;
    z-index: 1000;
}

/*********** 右边部分end ************/
</style>

<div id="pet">
  <div class="title">
    <h3>宠物配置器</h3>   
  </div>

  <div class="left">
      <ul id="options">
          @foreach ($pet_type->parts as $part)
          <li>
            <div>{{ $part->name }}:</div>
            <select name="" >
                @foreach ($part->items as $item)
                <option value="{{ Storage::disk(config('admin.upload.disk'))->url($item->image) }}">{{ $item->name }}</option>
                @endforeach
            </select>

            <div>z轴:</div>
            <input type="text" value="{{ $part->z_index }}" class="z_axis">
            
            <div>x轴:</div>
            <input type="text" value="{{ $part->x_index }}" class="x_axis">
            
            <div>y轴:</div>
            <input type="text" value="{{ $part->y_index }}" class="y_axis">

          </li>
          @endforeach
      </ul>
      
      <button type="button" id="buildDog">生成狗狗</button>
      <button type="button" id="random-buildDog">随机生成</button>
  </div>

  <div class="right">
    <div class="background">
        <img src="{{ Storage::disk(config('admin.upload.disk'))->url($pet_type->image) }}" alt="">
    </div>

    <!-- 拼狗狗组件 -->

    @foreach ($pet_type->parts as $part)
    <div class="components">
        <img src="" alt="">
    </div>
    @endforeach
   


  </div>
</div>

<script>

$(function(){
    var commonUrl = "/images/dog/";
    var optionList = [];
    var options = $("#options");

    // 获取基本数据
    function getLocalConfig(){
        optionList = [];

        for(var i=0; i  < options.find("li").length; i++ ){
            var _obj = {};
            _obj.x = options.find("li").eq(i).find(".x_axis").val();
            _obj.y = options.find("li").eq(i).find(".y_axis").val();
            _obj.z = options.find("li").eq(i).find(".z_axis").val();
            _obj.img = options.find("li").eq(i).find("select").val();
            optionList.push(_obj);
        }
    }

    function buildDog(isRandom){
        if(isRandom == true){
            getRandomValue();
        }

        getLocalConfig();
        for(var i = 0; i<optionList.length; i++ ){
            var components = $(".components").eq(i);
            components.find("img").attr("src",optionList[i].img);
            components.css({left:optionList[i].x+"px",top:optionList[i].y+"px",zIndex:(1000+parseInt(optionList[i].z))});
        }
    }

    function getRandomValue(){
        var selectAll = $("select");
        for(var i = 0; i<selectAll.length; i++){
            var _index = getRandom(selectAll.eq(i).find("option").length);
            selectAll.get(i).selectedIndex = _index;
        }
    }

    function getRandom(value){
        return Math.floor(Math.random()*value);
    }

    $("#buildDog").click(function(){
        buildDog();
    });

    $("#random-buildDog").click(function(){
        buildDog(true);
    });

    $(document).keydown(function(event){
        if(event.keyCode == 13){
            buildDog();
        }
　　});

    buildDog();
    
});

</script>