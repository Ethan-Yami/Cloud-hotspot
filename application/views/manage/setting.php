<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title></title>
  <link href="//cdn.bootcss.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
  <script src="//cdn.bootcss.com/jquery/2.0.3/jquery.min.js"></script>
  <script src="//cdn.bootcss.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
</head>
<body>
  <div class="container" style="">
    <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <form class="form-horizontal" id="post">
                                <br/>
                                <br/>
                                <br/>

                                <div class="form-group">
                                <label class="col-sm-4 col-xs-4 text-center">机构名称</label>

                                    <div class="col-sm-7 col-xs-7">
                                    <input type="text" id="company" name="data[company]" class="form-control" value="{{bech['company']}}">
                                    </div>

                                   
                                </div>


                                 <div class="form-group">
                                <label class="col-sm-4 col-xs-4 text-center">联系人</label>

                                    <div class="col-sm-7 col-xs-7">
                                    <input type="text" name="data[truename]" class="form-control" value="{{bech['truename']}}">
                                    </div>

                                   
                                </div>


                                 <div class="form-group">
                                <label class="col-sm-4 col-xs-4 text-center">联系电话</label>

                                    <div class="col-sm-7 col-xs-7">
                                    <input type="text" name="data[cellphone]" class="form-control" value="{{bech['cellphone']}}">
                                    </div>

                                   
                                </div>

                                 <div class="form-group">
                                <label class="col-sm-4 col-xs-4 text-center">机构地址</label>

                                    <div class="col-sm-7 col-xs-7">
                                    <input type="text" name="data[address]" class="form-control" value="{{bech['address']}}">
                                    </div>

                                   
                                </div>
                                <div class="hr-line-dashed"></div>
                                
                                <div class="form-group">
                                    <div class="col-sm-12 col-sm-offset-12 text-center">   
                                      <button class="btn btn-success" id="saving" type="button">保存信息</button>
                                    </div>

                                </div>
                              <input type="hidden" name="do" value="{{do}}">
            </form>
        </div>
       

    </div>
    
  </div>


  <script src="https://cdn.bootcss.com/sweetalert/1.1.3/sweetalert.min.js"></script>
  <link href="https://cdn.bootcss.com/sweetalert/1.1.3/sweetalert.min.css" rel="stylesheet">
  <script type="text/javascript">
    
    $("#saving").click(function(event) {
      /* Act on the event */

      $.ajax({
        url: '?do={{do}}',
        type: 'POST',
        dataType: 'json',
        data: $("#post").serialize(),
      })
      .done(function(ret) {
        if(ret.status=='success'){            
             parent.$('#company_place').text($("#company").val());
             swal({
                  title: "完成!",
                  text: "已经为您保存完成!",
                  type: "success"
              });

        }
        //console.log("success");
      });
      
    });



  </script>
</body>
</html>