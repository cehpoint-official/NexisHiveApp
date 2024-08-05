$.ajaxSetup({
  headers: {
    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
  },
});

var user_type = $("#user_type").val();

var toastPosition = "bottomCenter";

// var defaultImage =

$(document).on("hidden.bs.modal", function () {
  $("form")[0].reset();
  $(this).data("bs.modal", null);
  $(".swiper-slide video").attr("src", "");
});
