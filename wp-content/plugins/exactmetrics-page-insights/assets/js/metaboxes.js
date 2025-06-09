jQuery(document).ready(function () {
  if (0 === jQuery('#exactmetrics-metabox-page-insights').length) {
    return;
  }

  jQuery('#exactmetrics_show_page_insights').click(function (event) {
    event.preventDefault();

    get_page_insights_ajax(function () {
      jQuery('#exactmetrics-page-insights-content').slideDown('slow');
      jQuery('#exactmetrics_show_page_insights').fadeOut('slow');
    });
  });

  jQuery('#exactmetrics_hide_page_insights').click(function (event) {
    event.preventDefault();
    jQuery('#exactmetrics-page-insights-content').slideUp('slow', function () {
      jQuery('#exactmetrics_show_page_insights').fadeIn('slow');
    });
  });

  jQuery('.exactmetrics-page-insights__tabs-tab').click(function (event) {
    event.preventDefault();
    let tab_target = jQuery(this).data('tab');

    jQuery('.exactmetrics-page-insights__tabs-tab.active').removeClass('active');
    jQuery(this).addClass('active');

    jQuery('.exactmetrics-page-insights-tabs-content__tab.active').removeClass('active');
    jQuery('#' + tab_target).addClass('active');

    get_page_insights_ajax();
  });

  jQuery('#exactmetrics-metabox-skip-tracking input[name=_mi_skip_tracking]').change(function (event) {
    let page_insights = jQuery('#exactmetrics-metabox-page-insights');
    if (0 === page_insights.length) {
      return;
    }

    if (this.checked) {
      page_insights.fadeOut('slow');
    } else {
      page_insights.fadeIn('slow');
    }
  });

  function get_page_insights_ajax(callback) {
    var post_id = jQuery('#post_ID').val();
    if (!post_id) {
      return;
    }

    if (jQuery('#exactmetrics-metabox-page-insights[data-skip-requests]').length) {
      if (callback) {
        callback();
      }
      return;
    }

    var active_tab = jQuery('#exactmetrics-page-insights-content .exactmetrics-page-insights__tabs-tab.active');
    if (active_tab[0].hasAttribute('exactmetrics-loaded')) {
      if (callback) {
        callback();
      }
      return;
    }

    var interval = active_tab.data('interval');

    var show_btn_text = jQuery('#exactmetrics_show_page_insights').text();
    jQuery('#exactmetrics_show_page_insights').text(exactmetrics_page_insights_admin.loading_txt);

    jQuery('#' + active_tab.data('tab') + ' [data-exactmetrics-metric]').text('---');

    jQuery.ajax({
      url: ajaxurl,
      data: {
        action: 'exactmetrics_pageinsights_meta_report',
        security: exactmetrics_page_insights_admin.admin_nonce,
        isnetwork: exactmetrics_page_insights_admin.isnetwork,
        report: 'pageinsights',
        interval: interval,
        post_id: post_id,
      }
    }).done(function (response) {
      jQuery('#exactmetrics_show_page_insights').text(show_btn_text);

      if (response.success && response.data) {
        for (const [index, element] of Object.entries(response.data)) {
          jQuery('#' + active_tab.data('tab') + ' [data-exactmetrics-metric="' + index + '"]').text(element.value);
        }
        active_tab.attr('exactmetrics-loaded', '1');

        if (callback) {
          callback();
        }
      } else {
        var text = response.data.message ? response.data.message : exactmetrics_page_insights_admin.error_default;
        jQuery('#exactmetrics-metabox-page-insights').html(text);
      }
    }).error(function (XMLHttpRequest, textStatus, errorThrown) {
      console.log(XMLHttpRequest);

      var error_details_text = '' === XMLHttpRequest.responseText ? exactmetrics_page_insights_admin.error_default : XMLHttpRequest.status + ' - ' + XMLHttpRequest.responseText;
      jQuery(overlay_content).html(error_details_text);
    });
  }

});
