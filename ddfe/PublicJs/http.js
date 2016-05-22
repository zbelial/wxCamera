/*************************
 *						 *
 *       关于用户			 *
 *  					 *
 *************************/
// window.onload = function() {

// }

/****** ajax对象 *******/
// var projecturl = window.location.href.split("?")[0]; // 获取url前缀
var http_method = {

	ajaxmethod : function(url,datadic,types,dataType,successfunc) {
		$.ajax({
			url:url,
			data:datadic,
			type:types,
			dataType:dataType,
			success: function(data){
				// console.log(data);
				successfunc(data);

			},error: function(e) {
				// console.log(e);
				alert("请求错误！");
                successfunc(e);
			}
		});
	},
}

function getElement() {
	// 得到url参数
	var $_GET = (function(){
        var url = window.document.location.href.toString();
        var u = url.split("?");
        if(typeof(u[1]) == "string"){
            var uArr = u[1].split("&");
            for(var i = 0;i < uArr.length; i++) {
                var temp = uArr[i].split("=");
                uArr[i] = temp[1];
            }
            return uArr;
        } else {
            return {};
        }
    })();

    return $_GET;
}

/*
 *  统一UI
 */

var UITools = {
	Navbar : function() {
		// var body = $('body').html();
		// alert(body);
		var indexURL = "?Home=Bg&Cont=index&Meth=start";
		var loginoutURL = "?Home=Bg&Cont=user&Meth=loginout";
        var writeURL = "?Home=Bg&Cont=article&Meth=start";

		var navui = 
            '<nav class="navbar navbar-inverse navbar-fixed-top">'
      		+ '<div class="container" >'
        	+ '<div class="navbar-header">'
          	+ '<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">'
            + '<span class="sr-only">Toggle navigation</span>'
            + '<span class="icon-bar"></span>'
            + '<span class="icon-bar"></span>'
            + '<span class="icon-bar"></span>'
          	+ '</button>'
          	+ '<a class="navbar-brand" href="#">Bootstrap theme</a>'
        	+ '</div>'
        	+ '<div id="navbar" class="navbar-collapse collapse">'
          	+ '<ul class="nav navbar-nav navbar-right">'
            + '<li><a href="#about">搜索</a></li>'
            + '<li>'
            + '<a href="'
            + indexURL
            + '">主页'
            + '</a></li>'
            + '<li><a href="' + loginoutURL
            + '">注销</a></li>'
            + '<li><a href="' + writeURL
            + '">写文章</a></li>'
          + '</ul>'
          + '<form class="navbar-form navbar-right">'
            + '<input type="text" class="form-control" placeholder="Search...">'
            + '</form>'
        + '</div>'
      + '</div>'
    + '</nav>';

		$('body').append(navui);
	},
    AddTypeForm : function(id,actionname) { // 传入html ui对象的id
        var typeform = 
        '<div style="padding-right:5%">'
        + '<input type="text" id="type_name" class="form-control" placeholder="type_name" required="" autofocus="">'
        + '<textarea type="text" id="type_content" class="form-control" placeholder="type_content" required="" autofocus=""></textarea>'
        + '<button class="btn btn-lg btn-primary btn-block" type="button" onclick="'
        + actionname
        + '">提交</button>'
        + '</div>';
        $('#' + id).html(typeform);
    },
}

function test() {
	alert('test');
}






