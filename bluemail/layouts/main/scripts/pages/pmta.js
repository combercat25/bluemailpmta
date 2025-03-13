var PMTA = function () 
{
    // Refresh Servers List
    var handleServersRefresh = function () 
    {
        $('#refresh-servers').click(function(evt) 
        {    
            evt.preventDefault();

            $("#servers").html('');
            $("#pmta-links").html('<li><a href="javascript:;"> No Servers Selected</a></li>');
            
            Bluemail.blockUI();
            
            $.ajax({
                type: 'post',
                url: Bluemail.getBaseURL() + "/mail/getServers.json",
                data :  {},
                dataType : 'json',
                success:function(result) 
                {
                    if(result !== null)
                    {
                        $("#servers").html('');
                        
                        var servers = result['servers'];

                        for (var j in servers)
                        {
                            var server = servers[j];
                            $("#servers").append('<option data-main-ip="'+server['main_ip']+'" value="'+server['id']+'">'+server['name']+'</option>'); 
                        }

                        Bluemail.unblockUI();
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) 
                {
                    Bluemail.unblockUI();
                    Bluemail.alertBox({title: textStatus + ' : ' + errorThrown, type: "error", allowOutsideClick: "true", confirmButtonClass: "btn-danger"});
                }
            });

        });
    };

    var handleServersChange = function()
    {
        $('#servers').change(function(){
            $("#pmta-links").html('');
            $(this).find('option:selected').each(function(){
                var id = $(this).val();
                var ip = $(this).attr('data-main-ip');
                var name = $(this).text();
                $("#pmta-links").append('<li><a href="http://' + ip + ':' + $('#pmta-port').val() + '" target="pmta_' + id + '"> ' + name + ' </a></li>');
            });
        });
    };
     
    // Refresh Servers List
    var handleFormSubmit = function () 
    {
        $('.submit-form').click(function(evt) 
        {    
            evt.preventDefault();

            var action = $(this).val();
            var servers = $('#servers').val();

            if(servers == '' || servers == undefined)
            {
                Bluemail.alertBox({title:"Please Select at least one server !",type:"error",allowOutsideClick:"true",confirmButtonClass:"btn-danger"});
                return false;
            }

            Bluemail.blockUI();
            $('#results').html("");
            
            for (var i in servers)
            {
                var data = $('#manage-pmta').serialize() + "&action=" + action + "&server_id=" + servers[i];
                
                $.ajax({
                    type: 'post',
                    url: Bluemail.getBaseURL() + "/pmta/manage.json",
                    data :  data,
                    dataType : 'json',
                    success:function(result) 
                    {
                        if(result !== null)
                        {
                            $('#results').append(result['results']);
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) 
                    {
                        Bluemail.unblockUI();
                        Bluemail.alertBox({title: textStatus + ' : ' + errorThrown, type: "error", allowOutsideClick: "true", confirmButtonClass: "btn-danger"});
                    }
                });
            }
            
            Bluemail.unblockUI();
        });
    };
    
    var handleMonitorsServersChange = function()
    {
        $('#pmta-servers').change(function(){  
            $('#pmtas').html("");
            var index = 0;
            var monitors = '<div class="row">';
     
            $(this).find('option:selected').each(function(){
                
                var ip = $(this).attr('data-main-ip');
                var name = $(this).text();
                var port = $('#pmta-port').val();
                
                if(index > 0 && index % 2 == 0)
                {
                    monitors += '</div><div class="row">';
                }

                monitors += '<div class="col-md-6"><div class="portlet light bordered"> <div class="portlet-title"> <div class="caption"> <i class="icon-equalizer font-blue-dark"></i> <span class="caption-subject font-blue-dark uppercase">' + name + '</span> </div> </div> <div class="portlet-body">'
                monitors += '<iframe src="http://' + ip + ':' + port + '" style="border:none;width:100%;height:400px"></iframe>';
                monitors += '</div></div></div>';
            });
            
            monitors + "</div>";
        
            $('#pmtas').html(monitors);
        });
    };
    
    return {
        init: function () 
        {
            handleServersRefresh();
            handleServersChange();
            handleFormSubmit();
            handleMonitorsServersChange();
        }
    };

}();

jQuery(document).ready(function () {
    PMTA.init(); 
});