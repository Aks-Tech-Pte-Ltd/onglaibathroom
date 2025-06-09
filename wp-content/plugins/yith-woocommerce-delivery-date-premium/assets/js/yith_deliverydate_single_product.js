jQuery(function ($) {

  var find_element = function (id_or_class_to_serch, first_element) {
      var element_to_find = false,
        max_iter = ywcdd_args.max_variable_iter,
        parent = first_element.parent();

      do {
        element_to_find = parent.find(id_or_class_to_serch);
        parent = parent.parent();
        max_iter--;
      } while (!element_to_find.length && max_iter > 0);

      return element_to_find;
    },
    toggle_general_date_content = function (show, element) {
      var variable_date_info = find_element('#ywcdd_info_single_product.variable', element);

      if (variable_date_info.length) {
        if (show) {
          variable_date_info.show();
        } else {
          variable_date_info.hide();
        }
      }
    },
    get_closest_single_product_variation = function (element) {

      return find_element('#ywcdd_info_single_product_variation', element);
    },
    get_closest_single_product_variation_wrapper = function (element) {
      return find_element('#ywcdd_info_single_product_variation_wrap', element);
    }


  $('.variations_form.cart').on('found_variation', function (e, variation_data) {

    var t = $(this),
      single_product_variation = get_closest_single_product_variation(t),
      single_product_variation_wrapper = get_closest_single_product_variation_wrapper(t);
    last_shipping_info = typeof variation_data.ywcdd_last_shipping_info !== 'undefined' ? variation_data.ywcdd_last_shipping_info : '',
      delivery_date_info = typeof variation_data.ywcdd_delivery_info !== 'undefined' ? variation_data.ywcdd_delivery_info : '',
      show = !(variation_data.is_virtual || variation_data.is_downloadable);

    if (single_product_variation_wrapper.length) {
      if (show) {
        toggle_general_date_content(false, t);
        var template = wp.template('variation-ywcdd-date-info-template');

        var template_html = template({
          'variation': variation_data
        });

        single_product_variation.html(template_html);
        if ('' === last_shipping_info) {
          single_product_variation.find('#ywcdd_info_shipping_date').hide();
        }

        if ('' === delivery_date_info) {
          single_product_variation.find('#ywcdd_info_first_delivery_date').hide();
        }
        single_product_variation_wrapper.show();
      } else {
        single_product_variation.html('');
        single_product_variation_wrapper.hide();
        toggle_general_date_content(true, t);
      }
    }
  }).on('reset_data', function (e) {
    var t = $(this),
      single_product_variation = get_closest_single_product_variation(t),
      single_product_variation_wrapper = get_closest_single_product_variation_wrapper(t);
    if (single_product_variation_wrapper.length) {
      single_product_variation.html('');
      single_product_variation_wrapper.hide();
      toggle_general_date_content(true, t);
    }
  });

});
