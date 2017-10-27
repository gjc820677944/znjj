/**
 * Created by Administrator on 2017/10/25.
 */
/**
 * ajax确认请求
 * @param string url
 * @param string msg 请求提示
 */
function ajaxConfirm(url, msg){
    layer.confirm(msg, {
        btn: ['确认','取消'] //按钮
    }, function () {
        $.ajax({
            type: 'get',
            url: url,
            data: '',
            cache: false,
            async: false,
            dataType: 'json',
            success: function(result){
                var code = result.code;
                var msg = result.msg;
                var data = result.data;
                if(code>0){
                    layer.alert(msg);
                }
                else{
                    location.reload();
                }
            }
        });
    });
    return false;
}

/**
 * 更新单条数据的属性
 * @param int id 数据ID
 * @param string field 更新的字段
 * @param string val 更新后的值
 * @param string url url
 */
function changeAttr(id, field, val, url) {
    var data = "id="+id+"&field="+field+"&val="+val;
    $.ajax({
        type: 'get',
        url: url,
        data: data,
        cache: false,
        async: false,
        dataType: 'json',
        success: function(result){
            var code = result.code;
            var msg = result.msg;
            var data = result.data;
            if(code>0){
                layer.alert(msg);
            }
        }
    });
}