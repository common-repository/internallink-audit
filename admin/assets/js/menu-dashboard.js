jQuery(document).ready(function ($) {

    $('#update-archive').click(function () {

        //if another request is processed right now do not proceed with another ajax request
        if ($('#ajax-request-status').val() == 'processing') {
            return;
        }

        //prepare ajax request
        var data = {
            "action": "update_interlinks_archive",
            "security": ila_nonce
        };

        //show the ajax loader
        $('#ajax-loader').show();


        //set the ajax request status
        $('#ajax-request-status').val('processing');
    
        ila_swal({
          title: 'Processing Site Links...',
          text:"Please don't close this tab otherwise the process will stop and have to be continued later.",
          icon: ila_loader_url,
          closeOnClickOutside: false,
          buttons: false,
          closeOnEsc: false
        });

        //send ajax request
        $.post(ila_ajax_url, data, function (data) {

        ila_swal('Success!', 'Synchronization has been completed.', 'success').then(function() {
            window.location.replace(ila_admin_url + 'admin.php?page=ila-audit');
         });
            //reload the dashboard menu ----------------------------------------

        });

    });

    
    $('.ila_tr').click(function (e) {
        e.preventDefault();
        var post_id = $(this).data('pid');
        //
        var el = $("table[data-lid="+post_id+"]");
        if(el.parent().parent().hasClass('act')){
            el.parent().parent().addClass('dct');
            el.parent().parent().removeClass('act');
            el.find("tr:gt(0)").remove();
            return;
        }

        if(typeof post_id != "undefined"){
            $.post(ila_ajax_url, {
                "action": "list_in_out_links",
                "security": ila_nonce,
                "post_id": post_id
            }, function (d) {
             //   alert('dss');
                data = JSON.parse(d);
                $(".li_lists").addClass('dct');
                $(".li_lists").removeClass('act');
                el.parent().parent().removeClass('dct');
                el.parent().parent().addClass('act');
                el.find("tr:gt(0)").remove();
                var html = "";
                if(data!=''){
                    $.each(data, function (i, de) {
                        html += "<tr>" +
                            "<td>"+de.out_link+"</td>" +
                            "<td>"+de.out_text+"</td>" +
                            "<td>"+de.in_link+"</td>" +
                            "<td>"+de.in_text+"</td>" +
                            "</tr>";
                    })
                }else{
                    html += "<tr><td colspan=4 align='center'>No Data Available</td></tr>";
                }
                el.append(html);

            })
        }
    });

    if ($('#setting-success').length) {

      ila_swal('Success!', 'The InternalLink Audit Settings have been updated successfully!', 'success').then(function() {
                        window.location.replace(ila_admin_url + 'admin.php?page=ila-settings');
      });
  }
});