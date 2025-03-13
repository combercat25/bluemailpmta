var Mail = function () 
{
    ////////////// General Section ////////////

    // Disable Form Submit by click on a text filed
    var DisableSubmitTextClickEvent = function () 
    {
        $("input:text").on('keypress',function(e){
            if (e.keyCode == 13) {return false;}
        });
    };

    // HELP : Placeholders Display
    var handlePlaceHoldersHelpDisplayEvent = function () 
    {
        $("#show-placeholders-help").click(function(evt)
        {
            evt.preventDefault();
            
            // empty old results
            $("#modal-dialog .modal-title").html('');
            $("#modal-dialog .modal-body").html('');
            $("#modal-dialog .modal-footer").html('<a data-dismiss="modal" class="btn green" href="javascript:;">Close</a>'); 
            
            // fill the modal 
            $("#modal-dialog .modal-title").html('PlaceHolders Help');
            $("#modal-dialog .modal-body").html(atob($("#place-holders-help-html").val())); 
        });
    };
    
    ////////////// Servers Section ////////////
    
    // Refresh Servers List
    var handleServersRefreshEvent = function () 
    {
        $('#refresh-servers').click(function(evt) 
        {    
            evt.preventDefault();
            Bluemail.blockUI();
            
            //$("#servers").selectpicker('val',null);
            
            // clean the previous ips
            $("#available-ips").html('');
            $("#selected-ips").html('');
            
            $("#pmta-links").html('<li><a href="javascript:;"> No Servers Selected</a></li>');
            $("#drops-links").html('<li><a href="javascript:;"> No Servers Selected</a></li>');

            $.ajax({
                type: 'post',
                url: Bluemail.getBaseURL() + "/mail/getServers.json",
                data :  {},
                async: true,
                dataType : 'json',
                success:function(result) 
                {
                    if(result !== null)
                    {
                        $("#servers").html('');
                        //$("#vmta-servers").html('');
                        
                        var providers = result['providers'];
                        var servers = result['servers'];

                        for (var i in providers)
                        {
                            var provider = providers[i];
                            
                            //$("#servers").append('<optgroup label="'+provider['name']+'">');
                            
                            for (var j in servers)
                            {
                                var server = servers[j];
                                
                                if(server['provider_id'] == provider['id'])
                                {
                                    $("#servers").append('<option value="'+server['id']+'">'+server['name']+'</option>');
                                }
                            }
                        }
                        
                        //$("#servers").selectpicker('refresh');
                      //  $("#vmta-servers").selectpicker('refresh');
                        Bluemail.unblockUI();
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) 
                {
                    Bluemail.alertBox({title:textStatus + ' : ' + errorThrown,type:"error",allowOutsideClick:"true",confirmButtonClass:"btn-danger"});
                }
            });

        });
    };
    
    // Servers Change 
    var handleServersChangeEvent = function () 
    {
        $('#servers').on('change',function(e)
        {
            e.preventDefault();

            // clean the previous vmtas
            $("#available-vmtas").html('');
            $("#selected-vmtas").html('');
            $('#available-vmtas-sum').html('(0 VMTA Selected)');
            $('#selected-vmtas-sum').html('(0 VMTA Selected)');
            
            $("#pmta-links").html('<li><a href="javascript:;"> No Servers Selected</a></li>');
            $("#drops-links").html('<li><a href="javascript:;"> No Servers Selected</a></li>');
            
            var serverId = String($("#servers").val()).replace(/\,/g,'/');

            if(serverId != undefined && serverId != null && parseInt(serverId) != NaN && serverId != 'null' && serverId != '')
            {   
                Bluemail.blockUI();
                
                $("#available-vmtas").html('<option value="">Please Wait ...</option>');
                
                $.ajax({
                    type: 'post',
                    url: Bluemail.getBaseURL() + "/mail/getVmtas/"+serverId+".json",
                    data :  {},
                    dataType : 'json',
                    async: false,
                    success:function(result) 
                    {
                        if(result !== null)
                        {
                            $("#available-vmtas").html('');
                            
                            var servers = result['servers'];
                            var vmtas = result['vmtas'];
                            
                            for (var i in vmtas)
                            {
                                var value = vmtas[i];
                                $("#available-vmtas").append('<option value="'+value['server_id']+'|'+value['id']+'" title="'+value['server']+' | '+value['name']+' | '+value['ip_value']+' | '+value['domain']+'" >'+value['server']+' | '+value['name']+' | '+value['ip_value']+' | '+value['domain']+'</option>');
                            }
                            
                            $("#pmta-links").html('');
                            $("#drops-links").html('<li><a href="' + Bluemail.getBaseURL() + '/drops/lists.html" target="drops_all"> All Servers </a></li>');
                                
                            for (var i in servers)
                            {
                                var server = servers[i];
                                
                                $("#pmta-links").append('<li><a href="http://' + server['main_ip'] + ':' + $('#pmta-port').val() + '" target="pmta_' + server['id'] + '"> ' + server['name'] + ' </a></li>');
                                $("#drops-links").append('<li><a href="' + Bluemail.getBaseURL() + '/drops/lists/' + server['id'] + '.html" target="drops_' + server['id'] + '"> ' + server['name'] + ' </a></li>');
                            }
                            
                            $('#available-vmtas').change();
                            $('#selected-vmtas').change();
                
                            Bluemail.unblockUI();
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) 
                    {
                        Bluemail.alertBox({title:textStatus + ' : ' + errorThrown,type:"error",allowOutsideClick:"true",confirmButtonClass:"btn-danger"});
                    }
                });
            }
        });
    };
       
    // Selecting or Deselcting VMTAS event
    var handleVmtasChangeEvent = function () 
    {
        $('.select-all-options').click(function(e){
            e.preventDefault();
            var target = $(this).attr('data-target');
            var values = $("#"+target+">option").map(function() { return $(this).val(); });
            $("#"+target).val(values);
            $('#available-vmtas').change();
            $('#selected-vmtas').change();
            $('#'+target+'-sum').html('(' + values.length + ' VMTA Selected)');
        });
        
        $('.deselect-all-options').click(function(e){
            e.preventDefault();
            var target = $(this).attr('data-target');
            $("#"+target).val(null);
            $('#available-vmtas').change();
            $('#selected-vmtas').change();
            $('#'+target+'-sum').html('(0 VMTA Selected)');
        });
        
        // selecting event 
        $('#vmtas-selector').click(function(evt) 
        {    
            evt.preventDefault();  
            $('#available-vmtas option:selected').remove().appendTo('#selected-vmtas');
            $('#available-vmtas').change();
            $('#selected-vmtas').change();
            $('#available-vmtas-sum').html('(0 VMTA Selected)');
        });
        
        // deselecting event
        $('#vmtas-deselector').click(function(evt) 
        {    
            evt.preventDefault();
            $('#selected-vmtas option:selected').remove().appendTo('#available-vmtas');
            $('#available-vmtas').change();
            $('#selected-vmtas').change();
            $('#selected-vmtas-sum').html('(0 VMTA Selected)');
        });
        
        $('#available-vmtas,#selected-vmtas').on('change',function() 
        {
            var id = $(this).attr('id');
            var values = $("#"+id+">option:selected").map(function() { return $(this).val(); });
            $('#'+id+'-sum').html('(' + values.length + ' VMTA Selected)');
        });
        
        $('#vmtas-emails-proccess').on('change',function() 
        {
            var value = $(this).val();
            
            if(value == 'vmtas-rotation')
            {
                $("#number-of-emails").attr('disabled','true');
                $("#emails-period-value").attr('disabled','true');
                $("#emails-period-type").attr('disabled','true').selectpicker('refresh');
               // $("#vmtas-rotation").removeAttr('disabled');
                $("#x-delay").removeAttr('disabled');
                $("#batch").removeAttr('disabled');
            }
            else
            {
                $("#number-of-emails").removeAttr('disabled');
                $("#emails-period-value").removeAttr('disabled');
                $("#emails-period-type").removeAttr('disabled').selectpicker('refresh');
                //$("#vmtas-rotation").attr('disabled','true');
                $("#x-delay").attr('disabled','true');
                $("#batch").attr('disabled','true');
            }
        });
    };

    // VMTAs Settings Event
    var handleVmtasSettingsEvent = function ()
    {
        $('#vmtas-settings').click(function(evt)
        {
            evt.preventDefault();
            
            // empty old results
            $("#modal-dialog .modal-title").html('');
            $("#modal-dialog .modal-body").html(''); 
            $("#modal-dialog .modal-footer").html('<a data-dismiss="modal" class="btn green" href="javascript:;">Close</a>'); 
            
            var count = $('#selected-vmtas option:selected').length;
            
            if(count == 0)
            {
                Bluemail.alertBox({title:"No VMTAs Selected !",type:"error",allowOutsideClick:"true",confirmButtonClass:"btn-danger"});
                return false;
            }
            else
            {
                var index = 0;
                var html = '';
                var percentage = 100 / count;
                
                $('#selected-vmtas option:selected').each(function(){
                    var ip = $(this).html().split('|')[1].trim();
                    var border = index < count-1 ? 'style="border-bottom: 1px solid #E8E8E8;"' : '';
                    html += '<div class="row" ' + border + '> <div class="col-md-2"> <div class="form-group"><label class="control-label" style="padding-top:20px">' + ip + '</label></div> </div> <div class="col-md-10"> <div class="col-md-3"> <div class="form-group"> <label class="control-label">Number Of Emails</label> <input type="text" class="form-control ip-emails-number" value="1"> </div> </div> <div class="col-md-3"> <div class="form-group"> <label class="control-label">Time Period Value</label> <input type="text" class="form-control ip-emails-period-value" value="1" > </div> </div> <div class="col-md-3"> <div class="form-group"> <label class="control-label">Time Period Unit</label> <select class="form-control ip-emails-period-type"> <option value="seconds" selected="true">Seconds</option> <option value="minutes">Minutes</option> <option value="hours">Hours</option> </select> </div> </div> <div class="col-md-3"> <div class="form-group"> <label class="control-label">Emails Percentage</label> <input type="text" class="form-control ip-data-percentage" value="' + percentage + '" ></div> </div> </div> </div>';
                    index++;
                });
                
                $("#modal-dialog .modal-title").html('VMTAs Settings');
                $("#modal-dialog .modal-body").html(html); 
                $("#modal-dialog .modal-footer").prepend('<a data-dismiss="modal" id="save-ip-settings" class="btn green" href="javascript:;">Save Settings</a>'); 
                
                $('#save-ip-settings').on('click',function(){
                    Bluemail.alertBox({title:"VMTAs Settings Saved Successfully !",type:"success",allowOutsideClick:"true",confirmButtonClass:"btn-primary"});
                    $('#modal-dialog').modal('dismiss');
                    $("#modal-dialog .modal-footer").html('<a data-dismiss="modal" class="btn" href="javascript:;">Close</a>');
                });
            }
        });
    }
    
    // VMTAs Select By Textarea Event
    var handleSelectVMTAsTextAreaEvent = function ()
    {
        $('#vmtas-selector-text').click(function(e)
        {
            e.preventDefault();
            
            var vmtas = $("#vmtas-to-select").val();
           
            if(vmtas != null && vmtas != '')
            {
                vmtas = btoa(vmtas.split("\n").join(","));

                $.ajax({
                    url : Bluemail.getBaseURL() + "/mail/getVmtasText.json",
                    type: "POST",
                    data:
                    { 
                        vmtas : vmtas 
                    },
                    dataType: "JSON",
                    success:function(result) 
                    {
                        if(result !== null)
                        {
                            var servers = result['servers'];
                            var vmtas = result['vmtas'];
                            
                            $('#servers').val(servers).change(); 
                            
                            for (var i in vmtas)
                            {
                                $('#available-vmtas option').each(function()
                                {
                                    if($(this).html().split('|')[2].trim() == vmtas[i].trim())
                                    {
                                        $(this).prop('selected',true);
                                    }
                                });
                            }
                            
                            $('#available-vmtas').change();
                            $('#vmtas-selector').click();
                        }  
                    },
                    
                    error: function(jqXHR, textStatus, errorThrown) 
                    {
                        Bluemail.alertBox({title:textStatus + ' : ' + errorThrown,type:"error",allowOutsideClick:"true",confirmButtonClass:"btn-danger"});
                    }
                });
            }
        });
    };
      
    // VMTAs Frequency Switch Event
    var handleVMTAsFrequencySwitchEvent = function ()
    {
        $("#emails-frequency-switch").on('switchChange.bootstrapSwitch', function(event, state) {
            if(state != false)
            {
                $('#number-of-emails').prop('disabled',true);
                $('#emails-period-type').prop('disabled',true);
                $('#emails-period-value').prop('disabled',true);
            }
            else
            {
                $('#number-of-emails').removeAttr('disabled');
                $('#emails-period-type').removeAttr('disabled');
                $('#emails-period-value').removeAttr('disabled');
            }
        });
    };
    
    ////////////// Content Section ( sponsors , offers , creatives ..... ) ////////////
    
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
            $("#lists").html('');
            $("#sub-lists").html('');
            $('#emails-per-seeds').val(1).prop('disabled',true);
            
            // reset data count
            reloadDataLists();
            resetDataCount();

            if($('#sponsors').val() !== undefined && $('#sponsors').val() !== '')
            {    
                Bluemail.blockUI();

                var sponsorId = $('#sponsors').val();
         
                $.ajax({
                    type: 'post',
                    url: Bluemail.getBaseURL() + "/mail/getOffers/"+sponsorId+".json",
                    data :  {},
                    dataType : 'json',
                    async: false,
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
        
        $('#sponsors2').change(function() 
        {
            // clean the last results
            $("#creatives2").html('').selectpicker('refresh');
            $("#offers2").html('').selectpicker('refresh');

            if($('#sponsors2').val() !== undefined && $('#sponsors2').val() !== '')
            {    
                Bluemail.blockUI();

                var sponsorId = $('#sponsors2').val();
         
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
                                $("#offers2").append('<option value="'+value['id']+'">(' + value['production_id'] + ') '+ value['flag'] +' - '+value['name']+'</option>');
                            }
                            
                            // update the dropdown
                            $("#offers2").selectpicker('refresh');
                            
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
    
    // Offers Change 
    var handleOffersChangeEvent = function () 
    {
        $('#offers').change(function() 
        {
            // clean the last results
            $("#creatives").html('').selectpicker('refresh');
            $("#from-names").html('').selectpicker('refresh');
            $("#subjects").html('').selectpicker('refresh');
            $('#generate-links').attr('offer-id','0');
            $("#drop-body").val('');
            $("#lists").html('');
            $("#sub-lists").html('');
            $('#emails-per-seeds').val(1).prop('disabled',true);
            
            // reset data count
            reloadDataLists();
            resetDataCount();

            if($('#offers').val() !== undefined && $('#offers').val() !== '')
            {    
                Bluemail.blockUI();

                var offerId = $('#offers').val();
                
                $.ajax({
                    type: 'post',
                    url: Bluemail.getBaseURL() + "/mail/getOfferAssets/"+offerId+".json",
                    data :  {},
                    dataType : 'json',
                    async: false,
                    success:function(result) 
                    {
                        if(result !== null)
                        {
                            var lists = result['from-names'];

                            for (var i in lists)
                            {
                                var value = lists[i];
                                
                                if(value['value'].trim().toLowerCase().includes('list name') || value['value'].trim().toLowerCase().includes('listname'))
                                {
                                    value['value'] = '[EMAIL_NAME]';
                                }
                                
                                $("#from-names").append('<option value="'+value['id']+'">' + value['value'] +'</option>');
                            }
                            
                            var lists = result['subjects'];

                            for (var i in lists)
                            {
                                var value = lists[i];
                                $("#subjects").append('<option value="'+value['id']+'">' + value['value'] +'</option>');
                            }
                            
                            var lists = result['creatives'];

                            for (var i in lists)
                            {
                                var value = lists[i];
                                $("#creatives").append('<option value="'+value['id']+'">creative_' + value['id'] +'</option>');
                            }

                            $("#creatives").selectpicker('refresh');
                            $("#from-names").selectpicker('refresh');
                            $("#subjects").selectpicker('refresh');
                            $('#generate-links').attr('offer-id',$('#offers').val());
                            
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
        
        $('#offers2').change(function() 
        {
            // clean the last results
            $("#creatives2").html('').selectpicker('refresh');

            if($('#offers2').val() !== undefined && $('#offers2').val() !== '')
            {    
                Bluemail.blockUI();

                var offerId = $('#offers2').val();
                
                $.ajax({
                    type: 'post',
                    url: Bluemail.getBaseURL() + "/mail/getOfferCreatives/"+offerId+".json",
                    data :  {},
                    dataType : 'json',
                    success:function(result) 
                    {
                        if(result !== null)
                        {
                            var lists = result['creatives'];

                            for (var i in lists)
                            {
                                var value = lists[i];
                                $("#creatives2").append('<option value="'+value['id']+'">creative_' + value['id'] +'</option>');
                            }

                            $("#creatives2").selectpicker('refresh');
                            
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
    
    // FromNames And Subjects Change 
    var handleFromNamesAndSubjectsChangeEvent = function () 
    {
        // subject or fromname changed
        $("#subjects,#from-names").on('change',function()
        {
            var encoder = $(".encoding[target='" + $(this).attr('id') + "']").first();
            var status = $('.toggle-from-subject[data-target=' + $(this).attr('id') + ']').attr('data-current-status');
            encode($(this),encoder,status);
        });
        
        // subject or fromname changed
        $("#subjects-text,#from-names-text").on('keyup',function()
        {
            var encoder = $(".encoding[target='" + $(this).attr('id').replace('-text','') + "']");
            var status = $('.toggle-from-subject[data-target=' + $(this).attr('id').replace('-text','') + ']').attr('data-current-status');
            encode($('#' + $(this).attr('id').replace('-text','')),encoder,status);
        });
        
        // encoder select changed
        $(".encoding").on('change',function(){  
            var target = $("#" + $(this).attr('target'));
            var status = $('.toggle-from-subject[data-target=' + $(this).attr('target') + ']').attr('data-current-status');
            encode($(this).attr('target'),target,$(this),status);
        });

        var encode = function(id,target,encoder,status)
        {
            if(encoder != null && encoder != undefined && encoder instanceof jQuery)
            {
                var type = encoder.val();
                var value = (status == 'select') ? $("option:selected",target).html().replace(/(\r\n|\n|\r)/gm,"") : $("#" + target.attr('id') + '-text').val();
                var convertedValue = value;

                if(value != "[EMAIL_NAME]")
                {
                    if(type == 'plain')
                    {
                        convertedValue = "=?UTF-8?Q?" + value +  "?=";
                    }
                    else if(type == 'b64')
                    {
                        convertedValue = convertedValue = "=?UTF-8?B?" + btoa(value) +  "?=";
                    }
                    else if(type == 'uni')
                    {
                        convertedValue = "=?UTF-8?Q?=" + Bluemail.encodeToUnicode(value).replace(" ",'=') +  "?=";
                    }
                }

                if(status == 'select')
                {
                    $('#' + id).siblings('.btn-group').first().css('display','none').hide();
                    $('#' + id + '-text').removeClass('hide').css('display','block').show().val(convertedValue);
                    $('.toggle-from-subject[data-target=' + id + ']').attr('data-current-status','text');
                }
                else
                {
                    $('#' + id + '-text').val(convertedValue);
                }   
            } 
        }
    };
    
    // FromNames And Subjects Switch Change 
    var handleFromNamesAndSubjectsSwitchEvent = function () 
    {
        $('.toggle-from-subject').on('click',function(){
            var target = $(this).attr('data-target');
            var status = $(this).attr('data-current-status');

            if(status == 'select')
            {
                $('#' + target).siblings('.btn-group').first().css('display','none').hide();
                var value = $('#' + target + ' option:selected').text() == $('#' + target).attr('title') ? '' : $('#' + target + ' option:selected').text();
                $('#' + target + '-text').removeClass('hide').css('display','block').show().val(value);
                $(this).attr('data-current-status','text');
            }
            else
            {
                $('#' + target + '-text').addClass('hide').css('display','none').hide().val('');
                $('#' + target).siblings('.btn-group').first().css('display','block').show();
                $(this).attr('data-current-status','select');
                $('#' + target).change();
            }
        });
    }
    
    // Creatives Change
    var handleCreativesChangeEvent = function () 
    {
        $('#creatives').change(function() 
        {
            if($('#creatives').val() !== undefined && $('#creatives').val() !== '')
            {    
                // clean the last results
                $("#drop-body").val('');
                
                var creativeId = $('#creatives').val();
                
                $.ajax({
                    type: 'post',
                    url: Bluemail.getBaseURL() + "/mail/getCreative/" + creativeId + ".json",
                    data :  { },
                    dataType : 'JSON',
                    success:function(result) 
                    {
                        if(result !== null)
                        {
                            var creative = result['creative'];
                        
                            if(creative != '')
                            {
                                $("#drop-body").val(creative);
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
    
    // Message Creative HTML Display
    var handleCreativeDisplayEvent = function ()
    {
        $("#show-body-as-html").click(function(evt)
        {
            evt.preventDefault();
            var w = window.open();
            $(w.document.body).html('<center>' + $("#drop-body").val() + '</center>');
        });
    };
    
    // handle Generate Links Event
    var handleGenerateLinksEvent = function () 
    {
        $("#generate-links").click(function(evt)
        {
            evt.preventDefault();
            
            var offerId = $(this).attr('offer-id');
            
            if(offerId != undefined && offerId > 0)
            {
                $.ajax({
                    type: 'post',
                    url: Bluemail.getBaseURL() + "/mail/generateLinks/" + offerId + ".json",
                    data :  {},
                    dataType : 'json',
                    success:function(result) 
                    {
                        if(result !== null)
                        {
                            var links = result['links'];

                            // empty old results
                            $("#modal-dialog .modal-title").html('');
                            $("#modal-dialog .modal-body").html(''); 
                            $("#modal-dialog .modal-footer").html('<a data-dismiss="modal" class="btn green" href="javascript:;">Close</a>'); 

                            // fill the modal 
                            $("#modal-dialog .modal-title").html('Generated Links');
                            $("#modal-dialog .modal-body").html('<center>' + links + '</center>'); 
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) 
                    {
                        Bluemail.alertBox({title:textStatus + ' : ' + errorThrown,type:"error",allowOutsideClick:"true",confirmButtonClass:"btn-danger"});
                        return false;
                    }
                });
            }
            else
            {
                swal("Error!", "Please Select an Offer !", "error");
                return false;
            }
        });
    };
    
    var handleHeaderReset = function () 
    {
        $("#reset-header").click(function(evt)
        {
            evt.preventDefault();
            
            // confirm the action
            swal({
                title: "Are you sure?",
                text: "Your will reset your current header values !",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-danger",
                confirmButtonText: "Yes, reset it!",
                closeOnConfirm: false
              },
              function(){
                  $("#header").val(atob($("#header").attr('data-original-header'))); 
                    swal("Completed!", "Your header has been reseted.", "success");
              });
        });
        
         $("#reset-headers").click(function(evt)
        {
            evt.preventDefault();
            
            // confirm the action
            swal({
                title: "Are you sure?",
                text: "Your will reset your current header values !",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-danger",
                confirmButtonText: "Yes, reset it!",
                closeOnConfirm: false
              },
              function(){
                    $( ".nav-tabs li" ).each(function( index ) {
                        index = index + 1 ;
                     // console.log( index + ": " + $( this ).text() );
                      if(index != 1)
                      {
                          console.log('Removieg tab index -> ' + index);
                         $('#tab'+index).remove();   
                         $('a[href="#tab'+index+'"]').remove();
                      }
                    });
//                    $('#tab3').remove();
//                    $('a[href="#tab3"]').remove();
                    $('.nav nav-tabs').tabs( "refresh" );
                    swal("Completed!", "Your header has been reseted.", "success");
              });
        });
    };
    
    var handleUploadHerders = function ()
    {
        $("#upload-button").click(function(evt){
            evt.preventDefault();
            $("#upload-headers").click();
        });
       
        $("#remove-header").click(function(evt){
            evt.preventDefault();
            alert($(".tabbable .tabbable-tabdrop").tabs('option', 'active').val());
            //var headerTabs = $('#header-tabs').find(".ui-tabs-nav li:eq(1)").remove();
              //               $("#header-tabs").tabs("refresh");
        });
        
        $('#upload-headers').on('change', function()
        {
            var currentIndex = $('.nav-tabs').length + $('.tabdrop li').length;

            for (var i = 0; i < $(this).get(0).files.length; ++i)
            {
                var reader = new FileReader();
            
                reader.onload = function(event) 
                {
                    currentIndex++;
                    var content = event.target.result;
                    var li = $('<li/>');
                    var link = $('<a/>');
                    link.attr('href','#tab' + currentIndex).attr('data-toggle','tab').html('Header ' + currentIndex);
                    li.prepend(link);
                    li.appendTo('.tabbable .nav-tabs');
                    var tabPane = $('<div/>');
                    tabPane.addClass('tab-pane');
                    tabPane.attr('id','tab' + currentIndex);
                    tabPane.html('<textarea class="form-control" style="height: 178px;" name="headers[]" data-widearea="enable" spellcheck="false" wrap="off" data-original-header="">' + escapeHtml(content) + '</textarea>');
                    tabPane.appendTo('.tabbable .tab-content');
                };
                
                reader.readAsText($(this).get(0).files[i]);  
            } 
            return false;
        });
        return false;
    }; 
    
    var entityMap = {'&': '&amp;','<': '&lt;','>': '&gt;','"': '&quot;',"'": '&#39;','/': '&#x2F;','`': '&#x60;','=': '&#x3D;'};

    function escapeHtml (string) {
      return String(string).replace(/[&<>"'`=\/]/g, function (s) {
        return entityMap[s];
      });
    }
    
    var handleHeaderChange = function () 
    {
        $("#predefined-headers").change(function(evt)
        {
            evt.preventDefault();
            $('#header').val(atob($(this).val()));
        });
    };
    
    // Negative Upload Event
    var handleNegativeUploadEvent = function () 
    {
        // add click event to upload button
        $("#upload-negative").click(function(evt)
        {
            evt.preventDefault();
            $('#negative-file').click(); 
        });
        
        // add change event to file input
        $('#negative-file').on("change",function()
        {
            var size = Bluemail.formatBytes(this.files[0].size,2);
            var value = $(this).val() != '' && $(this).val() != undefined ? '[ Negative File : ' + $(this).val() + ' ( ' + size + ' ) ]' : '';
            $('#negative-file-name').html(value).show();
            $('#negative-remove').show();
            $("#upload-negative").hide();
        });
        
        // add click event to remove button
        $('#negative-remove').on("click",function(evt)
        {
            evt.preventDefault();   
            
            $('#negative-file').val(null);
            $('#negative-file-name').html('').hide();
            $('#negative-remove').hide();
            $("#upload-negative").show();
        });
    };
    
    var handleVerticalsSelectDeselectAll = function()
    {
        $('.select-all-verticals').click(function(e){
            e.preventDefault();
            var values = $("#verticals>option").map(function() { return $(this).val(); });
            $("#verticals").val(values);
        });
        
        $('.deselect-all-verticals').click(function(e){
            e.preventDefault();
            $("#verticals").val(null);
        });  
    };
    
  
    ////////////// Data Lists Section ////////////
    
    // ISP and FLAG Change
    var handleISPAndCountryChangeEvent = function () 
    {
        $('#isp,#country').change(function()
        {
            // block the ui
            Bluemail.blockUI({target:"#lists"});
            Bluemail.blockUI({target:"#sub-lists"});
            
            $("#lists").html('');
            $("#sub-lists").html('');
            $('#emails-per-seeds').val(1).prop('disabled',true);
            
            // reset data count
            resetDataCount();
            
            // unselect every chekcbox 
            $('#data-types .list-type-checkbox').each(function(){
                if ($(this).prop("checked") == true)
                {
                    $(this).prop("checked",false).closest('span').removeClass('checked');
                }
            });
            
            // unblock the ui
            Bluemail.unblockUI("#lists");
            Bluemail.unblockUI("#sub-lists");
        });
    };
    
    // Data Types Click
    var handleDataTypesClickEvent = function () 
    {
        $('#data-types .list-type-checkbox').change(function() 
        {
            var type = $(this).attr('data-type');
            
            if ($(this).prop("checked") == true)
            {
                if(type == 'seeds')
                {
                     $('#emails-per-seeds').val(1).prop('disabled',false).removeAttr('disabled');
                }
                
                var ispId = $('#isp').val();
            
                if(ispId == undefined || ispId == '')
                {
                    Bluemail.alertBox({title:'Please Select an ISP !',type:"error",allowOutsideClick:"true",confirmButtonClass:"btn-danger"});
                    return false;
                }

                if($(this).val() !== undefined && $(this).val() !== '')
                {    
                    // block the ui
                    Bluemail.blockUI({target:"#lists"});
                    Bluemail.blockUI({target:"#sub-lists"});

                    var typeId = $(this).val();
                    var country = $('#country').val();
                    var offer = $('#offers').val() !== undefined && $('#offers').val() !== '' ? $('#offers').val() : 0;

                    $.ajax({
                        type: 'post',
                        url: Bluemail.getBaseURL() + "/mail/getDataLists/"+typeId+"/"+ispId+"/"+country+"/"+offer+".json",
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
                                    var color =  (value['remain'] - value['count'] < 0) ? 'class="font-red"' : '';
                                    $("#lists").append('<div class="list-row-checkboxes"><input class="list-checkbox" type="checkbox" value="' + value['id']+ '" data-list="' + value['name'] + '" /><span class="checkbox-label">' + value['name'] + ' ( Count : ' + value['count'] + ' , <span ' + color + ' >Left :  ' + value['remain'] + ' </span> ) </span></div>');
                                }

                                $("#lists input[type='checkbox']").uniform();

                                // unblock the ui
                                Bluemail.unblockUI("#lists");
                                Bluemail.unblockUI("#sub-lists");

                                // add lists change event
                                handleDataListClickEvent();
                            }
                        },
                        error: function (jqXHR, textStatus, errorThrown) 
                        {
                            Bluemail.alertBox({title:textStatus + ' : ' + errorThrown,type:"error",allowOutsideClick:"true",confirmButtonClass:"btn-danger"});
                        }
                    });
                }
            }
            else
            {
                if(type == 'seeds')
                {
                    $('#emails-per-seeds').val(1).prop('disabled',true);
                }
                
                // block the ui
                Bluemail.blockUI({target:"#lists"});
                Bluemail.blockUI({target:"#sub-lists"});
                
                $("#lists .list-row-checkboxes,#sub-lists .list-row-checkboxes").each(function(){    
                    var value = $('.list-checkbox',this).attr('data-list');
       
                    if(value.trim().indexOf(type) > -1)
                    {
                        $(this).remove();
                    }
                }); 

                // unblock the ui
                Bluemail.unblockUI("#sub-lists");
                Bluemail.unblockUI("#lists");
            }
            
        });
    };
    
    // Data Lists Click
    var handleDataListClickEvent = function () 
    {  
        $('#lists .list-checkbox').unbind('change').on('change',function()
        {
            if ($(this).prop("checked") == true)
            {
                // block the ui
                Bluemail.blockUI({target:"#sub-lists"});
                
                var listName = $(this).val();
                var offer = $('#offers').val() !== undefined && $('#offers').val() !== '' ? $('#offers').val() : 0;
                
                if(listName != '')
                {
                    $.ajax({
                        type: 'post',
                        url: Bluemail.getBaseURL() + "/mail/getDataListChunks.json",
                        data :  {
                            'list' : listName,
                            'offer-id' : offer
                        },
                        dataType : 'json',
                        success:function(result) 
                        {
                            if(result !== null)
                            {
                                var lists = result['sub-lists'];

                                for (var i in lists)
                                {
                                    var subList = lists[i];
                                    
                                    if(subList['count'] != 0)
                                    {
                                        var value = subList['value'];
                                        var html = subList['name'] + '_chunk_' + subList['index'] + '  ( Count : ' + subList['count'] + ' )';
                                        $("#sub-lists").append('<div class="list-row-checkboxes"><input class="list-checkbox" type="checkbox" name="lists[]" data-count="' + subList['count'] + '" value="' + value +'" data-list="' + subList['name'] + '" /><span class="checkbox-label">' + html + '</span></div>');
                                    }
                                }

                                $("#sub-lists input[type='checkbox']").uniform();
                                
                                // attach an change event to sub-lists 
                                handleDataSublistsListClickEvent();
                                
                                // unblock the ui
                                Bluemail.unblockUI("#sub-lists");
                            }
                        },
                        error: function (jqXHR, textStatus, errorThrown) 
                        {
                            Bluemail.alertBox({title:textStatus + ' : ' + errorThrown,type:"error",allowOutsideClick:"true",confirmButtonClass:"btn-danger"});
                        }
                    });
                }
            }
            else
            {
                var list = $(this).val().split('.')[1];
                
                // block the ui
                Bluemail.blockUI({target:"#sub-lists"});
                
                $("#sub-lists .list-row-checkboxes").each(function(){    
                     var value = $('.list-checkbox',this).attr('data-list');
                    
                    if(list.trim() == value.trim())
                    {
                        $(this).remove();
                    }
                }); 
            
                // unblock the ui
                Bluemail.unblockUI("#sub-lists");
            }
        });
    };
    
    // Data SubLists Click
    var handleDataSublistsListClickEvent = function ()
    {
        $('#sub-lists .list-checkbox').on('change',function()
        {
            var total = 0;
           
            $("#sub-lists .list-checkbox").each(function()
            {    
                if ($(this).prop("checked") == true)
                {
                    total += parseInt($(this).attr('data-count'));
                }
            }); 
            
            $('#data-count').val(total);
        });
    };
    
    // Data Start Index Change
    var handleDataStartChangeEvent = function ()
    {
        $('#data-start').on('keyup',function()
        {
            
        });
    };
    
    // Data Start Index Change
    var handleDataSubListsSelectAll = function ()
    {
        $('#select-all-lists').on('click',function()
        {
            // select event in sublists 
            $("#sub-lists .list-checkbox").each(function()
            {
                $(this).prop("checked",false);
                $(this).click();
            });
        });
        
        $('#deselect-all-lists').on('click',function()
        {
            // select event in sublists 
            $("#sub-lists .list-checkbox").each(function()
            {
                $(this).prop("checked",true);
                $(this).click();
            });
        });
        
        $('#select-lists').on('click',function()
        {
            // select event in lists 
            $("#lists .list-checkbox").each(function()
            {
                $(this).prop("checked",false);
                $(this).click();
            });
        });
        
        $('#deselect-lists').on('click',function()
        {
            // select event in lists 
            $("#lists .list-checkbox").each(function()
            {
                $(this).prop("checked",true);
                $(this).click();
            });
        });
    };
    
    // Data Emails Per Seeds Change
    var handleEmailsPerSeedsChangeEvent = function()
    {
        $('#emails-per-seeds').keyup(function()
        {          
          
        });
    };
    
    // handle Auto Response Switch
    var handleAutoResponseSwitch = function()
    {
        $('#auto-response').on('change',function()
        {          
            var value = $(this).val();
            
            if(value == 'off')
            {
                $('#auto-response-frequency').val('').prop('disabled',true);
                $('#auto-response-emails').val('').prop('disabled',true);
                $("#random-case-auto-response").prop('disabled',true);
                $("#random-case-auto-response").selectpicker('refresh');
            }
            else
            {
                $('#auto-response-frequency').val('1000').removeAttr('disabled');
                $('#auto-response-emails').val('').removeAttr('disabled');
                $("#random-case-auto-response").removeAttr('disabled');
                $("#random-case-auto-response").selectpicker('refresh');
            }
        });
    };

    // Reset Data Count
    var resetDataCount = function () 
    {
        $('#data-start').val(0);
        $('#data-count').val(0);
    };
    
    // Reset Data Count
    var reloadDataLists = function () 
    {
        $('#data-types .list-type-checkbox').each(function(){
            if ($(this).prop("checked") == true)
            {
                $(this).click();
                $(this).click();
            }
        });
    };
    
    
    ////////////// Form Submit Section ////////////
    
    // Form Submit Buttons Click
    var handleFormSubmitEvent = function () 
    {
        $(".submit-form").click(function(e) 
        {
            e.preventDefault();
            
            var submitButtonName = $(this).attr('send-type');
            
            // add a confirmation to the form
            swal({
                title: "Form Confirmation",
                text: "You're about to procceed a " + submitButtonName,
                type: "info",
                showCancelButton: true,
                closeOnConfirm: false,
                showLoaderOnConfirm: true
            }, 
            function ()
            {
                // get the form data 
                 var formData = new FormData($("#mail-form")[0]);
                 var formURL = $("#mail-form").attr("action");

                 // add submit button
                 formData.append(submitButtonName,'true');

                 $.ajax(
                 {
                    url : formURL,
                    type: "POST",
                    data : formData,
                    cache: false,
                    contentType: false,
                    processData: false,
                    dataType: 'JSON',
                    success : function(data) 
                    {
                       if(data != null)
                       {
                          var button = (data['type'] == 'error') ? 'btn-danger' : 'btn-primary';
                          swal({title:data['message'],type:data['type'],allowOutsideClick:"true",confirmButtonClass:button});
                       }
                    },
                    error: function(jqXHR, textStatus, errorThrown) 
                    {
                       Bluemail.alertBox({title:textStatus + ' : ' + errorThrown,type:"error",allowOutsideClick:"true",confirmButtonClass:"btn-danger"});
                    }
                }); 
            });   
        });
    };
    
    // Message Creative HTML Display
    var handleHeaderDisplayEvent = function () 
    {
        $(".show-header").click(function()
        {
            $("#modal-dialog .modal-body .header-value").val(atob($(this).attr('data-header'))); 
        });
    };
    
    // Message Creative HTML Display
    var handleDraggablePortlets = function () 
    {
        if (!jQuery().sortable) {
            return;
        }

        $("#draggable-container").sortable({
            connectWith: ".portlet",
            items: ".portlet", 
            opacity: 0.8,
            handle : '.portlet-title',
            coneHelperSize: true,
            placeholder: 'portlet-sortable-placeholder',
            forcePlaceholderSize: true,
            tolerance: "pointer",
            helper: "clone",
            tolerance: "pointer",
            forcePlaceholderSize: !0,
            helper: "clone",
            cancel: ".portlet-sortable-empty, .portlet-fullscreen", // cancel dragging if portlet is in fullscreen mode
            revert: 250, // animation in milliseconds
            update: function(b, c) {
                if (c.item.prev().hasClass("portlet-sortable-empty")) {
                    c.item.prev().before(c.item);
                }                    
            }
        });
    };
    
    // Manage VMTAs
    var handleVMTAs = function () 
    {
        $("#save-vmtas").on('click',function(e) 
        {
            e.preventDefault();
            
            var serverId = $("#vmta-servers").val();
            var vmtaName = $("#vmta-name").val();
            var vmtas = $("#vmta-mapping").val();
            var vmtasType = $("#vmta-type").val();
            
            if(serverId == undefined || serverId == null || serverId == 0)
            {
                Bluemail.alertBox({title:"Please Select a Server !",type:"error",allowOutsideClick:"true",confirmButtonClass:"btn-danger"});
            }
            
            if(vmtas == undefined || vmtas == null )
            {
                Bluemail.alertBox({title:"Please Enter New VMTAs !",type:"error",allowOutsideClick:"true",confirmButtonClass:"btn-danger"});
            }
            
            $('#save-vmtas').html("<i class='fa fa-spinner fa-spin'></i> Loading ...");
            $('#save-vmtas').attr('disabled','disabled');
            
            $.ajax(
            {
                type: 'post',
                url: Bluemail.getBaseURL() + "/mail/addVmtas.json",
                data :  {
                    'server-id' : serverId,
                    'vmta-name' : vmtaName,
                    'vmtas' : vmtas,
                    'vmtas-type' : vmtasType
                },
                dataType : 'json',
                success : function(data) 
                {
                    $('#save-vmtas').html('Save');
                    $('#save-vmtas').removeAttr('disabled');
                        
                    if(data != null)
                    {
                        if(data['type'] == 'success')
                        {
                            Bluemail.alertBox({title:data['message'],type:"success",allowOutsideClick:"true",confirmButtonClass:"btn-primary"});
                            $('#vmta-manage').modal('toggle');
                            $("#vmta-name").val(null);
                            $("#vmta-mapping").val(null);
                            $("#servers").change();
                        }
                        else
                        {
                            Bluemail.alertBox({title:data['message'],type:"error",allowOutsideClick:"true",confirmButtonClass:"btn-danger"});
                        } 
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) 
                {
                    $('#save-vmtas').html('Save');
                    $('#save-vmtas').removeAttr('disabled');
                    Bluemail.alertBox({title:textStatus + ' : ' + errorThrown,type:"error",allowOutsideClick:"true",confirmButtonClass:"btn-danger"});
                }
           });  
        });
        
        $("#reset-vmtas").on('click',function(e) 
        {
            e.preventDefault();
            
            var serverId = $("#vmta-servers").val();
            
            if(serverId == undefined || serverId == null || serverId == 0)
            {
                Bluemail.alertBox({title:"Please Select a Server !",type:"error",allowOutsideClick:"true",confirmButtonClass:"btn-danger"});
            }
            
            $('#reset-vmtas').html("<i class='fa fa-spinner fa-spin'></i> Loading ...");
            $('#reset-vmtas').attr('disabled','disabled');
            
            $.ajax(
            {
                type: 'post',
                url: Bluemail.getBaseURL() + "/mail/resetVmtas.json",
                data :  {
                    'server-id' : serverId
                },
                dataType : 'json',
                success : function(data) 
                {
                    $('#reset-vmtas').html('Reset');
                    $('#reset-vmtas').removeAttr('disabled');
                        
                    if(data != null)
                    {
                        if(data['type'] == 'success')
                        {
                            Bluemail.alertBox({title:data['message'],type:"success",allowOutsideClick:"true",confirmButtonClass:"btn-primary"});
                            $('#vmta-manage').modal('toggle');
                            $("#vmta-name").val(null);
                            $("#vmta-mapping").val(null);
                            $("#servers").change();
                        }
                        else
                        {
                            Bluemail.alertBox({title:data['message'],type:"error",allowOutsideClick:"true",confirmButtonClass:"btn-danger"});
                        } 
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) 
                {
                    $('#reset-vmtas').html('Save');
                    $('#reset-vmtas').removeAttr('disabled');
                    Bluemail.alertBox({title:textStatus + ' : ' + errorThrown,type:"error",allowOutsideClick:"true",confirmButtonClass:"btn-danger"});
                }
           });  
        });
        
        $("#delete-vmtas").on('click',function(e) 
        {
            e.preventDefault();
            
            var serverId = $("#vmta-servers").val();
            var vmtaName = $("#vmta-mapping").val();
            
            if(serverId == undefined || serverId == null || serverId == 0)
            {
                Bluemail.alertBox({title:"Please Select a Server !",type:"error",allowOutsideClick:"true",confirmButtonClass:"btn-danger"});
            }
            
            $('#delete-vmtas').html("<i class='fa fa-spinner fa-spin'></i> Loading ...");
            $('#delete-vmtas').attr('disabled','disabled');
            
            $.ajax(
            {
                type: 'post',
                url: Bluemail.getBaseURL() + "/mail/deleteVmtas.json",
                data :  {
                    'server-id' : serverId ,
                    'vmta-name' : vmtaName
                },
                dataType : 'json',
                success : function(data) 
                {
                    $('#delete-vmtas').html('Delete vmta');
                    $('#delete-vmtas').removeAttr('disabled');
                        
                    if(data != null)
                    {
                        if(data['type'] == 'success')
                        {
                            Bluemail.alertBox({title:data['message'],type:"success",allowOutsideClick:"true",confirmButtonClass:"btn-primary"});
                            $('#vmta-manage').modal('toggle');
                            $("#vmta-name").val(null);
                            $("#vmta-mapping").val(null);
                            $("#servers").change();
                        }
                        else
                        {
                            Bluemail.alertBox({title:data['message'],type:"error",allowOutsideClick:"true",confirmButtonClass:"btn-danger"});
                        } 
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) 
                {
                    $('#delete-vmtas').html('Delete vmta');
                    $('#delete-vmtas').removeAttr('disabled');
                    Bluemail.alertBox({title:textStatus + ' : ' + errorThrown,type:"error",allowOutsideClick:"true",confirmButtonClass:"btn-danger"});
                }
           });  
        });
        
       
    };
    
    // Manage VMTAs
    var handleSendDomains = function () 
    {
        $('#SendDomainServer').change(function() 
        {
            serverId = $(this).val();
            $("#serverDomains").empty();
            
            $.ajax(
            {
                type: 'post',
                url: Bluemail.getBaseURL() + "/servers/getServerDomains.json",
                data :  {
                    'server-id' : serverId,
                },
                dataType : 'json',
                success : function(data) 
                {
                    //$('#serverDomains').val('');
                        
                    if(data != null)
                    {
                        if(data['type'] == 'success')
                        {
                            $("#serverDomains").empty();
                            var domains = data['resault'];
                             
                            for (var i in domains)
                            {
                                var domain = domains[i];
                                console.log(domain['domain']);
                                
                                $("#serverDomains").append('<option value="'+domain['domain']+'"> '+domain['domain']+' </option>');
                            }
                            
                            $("#serverDomains").selectpicker('refresh');
                            
                            
                            
                        }
                        else
                        {
                            Bluemail.alertBox({title:data['message'],type:"error",allowOutsideClick:"true",confirmButtonClass:"btn-danger"});
                        } 
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) 
                {
                    Bluemail.alertBox({title:textStatus + ' : ' + errorThrown,type:"error",allowOutsideClick:"true",confirmButtonClass:"btn-danger"});
                }
           }); 
            
        });
    };
    
    // Manage VMTAs
    var handleResend = function () 
    {
        if($('#resend').val() != null && $('#resend').val() != '')
        {
            var resndDrop = atob($('#resend').val());
            var offset = $('#procces').val();
            if(resndDrop != null && resndDrop != '')
            {

                Drop = jQuery.parseJSON(resndDrop);
                console.log(Drop);
                
                if(Drop !== null){
                    //************* inserting input values **************//
                    /*
                     * Emails Proccess Type Section
                     * Emails , Period , Batch , Vmtas Rotation , X-Delay(MilliS) ** 
                     * From Email , Return Path , Reply To , To , Bounce Email , Received , Header
                     * Body Email , PlaceHolders Rotation , Body PlaceHolders
                     * Test Frequency , Recipients ( Separated By ; ) , Auto Reply Rotation , Auto Reply Emails
                     * Offset , Limit , Emails/Seed
                     */

                    $('#vmtas-emails-proccess').val(Drop['vmtas-emails-proccess']).selectpicker('refresh');
                    $('#vmtas-emails-proccess').change();
                    
                    if(Drop['vmtas-emails-proccess'] !== '' && Drop['vmtas-emails-proccess'] === 'vmtas-rotation')
                    {
                        $('#batch').val(Drop['batch']);
                        $('#vmtas-x-delay').val(Drop['x-delay']);
                    }
                    else
                    {
                        $('#number-of-emails').val(Drop['number-of-emails']);
                        $('#emails-period-value').val(Drop['emails-period-value']);
                    }
                    
                    $('#vmtas-rotation').val(Drop['vmtas-rotation']);
                    $('#from-email').val(Drop['from-email']);
                    $('#return-path').val(Drop['return-path']);
                    $('#reply-to').val(Drop['reply-to']);
                    $('#to').val(Drop['to']);
                    $('#bounce-email').val(Drop['bounce-email']);
                    $('#received').val(Drop['received']);
                    $('#header').val(Drop['headers']);
                    $('#place-holder-rotation').val(Drop['placeholders-rotation']);
                    $('#place-holder').val(Drop['body-placeholders']);
                    $('#test-frequency').val(Drop['send-test-after']);
                    $('#rcpts').val(Drop['recipients-emails']);


                        //************* inserting Drop Down values **************//



                        //************* Servers Area  **************//

                        $('#servers').val(Drop['servers']);
                        $('#servers').change();

                        if(Drop['selected-vmtas'] != null && Drop['selected-vmtas'] != '')
                        {
                            for(var i=0;i<Drop['selected-vmtas'].length;i++)
                            {

                                $("#available-vmtas > option").each(function() 
                                {
                                    var Svmta = this.value;

                                    if(Svmta.toLowerCase().indexOf(Drop['selected-vmtas'][i]) >= 0)
                                    {
                                        $(this).prop('selected', true);
                                    }
                                });
                            }

                            $("#vmtas-selector").click();
                        }

                        //************* Sponsors  Area  **************//

                       $('#sponsors').val(Drop['sponsor']).selectpicker('refresh');
                       $('#sponsors').change();

                       $('#offers').val(Drop['offer']).selectpicker('refresh');
                       $('#offers').change();

                       //$('#creatives').val(Drop['creative']).selectpicker('refresh');
                       //$('#creatives').change();
                       
                       $('#drop-body').val(Drop['body']);

                       $('#from-names').val(Drop['from-name-id']).selectpicker('refresh');
                       $('#from-names').change();

                       $('#subjects').val(Drop['subject-id']).selectpicker('refresh');
                       $('#subjects').change();

                       //************* DATA  Area  **************//

                       $('#isp').val(Drop['isp-id']).selectpicker('refresh');
                       $('#isp').change();

                       $('#country').val(Drop['country']).selectpicker('refresh');
                       $('#country').change();

                       $('#upload-images').val(Drop['upload-images']).selectpicker('refresh');
                       $('#upload-images').change();

                       $('#track-opens').val(Drop['track-opens']).selectpicker('refresh');
                       $('#track-opens').change();
                       
                       $('#data-start').val(offset);
                       $('#data-count').val(Drop['data-count']);

                       // 
                       //var lists = Drop['lists'];
                       //var listType = lists.split('|')[2];
                       //var type = listType.split('.')[1];
                       //type = type.split('_')[0];
                       //alert(type);

                        //$('#data-types').val(type).selectpicker('refresh');
                        //$('#data-types').change();





                    }
            }
        }
    }
    
    // return call
    return { init: function () 
    {
        // general section
        
        DisableSubmitTextClickEvent();
        handlePlaceHoldersHelpDisplayEvent();
        handleDraggablePortlets();
        
        // servers section
        handleServersRefreshEvent();
        handleServersChangeEvent();
        handleVmtasChangeEvent();
        handleVmtasSettingsEvent();
        handleSelectVMTAsTextAreaEvent();
        handleVMTAsFrequencySwitchEvent(); 
        handleVMTAs();
        
        // content section ( sponsors , offers , creatives ..... )
        handleSponsorsChangeEvent();
        handleOffersChangeEvent();
        handleFromNamesAndSubjectsChangeEvent();
        handleFromNamesAndSubjectsSwitchEvent();
        handleCreativesChangeEvent();
        handleCreativeDisplayEvent();
        handleGenerateLinksEvent();
        handleHeaderReset();
        handleHeaderChange();
        handleNegativeUploadEvent();
        handleVerticalsSelectDeselectAll();
        handleAutoResponseSwitch();
        handleHeaderDisplayEvent();
        handleUploadHerders();
        handleSendDomains();
        
        // data lists section 
        handleISPAndCountryChangeEvent();
        handleDataTypesClickEvent();
        handleDataStartChangeEvent();
        handleDataSubListsSelectAll();
        handleEmailsPerSeedsChangeEvent();
        
        // form submit section
        handleFormSubmitEvent();
        // 
        handleResend();
        
    }};
}();

// initialize and activate the script
$(function() 
{
   

    Mail.init();
    $(window).on('beforeunload',function(){
      return 'Are you sSure a Si !!';
    });
    wideArea();
    

});