(function ($) {
  "use strict";

  

  //   wishlist
  $(document).on("click", ".wishlist", function (e) {
    e.preventDefault();
    const $this = $(this);
    if ($(this).data("href")) {
      $.get($(this).data("href"), function (data) {
        if (data[0] == 1) {
          toastr.success(data["success"]);
          var wcount = data[1] || 0;
          $('.wishlist-count').text(wcount);
          $('#wishlist-count').text(wcount);
          if (wcount > 0) {
            $('.wishlist-badge').css('display', 'inline-block');
          } else {
            $('.wishlist-badge').css('display', 'none');
          }
          $this.children().addClass("active");
        } else {
          toastr.error(data["error"]);
        }
      });
    }
  });

  $(document).on("click", ".removewishlist", function (e) {
    e.preventDefault();
    let $this = $(this);
    $.get($(this).attr("data-href"), function (data) {
      var wcount = data[1] || 0;
      $('.wishlist-count').text(wcount);
      $('#wishlist-count').text(wcount);
      if (wcount > 0) {
        $('.wishlist-badge').css('display', 'inline-block');
      } else {
        $('.wishlist-badge').css('display', 'none');
      }
      $this.parent().parent().parent().remove();
    });
  });

  //   compare
  $(document).on("click", ".compare_product", function (e) {
    e.preventDefault();

    const $this = $(this);
    const url = $this.attr('data-href') || $this.data("href") || $this.attr('href');
    if (!url || url === 'javascript:;') {
      toastr.error('Unable to add product to compare list.');
      return;
    }

    $.ajax({
      url: url,
      method: 'GET',
      dataType: 'json',
      cache: false,
      success: function (data) {

        const count = Number(data.count ?? 0);
        const $badge = $(".compare-badge");
        const $count = $(".compare-count");
        const $countAlt = $("#compare-count");

        if ($count.length) {
          $count.text(count);
        }
        if ($countAlt.length) {
          $countAlt.text(count);
        }

        if ($badge.length) {
          if (count > 0) {
            $badge.css("display", "inline-block");
          } else {
            $badge.css("display", "none");
          }
        }

        if (data.success) {
          toastr.success(data.success);
        } else if (data.error) {
          toastr.error(data.error);
        }
      },
      error: function (xhr) {
        let message = 'Unable to add product to compare list.';
        if (xhr.responseJSON && xhr.responseJSON.error) {
          message = xhr.responseJSON.error;
        } else if (xhr.responseText) {
          try {
            const json = JSON.parse(xhr.responseText);
            if (json.error) {
              message = json.error;
            }
          } catch (e) {
            // ignore invalid JSON response
          }
        }
        toastr.error(message);
      }
    });
  });

  // Product Add Qty
  $(document).on("click", ".qtplus", function () {
    var $tselector = $("#order-qty");
    var stock = $("#stock").val();
    var total = $($tselector).val();
    if (stock != "") {
      var stk = parseInt(stock);
      if (total < stk) {
        total++;
        $($tselector).val(total);
      }
    } else {
      total++;
    }

    $($tselector).val(total);
  });

  // Product Minus Qty
  $(document).on("click", ".qtminus", function () {
    var $tselector = $("#order-qty");
    var total = $($tselector).val();
    if (total > 1) {
      total--;
    }
    $($tselector).val(total);
  });

  $(".qttotal").keypress(function (e) {
    if (this.value.length == 0 && e.which == 48) {
      return false;
    }
    if (e.which != 8 && e.which != 32) {
      if (isNaN(String.fromCharCode(e.which))) {
        e.preventDefault();
      }
    }
  });

  // aDD TO FAVORITE
  $(document).on("click", ".favorite-prod", function () {
    var $this = $(this);
    $.get($(this).data("href"), function (data) {
      $this.attr("data-href", "");
      $this.attr("disabled", true);
      $this.removeClass("favorite-prod");
      $this.html(data["icon"] + " " + data["text"]);
    });
  });

  $(document).on("click", ".stars", function () {
    $(".stars").removeClass("active");
    $(this).addClass("active");
    $("#rating").val($(this).data("val"));
  });

  // add to card
  $(document).on("click", ".add_cart_click", function (e) {
    e.preventDefault();
    $.get($(this).attr("data-href"), function (data) {
      if (data == "digital") {
        toastr.error(lang.cart_already);
      } else if (data[0] == 0) {
        toastr.error(lang.cart_out);
      } else {
        $("#cart-count").html(data[0]);
        $("#cart-count1").html(data[0]);
        $("#total-cost").html(data[1]);
        $(".cart-total-price").html("$" + data[1]);
        $(".cart-popup").load(mainurl + "/carts/view");
        toastr.success(lang.cart_success);
      }
    });
    return true;
  });

  $(document).on("click", ".quantity-up", function () {
    var pid = $(this).parent().find(".prodid").val();
    var itemid = $(this).parent().find(".itemid").val();
    var size_qty = $(this).parent().find(".size_qty").val();

    var size_price = $(this)
      .parent()
      .parent()
      .parent()
      .find(".size_price")
      .val();

    var stck = $("#stock" + itemid).val();
    var qty = parseInt($("#qty" + itemid).val());
    if (stck != "") {
      var stk = parseInt(stck);
      if (qty < stk) {
        qty++;
        $("#qty" + itemid).html(qty);
      }
    } else {
      qty++;
      $("#qty" + itemid).html(qty);
    }
    $.ajax({
      type: "GET",
      url: mainurl + "/addbyone",
      data: {
        id: pid,
        itemid: itemid,
        size_qty: size_qty,
        size_price: size_price,
      },
      success: function (data) {
        $(".gocover").hide();
        if (data == 0) {
          toastr.error(lang.cart_out);
        } else {
          $.get(mainurl + "/carts", function (response) {
            $(".load_cart").html(response);
          });
        }
      },
    });
  });

  $(document).on("click", ".quantity-down", function () {
    var pid = $(this).siblings(".prodid").val();
    var itemid = $(this).siblings(".itemid").val();
    var size_qty = $(this).siblings(".size_qty").val();
    var size_price = $(this).siblings(".size_price").val();
    var qty = parseInt($("#qty" + itemid).val());
    var minimum_qty = $(this).siblings(".minimum_qty").val();

    $(".gocover").show();
    if (qty <= 1) {
      $("#qty" + itemid).val("1");
      $(".gocover").hide();
      return false;
    } else if (qty < minimum_qty) {
      return false;
    } else {
      $(".gocover").show();

      $("#qty" + itemid).val(qty);
      $.ajax({
        type: "GET",
        url: mainurl + "/reducebyone",
        data: {
          id: pid,
          itemid: itemid,
          size_qty: size_qty,
          size_price: size_price,
        },
        success: function (data) {
          if (data.qty >= 1) {
            $.get(mainurl + "/carts", function (response) {
              $(".load_cart").html(response);
            });
          } else {
            return false;
          }
        },
      });
    }
  });

  $(document).on("click", ".cart_size", function () {
    let qty = $(this).data("qty");
    $("#stock").val(qty);
    updateProductPrice();
  });
  $(document).on("click", ".cart_color", function () {
    updateProductPrice();
  });
  $(document).on("click", ".cart_attr", function () {
    updateProductPrice();
  });

  function updateProductPrice() {
    let size_price = $(".cart_size input:checked").attr("data-price");
    let color_price = $(".cart_color input:checked").attr("data-price");
    let attr_price = $(".cart_attr:checked")
      .map(function () {
        return $(this).data("price");
      })
      .get()
      .reduce((a, b) => a + b, 0);
    let main_price = $("#product_price").val();

    if (size_price == undefined) {
      size_price = 0;
    }
    if (color_price == undefined) {
      color_price = 0;
    }

    let total =
      parseFloat(size_price) +
      parseFloat(color_price) +
      parseFloat(attr_price) +
      parseFloat(main_price);

    var pos = $("#curr_pos").val();
    var sign = $("#curr_sign").val();
    if (pos == "0") {
      $("#sizeprice").html(sign + total);
    } else {
      $("#sizeprice").html(total + sign);
    }
  }

  $(document).on("click", "#addtodetailscart", function (e) {
    let pid = "";
    let qty = "";
    let size_key = "";
    let size = "";
    let size_qty = "";
    let size_price = "";
    let color = "";
    let color_price = "";
    let values = "";
    let keys = "";
    let prices = "";

    // get all the input values
    pid = $("#product_id").val();
    qty = $("#order-qty").val();
    size_key = $(".cart_size input:checked").val();
    size = $(".cart_size input:checked").attr("data-key");
    size_qty = $(".cart_size input:checked").attr("data-qty");
    size_price = $(".cart_size input:checked").attr("data-price");
    color = $(".cart_color input:checked").attr("data-color");
    color_price = $(".cart_color input:checked").attr("data-price");
    values = $(".cart_attr:checked")
      .map(function () {
        return $(this).val();
      })
      .get();
    keys = $(".cart_attr:checked")
      .map(function () {
        return $(this).attr("data-key");
      })
      .get();
    prices = $(".cart_attr:checked")
      .map(function () {
        return $(this).attr("data-price");
      })
      .get();

    //return true;

    $.ajax({
      type: "GET",
      url: mainurl + "/addnumcart",
      data: {
        id: pid,
        qty: qty,
        size: size,
        color: color,
        color_price: color_price,
        size_qty: size_qty,
        size_price: size_price,
        size_key: size_key,
        keys: keys,
        values: values,
        prices: prices,
      },
      success: function (data) {
        if (data == "digital") {
          toastr.error("Already Added To Cart");
        } else if (data == 0) {
          toastr.error("Out Of Stock");
        } else if (data[3]) {
          toastr.error(lang.minimum_qty_error + " " + data[4]);
        } else {
          $("#cart-count").html(data[0]);
          $("#cart-count1").html(data[0]);
          if (data[1]) $(".cart-total-price").html("$" + data[1]);
          $(".cart-popup").load(mainurl + "/carts/view");
          $("#cart-items").load(mainurl + "/carts/view");
          toastr.success("Successfully Added To Cart");
        }
      },
    });
  });

  $(document).on("click", "#addtobycard", function () {
    let pid = "";
    let qty = "";
    let size_key = "";
    let size = "";
    let size_qty = "";
    let size_price = "";
    let color = "";
    let color_price = "";
    let values = "";
    let keys = "";
    let prices = "";

    // get all the input values
    pid = $("#product_id").val();
    qty = $("#order-qty").val();
    size_key = $(".cart_size input:checked").val();
    size = $(".cart_size input:checked").attr("data-key");
    size_qty = $(".cart_size input:checked").attr("data-qty");
    size_price = $(".cart_size input:checked").attr("data-price");
    color = $(".cart_color input:checked").attr("data-color");

    if (size_key == undefined) {
      size_key = "";
    }
    if (size == undefined) {
      size = "";
    }
    if (size_qty == undefined) {
      size_qty = "";
    }

    if (color != undefined) {
      color = color.replace("#", "");
    } else {
      color = "";
    }

    color_price = $(".cart_color input:checked").attr("data-price");
    values = $(".cart_attr:checked")
      .map(function () {
        return $(this).val();
      })
      .get();
    keys = $(".cart_attr:checked")
      .map(function () {
        return $(this).attr("data-key");
      })
      .get();
    prices = $(".cart_attr:checked")
      .map(function () {
        return $(this).attr("data-price");
      })
      .get();

    window.location =
      mainurl +
      "/addtonumcart?id=" +
      pid +
      "&qty=" +
      qty +
      "&size=" +
      size +
      "&color=" +
      color +
      "&color_price=" +
      color_price +
      "&size_qty=" +
      size_qty +
      "&size_price=" +
      size_price +
      "&size_key=" +
      size_key +
      "&keys=" +
      keys +
      "&values=" +
      values +
      "&prices=" +
      prices;
  });
})(jQuery);
