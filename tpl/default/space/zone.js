/**
 * �ռ�js
 */
var hasbg=0;//Ĭ��δ���ñ���
	pbgimg!=bgimg?hasbg=1:'';//�ж�

$(function() {
	$("#sTyle a.item").live('click', (function() {
		var id = $(this).attr("id");
		$("#sTyle span").removeClass("selected");
		$("#span_" + id).addClass("selected");
		$("#sTyle a.item").removeClass("selected");
		$(this).addClass("selected");
		$("#nStyle").val(id);
		$("body").removeClass().addClass(id);
	}));

})
/**
 * ����ҳ��
 * 
 * @param i
 *            �ռ�UID
 * @param c
 *            ��ǰ��� �������ʹ��
 * @param p
 *            =1��ʾ��� =2��ʾ����
 * @returns {Boolean}
 */
function customLoad(i, c, p) {
	if (i == uid) {
		var url = SITEURL + "/index.php?do=space&member_id=" + i + "&ac=custom";
		var o1 = 'sTyle';
		var o2 = 'bGround';
		var o = $("#" + ((p == 1) ? o1 : o2));
		var load = o.attr('load');
		if (load == 1) {
			o.slideToggle();
		} else {
			if (p == 1) {
				o.load(url + '&skin=' + c + '&t=style #spacestyle')
						.slideToggle().attr('load', 1);
			} else {
				$.get(url + '&t=bground', function(text) {
					o.html(text).slideToggle().attr('load', 1);
				}, 'text')
			}
		}
		p == 1 ? $("#" + o2).slideUp() : $("#" + o1).slideUp();
	} else {
		showDialog(L.z_access_forbidden, 'alert', L.operate_notice);
		return false;
	}
}
/**
 * ����Ƥ��
 * 
 * @param {Object}
 */
function saveStyle() {
	var s = $("#spacestyle").attr('s');// ԭʼƤ��
	var n = $("#sTyle a.item.selected").attr('id');
	if (n == s) {// δ���
		unSave();
	} else {
		var url = SITEURL + "/index.php?do=space";
		$.getJSON(url, {
			member_id : uid,
			ac : 'custom',
			t : 'style',
			sbt : 1,
			skin : n
		}, function(json) {
			if (json.status == 1) {
				$("#spacestyle").attr('s', n);// ��Ƥ��
			}
			$("#sTyle").slideUp();
		});
	}
}
/**
 * ȡ������
 */
function unSave() {
	var s = $("#spacestyle").attr('s');// ԭʼƤ��
	$("body").removeClass().addClass(s);
	$("#sTyle a.item").removeClass("selected");
	$("#" + s).addClass("selected");
	$("#sTyle").slideUp();
}
/**
 * �����ϴ�
 * 
 * @param file_name�ļ���
 * @param width
 *            ���
 * @param height
 *            �߶�
 */
function fileUpload(file_name) {
	var s = $("#" + file_name).data('uploadify').queueData.queueSize;
		if(s){
			$("#" + file_name).uploadify('upload');
		}else{
			savePos();
		}
}
/**
 * �ϴ���ɺ�ĺ�������
 */
function uploadResponse(json) {
	if (json.fid) {
		var repeat = $("#repeat").val();
		var scroll = $("#scroll").val();
		var position = $("#position").val();
		var url = SITEURL + "/index.php?do=space&member_id="+uid+"&ac=custom&t=bground";
		$.getJSON(url, {
			fields : json.filename,
			filePath : json.msg.up_file,
			sbt:1,
			repeat:repeat,
			scroll:scroll,
			position:position,
			ajax:1
		}, function(data) {
			if (data.status == 1) {
				var bg = "url('"+json.msg.up_file+"')";
				$("#bgsrc").attr('src',json.msg.up_file);
				//showDialog(json.msg, 'notice', L.operate_notice);
				savePos(bg);
				hasbg = 1;
				$("#bGround").slideUp();
				return false;
			} else {
				showDialog(data.msg, 'alert', L.operate_notice);
				return false;
			}
		});
	}
}
/**
 * ���±�������
 */
function savePos(bg,re){
	var repeat = $("#repeat").val();
	var scroll = $("#scroll").val();
	var position = $("#position").val();
	var url = SITEURL + "/index.php?do=space&member_id="+uid+"&ac=custom&t=bground";
	$.post(url, {
		sbt:1,
		repeat:repeat,
		scroll:scroll,
		position:position
	}, function(text) {
		if(bg){
			$('body').css({'background-image':bg});
		}else{
			$(".wrapper_bk,#wrapper,.container_con").css('background-image','');
		}
		re!=1&&hasbg?$(".wrapper_bk,#wrapper,.container_con").css('background-image','none'):'';
		$('body').css({'background-repeat':repeat,'background-attachment':scroll,'background-position':position+' top'});
		$("#bGround").slideUp();
	},'text');
}
/**
 * ������ԭ
 * @param obj
 */
function change_default() {
	$.getJSON(SITEURL + "/index.php?do=space&ac=custom&t=bground&rever=change&member_id="+uid,
			function(data) {
				if (data.status == 1) {
					$("#bgsrc").attr('src',bgimg);
					savePos('',1);
					hasbg=0;
					$('body').css({'background-image':''});
					$("#bGround").slideUp();
					$("#sitebg").remove();
				} else {
					showDialog(data.msg, 'alert', L.operate_notice);
				}
				return false;
			});
}
/**
ҳ��ˢ��
*/
function winLoad(){
	window.location.reload();
}