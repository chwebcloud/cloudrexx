(function($) {
    cx.ready(function() {
        $('#instance_table').append('<div id ="load-lock"></div>');
        
        $(".defaultCodeBase").change(function() {
            domainUrl = cx.variables.get('baseUrl', 'MultiSite') + cx.variables.get('cadminPath', 'contrexx') + "index.php?cmd=JsonData&object=MultiSite&act=updateDefaultCodeBase";
            cx.jQuery.ajax({
                dataType: "json",
                url: domainUrl,
                data: {
                    defaultCodeBase: $(this).val(),
                },
                type: "POST",
               
                success: function(response) {
                    if (response.data) {
                        cx.tools.StatusMessage.showMessage(response.data, null,2000);
                    }
                }

            });
        });
        $('.changeWebsiteStatus').focus(function() {
//Store old value
            $(this).data('lastValue', $(this).val());
            
            cx.bind("loadingStart", cx.lock, "websitestatus");
            cx.bind("loadingEnd", cx.unlock, "websitestatus");
            //changing dropdown value
        }).change(function() {
            domainUrl = cx.variables.get('baseUrl', 'MultiSite') + cx.variables.get('cadminPath', 'contrexx') + "index.php?cmd=JsonData&object=MultiSite&act=updateWebsiteState";
            var websiteDetails = $(this).attr('data-websiteDetails').split("-");
            if (confirm("Please confirm to change the state of website " + websiteDetails[1] + ' to ' + $(this).val())) {
                cx.trigger("loadingStart", "websitestatus", {});
                cx.tools.StatusMessage.showMessage("<div id=\"loading\">" + cx.jQuery('#loading').html() + "</div>");
                cx.jQuery.ajax({
                    dataType: "json",
                    url: domainUrl,
                    data: {
                        websiteId: websiteDetails[0],
                        status: $(this).val()
                    },
                    type: "POST",
                    success: function(response) {
                        if (response.data) {
                            cx.tools.StatusMessage.showMessage(response.data, null, 2000);
                        }
                        cx.trigger("loadingEnd", "websitestatus", {});
                    }

                });
            }else{
                $(this).val($(this).data('lastValue'));
            }
        });
        /**
         * Locks the website status in order to prevent user input
         */
        cx.lock = function() {
            cx.jQuery("#load-lock").show();
        };
        /**
         * Unlocks the website status in order to allow user input
         */
        cx.unlock = function() {
            cx.jQuery("#load-lock").hide();
        };
        // show license
        $('.showLicense').click(function() {
            var className = $(this).attr('class');
            var id = parseInt(className.match(/[0-9]+/)[0], 10);
            var title = $(this).attr('title');
            cx.tools.StatusMessage.showMessage("<div id=\"loading\">" + $('#loading').html() + "</div>");
            domainUrl = cx.variables.get('baseUrl', 'MultiSite') + cx.variables.get('cadminPath', 'contrexx') + "index.php?cmd=JsonData&object=MultiSite&act=getLicense";
            $.ajax({
                url: domainUrl,
                type: "POST",
                data: {command: 'getLicense', websiteId: id},
                dataType: "json",
                success: function(response) {
                    cx.trigger("loadingEnd", "executeSql", {});
                    if (response.status == 'error') {
                        cx.tools.StatusMessage.showMessage(response.message, null, 4000);
                    }
                    if (response.status == 'success') {
                        cx.tools.StatusMessage.showMessage(cx.variables.get('licenseInfo', "multisite/lang"), null, 3000);
                        var theader = '<table cellspacing="0" cellpadding="3" border="0" class="adminlist" width="100%">';
                        var tbody = '';
                        $.each(response.data.result, function(key, data) {
                            tbody += '<tr>';
                            tbody += '<td>' + key + '</td>';
                            tbody += '<td>' + data + '</td>';
                            tbody += '</tr>';
                        });
                        var tfooter = '</table>';
                        html = theader + tbody + tfooter;
                    }
                    cx.tools.StatusMessage.showMessage(cx.variables.get('licenseInfo', "multisite/lang"), null, 3000);
                    cx.ui.dialog({
                        width: 820,
                        height: 400,
                        title: title,
                        content: html,
                        autoOpen: true,
                        buttons: {
                            "Close": function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            });
        });
        // execute query
        $('.executeQuery').click(function() {
            $('#instance_table').append('<div id ="load-lock"></div>');
            cx.bind("loadingStart", cx.lock, "executeSql");
            cx.bind("loadingEnd", cx.unlock, "executeSql");
            cx.lock = function() {
                $("#load-lock").show();
            };
            cx.unlock = function() {
                $("#load-lock").hide();
            };
            var title = $(this).attr('title');
            var paramsArr = ($(this).attr('data-params')).split(':');
            var argName = paramsArr[0];
            var argValue = paramsArr[1];
            var initialContent = '<div><form id="ExecuteSql"><div id="statusMsg"></div><div id="resultSet"></div><textarea rows="10" cols="100" id="queryContent" name="executeQuery"></textarea></form></div>';
            domainUrl = cx.variables.get('baseUrl', 'MultiSite') + cx.variables.get('cadminPath', 'contrexx') + "index.php?cmd=JsonData&object=MultiSite&act=getLicense";
            cx.ui.dialog({
                width: 820,
                height: 400,
                title: title,
                content: initialContent,
                autoOpen: true,
                modal: true,
                buttons: {
                    "Cancel": function() {
                        $(this).dialog("close");
                    },
                    "Execute": function() {
                        $('#resultSet').html('');
                        cx.trigger("loadingStart", "executeSql", {});
                        cx.tools.StatusMessage.showMessage("<div id=\"loading\">" + $('#loading').html() + "</div>");
                        var query = $('#queryContent').val();
                        if (query == '') {
                            cx.tools.StatusMessage.showMessage(cx.variables.get('plsInsertQuery', "multisite/lang"), null, 3000);
                            cx.trigger("loadingEnd", "executeSql", {});
                            return false;
                        } else {
                            domainUrl = cx.variables.get('baseUrl', 'MultiSite') + cx.variables.get('cadminPath', 'contrexx') + "index.php?cmd=JsonData&object=MultiSite&act=executeSql";
                            $.ajax({
                                url: domainUrl,
                                type: "POST",
                                data:{
                                    query: query,
                                    mode: argName,
                                    id: argValue,
                                    command: 'executeSql'
                                    },
                                dataType: "json",
                                success: function(response) {
                                      if (response.status == 'error') {
                                        cx.trigger("loadingEnd", "executeSql", {});
                                        cx.tools.StatusMessage.showMessage(cx.variables.get('errorMsg', "multisite/lang"), null, 3000);
                                        $('#statusMsg').text(response.message);
                                    }
                                    var html = '';
                                    $.each(response.data, function(key, value) {
                                        if (value.status) {
                                            var theader = '<table cellspacing="0" cellpadding="3" border="0" class="adminlist">';
                                            var col_count = 0;
                                            var tbody = "";
                                            var thead = "";
                                            if (value.sqlResult) {
                                                var cols = Object.keys(value.sqlResult).length;
                                                $.each(value.sqlResult, function(key, data) {
                                                    tbody += "<tr class =row1>";
                                                    if (col_count == 0) {
                                                        thead += "<th>" + cx.variables.get('sqlQuery', "multisite/lang") + "</th>";
                                                        thead += "<th>" + cx.variables.get('sqlStatus', "multisite/lang") + "</th>";
                                                    }
                                                    if (col_count < cols) {
                                                        tbody += "<td>" + key + "</td>";
                                                        tbody += "<td>" + data + "</td>";
                                                    }
                                                    col_count++;
                                                    tbody += "</tr>";
                                                });
                                                html += "<strong>" + cx.variables.get('queryExecutedWebsite', "multisite/lang") + value.websiteName + "</strong><br/>" + theader + thead + tbody + "</table></br>";
                                            }

                                            if (value.selectQueryResult) {
                                                $.each(value.selectQueryResult, function(key, data) {
                                                    var count = 0;
                                                    var tsbody = "";
                                                    var tshead = "";
                                                    var no_cols = (data).length;
                                                    $.each(data, function(key, data) {
                                                        tsbody += "<tr class =row1>";
                                                        for (key in data) {
                                                            if (count == 0) {
                                                                tshead += "<th>";
                                                                tshead += key;
                                                                tshead += "</th>"
                                                            }
                                                            if (count < no_cols) {
                                                                tsbody += "<td>";
                                                                tsbody += data[key];
                                                                tsbody += "</td>"
                                                            }
                                                        }
                                                        count++;
                                                        tsbody += "</tr>";
                                                    });
                                                    html += theader + tshead + tsbody + "</table></br>";
                                                });
                                            }
                                            cx.tools.StatusMessage.showMessage(cx.variables.get('completedMsg', "multisite/lang"), null, 3000);
                                        } else {
                                            cx.tools.StatusMessage.showMessage(cx.variables.get('errorMsg', "multisite/lang"), null, 3000);
                                            $('#executeSqlQuery #statusMsg').show().text(value.error);
                                        }
                                    });
                                    cx.trigger("loadingEnd", "executeSql", {});
                                    if (html != '') {
                                        $('#resultSet').html(html);
                                    }
                                }
                            });
                        }
                    }
                }
            });
        });
    });
})(jQuery);