var APP_BASE_URL = (function() {
  var el = document.querySelector('meta[name="base-url"]');
  return ((el && el.getAttribute("content")) || "").replace(/\/+$/, "");
})();

function appUrl(path) {
  return APP_BASE_URL + (path.charAt(0) === "/" ? path : "/" + path);
}

function observeSwiperAutoplayGlobal(e) {
  if (!e || !(e.el instanceof Element)) return;
  if (window.IntersectionObserver) {
    var t = !0;
    new IntersectionObserver((function(o) {
      o.forEach((function(o) {
        o.isIntersecting ? t ? (t = !1, e.slideNext(600), e.autoplay.start()) : e.autoplay.start() : e.autoplay.stop()
      }))
    }), {
      threshold: .1,
      rootMargin: "50px 0px"
    }).observe(e.el)
  } else e.autoplay.start()
}

function doWishlistAjax(e) {
  $.ajax({
    url: e.attr("action"),
    type: "POST",
    data: e.serialize(),
    success: function(t) {
      var o = t.count || 0;
      $(".wishlist-count").text(o), $("#wishlist-count").text(o), o > 0 ? $(".wishlist-badge").css("display", "inline-block") : $(".wishlist-badge").css("display", "none"), "added" === t.action ? toastr.success(t.message || "Product added to wishlist!") : "removed" === t.action ? toastr.success(t.message || "Product removed from wishlist!") : t.error && toastr.error(t.error);
      var a = e.find("svg path").first();
      a.length && ("added" === t.action ? (a.attr("fill", "#E63946"), a.attr("stroke", "none")) : "removed" === t.action && (a.attr("fill", "none"), a.attr("stroke", "var(--primary)")));
      var n = e.find("button i.fa-heart");
      if (n.length) {
        var s = e.find("button span");
        "added" === t.action ? (n.removeClass("far").addClass("fas"), s.text("Remove Wishlist")) : (n.removeClass("fas").addClass("far"), s.text("Add Wishlist"))
      }
    },
    error: function(e) {
      401 === e.status && (window.location = appUrl("/login"))
    }
  })
}
$(document).ready((function() {
  $("[data-color-code]").each((function() {
    $(this).css("background-color", $(this).attr("data-color-code"))
  })), $("[data-outline-color-code]").each((function() {
    $(this).css("outline-color", $(this).attr("data-outline-color-code"))
  })), $("[data-background]").each((function() {
    $(this).css("background-image", "url(" + $(this).attr("data-background") + ")")
  }));
  const e = $(".overlay"),
    t = $(".mobile-menu");
  $(".header-toggle").on("click", (function() {
    t.toggleClass("active"), e.addClass("active")
  })), $(".close").on("click", (function() {
    t.removeClass("active"), e.removeClass("active")
  }));
  const o = $(".header-top");
  const headerSection = $(".header-section");
  const infoBar = headerSection.find(".info-bar");
  let lastScrollPosition = $(window).scrollTop();
  let scrollFrameScheduled = false;
  $(window).on("scroll", function() {
    if (scrollFrameScheduled) return;

    scrollFrameScheduled = true;
    window.requestAnimationFrame(function() {
      const currentScrollPosition = window.scrollY;

      if (currentScrollPosition <= 0) {
        o.addClass("header-return-to-flow");
        o.removeClass("sticky");
        headerSection.removeClass("header-scrolled info-row-visible");
        if (headerSection.length) {
          headerSection[0].style.removeProperty("--header-placeholder-height");
          headerSection[0].style.removeProperty("--info-bar-height");
        }
        window.requestAnimationFrame(function() {
          o.removeClass("header-return-to-flow");
        });
        lastScrollPosition = currentScrollPosition;
        scrollFrameScheduled = false;
        return;
      }

      if (window.innerWidth >= 768 && headerSection.length) {
        const isHeaderAlreadyScrolled = headerSection.hasClass("header-scrolled");
        if (currentScrollPosition <= 65 && !isHeaderAlreadyScrolled) {
          lastScrollPosition = currentScrollPosition;
          scrollFrameScheduled = false;
          return;
        }

        if (!headerSection.hasClass("header-scrolled")) {
          headerSection[0].style.setProperty("--header-placeholder-height", `${headerSection.outerHeight()}px`);
          headerSection[0].style.setProperty("--info-bar-height", `${infoBar.outerHeight()}px`);
          headerSection.addClass("header-scrolled");
        }

        const scrollDelta = currentScrollPosition - lastScrollPosition;
        if (Math.abs(scrollDelta) >= 8) {
          headerSection.toggleClass("info-row-visible", scrollDelta < 0);
          lastScrollPosition = currentScrollPosition;
        }
      } else {
        headerSection.removeClass("header-scrolled info-row-visible");
        lastScrollPosition = currentScrollPosition;
        currentScrollPosition > 65 ? o.addClass("sticky") : o.removeClass("sticky");
      }

      if (window.innerWidth >= 768) o.addClass("sticky");
      scrollFrameScheduled = false;
    });
  });
  const a = $("#searchIcon"),
    n = $("#searchBar");
  a.on("click", (function() {
    n.addClass("show"), e.addClass("active");
    var t = bootstrap.Tooltip.getInstance(this);
    t && (t.hide(), t.disable()), setTimeout((function() {
      n.find(".form__control").trigger("focus")
    }), 50)
  })), $(".nice-select").niceSelect(), (new WOW).init();
  const s = $("#create-password"),
    i = $(".eye-off"),
    r = $(".eye-on");
  i.on("click", (function() {
    s.attr("type", "text"), i.hide(), r.show()
  })), r.on("click", (function() {
    s.attr("type", "password"), r.hide(), i.show()
  }));
  const c = $("#confirm-password"),
    l = $(".confirm-eye-off"),
    d = $(".confirm-eye-on");
  l.on("click", (function() {
    c.attr("type", "text"), l.hide(), d.show()
  })), d.on("click", (function() {
    c.attr("type", "password"), d.hide(), l.show()
  }));
  const u = document.getElementById("countdown-date");
  if (u) var p = new Date(u.value).getTime(),
    h = setInterval((function() {
      var e = (new Date).getTime(),
        t = p - e,
        o = Math.floor(t / 864e5),
        a = Math.floor(t % 864e5 / 36e5),
        n = Math.floor(t % 36e5 / 6e4),
        s = Math.floor(t % 6e4 / 1e3);
      document.getElementById("days").textContent = o, document.getElementById("hours").textContent = a, document.getElementById("minutes").textContent = n, document.getElementById("seconds").textContent = s, t < 0 && (clearInterval(h), document.getElementById("countdown").innerHTML = "<p>Deal Expired!</p>")
    }), 1e3);
  $(".collapse-item").on("show.bs.collapse", (function() {
    $(this).prev().find(".collapse-icon").removeClass("collapsed")
  })), $(".collapse-item").on("hide.bs.collapse", (function() {
    $(this).prev().find(".collapse-icon").addClass("collapsed")
  })), $(document).on("show.bs.collapse", ".main-list > .collapse", (function() {
    var e = this;
    $(".main-list > .collapse.show").each((function() {
      if (this !== e) {
        var t = bootstrap.Collapse.getInstance(this);
        t && t.hide()
      }
    }))
  })), e.on("click", (function() {
    t.removeClass("active"), e.removeClass("active"), n.removeClass("show");
    var o = bootstrap.Tooltip.getInstance(a[0]);
    o && o.enable()
  })), $(document).on("keydown", (function(t) {
    if ("Escape" === t.key) {
      n.removeClass("show"), e.removeClass("active");
      var o = bootstrap.Tooltip.getInstance(a[0]);
      o && o.enable()
    }
  })), $(".gs-dashboard-user-sidebar-wrapper svg, .user-dropdown-wrapper svg").each((function() {
    var e = $(this);
    e.find("path").each((function() {
      var t = $(this);
      t.attr("fill") && e.addClass("has-fill"), t.attr("stroke") && e.addClass("has-stroke")
    }))
  })), $(".gs-vendor-toggle-btn").on("click", (function() {
    $(".gs-vendor-sidebar-wrapper").toggleClass("collapsed"), $(".gs-vendor-header-outlet-wrapper").toggleClass("increased-width")
  })), $(".input-file").on("change", (function() {
    var e = $(this).val().split("\\").pop();
    $(this).siblings(".fileName").text(e || "No file chosen")
  }));
  const m = $(".physical-product-inputes-wrapper"),
    f = $(".digital-product-inputes-wrapper");
  $(".physical-product-radio").on("click", (function() {
    m.addClass("show"), f.removeClass("show")
  })), $(".digital-product-radio").on("click", (function() {
    f.addClass("show"), m.removeClass("show")
  }));
  const g = $(".upload-by-file"),
    v = $(".upload-by-url");
  $(".upload-by-file-radio").on("click", (function() {
    g.addClass("show"), v.removeClass("show")
  })), $(".upload-by-url-radio").on("click", (function() {
    v.addClass("show"), g.removeClass("show")
  }));
  $(".has-sub-menu a").on("click", (function() {
    $(".collapse").not($(this).next(".collapse")).collapse("hide")
  })), $("#toggle-vendor-noti").on("click", (function() {
    $(".gs-vendor-header-noti").toggleClass("active")
  })), $(document).on("click", (function(e) {
    $(e.target).closest(".gs-vendor-header-noti, #toggle-vendor-noti").length || $(".gs-vendor-header-noti").removeClass("active")
  })), $(window).on("resize", (function() {
    $(".nicEdit-panelContain").parent().width("100%"), $(".nicEdit-panelContain").parent().next().width("99.6%")
  }))
})), document.addEventListener("DOMContentLoaded", (function() {})), (() => {
  const syncCartQtyButtons = (cartItem, quantity, stock) => {
    const incBtn = cartItem.querySelector('.change-qty[data-action="increase"]');
    if (incBtn) {
      incBtn.disabled = stock > 0 && quantity >= stock;
      incBtn.classList.toggle('disabled', incBtn.disabled);
    }

    const decBtn = cartItem.querySelector('.change-qty[data-action="decrease"]');
    if (decBtn) {
      decBtn.disabled = quantity <= 1;
      decBtn.classList.toggle('disabled', decBtn.disabled);
    }
  };

  document.querySelectorAll(".cart-item").forEach((cartItem) => {
    const quantity = parseInt(cartItem.querySelector('.qty-input')?.value, 10) || 1;
    const stock = parseInt(cartItem.getAttribute('data-stock'), 10) || 0;
    syncCartQtyButtons(cartItem, quantity, stock);
  });

  document.querySelectorAll(".change-qty").forEach((e => {
  e.addEventListener("click", (function() {
    const e = this.getAttribute("data-action"),
      t = this.closest(".cart-item");
    if (!t) return void console.error("Error: Could not find a parent element with class '.cart-item'. Please check your HTML structure.");
    const o = t.getAttribute("data-id"),
      a = t.querySelector(".qty-input");
    if (!a) return void console.error("Error: Could not find an input element with class '.qty-input' inside the cart item.");
    let n = parseInt(a.value) || 1;
    if ("increase" === e) n++;
    else {
      if (!("decrease" === e && n > 1)) return;
      n--
    }
    const s = document.querySelector('meta[name="csrf-token"]'),
      i = s ? s.getAttribute("content") : "";
    fetch(appUrl(`/cart/update/${o}`), {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": i
      },
      body: JSON.stringify({
        quantity: n
      })
    }).then((e => e.json())).then((e => {
      if (e.success) {
        a.value = e.qty ?? n;
        const stock = Number.isFinite(Number(e.stock)) ? Number(e.stock) : (parseInt(t.getAttribute('data-stock')) || 0);
        t.setAttribute('data-stock', stock);
        syncCartQtyButtons(t, e.qty ?? n, stock);
        const priceEl = document.getElementById(`prc${o}`);
        priceEl && (priceEl.textContent = e.itemSubtotal);
        const s = document.querySelector(".total-cart-price-sub");
        s && (s.textContent = e.cartTotal);
        const i = document.querySelector(".total-cart-price-val");
        i && (i.textContent = e.cartTotal), "undefined" != typeof toastr && toastr.success("Cart updated successfully!")
      } else "undefined" != typeof toastr && toastr.error("Failed to update quantity.")
    })).catch((e => {
      console.error("Error updating cart:", e), "undefined" != typeof toastr && toastr.error("An error occurred while updating the cart.")
    }))
  }))
  }));
})(), $(document).ready((function() {
  observeSwiperAutoplayGlobal(new Swiper(".featured-products.product-cards-slider", {
    slidesPerView: 4,
    spaceBetween: 24,
    grabCursor: !0,
    swipeToSlide: !0,
    freeModeSticky: !0,
    touchThreshold: 5,
    speed: 1000,
    autoplay: {
      delay: 3e3,
      disableOnInteraction: !1,
      enabled: !1
    },
    navigation: {
      prevEl: ".featured-prev",
      nextEl: ".featured-next"
    },
    breakpoints: {
      1200: {
        slidesPerView: 4
      },
      834: {
        slidesPerView: 3
      },
      565: {
        slidesPerView: 2
      },
      0: {
        slidesPerView: 1
      }
    }
  }))
})), $(document).ready((function() {
  observeSwiperAutoplayGlobal(new Swiper(".best-selling.product-cards-slider", {
    slidesPerView: 4,
    spaceBetween: 24,
    grabCursor: !0,
    swipeToSlide: !0,
    freeModeSticky: !0,
    touchThreshold: 5,
    speed: 1000,
    autoplay: {
      delay: 3e3,
      disableOnInteraction: !1,
      enabled: !1
    },
    navigation: {
      prevEl: ".best-selling-prev",
      nextEl: ".best-selling-next"
    },
    breakpoints: {
      1200: {
        slidesPerView: 4
      },
      834: {
        slidesPerView: 3
      },
      565: {
        slidesPerView: 2
      },
      0: {
        slidesPerView: 1
      }
    }
  }))
})), $(document).on("submit", ".add-cart-form", (function(e) {
  e.preventDefault();
  let t = $(this);
  $.ajax({
    url: t.attr("action"),
    type: "POST",
    data: t.serialize(),
    success: function(e) {
      var t = e.cart_count || 0;
      $("#cart-count").text(t), e.cart_total && $(".cart-total-price").text(e.cart_total), $.get($("#cart_items .dropdown-menu").data("url"), (function(e) {
        $("#cart_items .dropdown-menu").html(e)
      })), toastr.success(e.message)
    },
    error: function(e) {
      console.log(e.responseText)
    }
  })
})), $(document).on("submit", ".gs-mini-cart-remove-form", (function(e) {
  e.preventDefault();
  var t = $(this);
  $.ajax({
    url: t.attr("action"),
    type: "POST",
    data: t.serialize(),
    success: function(e) {
      var t = e.cart_count || 0;
      $("#cart-count").text(t), e.cart_total && $(".cart-total-price").text(e.cart_total), $.get($("#cart_items .dropdown-menu").data("url"), (function(e) {
        $("#cart_items .dropdown-menu").html(e)
      })), toastr.success(e.message)
    },
    error: function(e) {
      console.log(e.responseText)
    }
  })
})), window.addEventListener("pageshow", (function(e) {
  e.persisted && window.location.reload()
})), $(document).ready((function() {
  var e;
  $("#category-menu-toggle").on("click", (function(e) {
    e.stopPropagation(), $("#click-category-menu").toggleClass("d-none"), $("#category-menu-bar-icon").toggleClass("la-angle-down la-angle-up")
  })), $(document).on("click", (function(e) {
    $(e.target).closest("#category-menu-bar").length || $(e.target).closest(".aiz-category-menu").length || ($("#click-category-menu").addClass("d-none"), $("#category-menu-bar-icon").addClass("la-angle-down").removeClass("la-angle-up"), $(".sub-cat-panel").removeClass("active"))
  })), $(document).on("mouseenter", ".category-nav-element.has-children", (function() {
    var t = $(this).data("id"),
      o = $('.sub-cat-panel[data-subcat-of="' + t + '"]'),
      a = $(this).position().top;
    clearTimeout(e), $(".sub-cat-panel").removeClass("active"), o.css("top", a + "px").addClass("active")
  })), $(document).on("mouseleave", ".category-nav-element.has-children", (function() {
    var t = $('.sub-cat-panel[data-subcat-of="' + $(this).data("id") + '"]');
    e = setTimeout((function() {
      t.removeClass("active")
    }), 300)
  })), $(document).on("mouseenter", ".sub-cat-panel", (function() {
    clearTimeout(e)
  })), $(document).on("mouseleave", ".sub-cat-panel", (function() {
    var t = $(this);
    e = setTimeout((function() {
      t.removeClass("active")
    }), 300)
  })), $('.front-header-search [data-toggle="class-toggle"]').on("click", (function() {
    $(".front-header-search").toggleClass("show")
  })), $('[data-target=".aiz-top-menu-sidebar"]').on("click", (function() {
    $(".aiz-top-menu-sidebar").toggleClass("show")
  }))
})), $(document).on("submit", ".wishlist-form", (function(e) {
  e.preventDefault(), doWishlistAjax($(this))
})), $(document).on("click", ".wishlist-btn", (function(e) {
  e.preventDefault();
  var t = $(this).closest("form");
  t.length && doWishlistAjax(t)
})), $(document).on("click", ".remove-wishlist-btn", (function(e) {
  e.preventDefault();
  var button = $(this);
  var form = button.closest("form");
  if (!form.length) return;
  button.prop("disabled", true);
  $.ajax({
    url: form.attr("action"),
    type: "POST",
    data: form.serialize(),
    headers: { Accept: "application/json" },
    success: function(response) {
      if (!response.success) {
        button.prop("disabled", false);
        toastr.error(response.message || "Could not remove this product.");
        return;
      }
      var count = response.count || 0;
      $(".wishlist-count").text(count);
      $("#wishlist-count").text(count);
      $(".wishlist-badge").toggle(count > 0);
      var card = button.closest(".wishlist-page-products-items");
      card.fadeOut(150, function() {
        $(this).remove();
        if ($(".wishlist-page-products-items").length === 0) {
          $(".wishlist-page-products").html('<div class="col-12"><div class="alert alert-info">No products in wishlist.</div></div>');
          $("#wishlist-clear-all-form").remove();
        }
      });
      toastr.success(response.message || "Product removed from wishlist.");
    },
    error: function(xhr) {
      button.prop("disabled", false);
      if (xhr.status === 401) {
        window.location = appUrl("/login");
        return;
      }
      toastr.error(xhr.responseJSON?.message || "Could not remove this product.");
    }
  });
})), document.addEventListener("error", (function(e) {
  var t = e.target;
  "IMG" === t.tagName && (t.hasAttribute("data-fallback") || (t.setAttribute("data-fallback", "1"), t.src = window.PLACEHOLDER_IMG || appUrl("/assets/images/placeholder.png")))
}), !0), $(document).ready((function() {
  $("#editor").length && $("#editor").summernote({
    height: 350,
    placeholder: "Write blog content here...",
    toolbar: [
      ["style", ["style"]],
      ["font", ["bold", "italic", "underline", "clear"]],
      ["fontname", ["fontname"]],
      ["para", ["ul", "ol", "paragraph"]],
      ["insert", ["link", "picture", "table"]],
      ["view", ["fullscreen", "codeview"]]
    ]
  })
})), document.addEventListener("DOMContentLoaded", (function() {
  [...document.querySelectorAll('[data-bs-toggle="tooltip"]')].map((e => new bootstrap.Tooltip(e, { trigger: "hover" }))), document.querySelectorAll('.action-btn[title]:not([data-bs-toggle="tooltip"])').forEach((function(e) {
    new bootstrap.Tooltip(e, { trigger: "hover" })
  })), document.addEventListener("contextmenu", (function(e) {
    var t = e.target.closest('[data-bs-toggle="tooltip"], .action-btn[title]');
    if (t) {
      var o = bootstrap.Tooltip.getInstance(t);
      o && o.hide()
    }
  })), document.addEventListener("keydown", (function(e) {
    "Escape" === e.key && document.querySelectorAll(".tooltip.show").forEach((function(e) {
      e.classList.remove("show"), e.style.display = ""
    }))
  })), document.addEventListener("scroll", (function() {
    document.querySelectorAll(".tooltip.show").forEach((function(e) {
      e.classList.remove("show"), e.style.display = ""
    }))
  }), !0)
})), $(document).ready((function() {
  var e = null,
    t = $("#searchInput"),
    o = $("#searchSuggestions");

  function a(e) {
    return String(null == e ? "" : e).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#39;")
  }
  t.on("keyup", (function() {
    var t = $(this).val().trim();
    clearTimeout(e), t.length < 3 ? o.removeClass("active").html("") : e = setTimeout((function() {
      $.ajax({
        url: window.searchSuggestionsUrl || appUrl("/api/search/suggestions"),
        method: "GET",
        data: {
          query: t
        },
        beforeSend: function() {
          o.html('<div class="search-suggestion-loading">Searching...</div>').addClass("active")
        },
        success: function(e) {
          if (0 !== e.length) {
            var t = "";
            $.each(e, (function(e, o) {
              t += '<a href="' + a(o.url) + '" class="search-suggestion-item">', t += '<img src="' + a(o.image) + '" alt="' + a(o.name) + '">', t += '<div class="search-suggestion-info">', t += '<div class="search-suggestion-name">' + a(o.name) + "</div>", t += '<div class="search-suggestion-meta">' + a(o.category) + "</div>", t += "</div>", t += '<div class="search-suggestion-price">' + a(o.price) + "</div>", t += "</a>"
            })), o.html(t).addClass("active")
          } else o.html('<div class="search-suggestion-no-results">No products found</div>').addClass("active")
        },
        error: function() {
          o.removeClass("active").html("")
        }
      })
    }), 300)
  })), t.on("focus", (function() {
    o.children().length > 0 && o.addClass("active")
  })), $(document).on("click", (function(e) {
    $(e.target).closest("#searchInput, #searchSuggestions").length || o.removeClass("active")
  })), t.on("keydown", (function(e) {
    "Escape" === e.key && o.removeClass("active")
  }))
})), $(".product-cards-slider").on("init", (function() {
  $(this).find(".slick-slide > div").addClass("my-wrapper")
})), $(".home-cate-slider").on("init", (function() {
  $(this).find(".slick-slide > div").addClass("my-cate-wrapper")
})), $(document).ready((function() {
  var e = window.location.href;
  $(".megamenu a").each((function() {
    $(this).attr("href") == e && ($(this).addClass("active"), $(this).closest(".single-menu").find("h5 a").addClass("active"), $(this).closest(".has-megamenu").addClass("active").children(".menu-item-with-icon").find(".nav-link").addClass("active"))
  }))
}));

$(document).ready(function () {

    const yearSelect = $('#home-filter-year');
    const makeSelect = $('#home-filter-make');
    const modelSelect = $('#home-filter-model');

    // Initial state
    makeSelect.prop('disabled', !yearSelect.val());
    modelSelect.prop('disabled', true);

    // Year change
    yearSelect.on('change', function () {

        const year = $(this).val();

        makeSelect.prop('disabled', !year);
        modelSelect.prop('disabled', true);

        makeSelect.val('');
        modelSelect.val('');

        modelSelect.html('<option value="">All Models</option>');

        if (year) {
            // Yahan aapka existing AJAX Make loading code chalega
        }
    });

    // Make change
    makeSelect.on('change', function () {

        const make = $(this).val();

        modelSelect.prop('disabled', !make);
        modelSelect.val('');

        if (!make) {
            modelSelect.html('<option value="">All Models</option>');
        }
    });

});
