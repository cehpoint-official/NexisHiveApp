var domainUrl = "https://socialmedia-admin.cehpointdemos.online/";
var sourceUrl = `${domainUrl}storage/`;

// add class on responsive


$(window).on("resize", function () {
    if ($(window).width() >= 1199) {
        $("table").removeClass("table-responsive");
    }

    if ($(window).width() <= 1199) {
        $("table").addClass("table-responsive");
    }
});




$(document).on("click", ".viewDescPost", function (e) {
    e.preventDefault();
    var desc = $(this).data("desc");
    $("#postDesc1").text(desc);
    $("#viewPostDescModal").modal("show");
});

$(document).on("click", ".viewPost", function (e) {
    e.preventDefault();
    function empty(element) {
      element.replaceChildren();
    }
    let parentClass = document.getElementById("post_contents");
    empty(parentClass);
    var desc = $(this).data("desc");
    var images = $(this).data("image");
    for (var i = 0; i < images.length; i++) {
        var parent = document.getElementById('post_contents');
        const fragment = document.createDocumentFragment();
        const img = fragment
        .appendChild(document.createElement("div"))
        .appendChild(document.createElement("img"));
        img.src = sourceUrl + images[i];
        parent.appendChild(fragment);
        // console.log(images[i]);
    }
    $("#post_contents").addClass("swiper-wrapper" );
    $( "#post_contents div" ).each(function() {
        $( this ).addClass( "swiper-slide" );
    });
    var swiper = new Swiper(".mySwiper", {
        spaceBetween: 30,
        pagination: {
            el: ".swiper-pagination",
            type: "fraction",
        },
        navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
        },
    });
    $("#postDesc").text(desc);
    $("#viewPostModal").modal("show");
});

$(document).on("click", ".viewVideoPost", function (e) {
    e.preventDefault();
    function empty(element) {
        element.replaceChildren(); 
    }
    let parentClass = document.getElementById("post_contents");
    empty(parentClass);
    var desc = $(this).data("desc");
    var images = $(this).data("image");
    for (var i = 0; i < images.length; i++) {
        var parent = document.getElementById('post_contents');
        const fragment = document.createDocumentFragment();
        const video = fragment
        .appendChild(document.createElement("div"))
        .appendChild(document.createElement("video"));
        video.src = sourceUrl + images[i];

        parent.appendChild(fragment);
    }
    $("#post_contents").addClass("swiper-wrapper");
    $("#post_contents div").each(function() {
        $(this).addClass("swiper-slide");
    });
    $("video").each(function() {
        $(this).attr('controls',true);;
    });
    var swiper = new Swiper(".mySwiper", {
        spaceBetween: 30,
        pagination: {
            el: ".swiper-pagination",
            type: "fraction",
        },
        navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
        },
    });

    $("#postDesc").text(desc);
    $("#viewPostModal").modal("show");
});


$(document).on("click", ".viewStory", function (e) {
  e.preventDefault();
  var story = $(this).data("image");

  $("#story_content").attr("src", sourceUrl + story);
  $("#viewStoryModal").modal("show");
});
$(document).on("click", ".viewStoryVideo", function (e) {
  e.preventDefault();
  var story = $(this).data("image");

  $("#story_content_video").attr("src", sourceUrl + story);
  $("#viewStoryVideoModal").modal("show");
});


  $(document).on("change", ".private", function (event) {
    event.preventDefault();

    swal({
      title: "Are you sure?",
      icon: "warning",
      buttons: true,
      dangerMode: true,
    }).then((willDelete) => {
      if (willDelete) {
        if (user_type == 1) {
          $id = $(this).attr("rel");
          if ($(this).prop("checked") == true) {
            $value = 1;
            console.log("Checkbox is Checked.");
            console.log("1 == true");
            iziToast.show({
              title: "Updated",
              message: "Room is Private Now",
              color: "green",
              position: toastPosition,
              transitionIn: "fadeInUp",
              transitionOut: "fadeOutDown",
              timeout: 3000,
              animateInside: true,
              iconUrl: `${domainUrl}asset/img/check-circle.svg`,
            });
          } else {
            $value = 0;
            console.log("Checkbox is unchecked.");
            console.log("0 == false");
            iziToast.show({
              title: "Removed",
              message: "Room is Public Now",
              color: "green",
              position: toastPosition,
              transitionIn: "fadeInUp",
              transitionOut: "fadeOutDown",
              timeout: 3000,
              animateInside: true,
              iconUrl: `${domainUrl}asset/img/check-circle.svg`,
            });
          }

          $.post(
            `${domainUrl}updatePrivateStatus`,
            {
              id: $id,
              is_private: $value,
            },

            function (returnedData) {
              console.log(returnedData);

              $("#roomsListTable").DataTable().ajax.reload(null, false);
              $("#userRoomsOwnTable").DataTable().ajax.reload(null, false);
            }
          ).fail(function (error) {
            console.log(error);
          });
        } else {
          iziToast.error({
            title: "Error!",
            message: " you are Tester ",
            position: toastPosition,
          });
        }
      } else {
        $("#roomsListTable").DataTable().ajax.reload(null, false);
        $("#userRoomsOwnTable").DataTable().ajax.reload(null, false);
      }
    });
  });

  $(document).on("change", ".is_join_request_enable", function (event) {
    event.preventDefault();

    swal({
      title: "Are you sure?",
      icon: "warning",
      buttons: true,
      dangerMode: true,
    }).then((willDelete) => {
      if (willDelete) {
        if (user_type == 1) {
          $id = $(this).attr("rel");

          if ($(this).prop("checked") == true) {
            $value = 1;
            console.log("Checkbox is Checked.");
            console.log("1 == true");
            iziToast.show({
              title: "Updated",
              message: "join request enable",
              color: "green",
              position: toastPosition,
              transitionIn: "fadeInUp",
              transitionOut: "fadeOutDown",
              timeout: 3000,
              animateInside: true,
              iconUrl: `${domainUrl}asset/img/check-circle.svg`,
            });
          } else {
            $value = 0;
            console.log("Checkbox is unchecked.");
            console.log("0 == false");
            iziToast.show({
              title: "Disable",
              message: "join request disable",
              color: "green",
              position: toastPosition,
              transitionIn: "fadeInUp",
              transitionOut: "fadeOutDown",
              timeout: 3000,
              animateInside: true,
              iconUrl: `${domainUrl}asset/img/check-circle.svg`,
            });
          }

          $.post(
            `${domainUrl}updateJoinRequestStatus`,
            {
              id: $id,
              is_join_request_enable: $value,
            },

            function (returnedData) {
              console.log(returnedData);

              $("#roomsListTable").DataTable().ajax.reload(null, false);
              $("#userRoomsOwnTable").DataTable().ajax.reload(null, false);
            }
          ).fail(function (error) {
            console.log(error);
          });
        } else {
          iziToast.error({
            title: "Error!",
            message: " you are Tester ",
            position: toastPosition,
          });
        }
      } else {
        $("#roomsListTable").DataTable().ajax.reload(null, false);
        $("#userRoomsOwnTable").DataTable().ajax.reload(null, false);
      }
    });
  });

  $(document).on("click", ".deleteThisRoom", function (e) {
    e.preventDefault();
    if (user_type == 1) {
      var id = $(this).attr("rel");
      swal({
        title: "Are you sure?",
        icon: "error",
        buttons: true,
        dangerMode: true,
        buttons: ["Cancel", "Yes"],
      }).then((deleteValue) => {
        if (deleteValue) {
          if (deleteValue == true) {
            $.ajax({
              type: "POST",
              url: `${domainUrl}deleteThisRoom`,
              dataType: "json",
              data: {
                room_id: id,
              },
              success: function (response) {
                if (response.status == false) {
                  console.log(response.message);
                } else if (response.status == true) {
                  iziToast.show({
                    title: "Deleted",
                    message: "Room Delete Successfully",
                    color: "green",
                    position: "bottomCenter",
                    transitionIn: "fadeInUp",
                    transitionOut: "fadeOutDown",
                    timeout: 3000,
                    animateInside: false,
                    iconUrl: `${domainUrl}asset/img/check-circle.svg`,
                  });
                  window.location.replace("../rooms");
                  console.log(response.message);
                }
              },
            });
          }
        }
      });
    } else {
      iziToast.show({
        title: "Oops",
        message: "You are tester",
        color: "red",
        position: toastPosition,
        transitionIn: "fadeInUp",
        transitionOut: "fadeOutDown",
        timeout: 3000,
        animateInside: false,
        iconUrl: `${domainUrl}asset/img/x.svg`,
      });
    }
  });


  
var app = {
  admobIsOn: "Admob is on",
  admobIsOff: "Admob is off",
};