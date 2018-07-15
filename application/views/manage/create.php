<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>云热点节点设置向导</title>
    <style type="text/css">
        #wizard {border:5px solid #789;font-size:12px;height:508px;margin:10px auto;width:580px;overflow:hidden;position:relative;-moz-border-radius:5px;-webkit-border-radius:5px;}
        #wizard .items{width:20000px; clear:both; position:absolute;}
        #wizard .right{float:right;}
        #wizard #status{height:35px;background:#123;padding-left:25px !important;}
        #status {list-style: none;}
        #status li{float:left;color:#fff;padding:10px 30px;}
        #status li.active{background-color:#369;font-weight:normal;}
        .input{width:240px; height:18px; margin:10px auto; line-height:20px; border:1px solid #d3d3d3; padding:2px}
        .page{padding:20px 30px;width:500px;float:left;}
        .page h3{height:42px; font-size:16px; border-bottom:1px dotted #ccc; margin-bottom:20px; padding-bottom:5px}
        .page h3 em{font-size:12px; font-weight:500; font-style:normal}
        .page p{line-height:24px;}
        .page p label{font-size:14px; display:block;}

    </style>

    <script src="//cdn.bootcss.com/jquery/2.0.1/jquery.min.js"></script>
    <script type="text/javascript" src="/Public/js/scrollable.js"></script>
</head>

<body style="overflow: hidden;">


<div id="main">

    <form action="#" method="post">
        <div id="wizard">
            <ul id="status">
                <li class="active"><strong>1.</strong>创建节点</li>
                <li><strong>2.</strong>填写用户</li>
                <li><strong>3.</strong>完成</li>
            </ul>

            <div class="items">
                <div class="page">
                    <h3>创建一个节点<br/><em>请填写您要增加的节点信息，用于管理。</em></h3>
                    <p>
                        <label>节点品牌：</label>
                        <input type="radio" name="brand">Mikrotik
                        <input type="radio" name="brand">Ubiquiti
                    </p>
                    <p>
                        <label>节点名称：</label>
                        <input type="text" class="input" id="branch" name="data[branch]" placeholder="请输入节点名称,例:云热点测试"/>
                    </p>
                    <p><label>IP地址：</label><input type="text" class="input" id="ip" name="data[ip]" placeholder="请输入IP地址,例:192.168.88.1" value="192.168.88.1"/></p>
                    <p><label>认证完成跳转URL：</label><input type="text" class="input" id="url" name="data[url]" placeholder="跳转URL,例:http://www.baidu.com" value="http://www.baidu.com"/></p>
                    <div class="btn_nav">
                        <input type="button" class="next right" value="下一步&raquo;" />
                    </div>
                </div>
                <div class="page">
                    <h3>填写用户信息<br/><em>请填写ROS hotspot中的用户名与密码。<br/><span style="color:red;">此用户与hotspot中要一致ip->hotspot->users->+</span></em></h3>
                    <p><label>用户名</label><input type="text" class="input" id="user" name="user[username]" placeholder="请输入用户名,例:user1"  /></p>
                    <p><label>密&nbsp;&nbsp;码</label><input type="password" id="pass" class="input" name="user[password]"  placeholder="请输入密码"/></p>
                    <p><label>确认密码：</label><input type="password" id="pass1" class="input" name="user[confirm]" placeholder="请输入确认密码"/></p>

                    <div class="btn_nav">
                        <input type="button" class="prev" style="float:left" value="&laquo;上一步" />
                        <input type="button" class="right" id="sub" value="下一步&raquo;" />
                        <input type="hidden" class="next" id="success"/>
                    </div>
                </div>
                <div class="page">
                    <h3>完成向导<br/><em>成功将节点相关信息生成。</em></h3>
                    <h4>恭喜您！</h4>
                    <p>请点击“下载”按钮下载节点文件。</p>

                    <br/>
                    <br/>
                    <br/>
                    <div class="btn_nav">
                        <input type="hidden" id="download" value="">
                        <input type="button" value="下载" onclick="downloads();"/>
                    </div>
                </div>
            </div>
        </div>
    </form><br />
    <br />
    <br />

</div>

<script type="text/javascript">
    $(function(){
        $("#wizard").scrollable({
            onSeek: function(event,i){
                $("#status li").removeClass("active").eq(i).addClass("active");
            },
            onBeforeSeek:function(event,i){
                if(i==1){
                    var user = $("#branch").val();
                    if(user==""){
                        alert("节点名不能为空！");
                        $("#branch").focus();
                        return false;
                    }
                    var ip = $("#ip").val();
                    var url = $("#url").val();
                    if(ip==""){
                        alert("ip地址不能为空！");
                        return false;
                    }
                    if(url==''){
                        alert("URL不能为空！");
                        return false;
                    }
                }

                if(i==2){
                    var user = $("#user").val();
                    if(user==""){
                        alert("请输入用户名！");
                        return false;
                    }
                    var pass = $("#pass").val();
                    var pass1 = $("#pass1").val();
                    if(pass==""){
                        alert("请输入密码！");
                        return false;
                    }
                    if(pass1 != pass){
                        alert("两次密码不一致！");
                        return false;
                    }

                }
            }
        });
        $("#sub").click(function(){

            var user = $("#user").val();
            if(user==""){
                alert("请输入用户名！");
                $("#user").focus();
                return false;
            }
            var pass = $("#pass").val();
            var pass1 = $("#pass1").val();
            if(pass==""){
                alert("请输入密码！");
                $("#pass").focus();
                return false;
            }
            if(pass1 != pass){
                alert("两次密码不一致！");
                $("#pass1").focus();

                return false;
            }

            var data = $("form").serialize();

            $.ajax({
                url: "?",
                type: 'POST',
                dataType: 'json',
                data: data,
            })
            .done(function(ret) {
                if(ret.status=='success'){
                    explode(ret.data);
                    $("#download").val(ret.id);
                    $("#success").click();
                }else if(ret.status=='false'){
                    alert(ret.message);
                }
            });


        });
    });


    function explode(config){
      var data = "<div class=\"col-md-3 col-sm-4 col-xs-6\"><div class=\"col-xs-12 choise_css\"><div class=\"panel text-center\"><div class=\"panel-body\"><i class=\"fa fa-dropbox fa-5x\" onclick=\"window.open('/hotspot/index?accesskey="+config['salt']+"','_blank')\"></i><h4>"+config['branch']+"</h4><span>"+config['overdue']+"</span></div></div></div></div>";
      parent.$('.container-fluid').append(data)
    } 
    function downloads(){

        var id  = $("#download").val();
        $('body').append("<iframe style='display:none;' src='/hotspot/downtest?id="+id+"'></iframe>" );


    }
</script>


</body>
</html>
