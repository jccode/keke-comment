/**
 * ҳͷjs
 */

$(function(){
	
	
	//��ʾ��¼��
	$("#login").click(function(){
			$("#login_box").toggleClass("hidden");
			$(this).toggleClass("selected");
			$("#txt_account").focus();
			return false;
	});
	
	//����-���ص�¼������
	var hideLoginPopup = function(){
		if (!$("#login_box").is(".hidden")) {
			$("#login").removeClass("selected");
			$("#login_box").addClass("hidden");
			
		}
	}; 
	//body����������ط���
	$("body").click(function(){
		hideLoginPopup();
		$("#user_menu").addClass("hidden");
		$("#search_select a").not(".selected").addClass("hidden");
	});
	
	//��¼���û������˵�
	$("#avatar").mouseDelay().hover(function(){
		$("#user_menu").removeClass("hidden");
	},
	function(){
		$("#user_menu").addClass("hidden");
	});
	
	
	//��ֹ������ط���
	$("#login_box,#user_menu,#search_select").click(function (e) {
		e.stopPropagation();
	});
		
	//����ѡ��
	$("#search_select a.selected").click(function(){
		$(this).nextAll("a").removeClass("hidden");
	});

	$("#search_select a").not(".selected").click(function(){
		$("#search_select .selected").attr("rel",$(this).attr("rel")).children("span").html($(this).html()).end().nextAll("a").addClass("hidden");
	})
	
	
	//����ѡ�� 
	$("#lan_menu a").click(function(){
		setLang($(this).attr("rel"));
	})

});

$(function (){ 
	$("#lan_menu").mouseDelay().hover(function(){
		$(this).addClass("hover").children().removeClass("hidden");
	},function (){ 
		$(this).removeClass("hover").children().not("a.selected").addClass("hidden");
	}); 
}); 


function search_keydown(event){
    if ($.browser.msie) {
        if (window.event.keyCode == 13) {
        	topSearch();
        }
    }
    else {
        if (event.keyCode == 13) {
        	topSearch();
        }
    }
}

function login_keydown(event){
    if ($.browser.msie) {
        if (window.event.keyCode == 13) {
        	ajaxLogin(INDEX);
        }
    }
    else {
        if (event.keyCode == 13) {
        	ajaxLogin(INDEX);
        }

   }
}
 
$("#search_btn").click(function(){topSearch();})
function topSearch(){
	var searchKey = $.trim($("#search_key").val());
 
	if(searchKey&&searchKey!=L.input_task_service){
		var type      = $("#search_select .selected").attr("rel");
		var link    = "index.php?do="+type+"&path=H2&search_key="+searchKey;
			$("#frm_search").attr("action",link);
		location.href=link;
	}
}
function setLang(o){
	var lang = o.value;
	var c    = $(o).children('option:selected').attr('c');
		setCurr(c);
		setTimeout(function(){
			if(lang==LANG){
				return false;
			}else{
				setcookie("_lang",lang,24*3600);
				document.location.replace(location.href);
			}
		},500);
}
function setCurr(c,t){
	var url  = SITEURL+'/index.php?do=ajax&ajax=ajax&ac=currency&curr='+c;
	$.post(url);
	t==1&&setTimeout(function(){
		document.location.replace(location.href);
	},500);
}
