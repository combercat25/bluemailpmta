var Data = function () 
{
    // data retreiving change 
    var handleDataRetreivingChange = function () 
    {
        $('#isp,#data-flag').change(function() 
        {
            // clean the last results
            $("#data-count-help").html("Data Count : 0");
            $("#lists").html('<option value="">Select List ....</option>');
                
            var isp = $('#isp').val();
            var flag = $('#data-flag').val();

            if(isp !== undefined && isp !== '' && flag !== undefined && flag !== '')
            {    
                // show loading
                $("#lists").html('<option value="">Please Wait ....</option>');

                $.ajax({
                    type: 'post',
                    url: Bluemail.getBaseURL() + "/data/getDataLists/" + isp + "/" + flag + ".json",
                    data :  {},
                    dataType : 'json',
                    success:function(result) 
                    {
                        if(result !== null)
                        {
                            $("#lists").html('');
                            
                            var lists = result['lists'];

                            for (var i in lists)
                            {
                                var value = lists[i];
                                $("#lists").append('<option value="'+value['id']+'">'+value['name']+'</option>').selectpicker('refresh');
                            }
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) 
                    {
                        Bluemail.alertBox({title: textStatus + ' : ' + errorThrown, type: "error", allowOutsideClick: "true", confirmButtonClass: "btn-danger"});
                    }
                });
            }
        });
    };
    
    // data seeds retreiving change 
    var handleDataSeedsRetreivingChange = function () 
    {
        $('#seeds-isp').change(function() 
        {
            // clean the last results
            $("#data-count-help").html("Data Count : 0");
            $("#lists").html('');
                
            var isp = $('#seeds-isp').val();

            if(isp !== undefined && isp !== '')
            {    
                // show loading
                $("#lists").html('');

                $.ajax({
                    type: 'post',
                    url: Bluemail.getBaseURL() + "/data/getDataSeedsLists/" + isp + ".json",
                    data :  {},
                    dataType : 'json',
                    success:function(result) 
                    {
                        if(result !== null)
                        {
                            var lists = result['lists'];

                            for (var i in lists)
                            {
                                var value = lists[i];
                                $("#lists").append('<option value="'+value['id']+'">'+value['name']+'</option>').selectpicker('refresh'); 
                            }
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) 
                    {
                        Bluemail.alertBox({title: textStatus + ' : ' + errorThrown, type: "error", allowOutsideClick: "true", confirmButtonClass: "btn-danger"});
                    }
                });
            }
        });
        
        // trigger it at the begining 
        $('#seeds-isp').change();
    };
    
    // get the data list count
    var handleDataCount = function () 
    {
        $('#lists').change(function() 
        {
            if($(this).val() !== undefined && $(this).val() !== '')
            {
                $("#data-count-help").html("Please Wait ....");
                
                var listId = $(this).val();
                
                $.ajax({
                    type: 'post',
                    url: Bluemail.getBaseURL() + "/data/getDataListCount.json",
                    data :  {
                        'data-list' : listId
                    },
                    dataType : 'json',
                    success:function(result) 
                    {
                        if(result !== null)
                        {
                            $("#data-count-help").html("Data Count : " + result['count']);
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) 
                    {
                        Bluemail.alertBox({title: textStatus + ' : ' + errorThrown, type: "error", allowOutsideClick: "true", confirmButtonClass: "btn-danger"});
                    }
                });
            }
        });
    };
 
    // get the data seeds emails
    var handleDataSeedsEmails = function () 
    {
        $('#lists').change(function() 
        {
            if($(this).val() !== undefined && $(this).val() !== '')
            { 
                var listName = $(this).val();
                //alert(listName);
                $.ajax({
                    type: 'post',
                    url: Bluemail.getBaseURL() + "/data/getDataListSeedsEmails.json",
                    data :  {'data-list' : listName},
                    dataType : 'json',
                    success:function(result) 
                    {
                        if(result !== null)
                        {
                            $('#emails').val(result['emails']);
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) 
                    {
                        Bluemail.alertBox({title: textStatus + ' : ' + errorThrown, type: "error", allowOutsideClick: "true", confirmButtonClass: "btn-danger"});
                    }
                });
            }
        });
    };
    
    // get the data seeds emails
    var handleDownloadEmails = function () 
    {
        $('.download-data').click(function(e) 
        {
            e.preventDefault();
            var button = $(this);
            var html = $(this).html();
            $(this).html("<i class='fa fa-spinner fa-spin'></i> Loading ...");
            $(this).attr('disabled','disabled');
            var listname = $('#lists').val();
            
            if(listname !== undefined && listname !== '')
            { 
                $.ajax({
                    type: 'post',
                    url: Bluemail.getBaseURL() + "/data/downloadData.json",
                    data :  {
                        'data-list' : listname
                    },
                    dataType : 'json',
                    success:function(result) 
                    {
                        if(result !== null)
                        {
                            var name = result['name'];
                            var content = result['content'];
                            var a         = document.createElement('a');
                            a.href        = 'data:attachment/csv,' +  encodeURIComponent(content);
                            a.target      = '_blank';
                            a.download    = name + '.csv';

                            document.body.appendChild(a);
                            a.click();
                            button.html(html);
                            button.removeAttr('disabled');
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) 
                    {
                        Bluemail.alertBox({title: textStatus + ' : ' + errorThrown, type: "error", allowOutsideClick: "true", confirmButtonClass: "btn-danger"});
                    }
                });
            }
        });
    };
    
    
    // handle data switch
    var handleDataTypeSwitch = function()
    {
        $('#data-type-add').change(function(){
            var value = $(this).val();

            if(value == 'seeds')
            {
                $('#flag').prop('disabled',true)
            }
            else
            {
                $('#flag').prop('disabled',false) 
            }
        });
    };
    
    // update bounce progress
    var handleBounceProccessProgress = function () 
    {
        $(".update-bounce-progress").click(function(evt)
        {
            evt.preventDefault();
            
            var proccessId = $(this).attr('data-proccess-id');
            
            if(proccessId !== undefined && proccessId !== '')
            {
                $("#proccess-progress-" + proccessId).html('<i class="fa fa-spinner fa-spin"></i>');
                $("#proccess-hard-bounce-" + proccessId).html('<i class="fa fa-spinner fa-spin"></i>');
                $("#proccess-clean-" + proccessId).html('<i class="fa fa-spinner fa-spin"></i>');
                
                $.ajax({
                    type: 'post',
                    url: Bluemail.getBaseURL()+"/data/updateBounceProgress/"+proccessId+".json",
                    data :  {},
                    dataType : 'json',
                    success:function(result) 
                    {
                        if(result !== null)
                        {
                            $("#proccess-progress-" + proccessId).html(result['progress']);
                            $("#proccess-hard-bounce-" + proccessId).html(result['hard_bounce']);
                            $("#proccess-clean-" + proccessId).html(result['clean']);
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) 
                    {
                        alert(errorThrown);
                    }
                });
            } 
        });
    };
    
    // Sponsors Change 
    var handleSponsorsChangeEvent = function () 
    {
        $('#sponsors').change(function() 
        {
            // clean the last results
            $("#creatives").html('').selectpicker('refresh');
            $("#from-names").html('').selectpicker('refresh');
            $("#subjects").html('').selectpicker('refresh');
            $('#generate-links').attr('offer-id','0');
            $("#offers").html('').selectpicker('refresh');
            $("#drop-body").val('');
            
            if($('#sponsors').val() !== undefined && $('#sponsors').val() !== '')
            {    
                Bluemail.blockUI();

                var sponsorId = $('#sponsors').val();
         
                $.ajax({
                    type: 'post',
                    url: Bluemail.getBaseURL() + "/mail/getOffers/"+sponsorId+".json",
                    data :  {},
                    dataType : 'json',
                    success:function(result) 
                    {
                        if(result !== null)
                        {
                            var lists = result['offers'];

                            for (var i in lists)
                            {
                                var value = lists[i];
                                $("#offers").append('<option value="'+value['id']+'">(' + value['production_id'] + ') '+ value['flag'] +' - '+value['name']+'</option>');
                            }
                            
                            // update the dropdown
                            $("#offers").selectpicker('refresh');
                            
                            Bluemail.unblockUI();
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) 
                    {
                        Bluemail.unblockUI();
                        Bluemail.alertBox({title:textStatus + ' : ' + errorThrown,type:"error",allowOutsideClick:"true",confirmButtonClass:"btn-danger"});
                    }
                });
            }
        });
    };
    
    // update suppression progress
    var handleSuppression = function () 
    {
        $('#suppression-sponsors').change(function() 
        {
            // clean the last results
            $("#offers").html('').selectpicker('refresh');
            
            if($('#suppression-sponsors').val() !== undefined && $('#suppression-sponsors').val() !== '')
            {    
                Bluemail.blockUI();

                var sponsorId = $('#suppression-sponsors').val();
         
                $.ajax({
                    type: 'post',
                    url: Bluemail.getBaseURL() + "/mail/getOffers/"+sponsorId+".json",
                    data :  {},
                    dataType : 'json',
                    success:function(result) 
                    {
                        if(result !== null)
                        {
                            var lists = result['offers'];

                            for (var i in lists)
                            {
                                var value = lists[i];
                                $("#suppression-offers").append('<option value="'+value['id']+'">(' + value['production_id'] + ') '+ value['flag'] +' - '+value['name']+'</option>');
                            }
                            
                            // update the dropdown
                            $("#suppression-offers").selectpicker('refresh');
                            
                            Bluemail.unblockUI();
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) 
                    {
                        Bluemail.unblockUI();
                        Bluemail.alertBox({title:textStatus + ' : ' + errorThrown,type:"error",allowOutsideClick:"true",confirmButtonClass:"btn-danger"});
                    }
                });
            }
        });

        $('#suppression-offers').change(function() 
        {
            // clean the last results
            $("#suppression-link").val('');
            
            if($('#suppression-offers').val() !== undefined && $('#suppression-offers').val() !== '')
            {    
                Bluemail.blockUI();

                var sponsorId = $('#suppression-sponsors').val();
                var offerId = $('#suppression-offers').val();
                
                $.ajax({
                    type: 'post',
                    url: Bluemail.getBaseURL() + "/data/getSuppressionLink.json",
                    data :  {
                        'sponsor-id' : sponsorId,
                        'offer-id' : offerId
                    },
                    dataType : 'json',
                    success:function(result) 
                    {
                        if(result !== null)
                        {
                            $("#suppression-link").val(result['link']);
                            Bluemail.unblockUI();
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) 
                    {
                        Bluemail.unblockUI();
                        Bluemail.alertBox({title:textStatus + ' : ' + errorThrown,type:"error",allowOutsideClick:"true",confirmButtonClass:"btn-danger"});
                    }
                });
            }
        });

        $(".update-suppression-progress").click(function(evt)
        {
            evt.preventDefault();
            
            var proccessId = $(this).attr('data-proccess-id');
            
            if(proccessId !== undefined && proccessId !== '')
            {
                $("#proccess-progress-" + proccessId).html('<i class="fa fa-spinner fa-spin"></i>');
                $("#proccess-emails-found-" + proccessId).html('<i class="fa fa-spinner fa-spin"></i>');
                
                $.ajax({
                    type: 'post',
                    url: Bluemail.getBaseURL()+"/data/updateSuppressionProgress/"+proccessId+".json",
                    data :  {},
                    dataType : 'json',
                    success:function(result) 
                    {
                        if(result !== null)
                        {
                            $("#proccess-progress-" + proccessId).html(result['progress']);
                            $("#proccess-emails-found-" + proccessId).html(result['emails_found']);
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) 
                    {
                        alert(errorThrown);
                    }
                });
            } 
        });
   
    };
    
    // Handel find emails 
    var handleFindEmailsEvent = function () 
    {
        $("#search-email").click(function(evt)
        {
            evt.preventDefault();
            
            $emails = $('#emails').val();
            
            Bluemail.blockUI();
         
            $.ajax({
                type: 'post',
                url: Bluemail.getBaseURL() + "/data/lists/find/",
                data :  {emails : $emails},
                dataType : 'json',
                
                success:function(result) 
                {
                    if(result !== null)
                    {
                        $('#emails-resalut').val();
                        $contentlobal = ' ';
                        $.each(result.resault, function(index)
                        {
                            $contentlobal = $contentlobal + ' The Email : ' + result.resault[index][0] + ' found on Table : ' + result.resault[index][1] + ' \n With The Excluded Offers : \n';
                            $offers = result.resault[index][2];
                            $.each($offers, function(index2)
                            {
                                $contentlobal = $contentlobal + $offers[index2] + '\n';
                                
                                console.log($offers[index2]);

                            });
                            $contentlobal = $contentlobal + ' \n ================= '+ '\n \n';                   
                            console.log($contentlobal);
                        });
                        
                        $('#emails-resalut').append($contentlobal);
                        //$('#emails-resalut').html($contentlobal);
                        Bluemail.unblockUI();
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) 
                {
                    Bluemail.unblockUI();
                    Bluemail.alertBox({title:textStatus + ' : ' + errorThrown,type:"error",allowOutsideClick:"true",confirmButtonClass:"btn-danger"});
                }
            });
            
        });
        
    };
    return {
        init: function () 
        {
            handleDataRetreivingChange();
            handleDataSeedsRetreivingChange();
            handleDataCount();
            handleDataSeedsEmails();
            handleDataTypeSwitch();
            handleBounceProccessProgress();
            handleSuppression();
            handleSponsorsChangeEvent();
            handleDownloadEmails();
            handleFindEmailsEvent();
        }
    };

}();

// initialize and activate the script
$(function(){ Data.init(); });